<?php

class wfWAFWordPress extends wfWAF {

	/** @var wfWAFRunException */
	private $learningModeAttackException;

	/**
	 * @param wfWAFBlockException $e
	 * @param int $httpCode
	 */
	public function blockAction($e, $httpCode = 403, $redirect = false, $template = null)
	{
		$failedRules = $e->getFailedRules();
		if( !is_array($failedRules) ) {
			$failedRules = array();
		}

		if( $this->isInLearningMode() && !$e->getRequest()->getMetadata('finalAction') && !in_array('blocked', $failedRules) ) {
			register_shutdown_function(array(
				$this,
				'whitelistFailedRulesIfNot404',
			));
			$this->getStorageEngine()->logAttack($e->getFailedRules(), $e->getParamKey(), $e->getParamValue(), $e->getRequest());
			$this->setLearningModeAttackException($e);
		} else {
			if( empty($failedRules) ) {
				$finalAction = $e->getRequest()->getMetadata('finalAction');
				if( is_array($finalAction) ) {
					$isLockedOut = isset($finalAction['lockout']) && $finalAction['lockout'];
					$finalAction = $finalAction['action'];
					if( $finalAction == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY_REDIR ) {
						$redirect = wfWAFIPBlocksController::currentController()->countryRedirURL();
					} else if( $finalAction == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY_BYPASS_REDIR ) {
						$redirect = wfWAFIPBlocksController::currentController()->countryBypassRedirURL();
					} else if( $finalAction == wfWAFIPBlocksController::WFWAF_BLOCK_UAREFIPRANGE ) {
						wfWAF::getInstance()->getRequest()->setMetadata(array_merge(wfWAF::getInstance()->getRequest()->getMetadata(), array(
							'503Reason' => 'Advanced blocking in effect.',
							'503Time' => 3600
						)));
						$httpCode = 503;
					} else if( $finalAction == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY ) {
						wfWAF::getInstance()->getRequest()->setMetadata(array_merge(wfWAF::getInstance()->getRequest()->getMetadata(), array(
							'503Reason' => 'Access from your area has been temporarily limited for security reasons.',
							'503Time' => 3600
						)));
						$httpCode = 503;
					} else if( is_string($finalAction) && strlen($finalAction) > 0 ) {
						wfWAF::getInstance()->getRequest()->setMetadata(array_merge(wfWAF::getInstance()->getRequest()->getMetadata(), array(
							'503Reason' => $finalAction,
							'503Time' => 3600
						)));
						$httpCode = 503;

						if( $isLockedOut ) {
							parent::blockAction($e, $httpCode, $redirect, '503-lockout'); //exits
						}
					}
				}
			} else if( array_search('blocked', $failedRules) !== false ) {
				parent::blockAction($e, $httpCode, $redirect, '403-blacklist'); //exits
			}

			parent::blockAction($e, $httpCode, $redirect, $template);
		}
	}

	/**
	 * @param wfWAFBlockXSSException $e
	 * @param int $httpCode
	 */
	public function blockXSSAction($e, $httpCode = 403, $redirect = false)
	{
		if( $this->isInLearningMode() && !$e->getRequest()->getMetadata('finalAction') ) {
			register_shutdown_function(array(
				$this,
				'whitelistFailedRulesIfNot404',
			));
			$this->getStorageEngine()->logAttack($e->getFailedRules(), $e->getParamKey(), $e->getParamValue(), $e->getRequest());
			$this->setLearningModeAttackException($e);
		} else {
			$failedRules = $e->getFailedRules();
			if( empty($failedRules) ) {
				$finalAction = $e->getRequest()->getMetadata('finalAction');
				if( is_array($finalAction) ) {
					$finalAction = $finalAction['action'];
					if( $finalAction == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY_REDIR ) {
						$redirect = wfWAFIPBlocksController::currentController()->countryRedirURL();
					} else if( $finalAction == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY_BYPASS_REDIR ) {
						$redirect = wfWAFIPBlocksController::currentController()->countryBypassRedirURL();
					} else if( $finalAction == wfWAFIPBlocksController::WFWAF_BLOCK_UAREFIPRANGE ) {
						wfWAF::getInstance()->getRequest()->setMetadata(array_merge(wfWAF::getInstance()->getRequest()->getMetadata(), array(
							'503Reason' => 'Advanced blocking in effect.',
							'503Time' => 3600
						)));
						$httpCode = 503;
					} else if( $finalAction == wfWAFIPBlocksController::WFWAF_BLOCK_COUNTRY ) {
						wfWAF::getInstance()->getRequest()->setMetadata(array_merge(wfWAF::getInstance()->getRequest()->getMetadata(), array(
							'503Reason' => 'Access from your area has been temporarily limited for security reasons.',
							'503Time' => 3600
						)));
						$httpCode = 503;
					} else if( is_string($finalAction) && strlen($finalAction) > 0 ) {
						wfWAF::getInstance()->getRequest()->setMetadata(array_merge(wfWAF::getInstance()->getRequest()->getMetadata(), array(
							'503Reason' => $finalAction,
							'503Time' => 3600
						)));
						$httpCode = 503;
					}
				}
			}

			parent::blockXSSAction($e, $httpCode, $redirect);
		}
	}

	/**
	 *
	 */
	public function runCron()
	{
		/**
		 * Removed sending attack data. Attack data is sent in @see wordfence::veryFirstAction
		 */
		$storage = $this->getStorageEngine();
		$cron = (array)$storage->getConfig('cron', null, 'livewaf');
		$run = array();
		$updated = false;
		if( is_array($cron) ) {
			/** @var wfWAFCronEvent $event */
			$cronDeduplication = array();
			foreach($cron as $index => $event) {
				$event->setWaf($this);
				if( $event->isInPast() ) {
					$run[$index] = $event;
					$newEvent = $event->reschedule();
					$className = get_class($newEvent);
					if( $newEvent instanceof wfWAFCronEvent && $newEvent !== $event && !in_array($className, $cronDeduplication) ) {
						$cron[$index] = $newEvent;
						$cronDeduplication[] = $className;
						$updated = true;
					} else {
						unset($cron[$index]);
						$updated = true;
					}
				} else {
					$className = get_class($event);
					if( in_array($className, $cronDeduplication) ) {
						unset($cron[$index]);
						$updated = true;
					} else {
						$cronDeduplication[] = $className;
					}
				}
			}
		}
		$storage->setConfig('cron', $cron, 'livewaf');

		if( $updated && method_exists($storage, 'saveConfig') ) {
			$storage->saveConfig('livewaf');
		}

		foreach($run as $index => $event) {
			$event->fire();
		}
	}

	/**
	 *
	 */
	public function whitelistFailedRulesIfNot404()
	{
		/** @var WP_Query $wp_query */ global $wp_query;
		if( defined('ABSPATH') && isset($wp_query) && class_exists('WP_Query') && $wp_query instanceof WP_Query && method_exists($wp_query, 'is_404') && $wp_query->is_404() && function_exists('is_admin') && !is_admin() ) {
			return;
		}
		$this->whitelistFailedRules();
	}

	/**
	 * @param $ip
	 * @return mixed
	 */
	public function isIPBlocked($ip)
	{
		return parent::isIPBlocked($ip);
	}

	/**
	 * @param wfWAFRequest $request
	 * @return bool|string false if it should not be blocked, otherwise true or a reason for blocking
	 */
	public function willPerformFinalAction($request)
	{
		try {
			$disableWAFIPBlocking = $this->getStorageEngine()->getConfig('disableWAFIPBlocking', null, 'synced');
			$advancedBlockingEnabled = $this->getStorageEngine()->getConfig('advancedBlockingEnabled', null, 'synced');
		} catch( Exception $e ) {
			return false;
		}

		if( $disableWAFIPBlocking || !$advancedBlockingEnabled ) {
			return false;
		}

		return wfWAFIPBlocksController::currentController()->shouldBlockRequest($request);
	}

	public function uninstall()
	{
		parent::uninstall();
		@unlink(rtrim(WFWAF_LOG_PATH, '/') . '/.htaccess');
		@unlink(rtrim(WFWAF_LOG_PATH, '/') . '/template.php');
		@unlink(rtrim(WFWAF_LOG_PATH, '/') . '/GeoLite2-Country.mmdb');

		self::_recursivelyRemoveWflogs(''); //Removes any remaining files and the directory itself
	}

	/**
	 * Removes a path within wflogs, recursing as necessary.
	 *
	 * @param string $file
	 * @param array $processedDirs
	 * @return array The list of removed files/folders.
	 */
	private static function _recursivelyRemoveWflogs($file, $processedDirs = array())
	{
		if( preg_match('~(?:^|/|\\\\)\.\.(?:/|\\\\|$)~', $file) ) {
			return array();
		}

		if( stripos(WFWAF_LOG_PATH, 'titan_logs') === false ) { //Sanity check -- if not in a wflogs folder, user will have to do removal manually
			return array();
		}

		$path = rtrim(WFWAF_LOG_PATH, '/') . '/' . $file;
		if( is_link($path) ) {
			if( @unlink($path) ) {
				return array($file);
			}

			return array();
		}

		if( is_dir($path) ) {
			$real = realpath($file);
			if( in_array($real, $processedDirs) ) {
				return array();
			}
			$processedDirs[] = $real;

			$count = 0;
			$dir = opendir($path);
			if( $dir ) {
				$contents = array();
				while( $sub = readdir($dir) ) {
					if( $sub == '.' || $sub == '..' ) {
						continue;
					}
					$contents[] = $sub;
				}
				closedir($dir);

				$filesRemoved = array();
				foreach($contents as $f) {
					$removed = self::_recursivelyRemoveWflogs($file . '/' . $f, $processedDirs);
					$filesRemoved = array($filesRemoved, $removed);
				}
			}

			if( @rmdir($path) ) {
				$filesRemoved[] = $file;
			}

			return $filesRemoved;
		}

		if( @unlink($path) ) {
			return array($file);
		}

		return array();
	}

	public function fileList()
	{
		$fileList = parent::fileList();
		$fileList[] = rtrim(WFWAF_LOG_PATH, '/') . '/.htaccess';
		$fileList[] = rtrim(WFWAF_LOG_PATH, '/') . '/template.php';
		$fileList[] = rtrim(WFWAF_LOG_PATH, '/') . '/GeoLite2-Country.mmdb';

		return $fileList;
	}

	/**
	 * @return wfWAFRunException
	 */
	public function getLearningModeAttackException()
	{
		return $this->learningModeAttackException;
	}

	/**
	 * @param wfWAFRunException $learningModeAttackException
	 */
	public function setLearningModeAttackException($learningModeAttackException)
	{
		$this->learningModeAttackException = $learningModeAttackException;
	}

	public static function permissions()
	{
		if( defined('WFWAF_LOG_FILE_MODE') ) {
			return WFWAF_LOG_FILE_MODE;
		}

		if( class_exists('wfWAFStorageFile') && method_exists('wfWAFStorageFile', 'permissions') ) {
			return wfWAFStorageFile::permissions();
		}

		static $_cachedPermissions = null;
		if( $_cachedPermissions === null ) {
			if( defined('WFWAF_LOG_PATH') ) {
				$template = rtrim(WFWAF_LOG_PATH . '/') . '/template.php';
				if( file_exists($template) ) {
					$stat = @stat($template);
					if( $stat !== false ) {
						$mode = $stat[2];
						$updatedMode = 0600;
						if( ($mode & 0020) == 0020 ) {
							$updatedMode = $updatedMode | 0060;
						}
						$_cachedPermissions = $updatedMode;

						return $updatedMode;
					}
				}
			}

			return 0660;
		}

		return $_cachedPermissions;
	}

	public static function writeHtaccess()
	{
		@file_put_contents(rtrim(WFWAF_LOG_PATH, '/') . '/.htaccess', <<<APACHE
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>
APACHE
		);
		@chmod(rtrim(WFWAF_LOG_PATH, '/') . '/.htaccess', (wfWAFWordPress::permissions() | 0444));
	}
}
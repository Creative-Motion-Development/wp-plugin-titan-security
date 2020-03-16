<?php

/*
	php_value auto_prepend_file ~/wp-content/plugins/waf/waf/bootstrap.php
*/

if( !defined('WFWAF_RUN_COMPLETE') ) {

	if( !defined('WFWAF_AUTO_PREPEND') ) {
		define('WFWAF_AUTO_PREPEND', true);
	}

	//todo: For server WP ENGINE
	/*if( !defined('WF_IS_WP_ENGINE') ) {
		define('WF_IS_WP_ENGINE', isset($_SERVER['IS_WPE']));
	}*/

	require_once(dirname(__FILE__) . '/class-user-ip-range.php');
	require_once(dirname(__FILE__) . '/class-ip-blocks-controller.php');
	require_once(dirname(__FILE__) . '/libs/waf/init.php');
	require_once(dirname(__FILE__) . '/class-wordpress-request.php');
	require_once(dirname(__FILE__) . '/class-wordpress-observer.php');
	require_once(dirname(__FILE__) . '/class-wordpress-firewall.php');

	if( !defined('WFWAF_LOG_PATH') ) {
		if( !defined('WP_CONTENT_DIR') ) { //Loading before WordPress
			exit();
		}
		//define('WFWAF_LOG_PATH', WP_CONTENT_DIR . '/titan_logs/');
		define('WFWAF_LOG_PATH', WTITAN_PLUGIN_DIR . '/includes/firewall/titan_logs/');
	}
	if( !is_dir(WFWAF_LOG_PATH) ) {
		@mkdir(WFWAF_LOG_PATH, (wfWAFWordPress::permissions() | 0755));
		@chmod(WFWAF_LOG_PATH, (wfWAFWordPress::permissions() | 0755));
		wfWAFWordPress::writeHtaccess();
	}

	try {
		//todo: For server WP ENGINE
		/*if( !defined('WFWAF_STORAGE_ENGINE') && WF_IS_WP_ENGINE ) {
			define('WFWAF_STORAGE_ENGINE', 'mysqli');
		}
		//todo: For server WP ENGINE
		if( defined('WFWAF_STORAGE_ENGINE') ) {
			switch( WFWAF_STORAGE_ENGINE ) {
				case 'mysqli':
					// Find the wp-config.php
					if( file_exists(dirname(WFWAF_LOG_PATH) . '/../wp-config.php') ) {
						$wfWAFDBCredentials = wfWAFUtils::extractCredentialsWPConfig(WFWAF_LOG_PATH . '/../../wp-config.php');
					} else if( file_exists(dirname(WFWAF_LOG_PATH) . '/../../wp-config.php') ) {
						$wfWAFDBCredentials = wfWAFUtils::extractCredentialsWPConfig(WFWAF_LOG_PATH . '/../../../wp-config.php');
					}

					if( !empty($wfWAFDBCredentials) ) {
						$wfWAFStorageEngine = new wfWAFStorageMySQL(new wfWAFStorageEngineMySQLi(), $wfWAFDBCredentials['tablePrefix']);
						$wfWAFStorageEngine->getDb()->connect($wfWAFDBCredentials['user'], $wfWAFDBCredentials['pass'], $wfWAFDBCredentials['database'], !empty($wfWAFDBCredentials['ipv6']) ? '[' . $wfWAFDBCredentials['host'] . ']' : $wfWAFDBCredentials['host'], !empty($wfWAFDBCredentials['port']) ? $wfWAFDBCredentials['port'] : null, !empty($wfWAFDBCredentials['socket']) ? $wfWAFDBCredentials['socket'] : null);
						if( array_key_exists('charset', $wfWAFDBCredentials) ) {
							$wfWAFStorageEngine->getDb()->setCharset($wfWAFDBCredentials['charset'], !empty($wfWAFDBCredentials['collation']) ? $wfWAFDBCredentials['collation'] : '');
						}
						if( function_exists('get_option') ) {
							$wfWAFStorageEngine->installing = !get_option('wordfenceActivated');
							$wfWAFStorageEngine->getDb()->installing = $wfWAFStorageEngine->installing;
						}
					} else {
						unset($wfWAFDBCredentials);
					}

					break;
			}
		}*/

		if( empty($wfWAFStorageEngine) ) {
			$wfWAFStorageEngine = new wfWAFStorageFile(WFWAF_LOG_PATH . 'attack-data.php', WFWAF_LOG_PATH . 'ips.php', WFWAF_LOG_PATH . 'config.php', WFWAF_LOG_PATH . 'rules.php', WFWAF_LOG_PATH . 'wafRules.rules');
		}

		//$wfWAFStorageEngine->setConfig('wafStatus', 'enabled');
		//$wfWAFStorageEngine->setConfig('wafDisabled', false);
		//$wfWAFStorageEngine->setConfig('learningModeGracePeriodEnabled', 0);
		//$wfWAFStorageEngine->unsetConfig('learningModeGracePeriod');

		wfWAF::setSharedStorageEngine($wfWAFStorageEngine);
		wfWAF::setInstance(new wfWAFWordPress(wfWAFWordPressRequest::createFromGlobals(), wfWAF::getSharedStorageEngine()));
		wfWAF::getInstance()->getEventBus()->attach(new wfWAFWordPressObserver);

		if( $wfWAFStorageEngine instanceof wfWAFStorageFile ) {
			$rulesFiles = array(
				WFWAF_LOG_PATH . 'rules.php',
				// WFWAF_PATH . 'rules.php',
			);
			foreach($rulesFiles as $rulesFile) {
				if( !file_exists($rulesFile) && !wfWAF::getInstance()->isReadOnly() ) {
					@touch($rulesFile);
				}
				@chmod($rulesFile, (wfWAFWordPress::permissions() | 0444));
				if( is_writable($rulesFile) ) {
					wfWAF::getInstance()->setCompiledRulesFile($rulesFile);
					break;
				}
			}
		} else if( $wfWAFStorageEngine instanceof wfWAFStorageMySQL ) {
			$wfWAFStorageEngine->runMigrations();
			$wfWAFStorageEngine->setDefaults();
		}

		if( !wfWAF::getInstance()->isReadOnly() ) {
			if( wfWAF::getInstance()->getStorageEngine()->needsInitialRules() ) {
				try {
					if( wfWAF::getInstance()->getStorageEngine()->getConfig('apiKey', null, 'synced') !== null && wfWAF::getInstance()->getStorageEngine()->getConfig('createInitialRulesDelay', null, 'transient') < time() ) {
						$event = new wfWAFCronFetchRulesEvent(time() - 60);
						$event->setWaf(wfWAF::getInstance());
						$event->fire();
						wfWAF::getInstance()->getStorageEngine()->setConfig('createInitialRulesDelay', time() + (5 * 60), 'transient');
					}
				} catch( wfWAFBuildRulesException $e ) {
					// Log this somewhere
					error_log($e->getMessage());
				} catch( Exception $e ) {
					// Suppress this
					error_log($e->getMessage());
				}
			}
		}

		if( WFWAF_DEBUG && file_exists(wfWAF::getInstance()->getStorageEngine()->getRulesDSLCacheFile()) ) {
			try {
				wfWAF::getInstance()->updateRuleSet(file_get_contents(wfWAF::getInstance()->getStorageEngine()->getRulesDSLCacheFile()), false);
			} catch( wfWAFBuildRulesException $e ) {
				$GLOBALS['wfWAFDebugBuildException'] = $e;
			} catch( Exception $e ) {
				$GLOBALS['wfWAFDebugBuildException'] = $e;
			}
		}

		try {
			wfWAF::getInstance()->run();
		} catch( wfWAFBuildRulesException $e ) {
			// Log this
			error_log($e->getMessage());
		} catch( Exception $e ) {
			// Suppress this
			error_log($e->getMessage());
		}
	} catch( wfWAFStorageFileConfigException $e ) {
		// Let this request through for now
		error_log($e->getMessage());
	} catch( wfWAFStorageEngineMySQLiException $e ) {
		// Let this request through for now
		error_log($e->getMessage());
	} catch( wfWAFStorageFileException $e ) {
		// We need to choose another storage engine here.
	}

	define('WFWAF_RUN_COMPLETE', true);
}

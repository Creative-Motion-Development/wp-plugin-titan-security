<?php

namespace WBCR\Titan\Server;

class Helper {

	private $serverConfig;
	/**
	 * @var string
	 */
	private $currentAutoPrependedFile;

	public static function instance($serverConfig = null, $currentAutoPrependedFile = null)
	{
		return new Helper($serverConfig, $currentAutoPrependedFile);
	}

	public static function isValidServerConfig($serverConfig)
	{
		$validValues = array(
			"apache-mod_php",
			"apache-suphp",
			"cgi",
			"litespeed",
			"nginx",
			"iis",
			'manual',
		);

		return in_array($serverConfig, $validValues);
	}

	/**
	 * Verifies the .htaccess block for mod_php if present, returning true if no changes need to happen, false
	 * if something needs to update.
	 *
	 * @return bool
	 */
	public static function verifyHtaccessMod_php()
	{
		if( WFWAF_AUTO_PREPEND && PHP_MAJOR_VERSION > 5 ) {
			return true;
		}

		$serverInfo = Info::createFromEnvironment();
		if( !$serverInfo->isApacheModPHP() ) {
			return true;
		}

		$htaccessPath = get_home_path() . '.htaccess';
		if( file_exists($htaccessPath) ) {
			$htaccessContent = file_get_contents($htaccessPath);
			$regex = '/# Wordfence WAF.*?# END Wordfence WAF/is';
			if( preg_match($regex, $htaccessContent, $matches) ) {
				$wafBlock = $matches[0];
				$hasPHP5 = preg_match('/<IfModule mod_php5\.c>\s*php_value auto_prepend_file \'.*?\'\s*<\/IfModule>/is', $wafBlock);
				$hasPHP7 = preg_match('/<IfModule mod_php7\.c>\s*php_value auto_prepend_file \'.*?\'\s*<\/IfModule>/is', $wafBlock);
				if( $hasPHP5 && !$hasPHP7 ) { //The only case we care about is having the PHP 5 block but not the 7 because downgrading is unlikely
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Updates the mod_php block of the .htaccess if needed to include PHP 7. Returns whether or not this was performed successfully.
	 *
	 * @return bool
	 */
	public static function fixHtaccessMod_php()
	{
		$htaccessPath = get_home_path() . '.htaccess';
		if( file_exists($htaccessPath) ) {
			$htaccessContent = file_get_contents($htaccessPath);
			$regex = '/# Wordfence WAF.*?# END Wordfence WAF/is';
			if( preg_match($regex, $htaccessContent, $matches, PREG_OFFSET_CAPTURE) ) {
				$wafBlock = $matches[0][0];
				$hasPHP5 = preg_match('/<IfModule mod_php5\.c>\s*php_value auto_prepend_file \'(.*?)\'\s*<\/IfModule>/is', $wafBlock, $php5Matches, PREG_OFFSET_CAPTURE);
				$hasPHP7 = preg_match('/<IfModule mod_php7\.c>\s*php_value auto_prepend_file \'.*?\'\s*<\/IfModule>/is', $wafBlock);
				if( $hasPHP5 && !$hasPHP7 ) {
					$beforeWAFBlock = substr($htaccessContent, 0, $matches[0][1]);
					$afterWAFBlock = substr($htaccessContent, $matches[0][1] + strlen($wafBlock));
					$beforeMod_php = substr($wafBlock, 0, $php5Matches[0][1]);
					$afterMod_php = substr($wafBlock, $php5Matches[0][1] + strlen($php5Matches[0][0]));
					$updatedHtaccessContent = $beforeWAFBlock . $beforeMod_php . $php5Matches[0][0] . "\n" . sprintf("<IfModule mod_php7.c>\n\tphp_value auto_prepend_file '%s'\n</IfModule>", $php5Matches[1][0] /* already escaped */) . $afterMod_php . $afterWAFBlock;

					return file_put_contents($htaccessPath, $updatedHtaccessContent) !== false;
				}
			}
		}

		return false;
	}

	/**
	 * @param string|null $serverConfig
	 * @param string|null $currentAutoPrependedFile
	 */
	public function __construct($serverConfig = null, $currentAutoPrependedFile = null)
	{
		$this->serverConfig = $serverConfig;
		$this->currentAutoPrependedFile = $currentAutoPrependedFile;
	}

	public function getFilesNeededForBackup()
	{
		$backups = array();
		$htaccess = $this->getHtaccessPath();
		switch( $this->getServerConfig() ) {
			case 'apache-mod_php':
			case 'apache-suphp':
			case 'litespeed':
			case 'cgi':
				if( file_exists($htaccess) ) {
					$backups[] = $htaccess;
				}
				break;
		}
		if( $userIni = ini_get('user_ini.filename') ) {
			$userIniPath = $this->getUserIniPath();
			switch( $this->getServerConfig() ) {
				case 'cgi':
				case 'apache-suphp':
				case 'nginx':
				case 'litespeed':
				case 'iis':
					if( file_exists($userIniPath) ) {
						$backups[] = $userIniPath;
					}
					break;
			}
		}

		return $backups;
	}

	public function downloadBackups($index = 0)
	{
		$backups = $this->getFilesNeededForBackup();
		if( $backups && array_key_exists($index, $backups) ) {
			$url = site_url();
			$url = preg_replace('/^https?:\/\//i', '', $url);
			$url = preg_replace('/[^a-zA-Z0-9\.]+/', '_', $url);
			$url = preg_replace('/^_+/', '', $url);
			$url = preg_replace('/_+$/', '', $url);
			header('Content-Type: application/octet-stream');
			$backupFileName = ltrim(basename($backups[$index]), '.');
			header('Content-Disposition: attachment; filename="' . $backupFileName . '_Backup_for_' . $url . '.txt"');
			readfile($backups[$index]);
			die();
		}
	}

	/**
	 * @return mixed
	 */
	public function getServerConfig()
	{
		return $this->serverConfig;
	}

	/**
	 * @param mixed $serverConfig
	 */
	public function setServerConfig($serverConfig)
	{
		$this->serverConfig = $serverConfig;
	}

	/**
	 * @param \WP_Filesystem_Base $wp_filesystem
	 * @throws \WBCR\Titan\Server\HelperException
	 */
	public function performInstallation($wp_filesystem)
	{
		$bootstrapPath = \WBCR\Titan\Model\Firewall::getWAFBootstrapPath();
		if( !$wp_filesystem->put_contents($bootstrapPath, \WBCR\Titan\Model\Firewall::getWAFBootstrapContent($this->currentAutoPrependedFile)) ) {
			throw new \WBCR\Titan\Server\HelperException('We were unable to create the <code>titan-firewall.php</code> file
in the root of the WordPress installation. It\'s possible WordPress cannot write to the <code>titan-firewall.php</code>
file because of file permissions. Please verify the permissions are correct and retry the installation.');
		}

		$serverConfig = $this->getServerConfig();

		$htaccessPath = $this->getHtaccessPath();
		$homePath = dirname($htaccessPath);

		$userIniPath = $this->getUserIniPath();
		$userIni = ini_get('user_ini.filename');

		$userIniHtaccessDirectives = '';
		if( $userIni ) {
			$userIniHtaccessDirectives = sprintf('<Files "%s">
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>
</Files>
', addcslashes($userIni, '"'));
		}

		// .htaccess configuration
		switch( $serverConfig ) {
			case 'apache-mod_php':
				$autoPrependDirective = sprintf("# Wordfence WAF
<IfModule mod_php5.c>
	php_value auto_prepend_file '%s'
</IfModule>
<IfModule mod_php7.c>
	php_value auto_prepend_file '%s'
</IfModule>
$userIniHtaccessDirectives
# END Wordfence WAF
", addcslashes($bootstrapPath, "'"), addcslashes($bootstrapPath, "'"));
				break;

			case 'litespeed':
				$escapedBootstrapPath = addcslashes($bootstrapPath, "'");
				$autoPrependDirective = sprintf("# Wordfence WAF
<IfModule LiteSpeed>
php_value auto_prepend_file '%s'
</IfModule>
<IfModule lsapi_module>
php_value auto_prepend_file '%s'
</IfModule>
$userIniHtaccessDirectives
# END Wordfence WAF
", $escapedBootstrapPath, $escapedBootstrapPath);
				break;

			case 'apache-suphp':
				$autoPrependDirective = sprintf("# Wordfence WAF
$userIniHtaccessDirectives
# END Wordfence WAF
", addcslashes($homePath, "'"));
				break;

			case 'cgi':
				if( $userIniHtaccessDirectives ) {
					$autoPrependDirective = sprintf("# Wordfence WAF
$userIniHtaccessDirectives
# END Wordfence WAF
", addcslashes($homePath, "'"));
				}
				break;
		}

		if( !empty($autoPrependDirective) ) {
			// Modify .htaccess
			$htaccessContent = $wp_filesystem->get_contents($htaccessPath);

			if( $htaccessContent ) {
				$regex = '/# Wordfence WAF.*?# END Wordfence WAF/is';
				if( preg_match($regex, $htaccessContent, $matches) ) {
					$htaccessContent = preg_replace($regex, $autoPrependDirective, $htaccessContent);
				} else {
					$htaccessContent .= "\n\n" . $autoPrependDirective;
				}
			} else {
				$htaccessContent = $autoPrependDirective;
			}

			if( !$wp_filesystem->put_contents($htaccessPath, $htaccessContent) ) {
				throw new \WBCR\Titan\Server\HelperException('We were unable to make changes to the .htaccess file. It\'s
				possible WordPress cannot write to the .htaccess file because of file permissions, which may have been
				set by another security plugin, or you may have set them manually. Please verify the permissions allow
				the web server to write to the file, and retry the installation.');
			}
			if( $serverConfig == 'litespeed' ) {
				// sleep(2);
				$wp_filesystem->touch($htaccessPath);
			}
		}
		if( $userIni ) {
			// .user.ini configuration
			switch( $serverConfig ) {
				case 'cgi':
				case 'nginx':
				case 'apache-suphp':
				case 'litespeed':
				case 'iis':
					$autoPrependIni = sprintf("; Wordfence WAF
auto_prepend_file = '%s'
; END Wordfence WAF
", addcslashes($bootstrapPath, "'"));

					break;
			}

			if( !empty($autoPrependIni) ) {

				// Modify .user.ini
				$userIniContent = $wp_filesystem->get_contents($userIniPath);
				if( is_string($userIniContent) ) {
					$userIniContent = str_replace('auto_prepend_file', ';auto_prepend_file', $userIniContent);
					$regex = '/; Wordfence WAF.*?; END Wordfence WAF/is';
					if( preg_match($regex, $userIniContent, $matches) ) {
						$userIniContent = preg_replace($regex, $autoPrependIni, $userIniContent);
					} else {
						$userIniContent .= "\n\n" . $autoPrependIni;
					}
				} else {
					$userIniContent = $autoPrependIni;
				}

				if( !$wp_filesystem->put_contents($userIniPath, $userIniContent) ) {
					throw new \WBCR\Titan\Server\HelperException(sprintf('We were unable to make changes to the %1$s file.
					It\'s possible WordPress cannot write to the %1$s file because of file permissions.
					Please verify the permissions are correct and retry the installation.', basename($userIniPath)));
				}
			}
		}
	}

	/**
	 * @param \WP_Filesystem_Base $wp_filesystem
	 * @return bool Whether or not the .user.ini still has a commented-out auto_prepend_file setting
	 * @throws \WBCR\Titan\Server\HelperException
	 *
	 */
	public function performIniRemoval($wp_filesystem)
	{
		$serverConfig = $this->getServerConfig();

		$htaccessPath = $this->getHtaccessPath();

		$userIniPath = $this->getUserIniPath();
		$userIni = ini_get('user_ini.filename');

		// Modify .htaccess
		$htaccessContent = $wp_filesystem->get_contents($htaccessPath);

		if( is_string($htaccessContent) ) {
			$htaccessContent = preg_replace('/# Wordfence WAF.*?# END Wordfence WAF/is', '', $htaccessContent);
		} else {
			$htaccessContent = '';
		}

		if( !$wp_filesystem->put_contents($htaccessPath, $htaccessContent) ) {
			throw new \WBCR\Titan\Server\HelperException('We were unable to make changes to the .htaccess file. It\'s
			possible WordPress cannot write to the .htaccess file because of file permissions, which may have been
			set by another security plugin, or you may have set them manually. Please verify the permissions allow
			the web server to write to the file, and retry the installation.');
		}
		if( $serverConfig == 'litespeed' ) {
			// sleep(2);
			$wp_filesystem->touch($htaccessPath);
		}

		if( $userIni ) {
			// Modify .user.ini
			$userIniContent = $wp_filesystem->get_contents($userIniPath);
			if( is_string($userIniContent) ) {
				$userIniContent = preg_replace('/; Wordfence WAF.*?; END Wordfence WAF/is', '', $userIniContent);
				$userIniContent = str_replace('auto_prepend_file', ';auto_prepend_file', $userIniContent);
			} else {
				$userIniContent = '';
			}

			if( !$wp_filesystem->put_contents($userIniPath, $userIniContent) ) {
				throw new \WBCR\Titan\Server\HelperException(sprintf('We were unable to make changes to the %1$s file.
				It\'s possible WordPress cannot write to the %1$s file because of file permissions.
				Please verify the permissions are correct and retry the installation.', basename($userIniPath)));
			}

			return strpos($userIniContent, 'auto_prepend_file') !== false;
		}

		return false;
	}

	/**
	 * @param \WP_Filesystem_Base $wp_filesystem
	 * @throws \WBCR\Titan\Server\HelperException
	 */
	public function performAutoPrependFileRemoval($wp_filesystem)
	{
		$bootstrapPath = \WBCR\Titan\Model\Firewall::getWAFBootstrapPath();
		if( !$wp_filesystem->delete($bootstrapPath) ) {
			throw new \WBCR\Titan\Server\HelperException('We were unable to remove the <code>wordfence-waf.php</code> file
in the root of the WordPress installation. It\'s possible WordPress cannot remove the <code>wordfence-waf.php</code>
file because of file permissions. Please verify the permissions are correct and retry the removal.');
		}
	}

	public function getHtaccessPath()
	{
		return get_home_path() . '.htaccess';
	}

	public function getUserIniPath()
	{
		$userIni = ini_get('user_ini.filename');
		if( $userIni ) {
			return get_home_path() . $userIni;
		}

		return false;
	}

	public function usesUserIni()
	{
		$userIni = ini_get('user_ini.filename');
		if( !$userIni ) {
			return false;
		}
		switch( $this->getServerConfig() ) {
			case 'cgi':
			case 'apache-suphp':
			case 'nginx':
			case 'litespeed':
			case 'iis':
				return true;
		}

		return false;
	}

	public function uninstall()
	{
		/** @var \WP_Filesystem_Base $wp_filesystem */ global $wp_filesystem;

		$htaccessPath = $this->getHtaccessPath();
		$userIniPath = $this->getUserIniPath();

		$adminURL = admin_url('/');
		$allow_relaxed_file_ownership = true;
		$homePath = dirname($htaccessPath);

		ob_start();
		if( false === ($credentials = request_filesystem_credentials($adminURL, '', false, $homePath, array(
				'version',
				'locale'
			), $allow_relaxed_file_ownership)) ) {
			ob_end_clean();

			return false;
		}

		if( !WP_Filesystem($credentials, $homePath, $allow_relaxed_file_ownership) ) {
			// Failed to connect, Error and request again
			request_filesystem_credentials($adminURL, '', true, ABSPATH, array(
				'version',
				'locale'
			), $allow_relaxed_file_ownership);
			ob_end_clean();

			return false;
		}

		if( $wp_filesystem->errors->get_error_code() ) {
			ob_end_clean();

			return false;
		}
		ob_end_clean();

		if( $wp_filesystem->is_file($htaccessPath) ) {
			$htaccessContent = $wp_filesystem->get_contents($htaccessPath);
			$regex = '/# Wordfence WAF.*?# END Wordfence WAF/is';
			if( preg_match($regex, $htaccessContent, $matches) ) {
				$htaccessContent = preg_replace($regex, '', $htaccessContent);
				if( !$wp_filesystem->put_contents($htaccessPath, $htaccessContent) ) {
					return false;
				}
			}
		}

		if( $wp_filesystem->is_file($userIniPath) ) {
			$userIniContent = $wp_filesystem->get_contents($userIniPath);
			$regex = '/; Wordfence WAF.*?; END Wordfence WAF/is';
			if( preg_match($regex, $userIniContent, $matches) ) {
				$userIniContent = preg_replace($regex, '', $userIniContent);
				if( !$wp_filesystem->put_contents($userIniPath, $userIniContent) ) {
					return false;
				}
			}
		}

		$bootstrapPath = \WBCR\Titan\Model\Firewall::getWAFBootstrapPath();
		if( $wp_filesystem->is_file($bootstrapPath) ) {
			$wp_filesystem->delete($bootstrapPath);
		}

		return true;
	}
}

class HelperException extends \Exception {

}
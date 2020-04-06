<?php

class wfErrorLogHandler {

	public static function getErrorLogs($deepSearch = false)
	{
		static $errorLogs = null;

		if( $errorLogs === null ) {
			$searchPaths = array(ABSPATH, ABSPATH . 'wp-admin', ABSPATH . 'wp-content');

			$homePath = get_home_path();
			if( !in_array($homePath, $searchPaths) ) {
				$searchPaths[] = $homePath;
			}

			$errorLogPath = ini_get('error_log');
			if( !empty($errorLogPath) && !in_array($errorLogPath, $searchPaths) ) {
				$searchPaths[] = $errorLogPath;
			}

			$errorLogs = array();
			foreach($searchPaths as $s) {
				$errorLogs = array_merge($errorLogs, self::_scanForLogs($s, $deepSearch));
			}
		}

		return $errorLogs;
	}

	private static function _scanForLogs($path, $deepSearch = false)
	{
		static $processedFolders = array(); //Protection for endless loops caused by symlinks
		if( is_file($path) ) {
			$file = basename($path);
			if( preg_match('#(?:error_log(\-\d+)?$|\.log$)#i', $file) ) {
				return array($path => is_readable($path));
			}

			return array();
		}

		$path = untrailingslashit($path);
		$contents = @scandir($path);
		if( !is_array($contents) ) {
			return array();
		}

		$processedFolders[$path] = true;
		$errorLogs = array();
		foreach($contents as $name) {
			if( $name == '.' || $name == '..' ) {
				continue;
			}
			$testPath = $path . DIRECTORY_SEPARATOR . $name;
			if( !array_key_exists($testPath, $processedFolders) ) {
				if( (is_dir($testPath) && $deepSearch) || !is_dir($testPath) ) {
					$errorLogs = array_merge($errorLogs, self::_scanForLogs($testPath, $deepSearch));
				}
			}
		}

		return $errorLogs;
	}

	public static function outputErrorLog($path)
	{
		$errorLogs = self::getErrorLogs();
		if( !isset($errorLogs[$path]) ) { //Only allow error logs we've identified
			global $wp_query;
			$wp_query->set_404();
			status_header(404);
			nocache_headers();

			$template = get_404_template();
			if( $template && file_exists($template) ) {
				include($template);
			}
			exit;
		}

		$fh = @fopen($path, 'r');
		if( !$fh ) {
			status_header(503);
			nocache_headers();
			echo "503 Service Unavailable";
			exit;
		}

		$headersOutputted = false;
		while( !feof($fh) ) {
			$data = fread($fh, 1 * 1024 * 1024); //read 1 megs max per chunk
			if( $data === false ) { //Handle the error where the file was reported readable but we can't actually read it
				status_header(503);
				nocache_headers();
				echo "503 Service Unavailable";
				exit;
			}

			if( !$headersOutputted ) {
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="' . basename($path));
				$headersOutputted = true;
			}
			echo $data;
		}
		exit;
	}
}
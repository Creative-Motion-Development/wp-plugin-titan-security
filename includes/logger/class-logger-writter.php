<?php

namespace WBCR\Titan\Logger;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) && ! defined( 'WFWAF_AUTO_PREPEND' ) ) {
	exit;
}

/**
 * Adds ability to log application message into .log file.
 *
 * It has 4 core levels:
 * - info: generic log message
 * - warning: log possible exceptions states or unusual
 * - error: log error-related logs
 * - debug: log stack traces, big outputs, etc.
 *
 * Each level has its constant. See LEVEL_* prefix.
 *
 * Additionally it is possible to configure flush interval and file name.
 *
 * Usage examples:
 *
 * ```php
 * // Info message level
 * \WBCR\Titan\Logger\Writter::info('Some generic message, good to know');
 *
 * // Warning message level
 * \WBCR\Titan\Logger\Writter::warning('Something does not work or unusual');
 *
 * // Error message level
 * \WBCR\Titan\Logger\Writter::error('Something critical happened');
 *
 * // Debug message level
 * \WBCR\Titan\Logger\Writter::debug('Some message used for debug purposed. Could be stack trace.');
 * ```
 *
 * @author        Alexander Teshabaev <sasha.tesh@gmail.com>
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 2018, Webcraftic
 * @version       1.0
 */
if ( ! class_exists( '\WBCR\Titan\Logger\Writter' ) ) {

	class Writter {

		const LEVEL_INFO = 'info';
		const LEVEL_WARNING = 'warning';
		const LEVEL_ERROR = 'error';
		const LEVEL_DEBUG = 'debug';

		/**
		 * @var null|string Request hash.
		 */
		public static $hash = null;

		/**
		 * @var null|string Directory where log file would be saved.
		 */
		public static $dir = null;

		/**
		 * @var string File log name where logs would be flushed.
		 */
		public static $file = 'app.log';

		/**
		 * @var int Flushing interval. When $_logs would reach this number of items they would be flushed to log file.
		 */
		public static $flush_interval = 1000;

		/**
		 * @var int Rotate size in bytes. Default: 5 Mb.
		 */
		public static $rotate_size = 5000000;

		/**
		 * @var int Number of rotated files. When size of $rotate_size matches current file, current file would be rotated.
		 * For example, there are 3 files, current file became size of $rotate_size, third file would be deleted, two first
		 * shifted and empty one created.
		 */
		public static $rotate_limit = 3;

		/**
		 * @var array List of logs to be dumped.
		 */
		private static $_logs = [];

		/**
		 * WRIOP_BackupLogger constructor.
		 */
		public function __construct() {
			$this->init();
		}

		/**
		 * Initiate object.
		 */
		public function init() {
			static::$hash = substr( uniqid(), 0, 6 );

			add_action( 'shutdown', [ '\WBCR\Titan\Logger\Writter', 'flush' ], 9999, 0 );
		}

		/**
		 * Get directory to save collected logs.
		 *
		 * In addition to that, it manages log rotation so that it does not become too big.
		 *
		 * @return string|false false on failure, string on success.
		 */
		public static function get_dir() {

			$base_dir = static::get_base_dir();

			if ( $base_dir === null ) {
				return false;
			}

			$root_file = $base_dir . static::$file;

			// Check whether file exists and it exceeds rotate size, then should rotate it copy
			if ( file_exists( $root_file ) && filesize( $root_file ) >= self::$rotate_size ) {
				$name_split = explode( '.', self::$file );

				if ( ! empty( $name_split ) && isset( $name_split[0] ) ) {
					$name_split[0] = trim( $name_split[0] );

					for ( $i = self::$rotate_limit; $i >= 0; $i -- ) {

						$cur_name = $name_split[0] . $i;
						$cur_path = $base_dir . $cur_name . '.log';

						$next_path = $i !== 0 ? $base_dir . $name_split[0] . ( $i - 1 ) . '.log' : $root_file;

						if ( file_exists( $next_path ) ) {
							@copy( $next_path, $cur_path );
						}
					}
				}

				// Need to empty root file as it was supposed to be copied to next rotation :)
				@file_put_contents( $root_file, '' );
			}

			return $root_file;
		}

		/**
		 * Get base directory, location of logs.
		 *
		 * @return null|string NULL in case of failure, string on success.
		 */
		public static function get_base_dir() {
			$wp_upload_dir = wp_upload_dir();

			if ( isset( $wp_upload_dir['error'] ) && $wp_upload_dir['error'] !== false ) {
				return null;
			}

			$base_path = wp_normalize_path( trailingslashit( $wp_upload_dir['basedir'] ) . 'wtitan-logger/' );

			$folders = glob( $base_path . 'logs-*' );

			if ( ! empty( $folders ) ) {
				$exploded_path        = explode( '/', trim( $folders[0] ) );
				$selected_logs_folder = array_pop( $exploded_path );
			} else {
				if ( function_exists( 'wp_salt' ) ) {
					$hash = md5( wp_salt() );
				} else {
					$hash = md5( AUTH_KEY );
				}

				$selected_logs_folder = 'logs-' . $hash;
			}

			$path = $base_path . $selected_logs_folder . '/';

			if ( ! file_exists( $path ) ) {
				@mkdir( $path, 0755, true );
			}

			// Create .htaccess file to protect log files
			$htaccess_path = $path . '.htaccess';

			if ( ! file_exists( $htaccess_path ) ) {
				$htaccess_content = 'deny from all';
				@file_put_contents( $htaccess_path, $htaccess_content );
			}

			// Create index.htm file in case .htaccess is not support as a fallback
			$index_path = $path . 'index.html';

			if ( ! file_exists( $index_path ) ) {
				@file_put_contents( $index_path, '' );
			}

			return $path;
		}

		/**
		 * Get all available log paths.
		 *
		 * @return array|bool
		 */
		public static function get_all() {
			$base_dir = static::get_base_dir();

			if ( $base_dir === null ) {
				return false;
			}

			$glob_path = $base_dir . '*.log';

			return glob( $glob_path );
		}

		/**
		 * Get total log size in bytes.
		 *
		 * @return int
		 * @see size_format() for formatting.
		 */
		public static function get_total_size() {
			$logs  = static::get_all();
			$bytes = 0;

			if ( empty( $logs ) ) {
				return $bytes;
			}

			foreach ( $logs as $log ) {
				$bytes += @filesize( $log );
			}

			return $bytes;
		}

		/**
		 * Empty all log files and deleted rotated ones.
		 *
		 * @return bool
		 */
		public static function clean_up() {

			$base_dir = static::get_base_dir();

			if ( $base_dir === null ) {
				return false;
			}

			$glob_path = $base_dir . '*.log';

			$files = glob( $glob_path );

			if ( $files === false ) {
				return false;
			}

			if ( empty( $files ) ) {
				return true;
			}

			$unlinked_count = 0;

			foreach ( $files as $file ) {
				if ( @unlink( $file ) ) {
					$unlinked_count ++;
				}
			}

			return count( $files ) === $unlinked_count;
		}

		/**
		 * Flush all messages.
		 *
		 * @return bool
		 */
		public static function flush() {

			$messages = self::$_logs;

			self::$_logs = [];

			if ( empty( $messages ) ) {
				return false;
			}

			$file_content = PHP_EOL . implode( PHP_EOL, $messages );
			$is_put       = @file_put_contents( self::get_dir(), $file_content, FILE_APPEND );

			return $is_put !== false;
		}

		/**
		 *
		 * @param $level
		 * @param $message
		 *
		 * @return string
		 */
		public static function get_format( $level, $message ) {

			// Example: 2019-01-14 12:03:29.0593  [127.0.0.1][ee6a12][info] {message}
			$template = '%s [%s][%s][%s] %s';

			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';

			return sprintf( $template, date( 'd-m-Y H:i:s' ) . '.' . microtime( true ), $ip, static::$hash, $level, $message );
		}

		/**
		 * Get latest file content.
		 *
		 * @return bool|string
		 */
		public static function get_content() {
			if ( ! file_exists( static::get_dir() ) ) {
				return null;
			}

			return @file_get_contents( static::get_dir() );
		}

		/**
		 * Add new log message.
		 *
		 * @param string $level Log level.
		 * @param string $message Message to log.
		 *
		 * @return bool
		 */
		public static function add( $level, $message ) {

			//if ( $level === self::LEVEL_DEBUG ) {
			//$log_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;

			//if ( ! $log_debug ) {
			//return false;
			//}
			//}

			static::$_logs[] = static::get_format( $level, $message );

			if ( count( static::$_logs ) >= static::$flush_interval ) {
				static::flush();
			}

			return true;
		}

		/**
		 * Add info level log.
		 *
		 * @param string $message Message to log.
		 */
		public static function info( $message ) {
			static::add( self::LEVEL_INFO, $message );
		}

		/**
		 * Add error level log.
		 *
		 * @param string $message Message to log.
		 */
		public static function error( $message ) {
			static::add( self::LEVEL_ERROR, $message );
		}

		/**
		 * Add debug level log.
		 *
		 * @param $message
		 */
		public static function debug( $message ) {
			static::add( self::LEVEL_DEBUG, $message );
		}

		/**
		 * Add warning level log.
		 *
		 * @param string $message Message to log.
		 */
		public static function warning( $message ) {
			static::add( self::LEVEL_WARNING, $message );
		}

		/**
		 * Writes information to log about memory.
		 *
		 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
		 * @since  1.3.6
		 */
		public static function memory_usage() {
			$memory_avail = ini_get( 'memory_limit' );
			$memory_used  = number_format( memory_get_usage( true ) / ( 1024 * 1024 ), 2 );
			$memory_peak  = number_format( memory_get_peak_usage( true ) / ( 1024 * 1024 ), 2 );

			static::info( sprintf( "Memory: %s (avail) / %sM (used) / %sM (peak)", $memory_avail, $memory_used, $memory_peak ) );
		}
	}
}
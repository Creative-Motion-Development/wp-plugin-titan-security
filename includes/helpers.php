<?php
/**
 * Helpers functions
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 2017 Webraftic Ltd
 * @version 1.0
 */

namespace WBCR\Titan\Plugin;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helper {

	/**
	 * Allows you to get the base path to the plugin in the directory wp-content/plugins/
	 *
	 * @param $slug - slug for example "clearfy", "hide-login-page"
	 *
	 * @return int|null|string - "clearfy/clearfy.php"
	 */
	public static function getPluginBasePathBySlug( $slug ) {
		// Check if the function get_plugins() is registered. It is necessary for the front-end
		// usually get_plugins() only works in the admin panel.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		foreach ( $plugins as $base_path => $plugin ) {
			if ( strpos( $base_path, rtrim( trim( $slug ) ) ) !== false ) {
				return $base_path;
			}
		}

		return null;
	}

	/**
	 * Static method will check whether the plugin is activated or not. You can check whether the plugin exists
	 * by using its slug or the base path.
	 *
	 * @param string $slug - slug for example "clearfy", "hide-login-page" or base path "clearfy/clearfy.php"
	 *
	 * @return bool
	 */
	public static function isPluginActivated( $slug ) {
		if ( strpos( rtrim( trim( $slug ) ), '/' ) === false ) {
			$plugin_base_path = self::getPluginBasePathBySlug( $slug );

			if ( empty( $plugin_base_path ) ) {
				return false;
			}
		} else {
			$plugin_base_path = $slug;
		}

		require_once ABSPATH . '/wp-admin/includes/plugin.php';

		return is_plugin_active( $plugin_base_path );
	}

	/**
	 * Static method will check whether the plugin is installed or not. You can check whether the plugin exists
	 * by using its slug or the base path.
	 *
	 * @param string $slug - slug "clearfy" or base_path "clearfy/clearfy.php"
	 *
	 * @return bool
	 */
	public static function isPluginInstalled( $slug ) {
		if ( strpos( rtrim( trim( $slug ) ), '/' ) === false ) {
			$plugin_base_path = self::getPluginBasePathBySlug( $slug );

			if ( ! empty( $plugin_base_path ) ) {
				return true;
			}
		} else {

			// Check if the function get_plugins() is registered. It is necessary for the front-end
			// usually get_plugins() only works in the admin panel.
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugins = get_plugins();

			if ( isset( $plugins[ $slug ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Is permalink enabled?
	 * @return bool
	 * @since 1.0.0
	 * @global WP_Rewrite $wp_rewrite
	 */
	public static function isPermalink() {
		global $wp_rewrite;

		if ( ! isset( $wp_rewrite ) || ! is_object( $wp_rewrite ) || ! $wp_rewrite->using_permalinks() ) {
			return false;
		}

		return true;
	}

	/**
	 * Try to get variable from JSON-encoded post variable
	 *
	 * Note: we pass some params via json-encoded variables, as via pure post some data (ex empty array) will be absent
	 *
	 * @param string $name $_POST's variable name
	 *
	 * @return array
	 */
	public static function maybeGetPostJson( $name ) {
		if ( isset( $_POST[ $name ] ) and is_string( $_POST[ $name ] ) ) {
			$result = json_decode( stripslashes( $_POST[ $name ] ), true );
			if ( ! is_array( $result ) ) {
				$result = array();
			}

			return $result;
		} else {
			return array();
		}
	}

	/**
	 * Escape json data
	 *
	 * @param array $data
	 *
	 * @return string escaped json string
	 */
	public static function getEscapeJson( array $data ) {
		return htmlspecialchars( json_encode( $data ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Recursive sanitation for an array
	 *
	 * @param $array
	 *
	 * @return mixed
	 * @since 2.0.5
	 *
	 */
	public static function recursiveSanitizeArray( $array, $function ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = self::recursiveSanitizeArray( $value, $function );
			} else {
				if ( function_exists( $function ) ) {
					$value = $function( $value );
				}
			}
		}

		return $array;
	}

	/*
	 * Flushes as many page cache plugin's caches as possible.
	 *
	 * @return void
	 */
	public static function flushPageCache() {
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
				wp_cache_clear_cache( $blog_id );
			} else {
				wp_cache_clear_cache();
			}
		} else if ( has_action( 'cachify_flush_cache' ) ) {
			do_action( 'cachify_flush_cache' );
		} else if ( function_exists( 'w3tc_pgcache_flush' ) ) {
			w3tc_pgcache_flush();
		} else if ( function_exists( 'wp_fast_cache_bulk_delete_all' ) ) {
			wp_fast_cache_bulk_delete_all();
		} else if ( class_exists( 'WpFastestCache' ) ) {
			$wpfc = new WpFastestCache();
			$wpfc->deleteCache();
		} else if ( class_exists( 'c_ws_plugin__qcache_purging_routines' ) ) {
			c_ws_plugin__qcache_purging_routines::purge_cache_dir(); // quick cache
		} else if ( class_exists( 'zencache' ) ) {
			zencache::clear();
		} else if ( class_exists( 'comet_cache' ) ) {
			comet_cache::clear();
		} else if ( class_exists( 'WpeCommon' ) ) {
			// WPEngine cache purge/flush methods to call by default
			$wpe_methods = [
				'purge_varnish_cache',
			];

			// More agressive clear/flush/purge behind a filter
			if ( apply_filters( 'wbcr/factory/flush_wpengine_aggressive', false ) ) {
				$wpe_methods = array_merge( $wpe_methods, [ 'purge_memcached', 'clear_maxcdn_cache' ] );
			}

			// Filtering the entire list of WpeCommon methods to be called (for advanced usage + easier testing)
			$wpe_methods = apply_filters( 'wbcr/factory/wpengine_methods', $wpe_methods );

			foreach ( $wpe_methods as $wpe_method ) {
				if ( method_exists( 'WpeCommon', $wpe_method ) ) {
					WpeCommon::$wpe_method();
				}
			}
		} else if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache();
		} else if ( file_exists( WP_CONTENT_DIR . '/wp-cache-config.php' ) && function_exists( 'prune_super_cache' ) ) {
			// fallback for WP-Super-Cache
			global $cache_path;
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
				prune_super_cache( get_supercache_dir( $blog_id ), true );
				prune_super_cache( $cache_path . 'blogs/', true );
			} else {
				prune_super_cache( $cache_path . 'supercache/', true );
				prune_super_cache( $cache_path, true );
			}
		}
	}


}

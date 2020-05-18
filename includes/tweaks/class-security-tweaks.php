<?php

namespace WBCR\Titan\Tweaks;

/**
 * This class configures the code cleanup settings
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 2017 Webraftic Ltd
 * @version       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Security {

	public function __construct() {
		$this->plugin = \WBCR\Titan\Plugin::app();

		if ( ! is_admin() ) {
			if ( $this->plugin->getPopulateOption( 'remove_meta_generator' ) ) {
				// Clean meta generator for Woocommerce
				if ( class_exists( 'WooCommerce' ) ) {
					remove_action( 'wp_head', 'woo_version' );
				}

				// Clean meta generator for SitePress
				if ( class_exists( 'SitePress' ) ) {
					global $sitepress;
					remove_action( 'wp_head', [ $sitepress, 'meta_generator_tag' ] );
				}

				// Clean meta generator for Wordpress core
				remove_action( 'wp_head', 'wp_generator' );
				add_filter( 'the_generator', '__return_empty_string' );

				// Clean all meta generators
				add_action( 'wp_head', [ $this, 'clean_meta_generators' ], 100 );
			}

			if ( $this->plugin->getPopulateOption( 'remove_html_comments' ) ) {
				add_action( 'wp_loaded', [ $this, 'clean_html_comments' ] );
			}

			/**
			 * Priority set to 9999. Higher numbers correspond with later execution.
			 * Hook into the style loader and remove the version information.
			 */

			if ( $this->plugin->getPopulateOption( 'remove_style_version' ) ) {
				add_filter( 'style_loader_src', [ $this, 'hideWordpressVersionInScript' ], 9999, 2 );
			}

			/**
			 * Hook into the script loader and remove the version information.
			 */

			if ( $this->plugin->getPopulateOption( 'remove_js_version' ) ) {
				add_filter( 'script_loader_src', [ $this, 'hideWordpressVersionInScript' ], 9999, 2 );
			}

			if ( $this->plugin->getPopulateOption( 'change_login_errors' ) ) {
				add_filter( 'login_errors', array( $this, 'changeLoginErrors' ) );
			}

			if ( $this->plugin->getPopulateOption( 'protect_author_get' ) ) {
				add_action( 'wp', array( $this, 'protectAuthorGet' ) );
			}

			// Removes the server responses a reference to the xmlrpc file.
			if ( $this->plugin->getPopulateOption( 'remove_x_pingback' ) ) {
				add_filter( 'template_redirect', array( $this, 'removeXmlRpcPingbackHeaders' ) );
				add_filter( 'wp_headers', array( $this, 'disableXmlRpcPingback' ) );

				// Remove <link rel="pingback" href>
				add_action( 'template_redirect', array( $this, 'removeXmlRpcTagBufferStart' ), - 1 );
				add_action( 'get_header', array( $this, 'removeXmlRpcTagBufferStart' ) );
				add_action( 'wp_head', array( $this, 'removeXmlRpcTagBufferEnd' ), 999 );

				// Remove RSD link from head
				remove_action( 'wp_head', 'rsd_link' );

				// Disable xmlrcp/pingback
				add_filter( 'xmlrpc_enabled', '__return_false' );
				add_filter( 'pre_update_option_enable_xmlrpc', '__return_false' );
				add_filter( 'pre_option_enable_xmlrpc', '__return_zero' );
				add_filter( 'pings_open', '__return_false' );

				// Force to uncheck pingbck and trackback options
				add_filter( 'pre_option_default_ping_status', '__return_zero' );
				add_filter( 'pre_option_default_pingback_flag', '__return_zero' );

				add_filter( 'xmlrpc_methods', array( $this, 'removeXmlRpcMethods' ) );
				add_action( 'xmlrpc_call', array( $this, 'disableXmlRpcCall' ) );

				// Hide options on Discussion page
				add_action( 'admin_enqueue_scripts', array( $this, 'removeXmlRpcHideOptions' ) );

				$this->xmlRpcSetDisabledHeader();
			}
		}
	}

	/**
	 * Remove wp version from any enqueued scripts
	 *
	 * @param string $target_url
	 *
	 * @return string
	 */
	public function hideWordpressVersionInScript( $src, $handle ) {
		if ( is_user_logged_in() ) {
			return $src;
		}

		$filename_arr      = explode( '?', basename( $src ) );
		$exclude_file_list = $this->plugin->getPopulateOption( 'remove_version_exclude', '' );
		$exclude_files_arr = array_map( 'trim', explode( PHP_EOL, $exclude_file_list ) );

		if ( strpos( $src, 'ver=' ) && ! in_array( str_replace( '?' . $filename_arr[1], '', $src ), $exclude_files_arr, true ) ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	/**
	 * Just disable pingback.ping functionality while leaving XMLRPC intact?
	 *
	 * @param $method
	 */
	public function disableXmlRpcCall( $method ) {
		if ( $method != 'pingback.ping' ) {
			return;
		}
		wp_die( 'This site does not have pingback.', 'Pingback not Enabled!', array( 'response' => 403 ) );
	}

	public function removeXmlRpcMethods( $methods ) {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );
		unset( $methods['wp.getUsersBlogs'] ); // Block brute force discovery of existing users
		unset( $methods['system.multicall'] );
		unset( $methods['system.listMethods'] );
		unset( $methods['system.getCapabilities'] );

		return $methods;
	}

	/**
	 * Disable X-Pingback HTTP Header.
	 *
	 * @param array $headers
	 *
	 * @return mixed
	 */
	public function disableXmlRpcPingback( $headers ) {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	/**
	 * Disable X-Pingback HTTP Header.
	 *
	 * @param array $headers
	 *
	 * @return mixed
	 */
	public function removeXmlRpcPingbackHeaders() {
		if ( function_exists( 'header_remove' ) ) {
			header_remove( 'X-Pingback' );
			header_remove( 'Server' );
		}
	}

	/**
	 * Start buffer for remove <link rel="pingback" href>
	 */
	public function removeXmlRpcTagBufferStart() {
		ob_start( array( $this, "removeXmlRpcTag" ) );
	}

	/**
	 * End buffer
	 */
	public function removeXmlRpcTagBufferEnd() {
		ob_flush();
	}

	/**
	 * @param $buffer
	 *
	 * @return mixed
	 */
	function removeXmlRpcTag( $buffer ) {
		preg_match_all( '/(<link([^>]+)rel=("|\')pingback("|\')([^>]+)?\/?>)/im', $buffer, $founds );

		if ( ! isset( $founds[0] ) || count( $founds[0] ) < 1 ) {
			return $buffer;
		}

		if ( count( $founds[0] ) > 0 ) {
			foreach ( $founds[0] as $found ) {
				if ( empty( $found ) ) {
					continue;
				}

				$buffer = str_replace( $found, "", $buffer );
			}
		}

		return $buffer;
	}

	/**
	 * Hide Discussion options with CSS
	 *
	 * @return null
	 */
	public function removeXmlRpcHideOptions( $hook ) {
		if ( 'options-discussion.php' !== $hook ) {
			return;
		}

		wp_add_inline_style( 'dashboard', '.form-table td label[for="default_pingback_flag"], .form-table td label[for="default_pingback_flag"] + br, .form-table td label[for="default_ping_status"], .form-table td label[for="default_ping_status"] + br { display: none; }' );
	}

	/**
	 * Set disabled header for any XML-RPC requests
	 */
	public function xmlRpcSetDisabledHeader() {
		// Return immediately if SCRIPT_FILENAME not set
		if ( ! isset( $_SERVER['SCRIPT_FILENAME'] ) ) {
			return;
		}

		$file = basename( $_SERVER['SCRIPT_FILENAME'] );

		// Break only if xmlrpc.php file was requested.
		if ( 'xmlrpc.php' !== $file ) {
			return;
		}

		$header = 'HTTP/1.1 403 Forbidden';

		header( $header );
		echo $header;
		die();
	}

	/**
	 * Change login error message
	 *
	 * @return string
	 */

	public function changeLoginErrors( $errors ) {
		if ( ! in_array( $GLOBALS['pagenow'], array( 'wp-login.php' ) ) ) {
			return $errors;
		}

		return __( '<strong>ERROR</strong>: Wrong login or password', 'titan-security' );
	}

	/**
	 * Protect author get
	 */

	public function protectAuthorGet() {
		if ( isset( $_GET['author'] ) ) {
			wp_redirect( home_url(), 301 );

			die();
		}
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.5.3
	 */
	public function clean_meta_generators() {
		ob_start( [ $this, 'replace_meta_generators' ] );
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.0.0
	 */
	public function clean_html_comments() {
		ob_start( [ $this, 'replace_html_comments' ] );
	}

	/**
	 * Replace <meta .* name="generator"> like tags
	 * which may contain versioning of
	 *
	 * @param $html
	 *
	 * @return string|string[]|null
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.5.3
	 *
	 */
	public function replace_meta_generators( $html ) {
		$raw_html = $html;

		$pattern = '/<meta[^>]+name=["\']generator["\'][^>]+>/i';
		$html    = preg_replace( $pattern, '', $html );

		// If replacement is completed with an error, user will receive a white screen.
		// We have to prevent it.
		if ( empty( $html ) ) {
			return $raw_html;
		}

		return $html;
	}

	/**
	 * !ngg_resource - can not be deleted, otherwise the plugin nextgen gallery will not work
	 *
	 * @param string $data
	 *
	 * @return mixed
	 */
	public function replace_html_comments( $html ) {
		$raw_html = $html;

		//CLRF-166 issue fix bug with noindex (\s?\/?noindex)
		$html = preg_replace( '#<!--(?!<!|\s?ngg_resource|\s?\/?noindex)[^\[>].*?-->#s', '', $html );

		// If replacement is completed with an error, user will receive a white screen.
		// We have to prevent it.
		if ( empty( $html ) ) {
			return $raw_html;
		}

		return $html;
	}

}

new Security();
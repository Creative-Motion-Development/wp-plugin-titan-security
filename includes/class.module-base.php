<?php

namespace WBCR\Titan;

use WBCR\Titan\Plugin;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for Titan module.
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @version       1.0
 */
abstract class Module_Base {

	/**
	 * Plugin object
	 *
	 * @since  7.0
	 * @var \Wbcr_Factory000_Plugin
	 */
	public $plugin;

	/**
	 * Current license key
	 *
	 * @since  7.0
	 * @var string
	 */
	protected $license_key = "";

	protected $module_dir;
	protected $module_url;

	/**
	 * Titan module constructor.
	 *
	 */
	public function __construct() {
		$this->plugin = Plugin::app();
		if ( Plugin::app()->premium->is_activate() ) {
			$this->license_key = Plugin::app()->premium->get_license()->get_key();
		}
	}

	/**
	 * Method renders layout template
	 *
	 * @param string $template_name Template name without ".php"
	 * @param array|string|int|float|bool|object $args Template arguments
	 *
	 * @return false|string
	 */
	protected function render_template( $template_name, $args = array() ) {
		$path = $this->module_dir . "/views/$template_name.php";
		if ( file_exists( $path ) ) {
			ob_start();
			extract( $args );
			include $path;
			unset( $path );

			return ob_get_clean();
		} else {
			return __( 'This template does not exist!', 'titan-security' );
		}
	}

	/**
	 * Method renders Java Script
	 *
	 * @param string $script_name Template name with ".js" "/module/assets/js/$script_name"
	 *
	 * @param array[] $args Arguments are converted to JS variables similar to the wp_localize_script function
	 *
	 * @return false|string
	 */
	protected function render_script( $script_name, $args = array() ) {
		$path = $this->module_dir . "/assets/js/$script_name";
		$url  = $this->module_url . "/assets/js/$script_name";
		if ( file_exists( $path ) ) {
			ob_start();
			echo "<script>";
			if ( is_array( $args ) ) {
				foreach ( $args as $key => $value ) {
					echo "var ".esc_html($key)." = " . json_encode( $value ) . ";\n";
				}
			}
			echo "</script>";
			echo "<script type='application/javascript' src='".esc_url($url)."'></script>";
			unset( $path );

			return ob_get_clean();
		} else {
			return __( 'This script file does not exist!', 'titan-security' );
		}
	}

	abstract public function showPageContent();
}

<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The file contains a short help info.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 2019 Webraftic Ltd
 * @version       1.0
 */
class Scanner extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'scanner';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-code-standards';

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_position = 0;

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $add_link_to_plugin_actions = true;

	/**
	 * Module URL
	 *
	 * @since  1.0
	 * @var string
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL."/includes/scanner";

	/**
	 * Module path
	 *
	 * @since  1.0
	 * @var string
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR."/includes/scanner";


	/**
	 * Scanner constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->menu_title                  = __( 'Scanner', 'titan-security' );
		$this->page_menu_short_description = __( 'Find malware and viruses', 'titan-security' );

		require_once $this->MODULE_PATH."/boot.php";

		parent::__construct( $plugin );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->styles->add(  $this->MODULE_URL . '/assets/css/scanner-dashboard.css' );
		$this->scripts->add( $this->MODULE_URL . '/assets/js/scanner.js', [ 'jquery' ]);
		$this->scripts->localize( 'update_nonce', wp_create_nonce("updates"));
	}

	/**
	 * Method renders layout template
	 *
	 * @param string $template_name Template name without ".php"
	 * @param array|string|int|float|bool|object $args Template arguments
	 *
	 * @return false|string
	 */
	private function render_template( $template_name, $args = array()) {
		$path = $this->MODULE_PATH."/views/$template_name.php";
		if( file_exists($path) ) {
			ob_start();
			include $path;
			unset($path);
			return ob_get_clean();
		} else {
			return __('This template does not exist!', 'titan-security');
		}
	}

	/**
	 * Method renders Java Script
	 *
	 * @param string $module Module name
	 * @param string $script_name Template name with ".js" "/module/assets/js/$script_name"
	 *
	 * @param array|string|int|float|bool|object $args  arguments
	 *
	 * @return false|string
	 */
	private function render_script( $module, $script_name, $args = array()) {
		$path = WTITAN_PLUGIN_DIR."/includes/".$module."/assets/js/$script_name";
		if( file_exists($path) ) {
			ob_start();
			echo "<script>";
			echo file_get_contents( $path);
			echo "</script>";
			unset($path);
			return ob_get_clean();
		} else {
			return __('This script file does not exist!', 'titan-security');
		}
	}

	/**
	 * Show page content
	 */
	public function showPageContent() {
		$modules = explode(',', $this->getOption( 'security_check_list', array()));
		$args = array(
			'modules' => array(
				'vulnerability' => __('Vulnerabilities', 'titan-security'),
				'audit' =>  __('Security audit', 'titan-security'),
			),
			'active_modules' => $modules,
		);
		echo $this->render_script('vulnerabilities','vulnerability_ajax.js', $args);
		echo $this->render_template( 'scanner', $args);
	}

}

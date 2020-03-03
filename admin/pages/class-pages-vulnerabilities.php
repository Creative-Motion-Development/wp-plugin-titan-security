<?php

namespace WBCR\Titan\Page;

use WBCR\Titan\WordpressVulnerabilities;
use WBCR\Titan\PluginsVulnerabilities;
use WBCR\Titan\ThemesVulnerabilities;

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
class Vulnerabilities extends \Wbcr_FactoryClearfy000_PageBase {

    /**
	 * {@inheritdoc}
	 */
	public $id = 'vulnerabilities';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-buddicons-replies';

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
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL."/includes/vulnerabilities";

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR."/includes/vulnerabilities";

	/**
	 * Logs constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->menu_title                  = __( 'Vulnerabilities', 'titan-security' );
		$this->page_menu_short_description = __( 'Vulnerabilities in your Wordpress, plugins, and themes', 'titan-security' );

		require_once $this->MODULE_PATH."/boot.php";

		add_action( 'wp_ajax_wtitan_get_vulners', array( $this, 'showVulnerabilities' ) );

		parent::__construct( $plugin );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 1.1.4
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->styles->add(  $this->MODULE_URL . '/assets/css/vulnerabilities-dashboard.css' );
		$this->scripts->add( $this->MODULE_URL . '/assets/js/vulnerabilities.js', [ 'jquery' ]);
		$this->scripts->localize( 'update_nonce', wp_create_nonce("updates"));
	}


	/**
	 * {@inheritdoc}
	 */
	public function showPageContent() {
			?>
			<div class="wbcr-content-section">
				<!-- ############################### -->
				<div class="wbcr-factory-page-group-header" style="margin:0">
					<strong>Wordpress Vulnerabilities</strong>
					<p>description</p>
				</div>
				<div class="wtitan-vulner-table-container wtitan-wp">
				</div>
				<!-- ############################### -->
				<div class="wbcr-factory-page-group-header" style="margin:0">
					<strong>Plugins Vulnerabilities</strong>
					<p>description</p>
				</div>
				<div class="wtitan-vulner-table-container wtitan-plugin">
				</div>
				<!-- ############################### -->
				<div class="wbcr-factory-page-group-header" style="margin:0">
					<strong>Themes Vulnerabilities</strong>
					<p>description</p>
				</div>
				<div class="wtitan-vulner-table-container wtitan-theme">
				</div>
				<!-- ############################### -->
			</div>
			<?php
	}

	/**
	 * {@inheritdoc}
	 */
	public function showVulnerabilities() {
		if(isset($_POST['target'])) {
			$target  = $_POST['target'];
			switch ($target)
			{
				case 'plugin':
					$vulners  = new PluginsVulnerabilities();
					break;
				case 'theme':
					$vulners  = new ThemesVulnerabilities();
					break;
				case 'wp':
					$vulners  = new WordpressVulnerabilities();
					break;
                default:
	                $vulners  = new PluginsVulnerabilities();
	                $vulners  = new ThemesVulnerabilities();
	                $vulners  = new WordpressVulnerabilities();
                    break;
			}
			echo $vulners->render_html_table();
			die();
		}
	}

}

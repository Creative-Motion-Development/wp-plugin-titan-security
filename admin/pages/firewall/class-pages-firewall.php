<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
use WBCR\Titan\Views;

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
class Firewall extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'firewall';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-tagcloud';

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * @var object|\WBCR\Titan\Views
	 */
	public $view;

	/**
	 * @var object|\WBCR\Titan\Model\Firewall
	 */
	public $firewall;

	/**
	 * Logs constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		$this->menu_title                  = __( 'Firewall', 'titan-security' );
		$this->page_menu_short_description = __( 'Stops Complex Attacks', 'titan-security' );

		$this->view = \WBCR\Titan\Plugin::app()->view();

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

		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/firewall/firewall-dashboard.css' );
		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/libs/circular-progress.js', [ 'jquery' ] );

		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/libs/sweetalert2.css' );
		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/sweetalert-custom.css' );

		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/libs/sweetalert3.min.js' );
		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/libs/popover.min.js' );
		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/firewall/firewall-dashboard.js' );
	}


	/**
	 * {@inheritdoc}
	 */
	public function showPageContent() {

		$data = array();

		$data['firewall_mode']           = 'disabled';
		$data['firewall_status_percent'] = 0.0;
		$data['firewall_status_color']   = "#5d05b7";

		$this->view->print_template( 'firewall/firewall-dashboard-page', $data );
	}
}

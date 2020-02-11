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
	 * Logs constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->menu_title                  = __( 'Scanner', 'anti-spam' );
		$this->page_menu_short_description = __( 'Find malware and viruses', 'anti-spam' );

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

		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/firewall-dashboard.css' );
		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/circular-progress.js', [ 'jquery' ] );
	}


	/**
	 * {@inheritdoc}
	 */
	public function showPageContent() {
		?>
        Html code
		<?php
	}

}

<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Base class for Titan pages
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Base extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * Scanner page constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		parent::__construct($plugin);
		$this->menuIcon = WTITAN_PLUGIN_URL . '/admin/assets/img/titan-icon.png';
	}

	/**
	 * Add assets
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets($scripts, $styles)
	{
		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/titan-security.css');

		parent::assets($scripts, $styles);
	}

	public function getPluginTitle() {
		return "<span class='wt-plugin-header-logo'>&nbsp;</span>".__( 'Titan Anti-spam & Security', 'titan-security' );
	}


}

<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
use WBCR\Titan\Views;

if( !defined('ABSPATH') ) {
	exit;
}

/**
 * The file contains a short help info.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 2019 Webraftic Ltd
 * @version       1.0
 */
class Brute_Force extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'bruteforce';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-tagcloud';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * @var object|\WBCR\Titan\Views
	 */
	public $view;


	/**
	 * Logs constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->plugin = $plugin;

		$this->menu_title = __('Bruteforce', 'titan-security');
		$this->page_menu_short_description = __('Stop login attacks', 'titan-security');

		$this->view = \WBCR\Titan\Plugin::app()->view();

		parent::__construct($plugin);
	}


	/**
	 * Enqueue page assets
	 *
	 * @return void
	 * @since 6.2
	 * @see   Wbcr_FactoryPages000_AdminPage
	 *
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		if( $this->plugin->is_premium() ) {
			$this->scripts->request([
				'control.checkbox',
				'control.dropdown',
				'bootstrap.tooltip',
				'holder.more-link'
			], 'bootstrap');

			$this->styles->request([
				'bootstrap.core',
				'bootstrap.form-group',
				'holder.more-link',
				'bootstrap.separator',
				'control.dropdown',
				'control.checkbox'
			], 'bootstrap');

			add_action('wbcr/factory/update_option', [$this, 'before_save']);
		}
	}

	/**
	 * Permalinks options.
	 *
	 * @return mixed[]
	 * @since 6.2
	 */
	public function getPageOptions()
	{


		$options[] = array(
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . __('<strong>Lockout</strong>.', 'titan-security') . '<p>' . __('Basic recommended security settings.', 'titan-security') . '</p></div>'
		);

		$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'bruteforce_enabled',
			'title' => __('Bruteforce enabled', 'titan-security'),
			'layout' => array('hint-type' => 'icon', 'hint-icon-color' => 'grey'),
			'hint' => __('Click to enable or disable protection brute force attacks.', 'titan-security'),
			'default' => false
		);

		$options[] = array(
			'type' => 'textbox',
			'name' => 'bruteforce_allowed_retries',
			'title' => __('Allowed retries', 'titan-security'),
			'default' => 4
		);
		$options[] = array(
			'type' => 'textbox',
			'name' => 'bruteforce_minutes_lockout',
			'title' => __('Minutes lockout', 'titan-security'),
			'default' => 20
		);
		$options[] = array(
			'type' => 'textbox',
			'name' => 'bruteforce_allowed_retries',
			'title' => __('Hours until retries are reset', 'titan-security'),
			'default' => 12
		);
		/*$options[] = array(
			'type' => 'textbox',
			'name' => 'bruteforce_allowed_retries',
			'title' => __('Allowed retries', 'titan-security'),
			'default' => 4
		);*/

		$options[] = array(
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . __('<strong>Whitelist</strong>.', 'titan-security') . '<p>' . __('Basic recommended security settings.', 'titan-security') . '</p></div>'
		);

		$options[] = array(
			'type' => 'textarea',
			'name' => 'bruteforce_whitelist_ips',
			'title' => __('Whitelist ips', 'titan-security'),
			'hint' => __('One IP or IP range (1.2.3.4-5.6.7.8) per line', 'titan-security'),
			'default' => ''
		);
		$options[] = array(
			'type' => 'textarea',
			'name' => 'bruteforce_whitelist_usernames',
			'title' => __('Whitelist usernames', 'titan-security'),
			'hint' => __('One Username per line', 'titan-security'),
			'default' => ''
		);

		$options[] = array(
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . __('<strong>Blacklist</strong>.', 'titan-security') . '<p>' . __('Basic recommended security settings.', 'titan-security') . '</p></div>'
		);

		$options[] = array(
			'type' => 'textarea',
			'name' => 'bruteforce_blacklist_ips',
			'title' => __('Blacklist ips', 'titan-security'),
			'hint' => __('One IP or IP range (1.2.3.4-5.6.7.8) per line', 'titan-security'),
			'default' => ''
		);
		$options[] = array(
			'type' => 'textarea',
			'name' => 'bruteforce_blacklist_usernames',
			'title' => __('Blacklist usernames', 'titan-security'),
			'hint' => __('One Username per line', 'titan-security'),
			'default' => ''
		);

		$form_options = [];

		$form_options[] = [
			'type' => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters('wtitan/tweaks_form/options', $form_options, $this);
	}

}

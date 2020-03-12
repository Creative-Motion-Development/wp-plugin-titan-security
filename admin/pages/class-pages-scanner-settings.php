<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Страница настроек для сканера.
 *
 * Не поддерживает режим работы с мультисаймами.
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2020 CreativeMotion
 * @version       1.0
 */
class Scanner_Settings extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var string
	 */
	public $id = "scanner-settings";

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public $page_parent_page = "scanner";

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var string
	 */
	public $page_menu_dashicon = 'dashicons-testimonial';

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * WBCR\Page\Settings constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->menu_title                  = __( 'Settings', 'titan-security' );
		$this->page_menu_short_description = __( 'Scanner settings', 'titan-security' );

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}

	/**
	 * Enqueue page assets
	 *
	 * @return void
	 * @since 6.2
	 * @see   Wbcr_FactoryPages000_AdminPage
	 *
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->scripts->request( [
			'control.checkbox',
			'control.dropdown',
			'bootstrap.tooltip',
			'holder.more-link'
		], 'bootstrap' );

		$this->styles->request( [
			'bootstrap.core',
			'bootstrap.form-group',
			'holder.more-link',
			'bootstrap.separator',
			'control.dropdown',
			'control.checkbox'
		], 'bootstrap' );
	}

	/**
	 * Permalinks options.
	 *
	 * @return mixed[]
	 * @since 6.2
	 */
	public function getPageOptions() {

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Basic Scanner Options.', 'titan-security' ) . '</strong>' . '<p>' . __( 'Additional modules to scanning.', 'titan-security' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'    => 'list',
			'way'     => 'checklist',
			'name'    => 'security_check_list',
			'title'   => __( 'Select the scan type', 'titan-security' ),
			'data'    => [
				[ 'vulnerability', 'Scan for vulnerabilities in Wordpress, plugins and themes' ],
				[ 'audit', 'Security audit' ],
				[ 'malware', 'Malware scan' ],
			],
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'Select what the scanner should check', 'titan-security' ),
			'default' => 'vulnerability'
		];

		$form_options = [];

		$form_options[] = [
			'type'  => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters( 'wantispam/settings_form/options', $form_options, $this );
	}
}

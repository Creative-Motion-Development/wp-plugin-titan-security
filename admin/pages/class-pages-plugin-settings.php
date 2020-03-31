<?php
namespace WBCR\Titan\Page;

/**
 * The plugin Settings.
 *
 * @since 7.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PluginSettings extends Base {

	/**
	 * The id of the page in the admin menu.
	 *
	 * Mainly used to navigate between pages.
	 *
	 * @since 7.0.0
	 *
	 * @var string
	 */
	public $id = "plugin_settings";

	/**
	 * @var string
	 */
	public $page_parent_page = 'none';

	/**
	 * @var string
	 */
	public $page_menu_dashicon = 'dashicons-list-view';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * @param \Wbcr_Factory000_Plugin $plugin
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->menu_title                  = __( 'Titan Settings', 'titan-security' );
		$this->page_menu_short_description = __( 'Useful tweaks', 'titan-security' );

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}


	/**
	 * Requests assets (js and css) for the page.
	 *
	 * @since 7.0.0
	 * @return void
	 *
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );
	}

	/**
	 * Permalinks options.
	 *
	 * @since 7.0.0
	 * @return mixed[]
	 */
	public function getPageOptions() {

		$options = [];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-clearfy-group-header">' . '<strong>' . __( 'Advanced settings', 'titan-security' ) . '</strong>' .
			          '<p>' . __( 'This group of settings allows you to configure the work of the plugin.', 'titan-security' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'titan_extra_menu',
			'title'   => __( 'Plugin menu in adminbar', 'titan-security' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'red' ],
			'hint'    => __( 'This setting allows you to enable/disable the additional menu of the plugin, in the admin bar.', 'titan-security' ),
			'default' => true
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'complete_uninstall',
			'title'   => __( 'Complete Uninstall', 'titan-security' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'    => __( "When the plugin is deleted from the Plugins menu, also delete all plugin settings.", 'titan-security' ),
			'default' => false
		];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-clearfy-group-header">' . '<strong>' . __( 'Antivirus settings', 'titan-security' ) . '</strong>' .
			          '<p>' . __( 'This group of settings allows you to configure the work of the plugin.', 'titan-security' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'   => 'dropdown',
			'way'    => 'default',
			'name'   => 'scanner_speed',
			'title'  => __( 'Scanning speed', 'titan-security' ),
			'layout' => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'   => __( "The speed of scanning affects the resources consumed", 'titan-security' ),
			'data'   => [
				[
					'value' => \WBCR\Titan\MalwareScanner\Scanner::SPEED_SLOW,
					'title' => __( 'Slow', 'titan-security' )
				],
				[
					'value' => \WBCR\Titan\MalwareScanner\Scanner::SPEED_MEDIUM,
					'title' => __( 'Medium', 'titan-security' )
				],
				[
					'value' => \WBCR\Titan\MalwareScanner\Scanner::SPEED_FAST,
					'title' => __( 'Fast', 'titan-security' )
				],
				[
					'value' => \WBCR\Titan\MalwareScanner\Scanner::SPEED_FASTEST,
					'title' => __( 'Fastest', 'titan-security' )
				],
			],
		];

		$formOptions = [];

		$formOptions[] = [
			'type'  => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters( 'wbcr/clearfy/settings_form_options', $formOptions, $this );
	}
}

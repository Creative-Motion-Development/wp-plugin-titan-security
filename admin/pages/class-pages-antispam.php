<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Страница общих настроек для этого плагина.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Antispam extends Base {

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var string
	 */
	public $id = "antispam";

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
	 *
	 */
	public $page_menu_position = 100;

	/**
	 * @var string
	 */
	public $menu_target = 'options-general.php';

	/**
	 * @var bool
	 */
	public $internal = false;

	/**
	 * Module URL
	 *
	 * @since  7.0
	 * @var string
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL . "/includes/antispam";

	/**
	 * Module path
	 *
	 * @since  7.0
	 * @var string
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR . "/includes/antispam";

	/**
	 * Module object
	 *
	 * @since  7.0
	 * @var object
	 */
	public $module;


	/**
	 * Antispam page constructor.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->menu_title                  = __( 'Anti-spam', 'titan-security' );
		$this->page_menu_short_description = __( 'Detected and stoped spam', 'titan-security' );

		$this->plugin = $plugin;
		parent::__construct( $plugin );
	}

	/**
	 * Enqueue page assets
	 *
	 * @since 6.2
	 * @return void
	 * @see   Wbcr_FactoryPages000_AdminPage
	 *
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->styles->add( $this->MODULE_URL . '/assets/css/settings.css' );
		$this->scripts->add( $this->MODULE_URL . '/assets/js/settings.js', [
			'jquery',
			'wbcr-factory-clearfy-000-global'
		], 'wantispam-settings' );
	}

	/**
	 * Permalinks options.
	 *
	 * @since 6.2
	 * @return mixed[]
	 */
	public function getPageOptions() {
		$is_premium = wantispam_is_license_activate();
		//$upgrade_premium_url = $this->plugin->get_support()->get_pricing_url();

		$blocked_total  = 0; // show 0 by default
		$antispam_stats = get_option( 'antispam_stats', [] );

		if ( isset( $antispam_stats['blocked_total'] ) ) {
			$blocked_total = $antispam_stats['blocked_total'];
		}

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Base options.', 'titan-security' ) . '</strong>' . '<p>' . sprintf( __( '%s spam comments were blocked by Anti-spam plugin so far.', 'titan-security' ), $blocked_total ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'save_spam_comments',
			'title'   => __( 'Save spam comments', 'titan-security' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'Save spam comments into spam section. Useful for testing how the plugin works.', 'titan-security' ),
			'default' => true
		];

		if ( $is_premium ) {
			$options[] = [
				'type'    => 'checkbox',
				'way'     => 'buttons',
				'name'    => 'comment_form_privacy_notice',
				'title'   => __( 'Display a privacy notice under your comment forms.', 'titan-security' ),
				'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
				'hint'    => __( 'To help your site with transparency under privacy laws like the GDPR, Antispam can display a notice to your users under your comment forms. This feature is disabled by default, however, you can turn it on above.', 'titan-security' ),
				'default' => false
			];
		}

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Modules.', 'titan-security' ) . '</strong>' . '<p>' . __( 'Additional modules to spam protect.', 'titan-security' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'     => 'checkbox',
			'way'      => 'buttons',
			'name'     => 'protect_register_form',
			'title'    => __( 'Protect Register Form', 'titan-security' ),
			'layout'   => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'     => __( 'Registration form can be protected in a matter of minutes with a few new fields and limits imposed.', 'titan-security' ),
			'default'  => false,
			'cssClass' => ! $is_premium ? [ 'factory-checkbox--disabled wantispam-checkbox-premium-label' ] : [],
		];
		$options[] = [
			'type'     => 'checkbox',
			'way'      => 'buttons',
			'name'     => 'protect_comments_form',
			'title'    => __( 'Advanced protection of comment forms', 'titan-security' ),
			'layout'   => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'     => sprintf( __( 'In order to protect your comment forms, you need to make it difficult or impossible for an automated tool to fill in or submit the form while keeping it as easy as possible for your customers to fill out the form.', 'titan-security' ), $this->plugin::app()->getPluginTitle() ),
			'default'  => false,
			'cssClass' => ! $is_premium ? [ 'factory-checkbox--disabled wantispam-checkbox-premium-label' ] : [],
		];
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			$options[] = [
				'type'     => 'checkbox',
				'way'      => 'buttons',
				'name'     => 'protect_contacts_form7',
				'title'    => __( 'Protect Contact Forms 7', 'titan-security' ),
				'layout'   => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
				'hint'     => __( 'Job Spam-Free for WordPress Contact Forms.', 'titan-security' ),
				'default'  => false,
				'cssClass' => ! $is_premium ? [ 'factory-checkbox--disabled wantispam-checkbox-premium-label' ] : [],
			];
		}
		if ( is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
			$options[] = [
				'type'     => 'checkbox',
				'way'      => 'buttons',
				'name'     => 'protect_ninja_forms',
				'title'    => __( 'Protect Ninja Forms', 'titan-security' ),
				'layout'   => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
				'hint'     => __( 'Protects contact forms of the Ninja Forms plugin from spam.', 'titan-security' ),
				'default'  => false,
				'cssClass' => ! $is_premium ? [ 'factory-checkbox--disabled wantispam-checkbox-premium-label' ] : [],
			];
		}
		if ( is_plugin_active( 'caldera-forms/caldera-core.php' ) ) {
			$options[] = [
				'type'      => 'checkbox',
				'way'       => 'buttons',
				'name'      => 'protect_caldera_forms',
				'title'     => __( 'Protect Caldera Forms', 'titan-security' ),
				'layout'    => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
				'hint'      => __( 'Caldera Forms has powerful anti-spam by default. The Anti-spam plugin provides additional anti-spam protection for your Caldera Forms.', 'titan-security' ),
				'default'   => false,
				'cssClass'  => ! $is_premium ? [ 'factory-checkbox--disabled wantispam-checkbox-premium-label' ] : [],
				'eventsOn'  => [
					'show' => '#wantispam-protect-caldera-forms-message'
				],
				'eventsOff' => [
					'hide' => '#wantispam-protect-caldera-forms-message'
				]
			];

			$options[] = [
				'type' => 'html',
				'html' => [ $this, 'protect_caldera_forms_warning' ]
			];
		}

		$form_options = [];

		$form_options[] = [
			'type'  => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters( 'wantispam/settings_form/options', $form_options, $this );
	}

	/**
	 * Adds an html warning notification html markup.
	 */
	public function protect_caldera_forms_warning() {
		?>
        <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="control-group col-sm-8">
                <div id="wantispam-protect-caldera-forms-message" class="wantispam-checkbox-warning-message">
					<?php printf( __( '<b>You have to make additional settings in the Caldera Forms plugin!</b><br> Please create an Anti-spam processor for each of your forms that you want to protect. You can read this <a href="%s" target="_blank" rel="noopener">manual</a> to learn more about how to create an Anti-spam processor in the Caldera Forms plugin.', 'clearfy' ), 'https://anti-spam.space/docs/anti-spam-processor-for-caldera-forms/' ) ?>
                </div>
            </div>
        </div>
		<?php
	}
}

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
class Brute_Force extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'bruteforce';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public $page_parent_page = "firewall";

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
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->menu_title                  = __( 'Limit login Attempts', 'titan-security' );
		$this->page_menu_short_description = __( 'Stop login attacks', 'titan-security' );

		$this->view = \WBCR\Titan\Plugin::app()->view();

		parent::__construct( $plugin );
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

		if ( $this->plugin->is_premium() ) {
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

			add_action( 'wbcr/factory/update_option', [ $this, 'before_save' ] );
		}
	}

	/**
	 * Permalinks options.
	 *
	 * @return mixed[]
	 * @since 6.2
	 */
	public function getPageOptions() {


		$options[] = array(
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . __( '<strong>Lockout</strong>.', 'titan-security' ) . '<p>' . __( 'Basic recommended security settings.', 'titan-security' ) . '</p></div>'
		);

		$options[] = array(
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'bruteforce_enabled',
			'title'   => __( 'Bruteforce enabled', 'titan-security' ),
			'layout'  => array( 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ),
			'hint'    => __( 'Click to enable or disable protection brute force attacks.', 'titan-security' ),
			'default' => false
		);

		$options[] = array(
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'bruteforce_gdpr',
			'title'   => __( 'GDPR compliance', 'titan-security' ),
			'layout'  => array( 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ),
			'hint'    => __( 'This makes the plugin GDPR compliant', 'titan-security' ),
			'default' => false
		);

		$options[] = array(
			'type'         => 'textbox',
			'name'         => 'bruteforce_allowed_retries',
			'title'        => __( 'Allowed retries', 'titan-security' ),
			'default'      => 4,
			'filter_value' => [ $this, 'filter_allowed_retries_option' ]
		);
		$options[] = array(
			'type'         => 'textbox',
			'name'         => 'bruteforce_minutes_lockout_raw',
			'title'        => __( 'Minutes lockout', 'titan-security' ),
			'default'      => 20,
			'filter_value' => [ $this, 'filter_minutes_lockout_option' ]
		);
		$options[] = array(
			'type'         => 'textbox',
			'name'         => 'bruteforce_valid_duration_raw',
			'title'        => __( 'Hours until retries are reset', 'titan-security' ),
			'default'      => 12,
			'filter_value' => [ $this, 'filter_valid_duration_option' ]
		);
		/*$options[] = array(
			'type' => 'textbox',
			'name' => 'bruteforce_allowed_retries',
			'title' => __('Allowed retries', 'titan-security'),
			'default' => 4
		);*/

		$options[] = array(
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . __( '<strong>Whitelist</strong>.', 'titan-security' ) . '<p>' . __( 'Basic recommended security settings.', 'titan-security' ) . '</p></div>'
		);

		$options[] = array(
			'type'         => 'textarea',
			'name'         => 'bruteforce_whitelist_ips_raw',
			'title'        => __( 'Whitelist ips', 'titan-security' ),
			'hint'         => __( 'One IP or IP range (1.2.3.4-5.6.7.8) per line', 'titan-security' ),
			'default'      => '',
			'filter_value' => [ $this, 'filter_whitelist_ips_option' ]
		);
		$options[] = array(
			'type'         => 'textarea',
			'name'         => 'bruteforce_whitelist_usernames_raw',
			'title'        => __( 'Whitelist usernames', 'titan-security' ),
			'hint'         => __( 'One Username per line', 'titan-security' ),
			'default'      => '',
			'filter_value' => [ $this, 'filter_whitelist_usernames_option' ]
		);

		$options[] = array(
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . __( '<strong>Blacklist</strong>.', 'titan-security' ) . '<p>' . __( 'Basic recommended security settings.', 'titan-security' ) . '</p></div>'
		);

		$options[] = array(
			'type'         => 'textarea',
			'name'         => 'bruteforce_blacklist_ips_raw',
			'title'        => __( 'Blacklist ips', 'titan-security' ),
			'hint'         => __( 'One IP or IP range (1.2.3.4-5.6.7.8) per line', 'titan-security' ),
			'default'      => '',
			'filter_value' => [ $this, 'filter_blacklist_ips_option' ]
		);
		$options[] = array(
			'type'         => 'textarea',
			'name'         => 'bruteforce_blacklist_usernames_raw',
			'title'        => __( 'Blacklist usernames', 'titan-security' ),
			'hint'         => __( 'One Username per line', 'titan-security' ),
			'default'      => '',
			'filter_value' => [ $this, 'filter_blacklist_usernames_option' ]
		);

		$form_options = [];

		$form_options[] = [
			'type'  => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters( 'wtitan/tweaks_form/options', $form_options, $this );
	}

	public function filter_allowed_retries_option( $value ) {
		return (int) $value;
	}

	public function filter_minutes_lockout_option( $value ) {
		$this->plugin->updateOption( 'bruteforce_minutes_lockout', (int) $value * 60 );

		return (int) $value;
	}

	public function filter_valid_duration_option( $value ) {
		$this->plugin->updateOption( 'bruteforce_valid_duration', (int) $value * 3600 );

		return (int) $value;
	}

	public function filter_whitelist_ips_option( $value ) {
		$white_list_ips = ( ! empty( $value ) ) ? explode( "\n", str_replace( "\r", "", stripslashes( $value ) ) ) : array();

		if ( ! empty( $white_list_ips ) ) {
			foreach ( $white_list_ips as $key => $ip ) {
				if ( '' == $ip ) {
					unset( $white_list_ips[ $key ] );
				}
			}
		}
		$this->plugin->updateOption( 'bruteforce_whitelist', $white_list_ips );

		return $value;
	}


	public function filter_whitelist_usernames_option( $value ) {
		$white_list_usernames = ( ! empty( $value ) ) ? explode( "\n", str_replace( "\r", "", stripslashes( $value ) ) ) : array();

		if ( ! empty( $white_list_usernames ) ) {
			foreach ( $white_list_usernames as $key => $ip ) {
				if ( '' == $ip ) {
					unset( $white_list_usernames[ $key ] );
				}
			}
		}
		$this->plugin->updateOption( 'bruteforce_whitelist_usernames', $white_list_usernames );

		return $value;
	}

	public function filter_blacklist_ips_option( $value ) {
		$black_list_ips = ( ! empty( $value ) ) ? explode( "\n", str_replace( "\r", "", stripslashes( $value ) ) ) : array();

		if ( ! empty( $black_list_ips ) ) {
			foreach ( $black_list_ips as $key => $ip ) {
				/*$range = array_map('trim', explode('-', $ip));
				if( count($range) > 1 && (float)sprintf("%u", ip2long($range[0])) > (float)sprintf("%u", ip2long($range[1])) ) {
					$this->show_error(__('The "' . $ip . '" IP range is invalid', 'titan-security'));
				}*/
				if ( '' == $ip ) {
					unset( $black_list_ips[ $key ] );
				}
			}
		}

		$this->plugin->updateOption( 'bruteforce_blacklist_ips', $black_list_ips );

		return $value;
	}

	public function filter_blacklist_usernames_option( $value ) {
		$black_list_usernames = ( ! empty( $value ) ) ? explode( "\n", str_replace( "\r", "", stripslashes( $value ) ) ) : array();

		if ( ! empty( $black_list_usernames ) ) {
			foreach ( $black_list_usernames as $key => $ip ) {
				if ( '' == $ip ) {
					unset( $black_list_usernames[ $key ] );
				}
			}
		}
		$this->plugin->updateOption( 'bruteforce_blacklist_usernames', $black_list_usernames );

		return $value;
	}
}

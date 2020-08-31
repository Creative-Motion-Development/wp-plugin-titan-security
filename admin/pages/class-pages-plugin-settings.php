<?php

namespace WBCR\Titan\Page;

use WBCR\Titan\Plugin;
use WBCR\Titan\Plugin\Helper;
use Wbcr_Factory000_Plugin;

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
	public $page_menu_dashicon = 'dashicons-admin-generic';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_position = 2;

	/**
	 * @param Wbcr_Factory000_Plugin $plugin
	 */
	public function __construct( Wbcr_Factory000_Plugin $plugin ) {
		$this->menu_title                  = __( 'Settings', 'titan-security' );
		$this->page_menu_short_description = __( 'Global plugin settings', 'titan-security' );

		parent::__construct( $plugin );

		$this->plugin = $plugin;

		add_action( 'wp_ajax_wtitan_import_settings', [ $this, 'import_settings' ] );
	}


	/**
	 * Requests assets (js and css) for the page.
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/libs/jquery.datetimepicker.full.min.js' );
		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/libs/jquery.datetimepicker.min.css' );

		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/import.js' );
		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/settings.js' );
		wp_enqueue_script( 'winp_page_settings_js', WTITAN_PLUGIN_URL . '/admin/assets/js/settings.js');

		$params = [
			'import_options_nonce' => wp_create_nonce( 'wtitan_import_options' ),
			'i18n'                 => [
				'success_update_settings' => __( 'Settings successfully updated!', 'titan-security' ),
				'unknown_error'           => __( 'During the setup, an unknown error occurred, please try again or contact the plugin support.', 'titan-security' ),
			]
		];
		wp_localize_script( 'winp_page_settings_js', 'wtitan_ajax', $params );

	}

	/**
	 * Permalinks options.
	 *
	 * @return mixed[]
	 * @since 7.0.0
	 */
	public function getPageOptions() {
		$is_premium = $this->plugin->is_premium();
		$options    = [];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Advanced settings', 'titan-security' ) . '</strong>' . '<p>' . __( 'This group of settings allows you to configure the work of the plugin.', 'titan-security' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'extra_menu',
			'title'   => __( 'Plugin menu in adminbar', 'titan-security' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'    => __( 'This setting allows you to enable/disable the additional menu of the plugin, in the admin bar.', 'titan-security' ),
			'default' => false
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
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Antivirus settings', 'titan-security' ) . '</strong>' . '<p>' . __( 'This group of settings allows you to configure the work of the plugin.', 'titan-security' ) . '</p>' . '</div>'
		];

		$data = [
			[ \WBCR\Titan\MalwareScanner\Scanner::SPEED_FREE, __( 'Free', 'titan-security' ) ],
			[ \WBCR\Titan\MalwareScanner\Scanner::SPEED_SLOW, __( 'Slow', 'titan-security' ) ],
			[ \WBCR\Titan\MalwareScanner\Scanner::SPEED_MEDIUM, __( 'Medium', 'titan-security' ) ],
			[ \WBCR\Titan\MalwareScanner\Scanner::SPEED_FAST, __( 'Fast', 'titan-security' ) ],
		];

		$options[] = [
			'type'     => 'dropdown',
			'way'      => 'buttons',
			'name'     => 'scanner_type',
			'title'    => __( 'Scanning type', 'titan-security' ),
			'layout'   => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'     => __( "The basic scan includes fewer signatures", 'titan-security' ) . "<br>" . __( "Advanced scanning includes a full set of signatures", 'titan-security' ),
			'data'     => [
				[ 'basic', __( 'Basic scan', 'titan-security' ) ],
				[ 'advanced', __( 'Advanced scan', 'titan-security' ) ],
			],
			'cssClass' => ! $is_premium ? [ 'factory-checkbox--disabled wtitan-control-premium-label' ] : [],
			'default'  => $this->plugin->is_premium() ? 'advanced' : 'basic',
		];

		$options[] = [
			'type'     => 'dropdown',
			'way'      => 'buttons',
			'name'     => 'scanner_speed',
			'title'    => __( 'Scanning speed', 'titan-security' ),
			'layout'   => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'     => __( "The speed of scanning affects the resources consumed", 'titan-security' ) . "<br>" . __( "Recommended speed: ", 'titan-security' ) . get_recommended_scanner_speed(),
			'data'     => $data,
			'cssClass' => ! $is_premium ? [ 'factory-checkbox--disabled wtitan-control-premium-label' ] : [],
			'default'  => $this->plugin->is_premium() ? \WBCR\Titan\MalwareScanner\Scanner::SPEED_SLOW : \WBCR\Titan\MalwareScanner\Scanner::SPEED_FREE,
		];

		$data_schedule = [
			[
				\WBCR\Titan\MalwareScanner\Scanner::SCHEDULE_DISABLED,
				__( 'Disabled', 'titan-security' ),
				__( 'Disable scheduled scanning', 'titan-security' )
			],
			[
				\WBCR\Titan\MalwareScanner\Scanner::SCHEDULE_DAILY,
				__( 'Daily', 'titan-security' ),
				__( 'Scan every day', 'titan-security' )
			],
			[
				\WBCR\Titan\MalwareScanner\Scanner::SCHEDULE_WEEKLY,
				__( 'Weekly', 'titan-security' ),
				__( 'Scan every week', 'titan-security' )
			],
			[
				\WBCR\Titan\MalwareScanner\Scanner::SCHEDULE_CUSTOM,
				__( 'Custom', 'titan-security' ),
				__( 'Select the date and time of the next scan', 'titan-security' )
			],
		];

		$options[] = [
			'type'     => 'dropdown',
			'way'      => 'buttons',
			'name'     => 'scanner_schedule',
			'title'    => __( 'Schedule scan', 'titan-security' ),
			'layout'   => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'     => __( "The speed of scanning affects the resources consumed", 'titan-security' ),
			'data'     => $data_schedule,
			'cssClass' => ! $is_premium ? [ 'factory-checkbox--disabled wtitan-control-premium-label' ] : [],
			'default'  => \WBCR\Titan\MalwareScanner\Scanner::SCHEDULE_DISABLED,
		];

		if ( Plugin::app()->is_premium() ) {
			/*
			* Schedule settings
			* */
			$options[] = [
				'type' => 'html',
				'html' => '<div class="wt-schedule-controls wt-schedule-controls-daily">'
			];
			$options[] = [
				'type'   => 'textbox',
				'name'   => 'scanner_schedule_daily',
				'title'  => __( 'Time for the daily scan', 'titan-security' ),
				'layout' => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
				'hint'   => __( "What time should start scanning", 'titan-security' ),
			];
			$options[] = [ 'type' => 'html', 'html' => '</div>' ];

//------------------------------------------------------------------

			$options[]          = [
				'type' => 'html',
				'html' => '<div class="wt-schedule-controls wt-schedule-controls-weekly">'
			];
			$data_schedule_week = [
				[ 1, 'Monday' ],
				[ 2, 'Tuesday' ],
				[ 3, 'Wednesday' ],
				[ 4, 'Thursday' ],
				[ 5, 'Friday' ],
				[ 6, 'Saturday' ],
				[ 7, 'Sunday' ],
			];
			$options[]          = [
				'type'    => 'dropdown',
				'way'     => 'default',
				'name'    => 'scanner_schedule_weekly_day',
				'title'   => __( 'Day for the weekly scan', 'titan-security' ),
				'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
				'hint'    => __( "Day of the week for the weekly scan", 'titan-security' ),
				'data'    => $data_schedule_week,
				'default' => '',
			];

			$options[] = [
				'type'   => 'textbox',
				'name'   => 'scanner_schedule_weekly_time',
				'title'  => __( 'Time for the weekly scan', 'titan-security' ),
				'layout' => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
				'hint'   => __( "Time for the weekly scan", 'titan-security' ),
			];
			$options[] = [ 'type' => 'html', 'html' => '</div>' ];

//------------------------------------------------------------------

			$options[] = [
				'type' => 'html',
				'html' => '<div class="wt-schedule-controls wt-schedule-controls-custom">'
			];
			$options[] = [
				'type'   => 'textbox',
				'name'   => 'scanner_schedule_custom',
				'title'  => __( 'Date and time for the Custom schedule', 'titan-security' ),
				'layout' => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
				'hint'   => __( "Date and time of the custom scan schedule", 'titan-security' ),
			];
			$options[] = [ 'type' => 'html', 'html' => '</div>' ];
//------------------------------------------------------------------
		}

		$options[] = [
			'type'    => 'dropdown',
			'way'     => 'buttons',
			'name'    => 'digest',
			'data'    => [
				[
					'enable',
					__( 'Enable', 'titan-security' ),
				],
				[
					'disable',
					__( 'Disable', 'titan-security' ),
				],
			],
			'title'   => __( 'Digest on E-mail', 'titan-security' ),
			'hint'    => __( "You can get a weekly digest on threats found", 'titan-security' ),
			'default' => 'disable'
		];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Import/Export', 'titan-security' ) . '</strong>' . '<p>' . __( 'This group of settings allows you to configure the work of the plugin.', 'titan-security' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type' => 'html',
			'html' => [ $this, 'export' ]
		];

		$formOptions = [];

		$formOptions[] = [
			'type'  => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters( 'wtitan/settings_form_options', $formOptions, $this );
	}

	/**
	 * Export settings
	 */
	public function export() {
		?>
        <div class="wbcr-titan-export-import">
            <p>
                <label for="wbcr-titan-export-textarea">
                    <strong><?php _e( 'Import/Export settings', 'titan-security' ) ?></strong>
                </label>
                <textarea id="wbcr-titan-export-textarea"><?php echo $this->getExportOptions(); ?></textarea>
                <button class="button wtitan-import-options-button"><?php _e( 'Import options', 'titan-security' ) ?></button>
            </p>
        </div>
		<?php
	}

	/**
	 * Получает и возвращает все опции разрешенные для экспорта
	 *
	 * @param string $return
	 *
	 * @return array|string
	 */
	public function getExportOptions( $return = 'json' ) {
		$export_options = $this->getAllowOptions();

		if ( $return == 'array' ) {
			return $export_options;
		}

		return htmlspecialchars( json_encode( $export_options ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Ajax действите, выполняется для получения всех доступных опций для экспорта.
	 */
	public function import_settings() {
		global $wpdb;

		check_ajax_referer( 'wtitan_import_options' );

		if ( ! $this->plugin->currentUserCan() ) {
			wp_send_json_error( array( 'error_message' => __( 'You don\'t have enough capability to edit this information.', 'titan-security' ) ) );
			die();
		}

		$settings = Helper::maybeGetPostJson( 'settings' );

		/**
		 * Используется для фильтрации импортируемых настроек,
		 * обычно это может пригодиться для компонентов, которым нужно выполнить дополнительные дествия к опциям,
		 * прежде чем продолжить импорт
		 *
		 * wtitan/filter_import_options
		 * @since 1.4.0
		 */
		$settings = apply_filters( 'wtitan/filter_import_options', $settings );

		$network_id = get_current_network_id();

		if ( empty( $settings ) || ! is_array( $settings ) ) {
			wp_send_json_error( array( 'error_message' => __( 'Settings are not defined or do not exist.', 'titan-security' ) ) );
			die();
		}

		$values        = array();
		$place_holders = array();

		if ( $this->plugin->isNetworkActive() ) {
			$query = "INSERT INTO {$wpdb->sitemeta} (site_id, meta_key, meta_value) VALUES ";
		} else {
			$query = "INSERT INTO {$wpdb->options} (option_name, option_value) VALUES ";
		}

		foreach ( $settings as $option_name => $option_value ) {
			$option_name      = sanitize_text_field( $option_name );
			$raw_option_value = $option_value;

			if ( is_serialized( $option_value ) ) {
				$option_value = unserialize( $option_value );
			}

			if ( is_array( $option_value ) || is_object( $option_value ) ) {
				$option_value = Helper::recursiveSanitizeArray( $option_value, 'wp_kses_post' );
				$option_value = maybe_serialize( $option_value );
			} else {
				$option_value = wp_kses_post( $option_value );
			}

			/**
			 * Используется для фильтрации импортируемых значений,
			 * обычно это может пригодиться для компонентов, которым нужно подменять домены, пути или какие-то правила
			 * при переносе с одного сайта на другой
			 *
			 * wtitan/filter_import_values
			 * @since 1.4.0
			 */
			$option_value = apply_filters( 'wtitan/filter_import_values', $option_value, $option_name, $raw_option_value );

			if ( $this->plugin->isNetworkActive() ) {
				array_push( $values, $network_id, $option_name, $option_value );
				$place_holders[] = "('%d', '%s', '%s')";/* In my case, i know they will always be integers */
			} else {
				array_push( $values, $option_name, $option_value );
				$place_holders[] = "('%s', '%s')";/* In my case, i know they will always be integers */
			}
		}

		$query .= implode( ', ', $place_holders );

		// Удаляем все опции
		$all_options = $this->getAllowOptions( false );

		if ( ! empty( $all_options ) ) {
			foreach ( $all_options as $name => $value ) {
				$this->plugin->deletePopulateOption( $name );
			}
		}

		// Сбрасываем кеш опций
		$this->plugin->flushOptionsCache();

		// Импортируем опции
		$wpdb->query( $wpdb->prepare( "$query ", $values ) );

		$send_data = array( 'status' => 'success' );

		//$package_plugin = WCL_Package::instance();
		//$send_data['update_notice'] = $package_plugin->getUpdateNotice();

		// Сбрасываем кеш для кеширующих плагинов
		Helper::flushPageCache();

		do_action( 'wtitan_imported_settings' );

		wp_send_json_success( $send_data );
		die();
	}

	/**
	 * @param bool
	 */
	public function getAllowOptions( $with_prefix = true ) {
		global $wpdb;

		$result = array();

		$excluded_options = array(
			'plugin_activated',
			'plugin_version',
			'audit_results_hided',
			'audit_results',
			'vulnerabilities_wordpress',
			'vulnerabilities_plugins',
			'vulnerabilities_themes',
			'scanner',
			'scanner_malware_matched',
			'scanner_files_count',
			'scanner_status',
			'files_hash',
			'freemius_api_clock_diff',
			'what_is_new_64',
			'license',
			'stats_transient_',
		);

		foreach ( $excluded_options as $key => $option ) {
			$excluded_options[ $key ] = $this->plugin->getOptionName( $option );
		}

		if ( $this->plugin->isNetworkActive() ) {
			$network_id = get_current_network_id();

			$request = $wpdb->get_results( $wpdb->prepare( "
					SELECT meta_key, meta_value
					FROM {$wpdb->sitemeta}
					WHERE site_id = '%d' AND meta_key
					LIKE '%s'", $network_id, $this->plugin->getPrefix() . "%" ) );
		} else {
			$request = $wpdb->get_results( $wpdb->prepare( "
					SELECT option_name, option_value
					FROM {$wpdb->options}
					WHERE option_name
					LIKE '%s'", $this->plugin->getPrefix() . "_%" ) );
		}

		if ( ! empty( $request ) ) {
			foreach ( $request as $option ) {
				if ( $this->plugin->isNetworkActive() ) {
					$option_name  = $option->meta_key;
					$option_value = $option->meta_value;
				} else {
					$option_name  = $option->option_name;
					$option_value = $option->option_value;
				}
				if ( ! in_array( $option_name, $excluded_options ) ) {
					$result[ $option_name ] = $option_value;
				}
			}
		}
		if ( ! $with_prefix ) {
			foreach ( $result as $key => $option ) {
				unset( $result[ $key ] );
				$k            = preg_replace( '/^titan_/', '', $key );
				$result[ $k ] = $option;
			}
		}

		return $result;
	}

}

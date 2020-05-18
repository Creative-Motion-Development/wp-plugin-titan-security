<?php

namespace WBCR\Titan\Page;

use WBCR\Titan;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scanner page class
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Check extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'check';

	/**
	 * {@inheritdoc}
	 */
	public $page_parent_page = 'none';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-plugins-checked';

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;


	/**
	 * Module URL
	 *
	 * @since  7.0
	 * @var string
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL . "/includes/check";

	/**
	 * Module path
	 *
	 * @since  7.0
	 * @var string
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR . "/includes/check";

	/**
	 * Module object
	 *
	 * @since  7.0
	 * @var object
	 */
	public $module;

	/**
	 * Scanner page constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->menu_title                  = __( 'Audit', 'titan-security' );
		$this->page_menu_short_description = __( 'Security audit and vulnerability detection', 'titan-security' );

		if ( $this->plugin->is_premium() ) {
			require_once $this->MODULE_PATH . "/boot.php";
			$this->module = new Titan\Check();
		}

		parent::__construct( $plugin );
	}

	/**
	 * {@inheritDoc}
	 * @param                         $notices
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @return array
	 * @since 6.5.2
	 *
	 * @see   \FactoryPages000_ImpressiveThemplate
	 */
	public function getActionNotices( $notices ) {

		$notices[] = [
			'conditions' => [
				'wtitan_prefix_changed' => 1
			],
			'type'       => 'success',
			'message'    => __( 'Database prefix has been changed!', 'titan-security' )

		];

		$notices[] = [
			'conditions' => [
				'wtitan_prefix_save_error' => 1,
				'wtitan_error_code'        => 'empty_prefix'
			],
			'type'       => 'danger',
			'message'    => __( 'Prefix cannot be empty!', 'titan-security' )

		];

		$notices[] = [
			'conditions' => [
				'wtitan_prefix_save_error' => 1,
				'wtitan_error_code'        => 'permission_error'
			],
			'type'       => 'danger',
			'message'    => sprintf( __( 'The database prefix cannot be changed because the wp-config.php file is not writable.', 'titan-security' ), 'https://users.freemius.com/login', 'https://users.freemius.com/login' )
		];

		return $notices;
	}

	/**
	 * Add assets
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->scripts->request( [
			'bootstrap.tab',
		], 'bootstrap' );

		$this->styles->request( [
			'bootstrap.tab',
		], 'bootstrap' );

		$this->styles->add( $this->MODULE_URL . '/assets/css/check-dashboard.css' );
		$this->scripts->add( $this->MODULE_URL . '/assets/js/check.js', [ 'jquery' ] );
		$this->scripts->localize( 'update_nonce', wp_create_nonce( "updates" ) );
		$this->scripts->localize( 'wtscanner', [
			'update_nonce' => wp_create_nonce( "updates" ),
			'hide_nonce'   => wp_create_nonce( "hide" ),
		] );

		$this->styles->add( WTITAN_PLUGIN_URL . '/includes/vulnerabilities/assets/css/vulnerabilities-dashboard.css' );
		$this->styles->add( WTITAN_PLUGIN_URL . '/includes/audit/assets/css/audit-dashboard.css' );
	}

	/**
	 * Show page content
	 */
	public function showPageContent() {
		if ( ! $this->plugin->is_premium() ) {
			$this->plugin->view->print_template( 'require-license-activate' );

			return;
		}

		$this->module->showPageContent();
	}

	public function fixDatabasePrefixAction() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You are not have permission for this action!', 'titan-security' ) );
		}

		if ( isset( $_POST['wtitan_save_prefix'] ) ) {
			$prefix          = $this->request->post( 'wtitan_new_prefix', '', true );
			$fixing_issue_id = $this->request->post( 'wtitan_fixing_issue_id', '', 'intval' );

			$this->confirmPageTemplate( [
				'title'       => __( 'Are you sure you want to change the database prefix?', 'titan-security' ),
				'description' => __( 'Attention! The prefix for the names of all tables in your database will be changed. Please backup the database and wp-config.php file. If an error occurs when changing the database prefix, you can restore data from backup by replacing the wp-config.php file and restoring the database backup manually.', 'titan-security' ),
				'actions'     => [
					[
						'url'   => wp_nonce_url( $this->getActionUrl( 'fix-database-prefix-confirm', [
							'wtitan_prefix'          => base64_encode( $prefix ),
							'wtitan_fixing_issue_id' => $fixing_issue_id
						] ), 'wtitan_save_prefix_confirm' ),
						'title' => __( 'Yes, confirm', 'titan-security' ),
						'class' => 'button button-primary'
					],
					[
						'url'   => $this->getActionUrl( 'fix-database-prefix-cancel' ),
						'title' => __( 'Cancel' ),
						'class' => 'button button-default'
					]

				]
			] );

			return;
		}

		global $table_prefix;

		$template = \WBCR\Titan\Plugin::app()->view()->get_template( 'audit/fix-database-prefix', [
			'current_prefix'  => $table_prefix,
			'random_prefix'   => strtolower( wp_generate_password( '4', false ) ) . '_',
			'fixing_issue_id' => $this->request->get( 'wtitan_fixing_issue_id', null, 'intval' )
		] );
		$this->showPage( $template );
	}

	public function fixDatabasePrefixConfirmAction() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You are not have permission for this action!', 'titan-security' ) );
		}

		check_admin_referer( 'wtitan_save_prefix_confirm' );

		global $table_prefix;
		$fixing_issue_id  = $this->request->request( 'wtitan_fixing_issue_id', '', 'intval' );
		$table_old_prefix = $table_prefix;
		$table_new_prefix = base64_decode( $this->request->get( 'wtitan_prefix', '', true ) );

		if ( empty( $table_new_prefix ) ) {
			$this->redirectToAction( 'fix-database-prefix', [
				'wtitan_prefix_save_error' => 1,
				'wtitan_error_code'        => 'empty_prefix',
				'wtitan_fixing_issue_id'   => $fixing_issue_id
			] );
		}

		if ( ! $this->edit_wp_config( $table_new_prefix ) ) {
			$this->redirectToAction( 'fix-database-prefix', [
				'wtitan_prefix_save_error' => 1,
				'wtitan_error_code'        => 'permission_error',
				'wtitan_fixing_issue_id'   => $fixing_issue_id
			] );
		}
		$this->rename_table_names( $table_old_prefix, $table_new_prefix );

		$table_name = $table_new_prefix . 'options';
		$this->update_table_values( $table_name, 'option_name', $table_old_prefix, $table_new_prefix );

		$table_name = $table_new_prefix . 'usermeta';
		$this->update_table_values( $table_name, 'meta_key', $table_old_prefix, $table_new_prefix );

		if ( ! empty( $_GET['wtitan_fixing_issue_id'] ) && is_numeric( $_GET['wtitan_fixing_issue_id'] ) ) {
			$fixing_issue_id = $this->request->get( 'wtitan_fixing_issue_id', '', 'intval' );
			$issues          = get_option( $this->plugin->getPrefix() . "audit_results", [] );

			if ( isset( $issues[ $fixing_issue_id ] ) ) {
				unset( $issues[ $fixing_issue_id ] );
				update_option( $this->plugin->getPrefix() . "audit_results", $issues, 'no' );
			}
		}

		$url = $this->getBaseUrl( 'dashboard', [
			'wtitan_prefix_changed' => 1
		] );

		$url = add_query_arg( 'action', 'index', $url );

		wp_safe_redirect( $url );

		die();
	}

	public function fixDatabasePrefixCancelAction() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You are not have permission for this action!', 'titan-security' ) );
		}

		$this->redirectToAction( 'index' );
	}

	/**
	 * Edit the wp-config.php file
	 *
	 * @param String $table_new_prefix The name of the new prefix
	 *
	 * @return bool
	 */
	private function edit_wp_config( $table_new_prefix ) {
		$path = ABSPATH . 'wp-config.php';

		if ( ! file_exists( $path ) || ! is_writeable( $path ) ) {
			return false;
		}

		@chmod( $path, 0777 );
		$content = file_get_contents( $path );

		if ( ! $content || empty( $content ) ) {
			return false;
		}

		$content = preg_replace( '/\$table_prefix\s?=\s?\'[A-z0-9_-]+\'[\s\t]?;/i', "\$table_prefix = '{$table_new_prefix}';", $content );

		if ( empty( $content ) ) {
			return false;
		}

		$handle = fopen( $path, 'w+' );

		fwrite( $handle, $content );
		rewind( $handle );

		$changed_file_content = fread( $handle, filesize( $path ) );

		fclose( $handle );
		@chmod( $path, 0644 );

		if ( false === strpos( $changed_file_content, "'$table_new_prefix'" ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Rename all the table names
	 *
	 * @param String $table_old_prefix The old prefix of the database table names
	 * @param String $table_new_prefix The new database prefix name
	 *
	 * @return    void
	 */
	private function rename_table_names( $table_old_prefix, $table_new_prefix ) {
		global $wpdb;

		$sql     = "SHOW TABLES LIKE '%'";
		$results = $wpdb->get_results( $sql, ARRAY_N );
		$queries = array();
		foreach ( $results as $result ) {

			$table_old_name = $result[0];
			$table_new_name = $table_old_name;

			if ( strpos( $table_old_name, $table_old_prefix ) === 0 ) {
				$table_new_name = $table_new_prefix . substr( $table_old_name, strlen( $table_old_prefix ) );
			}

			$sql       = "RENAME TABLE $table_old_name TO $table_new_name";
			$queries[] = false === $wpdb->query( $sql );
		}
	}

	/**
	 * Update a table column with the new prefix
	 *
	 * @param string $table_name the table that is going to be update
	 * @param string $field the table column that is going to be updated
	 * @param string $table_old_prefix The previous table prefix name
	 * @param string $table_new_prefix The new table prefix name
	 */
	private function update_table_values( $table_name, $field, $table_old_prefix, $table_new_prefix ) {
		global $wpdb;

		$sql     = "SELECT $field FROM $table_name WHERE $field LIKE '%$table_old_prefix%'";
		$results = $wpdb->get_results( $sql, ARRAY_N );

		foreach ( $results as $result ) {
			$old_value = $result[0];

			if ( strpos( $old_value, $table_old_prefix ) === 0 ) {
				$new_value = $table_new_prefix . substr( $old_value, strlen( $table_old_prefix ) );
				$sql       = "UPDATE $table_name SET $field='$new_value' WHERE $field='$old_value'";
				$wpdb->query( $sql );
			}
		}
	}
}

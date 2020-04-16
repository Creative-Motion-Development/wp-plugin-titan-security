<?php

namespace WBCR\Titan\Premium;

/**
 * The class extend Comments_List_Table
 *
 * Add new status tab and comments filter. We exclude from all list
 * comments that haven't checked for spam yet.
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 *
 * @copyright (c) 2018 Webraftic Ltd
 */
class Users_List_Table_Extension {

	public function __construct() {
		add_filter( 'users_list_table_query_args', function ( $args ) {
			if ( empty( $args['role'] ) ) {
				$args['role__not_in'] = [ 'spam_checking', 'spam' ];
			}

			return $args;
		} );

		add_action( 'restrict_manage_users', [ $this, 'add_empty_spam_button' ] );
		add_action( 'current_screen', [ $this, 'empty_spam' ] );
		add_filter( 'manage_users_columns', [ $this, 'register_new_column' ] );
		add_action( 'manage_users_custom_column', [ $this, 'register_new_column_handler' ], 10, 3 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue styles for "Manage users pages"
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param string $hook   Current page hook
	 */
	public function enqueue_scripts( $hook ) {
		if ( "users.php" !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wantispam-manage-comments', WANTISPAMP_PLUGIN_URL . '/admin/assets/css/manage-comments.css' );
	}

	/**
	 * Register a new column on the "Manage users page"
	 *
	 * Column need for showing a status check for spam.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param array $columns
	 *
	 * @return mixed
	 */
	public function register_new_column( $columns ) {
		$columns['wantispam_spam_checking_status'] = __( 'Spam checking status' );

		return $columns;
	}

	/**
	 * Register a new column handler
	 *
	 * Errors and status is printed after the user checked.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param string $column_name
	 * @param int    $comment_ID
	 */
	public function register_new_column_handler( $val, $column_name, $user_id ) {
		if ( "wantispam_spam_checking_status" === $column_name ) {
			$is_checked_comment = get_user_meta( $user_id, wantispamp_db_key( 'user_checked' ), true );
			$error              = get_user_meta( $user_id, wantispamp_db_key( 'spam_checking_fail' ), true );

			if ( ! empty( $error ) ) {
				return '<span class="wantispam-status-text wantispam-status-text--red">' . __( "Comment hasn't been checked for spam because of error:", 'titan-security' ) . ' ' . esc_html( $error ) . '</span>';
			} else if ( $is_checked_comment ) {
				return '<span class="wantispam-status-text wantispam-status-text--green">' . __( 'Successfully was checked for spam!', 'titan-security' ) . '</span>';
			}
		}

		return $val;
	}

	/**
	 * Add new button to users page in a filters line.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 */
	public function add_empty_spam_button() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_GET['role'] ) && 'spam' === $_GET['role'] ):
			$url = wp_nonce_url( admin_url( 'users.php?role=spam&wanspam_delete_all_spam' ), 'wanspam_delete_all_spam' );
			?>
            <a class="button button-default" href="<?php echo esc_url( $url ); ?>"><?php _e( 'Empty Spam', 'titan-security' ) ?></a>
		<?php
		endif;
	}

	/**
	 * Clear all spam users.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 */
	public function empty_spam() {
		$current_screen = get_current_screen();

		if ( ! empty( $current_screen ) && 'users' === $current_screen->id && isset( $_GET['wanspam_delete_all_spam'] ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( "You haven't permissions for make the action!" );
			}
			check_admin_referer( 'wanspam_delete_all_spam' );

			$users = get_users( [
				'role' => [ 'spam_checking', 'spam' ]
			] );

			if ( ! empty( $users ) ) {
				foreach ( $users as $user ) {
					wp_delete_user( $user->ID );
				}
			}
		}
	}

}

new \WBCR\Titan\Premium\Users_List_Table_Extension();











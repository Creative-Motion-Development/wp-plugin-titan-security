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
class Comments_List_Table_Extension {

	public function __construct() {
		add_filter( 'comment_status_links', [ $this, 'add_status_link' ] );
		add_filter( 'comments_list_table_query_args', [ $this, 'add_filter_comments' ] );
		add_filter( 'manage_edit-comments_columns', [ $this, 'register_new_column' ] );
		add_action( 'manage_comments_custom_column', [ $this, 'register_new_column_handler' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue styles for "Manage comments pages"
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param string $hook   Current page hook
	 */
	public function enqueue_scripts( $hook ) {
		if ( "edit-comments.php" !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wantispam-manage-comments', WANTISPAMP_PLUGIN_URL . '/admin/assets/css/manage-comments.css' );
	}

	/**
	 * Register a new column on the "Manage comments page"
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
	 * Errors and status is printed after the comment checked.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param string $column_name
	 * @param int    $comment_ID
	 */
	public function register_new_column_handler( $column_name, $comment_ID ) {
		if ( "wantispam_spam_checking_status" === $column_name ) {
			$is_checked_comment = get_comment_meta( $comment_ID, wantispamp_db_key( 'comment_checked' ), true );
			$error              = get_comment_meta( $comment_ID, wantispamp_db_key( 'spam_checking_fail' ), true );

			if ( ! empty( $error ) ) {
				echo '<span class="wantispam-status-text wantispam-status-text--red">' . __( "Comment hasn't been checked for spam because of error:", 'titan-security' ) . ' ' . esc_html( $error ) . '</span>';
			} else if ( $is_checked_comment ) {
				echo '<span class="wantispam-status-text wantispam-status-text--green">' . __( 'Successfully was checked for spam!', 'titan-security' ) . '</span>';
			}
		}
	}

	/**
	 * Add comments filter
	 *
	 * The comments filter exclude unchecked comments from all comments list.
	 * But if comment_status equeal spam_checking, the filter exclude all comments
	 * except unchecked comments.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function add_filter_comments( $args ) {
		$comment_status = isset( $_REQUEST['comment_status'] ) ? $_REQUEST['comment_status'] : 'all';
		if ( 'spam_checking' === $comment_status ) {
			$args['meta_key'] = wantispamp_db_key( 'spam_checking' );

			return $args;
		}

		$args['meta_query'] = [
			[
				'key'     => wantispamp_db_key( 'spam_checking' ),
				'compare' => 'NOT EXISTS',
			],
		];

		return $args;
	}

	/**
	 * Add new status link "Spam checking queue"
	 *
	 * In this tab user can look unchecked comments.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param array $status_links   All status links of comments list table
	 *
	 * @return array
	 */
	public function add_status_link( $status_links ) {
		global $wpdb;

		$admin_url  = admin_url( "edit-comments.php?comment_status=spam_checking" );
		$link_title = __( "Spam checking queue", 'titan-security' );

		$total = (int) $wpdb->get_var( $wpdb->prepare( "
					SELECT COUNT( * ) AS total
					FROM {$wpdb->comments} c LEFT JOIN {$wpdb->commentmeta} cm ON c.comment_ID = cm.comment_id
					WHERE cm.meta_key='%s'", wantispamp_db_key( 'spam_checking' ) ) );

		$counter        = sprintf( '<span class="count">(<span class="spam-checking-count">%d</span>)</span>', $total );
		$status_links[] = sprintf( '<a href="%s">%s %s</a>', $admin_url, $link_title, $counter );

		return $status_links;
	}

}

new \WBCR\Titan\Premium\Comments_List_Table_Extension();











<?php

namespace WBCR\Titan\Antispam;

use \WBCR\Titan\Plugin as Plugin;

/**
 * The class implement some protections ways against spam
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 *
 * @copyright (c) 2018 Webraftic Ltd
 */
class Protector {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );
		add_action( 'comment_form', [ $this, 'form_part' ] ); // add anti-spam inputs to the comment form

		if ( wantispam_is_license_activate() ) {
			add_action( 'comment_form_after', 'wantispam_display_comment_form_privacy_notice' );
		}

		if ( ! is_admin() ) { // without this check it is not possible to add comment in admin section
			add_filter( 'preprocess_comment', [ $this, 'check_comment' ], 1 );
		}
	}

	/**
	 * We enqueue js script required for the plugin to work. The script overwrites the values
	 * of hidden fields or determines whether the user uses javascript or not.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.1.1
	 */
	public function enqueue_script() {
		global $withcomments; // WP flag to show comments on all pages
		wp_register_script( 'anti-spam-script', WTITAN_PLUGIN_URL . '/assets/js/anti-spam.js', [ 'jquery' ], Plugin::app()->getPluginVersion(), true );

		if ( ( is_singular() || $withcomments ) && comments_open() ) { // load script only for pages with comments form
			wp_enqueue_script( 'anti-spam-script' );
		}
	}

	/**
	 * Renders required fields into the comment form on the page.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.1
	 */
	public function form_part() {
		if ( ! is_user_logged_in() ) { // add anti-spam fields only for not logged in users
			echo wantispam_get_required_fields();
		}
	}

	public function check_comment( $commentdata ) {
		$save_spam_comments = Plugin::app()->getPopulateOption( 'save_spam_comments', true );

		$comment_type = isset( $commentdata['comment_type'] ) ? $commentdata['comment_type'] : null;

		if ( ! is_user_logged_in() && $comment_type != 'pingback' && $comment_type != 'trackback' ) { // logged in user is not a spammer
			if ( $this->check_for_spam() ) {
				if ( $save_spam_comments ) {
					$this->store_comment( $commentdata );
				}
				$this->counter_stats();
				wp_die( 'Comment is a spam.' ); // die - do not send comment and show error message
			}
		}

		if ( $comment_type == 'trackback' ) {
			if ( $save_spam_comments ) {
				$this->store_comment( $commentdata );
			}
			$this->counter_stats();
			wp_die( 'Trackbacks are disabled.' ); // die - do not send trackback and show error message
		}

		return $commentdata; // if comment does not looks like spam
	}

	public function counter_stats() {
		$antispam_stats = get_option( 'antispam_stats', [] );
		if ( array_key_exists( 'blocked_total', $antispam_stats ) ) {
			$antispam_stats['blocked_total'] ++;
		} else {
			$antispam_stats['blocked_total'] = 1;
		}
		update_option( 'antispam_stats', $antispam_stats );
	}

	public function check_for_spam() {
		$spam_flag = false;

		$antspm_q = Plugin::app()->request->post( "wantispam_q", '', 'trim' );
		$antspm_d = Plugin::app()->request->post( "wantispam_d", '', 'trim' );
		$antspm_e = Plugin::app()->request->post( "wantispam_e_email_url_website", '', 'trim' );

		if ( $antspm_q != date( 'Y' ) ) { // year-answer is wrong - it is spam
			if ( $antspm_d != date( 'Y' ) ) { // extra js-only check: there is no js added input - it is spam
				$spam_flag = true;
			}
		}

		if ( ! empty( $antspm_e ) ) { // trap field is not empty - it is spam
			$spam_flag = true;
		}

		return $spam_flag;
	}

	public function store_comment( $commentdata ) {
		global $wpdb;

		if ( isset( $commentdata['user_ID'] ) ) {
			$commentdata['user_id'] = $commentdata['user_ID'] = (int) $commentdata['user_ID'];
		}

		$prefiltered_user_id = ( isset( $commentdata['user_id'] ) ) ? (int) $commentdata['user_id'] : 0;

		$commentdata['comment_post_ID'] = (int) $commentdata['comment_post_ID'];
		if ( isset( $commentdata['user_ID'] ) && $prefiltered_user_id !== (int) $commentdata['user_ID'] ) {
			$commentdata['user_id'] = $commentdata['user_ID'] = (int) $commentdata['user_ID'];
		} else if ( isset( $commentdata['user_id'] ) ) {
			$commentdata['user_id'] = (int) $commentdata['user_id'];
		}

		$commentdata['comment_parent'] = isset( $commentdata['comment_parent'] ) ? absint( $commentdata['comment_parent'] ) : 0;
		$parent_status                 = ( 0 < $commentdata['comment_parent'] ) ? wp_get_comment_status( $commentdata['comment_parent'] ) : '';
		$commentdata['comment_parent'] = ( 'approved' == $parent_status || 'unapproved' == $parent_status ) ? $commentdata['comment_parent'] : 0;

		if ( ! isset( $commentdata['comment_author_IP'] ) ) {
			$commentdata['comment_author_IP'] = sanitize_text_field($_SERVER['REMOTE_ADDR']);
		}
		$commentdata['comment_author_IP'] = preg_replace( '/[^0-9a-fA-F:., ]/', '', $commentdata['comment_author_IP'] );

		if ( ! isset( $commentdata['comment_agent'] ) ) {
			$commentdata['comment_agent'] = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
		}
		$commentdata['comment_agent'] = substr( $commentdata['comment_agent'], 0, 254 );

		if ( empty( $commentdata['comment_date'] ) ) {
			$commentdata['comment_date'] = current_time( 'mysql' );
		}

		if ( empty( $commentdata['comment_date_gmt'] ) ) {
			$commentdata['comment_date_gmt'] = current_time( 'mysql', 1 );
		}

		$commentdata = wp_filter_comment( $commentdata );

		$commentdata['comment_approved'] = wp_allow_comment( $commentdata );
		if ( is_wp_error( $commentdata['comment_approved'] ) ) {
			return $commentdata['comment_approved'];
		}

		$comment_ID = wp_insert_comment( $commentdata );
		if ( ! $comment_ID ) {
			$fields = [ 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content' ];

			foreach ( $fields as $field ) {
				if ( isset( $commentdata[ $field ] ) ) {
					$commentdata[ $field ] = $wpdb->strip_invalid_text_for_column( $wpdb->comments, $field, $commentdata[ $field ] );
				}
			}

			$commentdata = wp_filter_comment( $commentdata );

			$commentdata['comment_approved'] = wp_allow_comment( $commentdata );
			if ( is_wp_error( $commentdata['comment_approved'] ) ) {
				return $commentdata['comment_approved'];
			}

			$comment_ID = wp_insert_comment( $commentdata );
			if ( ! $comment_ID ) {
				return false;
			}
		}

		wp_set_comment_status( $comment_ID, 'spam' );
	}
}

new Protector();











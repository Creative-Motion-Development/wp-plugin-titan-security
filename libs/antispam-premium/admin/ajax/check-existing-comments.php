<?php
/**
 * Ajax action to check existing comments
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @author        Alexander Gorenkov <g.a.androidjc2@ya.ru>
 *
 * @since         6.2
 * @version       1.0
 * @copyright (c) 2019 Webcraftic Ltd
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_waspam-check-existing-comments', 'wantispamp_checking_existing_comments' );

/**
 * Checking existing comment
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
function wantispamp_checking_existing_comments() {
	check_admin_referer( 'waspam-check-existing-comments' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( - 1 );
	}

	$result = wantispamp_check_existing_comments();

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( [
			'error_message' => $result->get_error_message()
		] );
	}

	list( $status, $remaining ) = $result;

	if ( $status ) {
		wp_send_json_success( [
			'remaining' => $remaining
		] );
	}

	wp_send_json_error( [
		'error_message' => 'Empty AntiSpam API response'
	] );
}
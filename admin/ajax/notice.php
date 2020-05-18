<?php

/**
 * Used to hide notice.
 */
add_action( 'wp_ajax_wtitan_hide_trial_notice', function ()
{
	check_ajax_referer( 'wtitan_hide_trial_notice' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( - 1 );
	}

	WBCR\Titan\Plugin::app()->updateOption( 'trial_notice_dismissed', true );

	wp_send_json_success( [
		'message' => esc_html__( 'Logs clean-up successfully', 'titan-security' ),
	] );
} );
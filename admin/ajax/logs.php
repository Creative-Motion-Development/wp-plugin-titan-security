<?php

/**
 * Used to clean-up logs.
 */
add_action( 'wp_ajax_wtitan-logger-logs-cleanup', function ()
{
	check_admin_referer( 'wlogger_clean_logs', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( - 1 );
	}

	if ( ! \WBCR\Titan\Logger\Writter::clean_up() ) {
		wp_send_json_error( [
			'message' => esc_html__( 'Failed to clean-up logs. Please try again later.', 'robin-image-optimizer' ),
			'type'    => 'danger',
		] );
	}

	wp_send_json( [
		'message' => esc_html__( 'Logs clean-up successfully', 'robin-image-optimizer' ),
		'type'    => 'success',
	] );
} );
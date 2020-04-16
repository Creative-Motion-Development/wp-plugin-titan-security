<?php
/**
 * Cron schedules for the plugin
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 27.10.2019, Webcraftic
 * @version       1.0
 */

/**
 * Create a new interval for cron schedules (5 min)
 */
add_filter( 'cron_schedules', function ( $schedules ) {
	$schedules['five_minets'] = [
		'interval' => 5 * MINUTE_IN_SECONDS,
		'display'  => esc_html__( 'Every Five minets' ),
	];

	return $schedules;
} );

/**
 * This action is triggered when a 5 minute cron task is performed.
 * With this action, we check the queue status on the remote server
 * to complete the spam check.
 */
add_action( 'wantispamp_check_status_queue', function () {
	global $wpdb;

	\WBCR\Titan\Logger\Writter::warning( "Cron event [START]!" );

	$cm_api = new WBCR\Titan\Premium\Api\Request();

	// Check status for comments
	$checking_comments = $wpdb->get_results( $wpdb->prepare( "
		SELECT meta_value as uid, comment_id FROM {$wpdb->commentmeta} 
		WHERE meta_key='%s' LIMIT 50", wantispamp_db_key( 'spam_checking' ) ) );

	\WBCR\Titan\Logger\Writter::info( sprintf( "%d comments prepared for spam checking.", sizeof( $checking_comments ) ) );

	if ( ! empty( $checking_comments ) ) {
		$uid_list    = [];
		$comment_IDs = [];

		foreach ( (array) $checking_comments as $comment ) {
			$uid_list[]                   = $comment->uid;
			$comment_IDs[ $comment->uid ] = $comment->comment_id;
		}

		$request = $cm_api->check_status_queue( $uid_list );

		if ( ! is_wp_error( $request ) ) {
			foreach ( $request->response as $element ) {
				$spam       = ( 'done' === $element->status ) ? $element->spam : false;
				$comment_ID = $comment_IDs[ $element->uid ];

				if ( wantispamp_approve_comment( $element->uid, $comment_ID, $element->status, $spam ) ) {
					unset( $comment_IDs[ $element->uid ] );
				}
			}
		} else {
			$checking_comments_error = $request->get_error_message();
			\WBCR\Titan\Logger\Writter::error( sprintf( "Request error: %s", $checking_comments_error ) );
		}

		if ( ! empty( $comment_IDs ) ) {
			// Skipping comments that have not been processed (not found on server)
			foreach ( $comment_IDs as $comment_ID ) {
				$error_text = 'Comment #%d has been skipping: not found on server';
				\WBCR\Titan\Logger\Writter::error( sprintf( $error_text, $comment_ID ) );
				//wp_set_comment_status( $comment_ID, 'approve' );
				add_comment_meta( $comment_ID, wantispamp_db_key( 'spam_checking_fail' ), sprintf( $error_text, $comment_ID ) );
				delete_comment_meta( $comment_ID, wantispamp_db_key( 'spam_checking' ) );
				add_comment_meta( $comment_ID, wantispamp_db_key( 'comment_checked' ), 1 );
			}
		}
	}

	// Check status for users
	$checking_users = get_users( [ 'role' => 'spam_checking' ] );

	\WBCR\Titan\Logger\Writter::info( sprintf( "%d users prepared for spam cheking.", sizeof( $checking_users ) ) );

	if ( ! empty( $checking_users ) ) {
		foreach ( (array) $checking_users as $user ) {
			$user     = new \WP_User( $user->ID );
			$user_uid = get_user_meta( $user->ID, wantispamp_db_key( 'spam_checking' ) );

			\WBCR\Titan\Logger\Writter::info( sprintf( "User #%d, uid: %s prepared for sending to server.", $user->ID, $user_uid ) );
			$request = $cm_api->check_status_queue( $user_uid );

			if ( ! is_wp_error( $request ) ) {
				if ( 'done' === $request->response->status ) {
					if ( true === $request->response->spam ) {
						$user->set_role( 'spam' );
						\WBCR\Titan\Logger\Writter::info( sprintf( "User #%d noticed as spam!", $user->ID ) );
					} else {
						$user->set_role( 'subscriber' );
						$user->remove_role( 'spam_checking' );
						\WBCR\Titan\Logger\Writter::info( sprintf( "User #%d noticed as approve!", $user->ID ) );
					}

					add_user_meta( $user->ID, wantispamp_db_key( 'user_checked' ), 1 );
				} else if ( 'process' === $request->response[0]->status ) {
					\WBCR\Titan\Logger\Writter::info( sprintf( "User #%d hasn't checked yet!", $user->ID ) );
				}
			} else {
				$checking_user_error = __( "User hasn't been checked for spam due an error:", 'titan-security' ) . $request->get_error_message();
				add_user_meta( $user->ID, wantispamp_db_key( 'spam_checking_fail' ), $checking_user_error );
				$user->remove_role( 'spam_checking' );
				$user->set_role( 'subscriber' );

				\WBCR\Titan\Logger\Writter::error( sprintf( "User #%d hasn't been checked because of error: %s!", $user->ID, $checking_user_error ) );
			}
		}
	}

	\WBCR\Titan\Logger\Writter::warning( "Cron event [END]!" );
} );


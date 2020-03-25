<?php
/**
 * Plugin rest api
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>
 * @copyright (c) 07.12.2019, Webcraftic
 * @version       1.0
 */

/**
 * Endpoint for sync listings
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'wantispam/v1', '/sync/', [
		'methods'             => 'POST',
		'permission_callback' => function ( $request ) {
			$license_key = md5( wantispam_get_license_key() );

			if ( $license_key === $request->get_param( 'access_token' ) ) {
				return true;
			}

			return false;
		},
		'args'                => [
			'access_token'         => [
				'default'           => null,
				'required'          => true,
				'sanitize_callback' => 'sanitize_key',
			],
			'spam_checking_result' => [
				'default'  => [],
				'required' => true
			],
		],
		'callback'            => function ( \WP_REST_Request $request ) {
			global $wpdb;

			\WBCR\Titan\Logger\Writter::warning( "Rest API request [START PROCESS]." );

			$is_protect_comments_form = \WBCR\Titan\Plugin::app()->getPopulateOption( 'protect_comments_form' );
			$is_protect_register_form = \WBCR\Titan\Plugin::app()->getPopulateOption( 'protect_register_form' );

			if ( ! $is_protect_comments_form && ! $is_protect_register_form ) {
				\WBCR\Titan\Logger\Writter::error( "The plugin api is closed!" );

				return new WP_Error( 'plugin_api_closed', 'The plugin api is closed.', [ 'status' => 404 ] );
			}

			$checking_result = $request->get_param( 'spam_checking_result' );

			if ( empty( $checking_result ) || ! is_array( $checking_result ) ) {
				\WBCR\Titan\Logger\Writter::error( "The body var {spam_checking_result} must be array." );

				return new WP_Error( 'invalid_data_type', 'The body var {spam_checking_result} must be array.', [ 'status' => 404 ] );
			}

			\WBCR\Titan\Logger\Writter::info( sprintf( "Got data: %s", var_export( $checking_result, true ) ) );

			$comment_uids              = [];
			$checking_result_formatted = [];
			foreach ( (array) $checking_result as $checked_item ) {
				$comment_uids[]                                    = "'" . sanitize_key( $checked_item['uid'] ) . "'";
				$checking_result_formatted[ $checked_item['uid'] ] = $checked_item;
			}

			// Check status for comments
			$comments = $wpdb->get_results( $wpdb->prepare( "
					SELECT meta_value as uid, comment_id 
					FROM {$wpdb->commentmeta} 
					WHERE meta_key='%s' AND meta_value in (" . implode( ',', $comment_uids ) . ")", wantispamp_db_key( 'spam_checking' ) ) );

			\WBCR\Titan\Logger\Writter::info( sprintf( "Finded comments: %s", var_export( $comments, true ) ) );

			$updated_comments = [];
			if ( ! empty( $comments ) ) {
				foreach ( $comments as $comment ) {
					$type = wantispamp_normalize_bool( $checking_result_formatted[ $comment->uid ]['spam'] );
					$spam = ( 'done' === $checking_result_formatted[ $comment->uid ]['status'] ) ? $type : false;

					if ( wantispamp_approve_comment( $comment->uid, $comment->comment_id, $checking_result_formatted[ $comment->uid ]['status'], $spam ) ) {
						$updated_comments[] = $comment->uid;
						unset( $checking_result_formatted[ $comment->uid ] );
					}
				}
			}

			\WBCR\Titan\Logger\Writter::info( sprintf( "Comments updated: %s", var_export( $updated_comments, true ) ) );
			\WBCR\Titan\Logger\Writter::info( sprintf( "Comments haven't been updated: %s", var_export( $updated_comments, true ) ) );

			\WBCR\Titan\Logger\Writter::warning( sprintf( "Rest API request completed [END PROCESS]." ) );

			return [
				'success'          => true,
				'checked_comments' => $updated_comments,
				'skip_comments'    => array_keys( $checking_result_formatted )
			];
		}
	] );
} );


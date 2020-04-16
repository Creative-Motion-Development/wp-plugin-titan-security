<?php

namespace WBCR\Titan\Premium;

/**
 * The class implement some protections ways against spam
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 *
 * @copyright (c) 2018 Webraftic Ltd
 */
class Listener {

	/**
	 * CreativeMotion API wrapper
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 * @var \WBCR\Titan\Premium\Api\Request
	 */
	protected $cm_api;

	public function __construct() {
		$this->cm_api = new Api\Request();

		if ( ! is_admin() && \WBCR\Titan\Plugin::app()->getPopulateOption( 'protect_comments_form' ) ) { // without this check it is not possible to add comment in admin section
			add_action( 'comment_post', [ $this, 'check_comment_for_spam' ], 10, 3 );
		}

		if ( \WBCR\Titan\Plugin::app()->getPopulateOption( 'protect_register_form' ) ) {
			add_action( 'user_register', [ $this, 'check_user_for_spam' ] );
		}
	}

	/**
	 * Checking user registration for spam throught a server request.
	 *
	 * When user registered, our method check the user registration for spam.
	 * To check spam comment must be send to remote server and parse response.
	 * If server request has been failed, we are logging error in database.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param int $user_id   User ID.
	 */
	public function check_user_for_spam( $user_id ) {

		$user_ip = wantispamp_get_ip();

		//Add user metadata to the usermeta table
		update_user_meta( $user_id, wantispamp_db_key( 'signup_ip' ), $user_ip );

		$user = new \WP_User( $user_id );

		if ( ! $user->exists() ) {
			return;
		}

		// Set the user's role (and implicitly remove the previous role).
		$items = [
			[
				'uid'   => wantispamp_generate_uid(),
				'email' => $user->user_email,
				'ip'    => $user_ip
			]
		];

		\WBCR\Titan\Logger\Writter::info( sprintf( "Resitered new user #%d, ip: %s, email: %s.", $user->ID, $user_ip, $user->user_email ) );
		\WBCR\Titan\Logger\Writter::info( sprintf( "User #%d, uid: %s prepared for sending to server.", $user->ID, $items[0]['uid'] ) );

		$request = $this->cm_api->check_spam( $items );

		if ( ! is_wp_error( $request ) && $items[0]['uid'] === $request->response[0]->uid ) {
			if ( 'done' === $request->response[0]->status ) {
				if ( true === $request->response[0]->spam ) {
					$user->remove_all_caps();
					$user->set_role( 'spam' );
					\WBCR\Titan\Logger\Writter::info( sprintf( "User #%d noticed as spam!", $user->ID ) );
				} else {
					delete_user_meta( $user_id, wantispamp_db_key( 'spam_checking' ) );
					\WBCR\Titan\Logger\Writter::info( sprintf( "User #%d noticed as approve!", $user->ID ) );
				}

				add_user_meta( $user_id, wantispamp_db_key( 'user_checked' ), 1 );

				return;
			} else if ( 'process' === $request->response[0]->status ) {
				$user->remove_all_caps();
				$user->set_role( 'spam_checking' );
				add_user_meta( $user_id, wantispamp_db_key( 'spam_checking' ), $items[0]['uid'] );

				\WBCR\Titan\Logger\Writter::info( sprintf( "User check #%d has been delayed! The one will be after 5 min.", $user->ID ) );

				return;
			}
		}

		$error = __( "User #%d hasn't been checked for spam due an unknown error.", 'titan-security' );
		if ( is_wp_error( $request ) ) {
			$error = __( "User #%d hasn't been checked for spam due an error:", 'titan-security' ) . $request->get_error_message();
		}

		add_user_meta( $user_id, wantispamp_db_key( 'spam_checking_fail' ), sprintf( $error, $user->ID, $error ) );

		\WBCR\Titan\Logger\Writter::error( sprintf( $error, $user->ID, $error ) );
	}

	/**
	 * Checking comment for spam thought a server request.
	 *
	 * When user published comment, our method check the comment for spam.
	 * To check spam comment must be send to remote server and parse response.
	 * If server request has been failed, we are logging error in database.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param int        $comment_ID         The comment ID.
	 * @param int|string $comment_approved   1 if the comment is approved, 0 if not, 'spam' if spam.
	 * @param array      $commentdata        Comment data.
	 */
	public function check_comment_for_spam( $comment_ID, $comment_approved, $commentdata ) {
		\WBCR\Titan\Logger\Writter::info( sprintf( "Added new comment #%d, approved: %s, email: %s, ip: %s [START].", $comment_ID, $comment_approved, $commentdata['comment_author_email'], $commentdata['comment_author_IP'] ) );

		if ( 'spam' === $comment_approved ) {
			\WBCR\Titan\Logger\Writter::info( sprintf( "Comment #%d notices as spam [END]!", $comment_ID ) );

			return;
		}

		if ( 1 !== $comment_approved ) {
			wp_set_comment_status( $comment_ID, 'hold' );
			\WBCR\Titan\Logger\Writter::info( sprintf( "Comment #%d notices as hold!", $comment_ID ) );
		}

		// is js support?
		$is_js_enable       = \WBCR\Titan\Plugin::app()->request->post( "wantispam_d" );
		$start_form_filling = (int) \WBCR\Titan\Plugin::app()->request->post( 'wantispam_t', 0 );
		$start_form_filling = time() - $start_form_filling;

		$items = [
			[
				'uid'          => wantispamp_generate_uid(),
				'email'        => $commentdata['comment_author_email'],
				'ip'           => $commentdata['comment_author_IP'],
				'text'         => $commentdata['comment_content'],
				'username'     => $commentdata['comment_author'],
				'useragent'    => $commentdata['comment_agent'],
				'headers'      => wantispamp_get_all_headers(),
				'referrer'     => $_SERVER['HTTP_REFERER'],
				'js_on'        => date( 'Y' ) == $is_js_enable,
				'submit_time'  => $start_form_filling,
				'callback_url' => rest_url( 'wantispam/v1/sync/' )
			]
		];

		\WBCR\Titan\Logger\Writter::info( sprintf( "Comment #%d, uid: %s prepared to checking!", $comment_ID, $items[0]['uid'] ) );

		$request = $this->cm_api->check_spam( $items );

		if ( ! is_wp_error( $request ) && $items[0]['uid'] === $request->response[0]->uid ) {
			$spam = ( 'done' === $request->response[0]->status ) ? $request->response[0]->spam : false;
			if ( wantispamp_approve_comment( $items[0]['uid'], $comment_ID, $request->response[0]->status, $spam ) ) {
				return;
			}
		}

		$error = __( "Comment #%d hasn't been checked for spam due an unknown error.", 'titan-security' );
		if ( is_wp_error( $request ) ) {
			$error = __( "Comment #%d hasn't been checked for spam due an error:", 'titan-security' ) . $request->get_error_message();
		}

		add_comment_meta( $comment_ID, wantispamp_db_key( 'spam_checking_fail' ), sprintf( $error, $comment_ID ) );
		delete_comment_meta( $comment_ID, wantispamp_db_key( 'spam_checking' ) );

		\WBCR\Titan\Logger\Writter::error( sprintf( $error, $comment_ID ) );
	}
}

new \WBCR\Titan\Premium\Listener();











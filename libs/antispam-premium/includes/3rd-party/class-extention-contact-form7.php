<?php

namespace WBCR\Titan\Premium;

/**
 * Contact form 7 plugin integration
 *
 * It stops spam in the contact forms of the CF7 plugin.
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 19.12.2019, Webcraftic
 * @version       1.0
 */

// Contact Form7
if ( ! defined( 'WPCF7_VERSION' ) ) {
	return;
}

class Contact_Form7 extends Extension {

	public function __construct() {
		parent::__construct();

		add_filter( 'wpcf7_form_elements', [ $this, 'form_elements' ] );
		add_filter( WPCF7_VERSION >= '3.0.0' ? 'wpcf7_spam' : 'wpcf7_acceptance', [ $this, 'check_spam' ] );
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.1.1
	 *
	 * @param string $output
	 *
	 * @return string
	 */
	public function form_elements( $output ) {
		if ( is_user_logged_in() ) {
			return $output;
		}

		$output .= $this->get_add_required_fields();
		$output .= $this->get_display_comment_form_privacy_notice();

		return $output;
	}

	/**
	 * Test CF7 message for spam
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.1.1
	 *
	 * @param bool $param
	 *
	 * @return bool
	 */
	public function check_spam( $param ) {
		if ( is_user_logged_in() ) {
			return $param;
		}

		\WBCR\Titan\Logger\Writter::info( "Starts check spam for Contact form 7 [PROCESS START]." );

		if ( $this->check_spam_by_honeypot() ) {
			return WPCF7_VERSION >= '3.0.0' ? true : false;
		}

		// is js support?
		$is_js_enable = \WBCR\Titan\Plugin::app()->request->post( "wantispam_d" );

		$start_form_filling = (int) \WBCR\Titan\Plugin::app()->request->post( "wantispam_t", 0 );
		$start_form_filling = time() - $start_form_filling;

		$temp_msg_data = $this->get_fields_any( $_POST );

		$sender_email    = ( $temp_msg_data['email'] ? $temp_msg_data['email'] : '' );
		$sender_nickname = ( $temp_msg_data['nickname'] ? $temp_msg_data['nickname'] : '' );
		$subject         = ( $temp_msg_data['subject'] ? $temp_msg_data['subject'] : '' );
		//$contact_form    = ( $temp_msg_data['contact'] ? $temp_msg_data['contact'] : true );
		$message = ( $temp_msg_data['message'] ? $temp_msg_data['message'] : [] );

		if ( $subject != '' ) {
			$message['subject'] = $subject;
		}

		$items = [
			[
				'uid'           => wantispamp_generate_uid(), // todo: удалить после фикса бага на сервере
				'email'         => $sender_email,
				'ip'            => wantispamp_get_ip(),
				'text'          => $message,
				'username'      => $sender_nickname,
				'headers'       => wantispamp_get_all_headers(),
				'referrer'      => $_SERVER['HTTP_REFERER'],
				'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
				'js_on'         => date( 'Y' ) == $is_js_enable,
				'submit_time'   => $start_form_filling,
				'without_queue' => true
			]
		];

		\WBCR\Titan\Logger\Writter::info( sprintf( "Prepared params to send: %s", var_export( $items, true ) ) );

		$request = $this->cm_api->check_spam( $items );

		if ( ! is_wp_error( $request ) && $items[0]['uid'] === $request->response[0]->uid ) {
			$spam = ( 'done' === $request->response[0]->status ) ? wantispamp_normalize_bool( $request->response[0]->spam ) : false;

			if ( true === $spam ) {
				add_filter( 'wpcf7_display_message', 'show_response', 10, 2 );
				$param = WPCF7_VERSION >= '3.0.0' ? true : false;
				\WBCR\Titan\Logger\Writter::warning( 'Message marked as spam! ' );
			}
		} else {
			\WBCR\Titan\Logger\Writter::error( $request->get_error_message() );
			\WBCR\Titan\Logger\Writter::info( sprintf( "Server responsed: %s", var_export( $request, true ) ) );
		}

		\WBCR\Titan\Logger\Writter::info( "Stops check spam for Contact form 7 [PROCESS END]." );

		return $param;
	}
}

new Contact_Form7();






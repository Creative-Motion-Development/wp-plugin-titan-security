<?php

namespace WBCR\Titan\Premium;

/**
 * Ninja Forms plugin integration
 *
 * It stops spam in the Ninja Forms plugin.
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>, Github: https://github.com/temyk
 * @copyright (c) 24.12.2019, Webcraftic
 * @version       1.0
 */

if ( ! class_exists( 'Ninja_Forms' ) ) {
	return;
}

class Ninja_Forms extends Extension {

	public function __construct() {
		parent::__construct();

		add_filter( 'ninja_forms_display_after_fields', [ $this, 'form_elements' ], 10, 2 );
		add_filter( 'ninja_forms_submit_data', [ $this, 'check_spam' ], 10, 1 );
	}

	/**
	 * @since  1.1.1
	 *
	 * @param string $output
	 *
	 * @return string
	 */
	public function form_elements( $is_preview, $form_id ) {
		return $this->get_display_comment_form_privacy_notice();
	}

	/**
	 * Test message for spam
	 *
	 * @since  1.1.1
	 *
	 * @param array $form_data
	 *
	 * @return array
	 */
	public function check_spam( $form_data ) {

		$form_data = $this->convert_data_to_array( $form_data );
		\WBCR\Titan\Logger\Writter::info( "Starts check spam for Ninja Forms [PROCESS START]." );

		$temp_msg_data = $this->get_fields_any( $form_data );

		$sender_email    = ( $temp_msg_data['email'] ? $temp_msg_data['email'] : '' );
		$sender_nickname = ( $temp_msg_data['nickname'] ? $temp_msg_data['nickname'] : '' );
		$subject         = ( $temp_msg_data['subject'] ? $temp_msg_data['subject'] : '' );
		$message         = ( $temp_msg_data['message'] ? $temp_msg_data['message'] : [] );

		if ( $subject != '' ) {
			$message['subject'] = $subject;
		}

		$items = [
			[
				'uid'           => wantispamp_generate_uid(),
				'email'         => $sender_email,
				'ip'            => wantispamp_get_ip(),
				'text'          => $message,
				'username'      => $sender_nickname,
				'headers'       => wantispamp_get_all_headers(),
				'referrer'      => $_SERVER['HTTP_REFERER'],
				'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
				//'js_on'         => $is_js_enable,
				//'submit_time'   => $start_form_filling,
				//Считаем что JS включен и время заплнения формы достаточное
				'js_on'         => true,
				'submit_time'   => 9999999,
				'without_queue' => true
			]
		];

		\WBCR\Titan\Logger\Writter::info( sprintf( "Prepared params to send: %s", var_export( $items, true ) ) );

		$request = $this->cm_api->check_spam( $items );

		if ( ! is_wp_error( $request ) && $items[0]['uid'] === $request->response[0]->uid ) {
			$spam = ( 'done' === $request->response[0]->status ) ? wantispamp_normalize_bool( $request->response[0]->spam ) : false;

			if ( true === $spam ) {
				\WBCR\Titan\Logger\Writter::warning( 'Message marked as spam! ' );

				//Don't save submission
				add_filter( 'ninja_forms_run_action_type_save', '__return_false' );
				//Don't email about spam
				add_filter( 'ninja_forms_run_action_type_email', '__return_false' );
			}
		} else {
			\WBCR\Titan\Logger\Writter::error( $request->get_error_message() );
			\WBCR\Titan\Logger\Writter::info( sprintf( "Server responsed: %s", var_export( $request, true ) ) );
		}

		\WBCR\Titan\Logger\Writter::info( "Stops check spam for Ninja Forms [PROCESS END]." );

		return $form_data;
	}

	/**
	 * Convert $form_data to array(key => value)
	 *
	 * @since  1.1.1
	 *
	 * @param array $form_data
	 *
	 * @return array
	 */
	public function convert_data_to_array( $form_data ) {
		$result = [];
		foreach ( $form_data['fields'] as $field ) {
			$result[ $field['key'] ] = $field['value'];
		}

		return $result;
	}
}

new Ninja_Forms();






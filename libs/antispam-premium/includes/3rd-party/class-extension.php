<?php

namespace WBCR\Titan\Premium;

/**
 * Base class for create integration
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 19.12.2019, Webcraftic
 * @version       1.0
 */

// Contact Form7
if ( ! defined( 'WPCF7_VERSION' ) ) {
	return;
}

class Extension {

	public function __construct() {
		$this->plugin = \WBCR\Titan\Plugin::app();
		$this->cm_api = new Api\Request();

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );
	}

	/**
	 * We enqueue js script required for the plugin to work. The script overwrites the values
	 * of hidden fields or determines whether the user uses javascript or not.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.1.1
	 */
	public function enqueue_script() {
		wp_enqueue_script( 'anti-spam-script' );
	}

	/**
	 * Inserts anti-spam hidden fields
	 *
	 * @param string $render_honeypot_fields
	 *
	 * @return string
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.1.1
	 *
	 */
	public function get_add_required_fields( $render_honeypot_fields = true ) {
		return wantispam_get_required_fields( $render_honeypot_fields );
	}

	/**
	 * Get the privacy related notice underneath the comment form.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.1.1
	 */
	public function get_display_comment_form_privacy_notice() {
		return wantispam_display_comment_form_privacy_notice();
	}

	/**
	 * Print the privacy related notice underneath the comment form.
	 */
	public function print_display_comment_form_privacy_notice() {
		wantispam_display_comment_form_privacy_notice( true );
	}

	/**
	 * Gets honeypot fields.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.1.1
	 */
	public function get_honeypot_fields() {
		return wantispam_get_honeypot_fields();
	}

	/**
	 * Checks spam by honeypot
	 *
	 * @return bool
	 * @since  1.1.1
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	public function check_spam_by_honeypot() {
		$spam_flag = false;

		$antspm_q = $this->plugin->request->post( "wantispam_q", '', 'trim' );
		$antspm_d = $this->plugin->request->post( "wantispam_d", '', 'trim' );
		$antspm_e = $this->plugin->request->post( "wantispam_e_email_url_website", '', 'trim' );

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


	/**
	 * Changes CF7 status message
	 *
	 * @param string $hook URL of hooked page
	 *
	 * @since  1.1.1
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	public function show_response() {
		return __( 'Your message has been marked as spam!', 'titan-security' );
	}

	/*
	* Get data from an ARRAY recursively
	*
	* @return array
	*/
	protected function get_fields_any( $arr, $message = [], $email = null, $nickname = [], $subject = null, $contact = true, $prev_name = '' ) {
		if ( empty( $nickname ) ) {
			$nickname = [
				'nick'  => '',
				'first' => '',
				'last'  => ''
			];
		}

		//Skip request if fields exists
		$skip_params = [
			'ipn_track_id',    // PayPal IPN #
			'txn_type',        // PayPal transaction type
			'payment_status',    // PayPal payment status
			'ccbill_ipn',        // CCBill IPN
			'wantispam_j',        // skip wantispam-j field
			'wantispam_t',        // skip wantispam-j field
			'api_mode',         // DigiStore-API
			'loadLastCommentId' // Plugin: WP Discuz. ticket_id=5571
		];

		// Fields to replace with ****
		$obfuscate_params = [
			'password',
			'pass',
			'pwd',
			'pswd'
		];

		// Skip feilds with these strings and known service fields
		$skip_fields_with_strings = [
			// Common
			'wantispam_j', //Do not send wantispam-j
			'wantispam_t', //Do not send wantispam-j
			'nonce', //nonce for strings such as 'rsvp_nonce_name'
			'security',
			// 'action',
			'http_referer',
			'referer-page',
			'timestamp',
			'captcha',
			// Formidable Form
			'form_key',
			'submit_entry',
			// Custom Contact Forms
			'form_id',
			'ccf_form',
			'form_page',
			// Qu Forms
			'iphorm_uid',
			'form_url',
			'post_id',
			'iphorm_ajax',
			'iphorm_id',
			// Fast SecureContact Froms
			'fs_postonce_1',
			'fscf_submitted',
			'mailto_id',
			'si_contact_action',
			// Ninja Forms
			'formData_id',
			'formData_settings',
			'formData_fields_\d+_id',
			'formData_fields_\d+_files.*',
			// E_signature
			'recipient_signature',
			'output_\d+_\w{0,2}',
			// Contact Form by Web-Settler protection
			'_formId',
			'_returnLink',
			// Social login and more
			'_save',
			'_facebook',
			'_social',
			'user_login-',
			// Contact Form 7
			'_wpcf7',
			'ebd_settings',
			'ebd_downloads_',
			'ecole_origine',
			// Caldera Forms
			'submit',
		];

		// Reset $message if we have a sign-up data
		$skip_message_post = [
			'edd_action', // Easy Digital Downloads
		];

		if ( wantispamp_array( [ $_POST, $_GET ] )->get_keys( $skip_params )->result() ) {
			$contact = false;
		}

		if ( count( $arr ) ) {
			foreach ( $arr as $key => $value ) {
				if ( gettype( $value ) == 'string' ) {
					$tmp                = strpos( $value, '\\' ) !== false ? stripslashes( $value ) : $value;
					$decoded_json_value = json_decode( $tmp, true );

					// Decoding JSON
					if ( $decoded_json_value !== null ) {
						$value = $decoded_json_value;

						// Ajax Contact Forms. Get data from such strings:
						// acfw30_name %% Blocked~acfw30_email %% s@cleantalk.org
						// acfw30_textarea %% msg
					} else if ( preg_match( '/^\S+\s%%\s\S+.+$/', $value ) ) {
						$value = explode( '~', $value );
						foreach ( $value as &$val ) {
							$tmp = explode( ' %% ', $val );
							$val = [ $tmp[0] => $tmp[1] ];
						}
					}
				}

				if ( ! is_array( $value ) && ! is_object( $value ) ) {

					if ( in_array( $key, $skip_params, true ) && $key != 0 && $key != '' || preg_match( "/^wantispam-(j|t)/", $key ) ) {
						$contact = false;
					}

					if ( $value === '' ) {
						continue;
					}

					// Skipping fields names with strings from (array)skip_fields_with_strings
					foreach ( $skip_fields_with_strings as $needle ) {
						if ( preg_match( "/" . $needle . "/", $prev_name . $key ) == 1 ) {
							continue( 2 );
						}
					}
					unset( $needle );

					// Obfuscating params
					foreach ( $obfuscate_params as $needle ) {
						if ( strpos( $key, $needle ) !== false ) {
							$value = wantispamp_obfuscate_param( $value );
							continue( 2 );
						}
					}
					unset( $needle );

					// Removes whitespaces
					$value           = urldecode( trim( strip_shortcodes( $value ) ) ); // Fully cleaned message
					$value_for_email = trim( strip_shortcodes( $value ) );    // Removes shortcodes to do better spam filtration on server side.

					// Email
					if ( ! $email && preg_match( "/^\S+@\S+\.\S+$/", $value_for_email ) ) {
						$email = $value_for_email;
						// Names
					} else if ( preg_match( "/name/i", $key ) ) {

						preg_match( "/((name.?)?(your|first|for)(.?name)?)/", $key, $match_forename );
						preg_match( "/((name.?)?(last|family|second|sur)(.?name)?)/", $key, $match_surname );
						preg_match( "/(name.?)?(nick|user)(.?name)?/", $key, $match_nickname );

						if ( count( $match_forename ) > 1 ) {
							$nickname['first'] = $value;
						} else if ( count( $match_surname ) > 1 ) {
							$nickname['last'] = $value;
						} else if ( count( $match_nickname ) > 1 ) {
							$nickname['nick'] = $value;
						} else {
							$message[ $prev_name . $key ] = $value;
						}
						// Subject
					} else if ( $subject === null && preg_match( "/subject/i", $key ) ) {
						$subject = $value;
						// Message
					} else {
						$message[ $prev_name . $key ] = $value;
					}
				} else if ( ! is_object( $value ) ) {

					$prev_name_original = $prev_name;
					$prev_name          = ( $prev_name === '' ? $key . '_' : $prev_name . $key . '_' );

					$temp = $this->get_fields_any( $value, $message, $email, $nickname, $subject, $contact, $prev_name );

					$message  = $temp['message'];
					$email    = ( $temp['email'] ? $temp['email'] : null );
					$nickname = ( $temp['nickname'] ? $temp['nickname'] : null );
					$subject  = ( $temp['subject'] ? $temp['subject'] : null );
					if ( $contact === true ) {
						$contact = ( $temp['contact'] === false ? false : true );
					}
					$prev_name = $prev_name_original;
				}
			}
			unset( $key, $value );
		}

		foreach ( $skip_message_post as $v ) {
			if ( isset( $_POST[ $v ] ) ) {
				$message = null;
				break;
			}
		}
		unset( $v );

		//If top iteration, returns compiled name field. Example: "Nickname Firtsname Lastname".
		if ( $prev_name === '' ) {
			if ( ! empty( $nickname ) ) {
				$nickname_str = '';
				foreach ( $nickname as $value ) {
					$nickname_str .= ( $value ? $value . " " : "" );
				}
				unset( $value );
			}
			$nickname = $nickname_str;
		}

		$return_param = [
			'email'    => $email,
			'nickname' => $nickname,
			'subject'  => $subject,
			'contact'  => $contact,
			'message'  => $message
		];

		return $return_param;
	}
}





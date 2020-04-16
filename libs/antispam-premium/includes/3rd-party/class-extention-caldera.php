<?php

namespace WBCR\Titan\Premium;

use Caldera_Forms;

/**
 * Caldera Forms plugin integration
 *
 * It stops spam in the Caldera Forms plugin.
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>, Github: https://github.com/temyk
 * @copyright (c) 26.12.2019, Webcraftic
 * @version       1.0
 */

if ( ! defined( 'CFCORE_VER' ) ) {
	return;
}

class Caldera extends Extension {

	/**
	 * {@inheritDoc}
	 *
	 * @since  1.1.1
	 * @var string
	 */
	protected $prefix = 'caldera';

	public function __construct() {
		parent::__construct();

		add_filter( 'caldera_forms_render_form', [ $this, 'form_elements' ], 10, 2 );

		//Register processor for Caldera
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'add_processor' ], 10, 1 );
	}

	function add_processor( $processors ) {
		$processors['anti-spam-processor'] = array(
			'name'          => 'Anti-Spam',
			'description'   => 'Check Caldera Forms Data to spam',
			'pre_processor' => [ $this, 'check_spam' ],
			"template"      => CFCORE_PATH . "processors/akismet/config.php",
			"single"        => false,
		);

		return $processors;
	}

	/**
	 * @param string $output
	 *
	 * @return string
	 * @since  1.1.1
	 *
	 */
	public function form_elements( $out, $form ) {

		$fields = $this->get_add_required_fields( false ) . $this->get_display_comment_form_privacy_notice();
		$out    = str_replace( "</form>", $fields . "</form>", $out );

		return $out;
	}

	/**
	 * Test message for spam
	 *
	 * @param array $config
	 * @param array $form_data
	 * @param int $process_id
	 *
	 * @return array|void
	 * @since  1.1.1
	 *
	 */
	public function check_spam( $config, $form, $process_id ) {

		// is js support?
		$is_js_enable = \WBCR\Titan\Plugin::app()->request->post( "wantispam_d" );

		$start_form_filling = (int) \WBCR\Titan\Plugin::app()->request->post( "wantispam_t", 0 );
		$start_form_filling = time() - $start_form_filling;

		$author  = Caldera_Forms::do_magic_tags( $config['sender_name'] );
		$email   = Caldera_Forms::do_magic_tags( $config['sender_email'] );
		$url     = Caldera_Forms::do_magic_tags( $config['url'] );
		$message = Caldera_Forms::do_magic_tags( $config['content'] );

		$items = [
			[
				'uid'           => wantispamp_generate_uid(),
				'email'         => $email,
				'ip'            => wantispamp_get_ip(),
				'text'          => $message,
				'username'      => $author,
				'url'           => $url,
				'headers'       => wantispamp_get_all_headers(),
				'referrer'      => $_SERVER['HTTP_REFERER'],
				'user_agent'    => $_SERVER['HTTP_USER_AGENT'],
				'js_on'         => $is_js_enable,
				'submit_time'   => $start_form_filling,
				'without_queue' => true
			]
		];

		\WBCR\Titan\Logger\Writter::info( sprintf( "Prepared params to send: %s", var_export( $items, true ) ) );

		$request = $this->cm_api->check_spam( $items );

		if ( ! is_wp_error( $request ) && $items[0]['uid'] === $request->response[0]->uid ) {
			$spam = ( 'done' === $request->response[0]->status ) ? wantispamp_normalize_bool( $request->response[0]->spam ) : false;

			if ( true === $spam ) {
				\WBCR\Titan\Logger\Writter::warning( 'Message marked as spam! ' );

				return array(
					'note' => 'Message marked as spam! ',
					'type' => 'error'
				);
			}
		} else {
			\WBCR\Titan\Logger\Writter::error( $request->get_error_message() );
			\WBCR\Titan\Logger\Writter::info( sprintf( "Server responsed: %s", var_export( $request, true ) ) );
		}

		\WBCR\Titan\Logger\Writter::info( "Stops check spam for Caldera Forms [PROCESS END]." );

		return;
	}

}

new Caldera();
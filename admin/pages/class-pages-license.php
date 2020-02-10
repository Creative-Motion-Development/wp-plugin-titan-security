<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Страница лицензирования плагина.
 *
 * Поддерживает режим работы с мультисаймами. Вы можете увидеть эту страницу в панели настройки сети.
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 *
 * @copyright (c) 2018 Webraftic Ltd
 */
class License extends \Wbcr_FactoryClearfy000_LicensePage {

	/**
	 * {@inheritdoc}
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 * @var string
	 */
	public $id = 'license';

	/**
	 * {@inheritdoc}
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 * @var string
	 */
	public $page_parent_page;

	/**
	 * WCL_LicensePage constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->menu_title                  = __( 'License', 'anti-spam' );
		$this->page_menu_short_description = __( 'Product activation', 'anti-spam' );
		$this->plan_name                   = __( 'Titan security Pro', 'anti-spam' );

		parent::__construct( $plugin );

		add_action( 'admin_footer', [ $this, 'print_confirmation_modal_tpl' ] );
	}

	/**
	 * {@inheritDoc}
	 * @param                         $notices
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @return array
	 * @since 6.5.2
	 *
	 * @see   \FactoryPages000_ImpressiveThemplate
	 */
	public function getActionNotices( $notices ) {

		$notices[] = [
			'conditions' => [
				'wantispam_trial_activated' => 1
			],
			'type'       => 'success',
			'message'    => __( 'Trial is activated successfully!', 'anti-spam' )
		];

		$notices[] = [
			'conditions' => [
				'wantispam_trial_activated_error' => 1,
				'wantispam_error_code'            => 'interal_error'
			],
			'type'       => 'danger',
			'message'    => __( 'An unknown error occurred during trial activation. Details of the error are wrote in error log.', 'anti-spam' )
		];

		$notices[] = [
			'conditions' => [
				'wantispam_trial_activated_error' => 1,
				'wantispam_error_code'            => 'trial_already_activated'
			],
			'type'       => 'danger',
			'message'    => sprintf( __( 'You have already activated the trial earlier, you cannot activate the trial more than once. However, if your key has not expired yet, you can find it in your account (<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>), and then insert the key in the license form to activate the premium plugin. To restore access to your account, use your admin email.', 'anti-spam' ), 'https://users.freemius.com/login', 'https://users.freemius.com/login' )
		];

		return $notices;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 6.5.2
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		if ( ! $this->plugin->premium->is_activate() ) {
			$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/libs/sweetalert2.css' );
			$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/sweetalert-custom.css' );

			$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/libs/sweetalert3.min.js' );
			$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/trial-popup.js' );
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 6.5.2
	 */
	public function print_confirmation_modal_tpl() {
		if ( isset( $_GET['page'] ) && $this->getResultId() === $_GET['page'] ) {
			$terms_url   = "https://anti-spam.space/terms-of-use/";
			$privacy_url = "https://anti-spam.space/privacy/";

			?>
            <script type="text/html" id="wantispam-tmpl-confirmation-modal">
                <h2 class="swal2-title">
					<?php _e( 'Confirmation', 'anti-spam' ) ?>
                </h2>
                <div class="wantispam-swal-content">
                    <ul class="wantispam-list-infos">
                        <li>
							<?php _e( 'We are using some personal data, like admin\'s e-mail', 'anti-spam' ) ?>
                        </li>
                        <li>
							<?php printf( __( 'By agreeing to the trial, you confirm that you have read <a href="%s" target="_blank" rel="noreferrer noopener">Terms of Service</a> and the
           					 <a href="%s" target="_blank" rel="noreferrer noopener">Privacy Policy (GDPR compilant)</a>', 'anti-spam' ), $terms_url, $privacy_url ) ?>
                        </li>
                    </ul>
                </div>
            </script>
			<?php
		}
	}


	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 * @since  6.5
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	public function get_plan_description() {
		$activate_trial_url = wp_nonce_url( $this->getActionUrl( 'activate-trial' ), 'activate_trial' );

		$description = '<p style="font-size: 16px;">' . __( '<b>Anti-spam PRO</b> is a paid package of components for the popular free WordPress plugin named Anti-spam PRO. You get access to all paid components at one price.', 'clearfy' ) . '</p>';
		$description .= '<p style="font-size: 16px;">' . __( 'Paid license guarantees that you can download and update existing and future paid components of the plugin.', 'clearfy' ) . '</p>';

		if ( ! $this->plugin->premium->is_activate() ) {
			$description .= '<p>The free trial edition (no credit card) contains all of the features included in the paid-for version
                    of the product.</p>';
			$description .= '<a href="" data-url="' . esc_url( $activate_trial_url ) . '" id="js-wantispam-activate-trial-button" class="button button-default">' . __( 'Activate 30 days trial', 'anti-spam' ) . '</a>';
		}

		return $description;
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.6
	 */
	public function activateTrialAction() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'activate_trial' );

		\WBCR\Logger\writter::info( 'Start trial activation [PROCESS START]!' );

		$admin_email = get_option( 'admin_email' );
		$domain      = site_url();

		//$url     = 'https://dev.anti-spam.space/api/v1.0/trial/register';
		$url = 'https://api.anti-spam.space/api/v1.0/trial/register';

		$options = [
			'body' => [
				'email'  => $admin_email,
				'domain' => site_url(),
			]
		];

		// Get license key from remote server
		$request = wp_remote_post( $url, $options );

		if ( is_wp_error( $request ) ) {
			\WBCR\Logger\writter::error( 'Http request error: ' . $request->get_error_message() );
			\WBCR\Logger\writter::info( 'End trial activation [PROCESS END]!' );
			$this->redirectToAction( 'index', [
				'wantispam_trial_activated_error' => 1,
				'wantispam_error_code'            => 'interal_error'
			] );
		}

		$data = json_decode( $request['body'], true );

		if ( $data['status'] == 'fail' ) {
			if ( ! empty( $data['error'] ) ) {
				$message = $data['error']['message'];
				\WBCR\Logger\writter::error( sprintf( 'Trial activation failed for domain: %s, e-mail: %s with message: %s', $domain, $admin_email, $message ) );
				\WBCR\Logger\writter::info( 'End trial activation [PROCESS END]!' );

				if ( ! empty( $data['error']['code'] ) && 1001 === $data['error']['code'] ) {
					$this->redirectToAction( 'index', [
						'wantispam_trial_activated_error' => 1,
						'wantispam_error_code'            => 'trial_already_activated'
					] );
				} else {
					$this->redirectToAction( 'index', [
						'wantispam_trial_activated_error' => 1,
						'wantispam_error_code'            => 'interal_error'
					] );
				}
			}
		}

		$license_key = $data['response']['license_key'];

		if ( empty( $license_key ) || 32 !== strlen( $license_key ) ) {
			\WBCR\Logger\writter::error( 'License key format is not valid' );
			\WBCR\Logger\writter::info( 'End trial activation [PROCESS END]!' );
			$this->redirectToAction( 'index', [
				'wantispam_trial_activated_error' => 1,
				'wantispam_error_code'            => 'interal_error'
			] );
		}

		try {
			$this->plugin->premium->activate( $license_key );

			\WBCR\Logger\writter::info( sprintf( 'Trial activation success for domain: %s, e-mail: %s', $domain, $admin_email ) );
			\WBCR\Logger\writter::info( 'End trial activation [PROCESS END]!' );
			$this->redirectToAction( 'index', [
				'wantispam_trial_activated' => 1
			] );
		} catch( \Exception $e ) {
			\WBCR\Logger\writter::error( $e->getMessage() );
			\WBCR\Logger\writter::info( 'End trial activation [PROCESS END]!' );

			$this->redirectToAction( 'index', [
				'wantispam_trial_activated_error' => 1,
				'wantispam_error_code'            => 'interal_error'
			] );
		}

		// Redirect to index
		$this->redirectToAction( 'index' );
		\WBCR\Logger\writter::info( 'End trial activation [PROCESS END]!' );
	}
}
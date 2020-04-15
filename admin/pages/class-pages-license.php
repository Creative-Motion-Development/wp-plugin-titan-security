<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
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
	 */
	public $type = "page";

	/**
	 * {@inheritdoc}
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 * @var string
	 */
	public $page_parent_page;

	/**
	 * {@inheritdoc}
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  2.1.2
	 * @var int
	 */
	public $page_menu_position = 0;

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-admin-network';

	/**
	 * WCL_LicensePage constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->menu_title = __('License', 'titan-security');
		$this->page_menu_short_description = __('Product activation', 'titan-security');
		$this->plan_name = __('Titan security Pro', 'titan-security');
		$this->menuIcon = WTITAN_PLUGIN_URL . '/admin/assets/img/titan-icon.png';

		if($plugin->is_premium()) $this->page_menu_dashicon = 'dashicons-yes-alt';

		parent::__construct($plugin);

		add_action('admin_footer', [$this, 'print_confirmation_modal_tpl']);
		add_action('wp_ajax_wtitan_activate_trial', array($this, 'activate_trial'));
	}

	public function getPluginTitle()
	{
		return "<span class='wt-plugin-header-logo'>&nbsp;</span>" . __('Titan security', 'titan-security');
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
	public function getActionNotices($notices)
	{

		$notices[] = [
			'conditions' => [
				'wtitan_trial_activated' => 1
			],
			'type' => 'success',
			'message' => __('Trial is activated successfully!', 'titan-security')

		];

		$notices[] = [
			'conditions' => [
				'wtitan_trial_activated_error' => 1,
				'wtitan_error_code' => 'interal_error'
			],
			'type' => 'danger',
			'message' => __('An unknown error occurred during trial activation. Details of the error are wrote in error log.', 'titan-security')

		];

		$notices[] = [
			'conditions' => [
				'wtitan_trial_activated_error' => 1,
				'wtitan_error_code' => 'trial_already_activated'
			],
			'type' => 'danger',
			'message' => sprintf(__('You have already activated the trial earlier, you cannot activate the trial more than once. However, if your key has not expired yet, you can find it in your account (<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>), and then insert the key in the license form to activate the premium plugin. To restore access to your account, use your admin email.', 'titan-security'), 'https://users.freemius.com/login', 'https://users.freemius.com/login')
		];

		return $notices;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 6.5.2
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/titan-security.css');
		if( !$this->plugin->premium->is_activate() ) {
			$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/libs/sweetalert2.css');
			$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/sweetalert-custom.css');

			$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/libs/sweetalert3.min.js');
			$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/trial-popup.js');
			$this->scripts->localize('wtitan', [
				'trial_nonce' => wp_create_nonce("activate_trial"),
			]);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 6.5.2
	 */

	public function print_confirmation_modal_tpl()
	{
		if( isset($_GET['page']) && $this->getResultId() === $_GET['page'] ) {
			$terms_url = "https://titansitescanner.com/terms-of-use/";
			$privacy_url = "https://titansitescanner.com/privacy/";

			?>
			<script type="text/html" id="wtitan-tmpl-confirmation-modal">
				<h2 class="swal2-title">
					<?php _e('Confirmation', 'titan-security') ?>
				</h2>
				<div class="wtitan-swal-content">
					<ul class="wtitan-list-infos" style="padding: 5px 20px;">
						<li>
							<?php _e('We are using some personal data, like your\'s e-mail.', 'titan-security') ?>
						</li>
						<li>
							<?php printf(__('By agreeing to the trial, you confirm that you have read <a href="%s" target="_blank" rel="noreferrer noopener">Terms of Service</a> and the
           					 <a href="%s" target="_blank" rel="noreferrer noopener">Privacy Policy (GDPR compilant)</a>', 'titan-security'), $terms_url, $privacy_url) ?>
						</li>
						<li>
							<label for="wtitan-trial-email">Enter your E-mail:</label>
							<input type="text" style="margin-top: 15px; padding: 5px;" value="<?php echo get_option('admin_email'); ?>" id="wtitan-trial-email">
							<?php
							//_e( 'We are using some personal data, like your\'s e-mail.', 'titan-security' );
							?>
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
	public function get_plan_description()
	{
		$activate_trial_url = wp_nonce_url($this->getActionUrl('activate-trial'), 'activate_trial');

		$description = "";
		//		$description = '<p style="font-size: 16px;">' . __( '<b>Anti-spam PRO</b> is a paid package of components for the popular free WordPress plugin named Anti-spam PRO. You get access to all paid components at one price.', 'titan-security' ) . '</p>';
		//		$description .= '<p style="font-size: 16px;">' . __( 'Paid license guarantees that you can download and update existing and future paid components of the plugin.', 'titan-security' ) . '</p>';
		if( !$this->plugin->premium->is_activate() ) {
			$description .= '<p>The free trial edition (no credit card) contains all of the features included in the paid-for version
                    of the product.</p>';
			$description .= '<button id="wtitan-activate-trial-button" class="btn btn-primary">' . __('Activate 30 days trial', 'titan-security') . '</button>';
			$description .= "<span class='wt-spinner'></span>";
		}

		return $description;
	}

    /**
     * @author Egor Semenkov <highskil395472@gmail.ru>
     * @since  7.0.8
     */
    public function render_learnmore_section()
    {
        if( $this->is_premium ):
            ?>
            <p style="margin-top: 10px;">
                <?php printf(__('<a href="%s" target="_blank" rel="noopener">Lean more</a> about the premium version and get the license key to activate it now!', 'wbcr_factory_clearfy_000'), $this->plugin->get_support()->get_pricing_url(true, 'license_page')); ?>
            </p>
        <?php else: ?>
            <p style="margin-top: 10px;">
                <?php printf(__('Can’t find your key? Go to <a href="%s" target="_blank" rel="noopener">this page</a> and login using the e-mail address associated with your purchase.', 'wbcr_factory_clearfy_000'), "https://users.freemius.com/") ?>
            </p>
            <p style="margin-top: 10px;">
                <?php printf(__('We use certain personal data, such as your email address. By activating the license, you confirm that you have read the <a href="https://titansitescanner.com/terms-of-use/">Terms of Service</a> and <a href="https://titansitescanner.com/privacy/">Privacy Policy (in accordance with GDPR)</a>', 'wbcr_factory_clearfy_000'), $this->plugin->get_support()->get_contacts_url(true, 'license_page')) ?>
            </p>
        <?php endif;
    }

	/**
	 * @author Artem Prihodko <webtemyk@yandex.ru>
	 * @since  7.0
	 */

	public function activate_trial()
	{
		if( !current_user_can('manage_options') ) {
			return;
		}

		if( !isset($_POST['email']) || empty($_POST['email']) ) {
			return;
		}

		check_ajax_referer('activate_trial');

		\WBCR\Titan\Logger\Writter::info('Start trial activation [PROCESS START]!');

		$admin_email = $_POST['email'];
		$domain = site_url();

		//$url     = 'https://dev.anti-spam.space/api/v1.0/trial/register';
		$url = 'https://api.anti-spam.space/api/v1.0/trial/register';

		$options = [
			'body' => [
				'email' => $admin_email,
				'domain' => site_url(),
			]
		];

		// Get license key from remote server

		$request = wp_remote_post($url, $options);

		if( is_wp_error($request) ) {
			\WBCR\Titan\Logger\Writter::error('Http request error: ' . $request->get_error_message());
			\WBCR\Titan\Logger\Writter::info('End trial activation [PROCESS END]!');

			wp_send_json_error([
				'url' => $this->getPageUrl() . "&wtitan_trial_activated_error=1&wtitan_error_code=interal_error",
			]);
		}

		$data = json_decode($request['body'], true);

		if( $data['status'] == 'fail' ) {
			if( !empty($data['error']) ) {
				$message = $data['error']['message'];

				\WBCR\Titan\Logger\Writter::error(sprintf('Trial activation failed for domain: %s, e-mail: %s with message: %s', $domain, $admin_email, $message));
				\WBCR\Titan\Logger\Writter::info('End trial activation [PROCESS END]!');

				if( !empty($data['error']['code']) && 1001 === $data['error']['code'] ) {
					wp_send_json_error([
						'url' => $this->getPageUrl() . "&wtitan_trial_activated_error=1&wtitan_error_code=trial_already_activated",
					]);
				} else {
					wp_send_json_error([
						'url' => $this->getPageUrl() . "&wtitan_trial_activated_error=1&wtitan_error_code=interal_error",
					]);
				}
			}
		}

		$license_key = $data['response']['license_key'];

		if( empty($license_key) || 32 !== strlen($license_key) ) {
			\WBCR\Titan\Logger\Writter::error('License key format is not valid');
			\WBCR\Titan\Logger\Writter::info('End trial activation [PROCESS END]!');
			wp_send_json_error([
				'url' => $this->getPageUrl() . "&wtitan_trial_activated_error=1&wtitan_error_code=interal_error",
			]);
		}

		try {
			$this->plugin->premium->activate($license_key);

			\WBCR\Titan\Logger\Writter::info(sprintf('Trial activation success for domain: %s, e-mail: %s', $domain, $admin_email));
			\WBCR\Titan\Logger\Writter::info('End trial activation [PROCESS END]!');

			wp_send_json_error([
				'url' => $this->getPageUrl() . "&wtitan_trial_activated=1",
			]);
		} catch( \Exception $e ) {
			\WBCR\Titan\Logger\Writter::error($e->getMessage());
			\WBCR\Titan\Logger\Writter::info('End trial activation [PROCESS END]!');

			wp_send_json_error([
				'url' => $this->getPageUrl() . "&wtitan_trial_activated_error=1&wtitan_error_code=interal_error",
			]);
		}

		\WBCR\Titan\Logger\Writter::info('End trial activation [PROCESS END]!');

		// Redirect to index
		wp_send_json_error([
			'url' => $this->getPageUrl(),
		]);
	}
}
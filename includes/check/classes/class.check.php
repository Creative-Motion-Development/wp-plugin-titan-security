<?php
namespace WBCR\Titan;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WBCR\Titan\Client\Client;
use WBCR\Titan\Client\Request\SetNoticeData;

/**
 * The file contains a short help info.
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Check extends Module_Base {

	/**
	 * Vulnerabilities object
	 *
	 * @var Vulnerabilities
	 */
	public $vulnerabilities;

	/**
	 * Audit object
	 *
	 * @var Audit
	 */
	public $audit;

	/**
	 * Vulnerabilities_API constructor.
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->module_dir = WTITAN_PLUGIN_DIR."/includes/check";
		$this->module_url = WTITAN_PLUGIN_URL."/includes/check";
		$this->vulnerabilities = new Vulnerabilities();
		$this->audit = new Audit();

		add_action( 'wp_ajax_wtitan_scanner_hide', array( $this, 'hide_issue' ) );

		add_filter('wbcr/titan/adminbar_menu_title', function($title){
			$count = $this->get_count();
			return $title."<span class='wtitan-count-bubble'>{$count}</span>";
		});

		add_filter('wbcr/titan/admin_menu_title', function($title){
			$count = $this->get_count();
			return $title."<span class='update-plugins'><span class='plugin-count'>{$count}</span></span>";
		});
	}

	/**
	 * Get count of issues
	 *
	 * @return int
	 */
	public function get_count()
	{
		return (int)$this->vulnerabilities->get_count() + (int)$this->audit->get_count();
	}

	/**
	 * Show page content
	 */
	public function showPageContent() {
		$vuln_args = array(
			'wordpress' => $this->vulnerabilities->wordpress,
			'plugins'   => $this->vulnerabilities->plugins,
			'themes'    => $this->vulnerabilities->themes,
		);
		$content_vulner = $this->vulnerabilities->render_template( 'all-table', $vuln_args);

		$audit_args = array(
			'results' => $this->audit->get_audit(),
		);
		$content_audit = $this->audit->render_template( 'all-audit', $audit_args);

		$hided_args = array(
			'results' => $this->audit->get_hided(),
		);
		$content_hided = $this->render_template( 'hided', $hided_args);

		$args = array(
			'modules' => array(
				'hided' => array(
					'name' => '',
					'icon'    => 'dashicons-hidden',
					'content' => $content_hided,
				),
				'audit' => array(
					'name' => __('Security audit', 'titan-security'),
					'icon'    => 'dashicons-plugins-checked',
					'content' => $content_audit,
					'count'   => $this->audit->get_count(),
					'active'  => 'active',
				),
				'vulnerability' => array(
					'name'    => __('Vulnerabilities', 'titan-security'),
					'icon'    => 'dashicons-buddicons-replies',
					'content' => $content_vulner,
					'count'   => $this->vulnerabilities->get_count(),
				),
			),
			'active_modules' => "audit,vulnerability",
		);
		$script_args = array(
			'wtvulner' => array(
				'nonce' => wp_create_nonce('get_vulners'),
			),
		);
		echo $this->vulnerabilities->render_script('vulnerability_ajax.js', $script_args);

		$script_args = array(
			'wtaudit' => array(
				'nonce' => wp_create_nonce('get_audits'),
			),
		);
		echo $this->audit->render_script('audit_ajax.js', $script_args);

		echo $this->render_template( 'check', $args);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hide_issue() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 2 );
		} else {
			check_ajax_referer( 'hide' );

			if(isset($_POST['id']) && $_POST['id'] !== '' && isset($_POST['type']) && $_POST['type'] !== '') {
				$audit = $this->audit->get_audit();
				$hided = $this->audit->get_hided();
				$type = $_POST['type'];

				$hided[$type][] = $audit[$_POST['id']];
				unset($audit[$_POST['id']]);

				update_option( $this->plugin->getPrefix()."audit_results", $audit, 'no');
				update_option( $this->plugin->getPrefix()."audit_results_hided", $hided, 'no');
				$html = $this->render_template( 'hided', array(
					'results' => $hided,
				));
				wp_send_json_success(array(
					'html' => $html
				));
			}

			die();
		}
	}

}
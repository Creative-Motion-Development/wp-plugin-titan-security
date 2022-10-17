<?php

namespace WBCR\Titan;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
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
	 * Audit object
	 *
	 * @var Scanner
	 */
	public $scanner;

	/**
	 * Vulnerabilities_API constructor.
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$this->module_dir = WTITAN_PLUGIN_DIR . "/includes/check";
		$this->module_url = WTITAN_PLUGIN_URL . "/includes/check";
		$this->vulnerabilities = new Vulnerabilities();
		$this->audit = new Audit();
		$this->scanner = new Scanner();

		if( !has_action('wp_ajax_wtitan_scanner_hide') ) {
			add_action('wp_ajax_wtitan_scanner_hide', [$this, 'hide_issue']);
		}

		if( !has_filter('wbcr/titan/adminbar_menu_title') ) {
			add_filter('wbcr/titan/adminbar_menu_title', function ($title) {
				$count = $this->get_count();
				if( $count ) {
					return $title . "<span class='wtitan-count-bubble'>{$count}</span>";
				} else {
					return $title;
				}
			});
		}
		if( !has_filter('wbcr/titan/admin_menu_title') ) {
			add_filter('wbcr/titan/admin_menu_title', function ($title) {
				$count = $this->get_count();
				if( $count ) {
					return $title . "<span class='update-plugins'><span class='plugin-count'>{$count}</span></span>";
				} else {
					return $title;
				}
			});
		}
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
	 * Get page content
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function getPageContent($template = 'main')
	{
		ob_start();
		$this->showPageContent($template);
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	/**
	 * Show page content
	 *
	 * @param string $template
	 */
	public function showPageContent($template = 'main')
	{
		$vuln_args = [
			'wordpress' => $this->vulnerabilities->wordpress,
			'plugins' => $this->vulnerabilities->plugins,
			'themes' => $this->vulnerabilities->themes,
		];
		$content_vulner = $this->vulnerabilities->render_template('all-table', $vuln_args);

		$audit_args = [
			'results' => $this->audit->get_audit(),
		];
		$content_audit = $this->audit->render_template('all-audit', $audit_args);

		$hided_args = [
			'results' => $this->audit->get_hided(),
		];
		$content_hided = $this->render_template('hided', $hided_args);

		$content_malware = $this->scanner->render_template('results', $this->scanner->get_current_results());

		$args = [
			'modules' => [
				'hided' => [
					'name' => '',
					'icon' => 'dashicons-hidden',
					'content' => $content_hided,
					'style' => '',
				],
				'audit' => [
					'name' => __('Security audit', 'titan-security'),
					'icon' => 'dashicons-plugins-checked',
					'content' => $content_audit,
					'count' => $this->audit->get_count(),
					'style' => '',
					'active' => 'active',
				],
				'vulnerability' => [
					'name' => __('Vulnerabilities', 'titan-security'),
					'icon' => 'dashicons-buddicons-replies',
					'content' => $content_vulner,
					'count' => $this->vulnerabilities->get_count(),
					'style' => $this->plugin->is_premium() ? '' : 'wt-tabs-pro',
				],
				'malware' => [
					'name' => __('Malware', 'titan-security'),
					'icon' => 'dashicons-code-standards',
					'content' => $content_malware,
					'count' => $this->scanner->get_matched_count(),
					'style' => '',
				],
			],
			'active_modules' => "audit,vulnerability",
		];
		$script_args = [
			'wtvulner' => [
				'nonce' => wp_create_nonce('get_vulners'),
			],
		];
		echo $this->vulnerabilities->render_script('vulnerability_ajax.js', $script_args);

		$script_args = [
			'wtaudit' => [
				'nonce' => wp_create_nonce('get_audits'),
			],
		];
		echo $this->audit->render_script('audit_ajax.js', $script_args);

		echo $this->render_template($template, $args);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hide_issue()
	{
		if( !current_user_can('manage_options') ) {
			wp_die(-2);
		} else {
			check_ajax_referer('hide');

			if( isset($_POST['id']) && $_POST['id'] !== '' && isset($_POST['type']) && $_POST['type'] !== '' ) {
				$audit = $this->audit->get_audit();
				$hided = $this->audit->get_hided();

				$id = sanitize_key($_POST['id']);
				$type = sanitize_key($_POST['type']);

				$hided[$type][] = $audit[$id];
				unset($audit[$id]);

				update_option($this->plugin->getPrefix() . "audit_results", $audit, 'no');
				update_option($this->plugin->getPrefix() . "audit_results_hided", $hided, 'no');
				$html = $this->render_template('hided', [
					'results' => $hided,
				]);
				wp_send_json_success([
					'html' => $html
				]);
			}

			die();
		}
	}

}
<?php
namespace WBCR\Titan;

use WBCR\Titan\Client\Client;
use WBCR\Titan\Client\Request\SetNoticeData;

/**
 * The file contains a short help info.
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Scanner extends Module_Base {

	/**
	 * Vulnerabilities object
	 *
	 * @var Vulnerabilities
	 */
	public $vulnerabilities;

	/**
	 * Vulnerabilities_API constructor.
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->module_dir = WTITAN_PLUGIN_DIR."/includes/scanner";
		$this->module_url = WTITAN_PLUGIN_URL."/includes/scanner";
		$this->vulnerabilities = new Vulnerabilities();
	}

	/**
	 * Show page content
	 */
	public function showPageContent() {
		$modules = explode(',', $this->plugin->getOption( 'security_check_list', array()));
		$vuln_args = array(
			'wordpress' => $this->vulnerabilities->getWordpress(),
			'plugins'   => $this->vulnerabilities->getPlugins(),
			'themes'    => $this->vulnerabilities->getThemes(),
		);
		$content_vulner = $this->vulnerabilities->render_template( 'all-table', $vuln_args);


		$args = array(
			'modules' => array(
				'vulnerability' => array(
					'name' => __('Vulnerabilities', 'titan-security'),
					'content' => $content_vulner,
				),
				'audit' => array(
					'name' => __('Security audit', 'titan-security'),
					'content' => '',
				),
				'malware' => array(
					'name' => __('Malware scan', 'titan-security'),
					'content' => '',
				),
			),
			'active_modules' => $modules,
		);
		$script_args = array(
			'wtvulner' => array(
				'nonce' => wp_create_nonce('get_vulners'),
			),
		);
		echo $this->vulnerabilities->render_script('vulnerability_ajax.js', $script_args);
		echo $this->render_template( 'scanner', $args);
	}

}
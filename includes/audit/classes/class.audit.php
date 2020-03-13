<?php
namespace WBCR\Titan;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WBCR\Titan\Client\Client;
use WBCR\Titan\Client\Request\SetNoticeData;

/**
 * Security audit class
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Audit extends Module_Base {

	/**
	 * @see self::app()
	 * @var Audit
	 */
	private static $app;

	/**
	 * @var AuditResult[]
	 */
	public $results = array();

	/**
	 * @var Client
	 */
	public $client;

	/**
	 * Audit constructor.
	 *
	 */
	public function __construct() {
		parent::__construct();
		self::$app = $this;

		$this->module_dir = WTITAN_PLUGIN_DIR."/includes/audit";
		$this->module_url = WTITAN_PLUGIN_URL."/includes/audit";
		//$this->client = new Client($this->license_key);

		add_action( 'wp_ajax_wtitan_audit_all', array( $this, 'show_audit_all' ) );

		//AUDIT
		$this->get_audit();
	}

	/**
	 * @return Audit
	 * @since  1.0
	 */
	public static function app() {
		return self::$app;
	}

	/**
	 * Get audit
	 *
	 * @return AuditResult[] Results
	 */
	public function get_audit() {
		$this->results = get_option( $this->plugin->getPrefix()."audit_results", array());
		if(!is_array($this->results)) $this->results = array();
		return $this->results;
	}

	/**
	 * Get hided
	 *
	 * @return AuditResult[] Results
	 */
	public function get_hided() {
		$this->results = get_option( $this->plugin->getPrefix()."audit_results_hided", array());
		if(!is_array($this->results)) $this->results = array();
		return $this->results;
	}

	/**
	 * Do audit
	 *
	 * @return AuditResult[] Results
	 */
	public function do_audit() {
		$this->results = array();
		
//		$this->check_versions();
//		$this->check_debug();
//		$this->check_php_variables();
//		$this->check_https();
//		$this->check_users();
		$this->check_updates();

		update_option( $this->plugin->getPrefix()."audit_results_hided", array(), 'no');
		update_option( $this->plugin->getPrefix()."audit_results", $this->results, 'no');
		return $this->results;
	}

	/**
	 * Add result
	 *
	 * @param string $title
	 * @param string $description
	 * @param string $severity
	 * @param bool   $hided
	 */
	public function add($title, $description, $severity, $fix = '', $hided = false) {
		$this->results[] = new AuditResult( $title, $description, $severity, $fix,$hided);
	}

	/**
	 * Versions audit
	 *
	 * @return AuditResult[] Results
	 */
	public function check_versions() {
		//PHP
		$title = sprintf(__('Your PHP version %1s is less than the recommended %2s','titan-security'), PHP_VERSION, '7.2.0');
		$description = __('Older versions of PHP are slow and vulnerable','titan-security');
		if (WTITAN_DEBUG || version_compare(PHP_VERSION, '7.2.0') < 0) {
			$this->add( $title, $description,  'medium');
		}

		//MySQL
		global $wpdb;
		$title = sprintf(__('Your MySQL version %1s is less than the recommended %2s','titan-security'), $wpdb->db_version(), '4.0.0');
		$description = __('Older versions of MySQL are very slow and vulnerable','titan-security');
		if (WTITAN_DEBUG || version_compare($wpdb->db_version(), '4.0.0') < 0) {
			$this->add( $title, $description,  'medium');
		}

		//Wordpress
		global $wp_version;
		$title = sprintf(__('Your Wordpress version %1s is less than the recommended %2s','titan-security'), $wp_version, '5.2.0');
		$description = __('Older versions of Wordpress may be vulnerable','titan-security');
		if (WTITAN_DEBUG || version_compare($wp_version, '5.2.0') < 0) {
			$this->add( $title, $description,  'medium', admin_url('update-core.php'));
		}

		return $this->results;
	}

	/**
	 * Debug audit
	 *
	 * @return AuditResult[] Results
	 */
	public function check_debug() {
		//WP_DEBUG
		$title = __('Wordpress Debug mode is enabled on your site','titan-security');
		$description = __('Every good developer should enable debugging before starting work on a new plugin or theme. In fact, WordPress Codex "strongly recommends" that developers use WP_DEBUG. Unfortunately, many developers forget to disable debugging mode even when the site is running. Displaying debug logs in the web interface will allow hackers to learn a lot about your WordPress website.','titan-security');
		if (WTITAN_DEBUG || (defined('WP_DEBUG') && WP_DEBUG)) {
			$this->add( $title, $description,  'high');
		}

		//SAVEQUERIES
		$title = __('Wordpress Database Debug mode is enabled on your site','titan-security');
		$description = __('When its enabled, all SQL queries will be saved in the $wpdb->queries variable as an array. For security and performance reasons, this constant must be disabled on the production site.','titan-security');
		if (WTITAN_DEBUG || (defined('SAVEQUERIES') && SAVEQUERIES)) {
			$this->add( $title, $description,  'low');
		}

		//SCRIPT_DEBUG
		$title = __('Wordpress Script Debug Mode is enabled on your site','titan-security');
		$description = __('When enabled, WordPress will use non-compressed versions (dev versions) of JS and CSS files . The default is to use min versions of the files. For security and performance reasons, this constant must be disabled on the production site.','titan-security');
		if (WTITAN_DEBUG || (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)) {
			$this->add( $title, $description,  'low');
		}

		return $this->results;
	}

	/**
	 * PHP variables audit
	 *
	 * @return AuditResult[] Results
	 */
	public function check_php_variables() {
		//display_errors
		$title = __("The 'display_errors' PHP directive is enabled","titan-security");
		$description = __("Displaying any debugging information in the interface can be extremely bad for site security. If any PHP errors occur on your site , they must be registered in a secure location and not displayed to visitors or potential attackers.","titan-security");
		if (WTITAN_DEBUG || ini_get('display_errors')) {
			$this->add( $title, $description,  'high');
		}

		//allow_url_include
		$title = __("The 'allow_url_include' PHP directive is enabled","titan-security");
		$description = __("Enabling 'allow_url_include' PHP Directive will make your site vulnerable to cross-site attacks (XSS).","titan-security");
		if (WTITAN_DEBUG || ini_get('allow_url_include')) {
			$this->add( $title, $description,  'high');
		}

		return $this->results;
	}

	/**
	 * HTTPS audit
	 *
	 * @return AuditResult[] Results
	 */
	public function check_https() {
		$title = __("Your site works over HTTP, without using SSL","titan-security");
		$description = __("If the site uses HTTPS, its data is protected.","titan-security");
		if (WTITAN_DEBUG || empty($_SERVER['HTTPS'])) {
			$this->add( $title, $description,  'medium');
		}

		return $this->results;
	}

	/**
	 * Users audit
	 *
	 * @return AuditResult[] Results
	 */
	public function check_users() {
		$users = get_users( [
			'role'         => 'administrator',
		] );
		$admin = false;
		foreach ( $users as $user ) {
			if("admin" == $user->user_login || "administrator" == $user->user_login) $admin = true;
		}

		$title = __("The standard administrator login 'admin' is used","titan-security");
		$description = __("Since user names make up half of the login credentials, this made it easier for hackers to launch brute- force attacks. You need to set complex and unique names for your site administrators.","titan-security");
		if (WTITAN_DEBUG || $admin) {
			$this->add( $title, $description,  'medium');
		}

		return $this->results;
	}

	/**
	 * Updates audit
	 *
	 * @return AuditResult[] Results
	 */
	public function check_updates() {
		$plugins = get_plugins();

		//COMPATIBLE
		$no_requirement = array();
		foreach ( (array) $plugins as $plugin_file => $plugin_data ) {
			$requirement = validate_plugin_requirements($plugin_file);
			if(WTITAN_DEBUG || is_wp_error( $requirement))
			{
				$no_requirement[] = $plugin_data['Name'];
			}
		}

		$title = __("Incompatible plugins found","titan-security");
		$description = "<b>".__("Some plugins on your site are not compatible with PHP and Wordpress versions: ","titan-security")."</b>";
		if (WTITAN_DEBUG || !empty($no_requirement)) {
			$description .= "<br>- ".implode("<br>- ",$no_requirement);
			$this->add( $title, $description,  'medium');
		}

		//UPDATE Plugins
		$current = get_site_transient( 'update_plugins' );
		foreach ( (array) $current->response as $plugin_file => $plugin_data ) {
			if(WTITAN_DEBUG)
			{
				$plugins_update[] = $plugin_data->slug;
			}
		}
		$i = 0;
		foreach ( (array) $plugins as $plugin_file => $plugin_data ) {
			if ( isset( $current->response[ $plugin_file ] ) ) {
				$plugins[ $plugin_file ]['update'] = true;
				$i++;
			}
		}
		$title = sprintf(__('You have %1s plugins that need to be updated','titan-security'), $i);
		$description = "<b>".__("Need to update plugins, as previous versions may be vulnerable:","titan-security")."</b>";
		if (WTITAN_DEBUG || !empty($plugins_update)) $description .= "<br>- ".implode("<br>- ",$plugins_update);
		if (WTITAN_DEBUG || $i) {
			$this->add( $title, $description, 'medium', admin_url('update-core.php'));
		}

		//UPDATE Themes
		$themes = wp_get_themes();
		$current = get_site_transient( 'update_themes' );
		foreach ( (array) $current->response as $theme_file => $theme_data ) {
			if(WTITAN_DEBUG)
			{
				$themes_update[] = $theme_data['theme'];
			}
		}
		$i = 0;
		foreach ( (array) $themes as $key => $theme ) {
			if ( isset( $current->response[ $key ] ) ) {
				$themes[ $key ]->update = true;
				$i++;
			}

		}
		$title = sprintf(__('You have %1s themes that need to be updated','titan-security'), $i);
		$description = "<b>".__("Need to update themes, as previous versions may be vulnerable:","titan-security")."</b>";
		if (WTITAN_DEBUG || !empty($themes_update)) $description .= "<br>- ".implode("<br>- ",$themes_update);
		if (WTITAN_DEBUG || $i) {
			$this->add( $title, $description,  'medium', admin_url('update-core.php'));
		}

		return $this->results;
	}

	/**
	 * HTTPS audit
	 *
	 * @return AuditResult[] Results
	 */
	public function check_files() {
		//readme.html
		$title = __("Readme.html file is available in the site root","titan-security");
		$description = __("It is important to hide or delete the readme.html file, because it contains information about the WP version.","titan-security");
		if (WTITAN_DEBUG || file_exists( ABSPATH."readme.html")) {
			$this->add( $title, $description,  'low');
		}

		return $this->results;
	}

	/**
	 * @return int
	 */
	public function get_count() {
		return is_array($this->results) ? count($this->results) : 0;
	}


	/**
	 * Show page content
	 */
	public function showPageContent() {
	}

	/**
	 * {@inheritdoc}
	 */
	public function show_audit_all() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 2 );
		} else {
			check_ajax_referer( 'get_audits' );

			$args = array(
				'results' => $this->do_audit(),
			);
			echo $this->render_template( 'all-audit', $args );
			die();
		}
	}

}
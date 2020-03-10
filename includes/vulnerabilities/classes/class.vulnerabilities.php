<?php
namespace WBCR\Titan;

use WBCR\Titan\Client\Client;
use WBCR\Titan\Client\Entity\Vulnerability;
use WBCR\Titan\Client\Request\SetNoticeData;
use WBCR\Titan\Client\Request\VulnerabilityPlugin;
use WBCR\Titan\Client\Request\VulnerabilityTheme;

/**
 * The file contains a short help info.
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Vulnerabilities extends Module_Base {

	/**
	 * @see self::app()
	 * @var Vulnerabilities
	 */
	private static $app;

	/**
	 * @var Client
	 */
	public $client;

	/**
	 * @var array
	 */
	public $wordpress = array();

	/**
	 * @var array
	 */
	public $plugins = array();

	/**
	 * @var array
	 */
	public $themes = array();

	/**
	 * Vulnerabilities constructor.
	 *
	 */
	public function __construct() {
		parent::__construct();
		self::$app = $this;

		$this->module_dir = WTITAN_PLUGIN_DIR."/includes/vulnerabilities";
		$this->module_url = WTITAN_PLUGIN_URL."/includes/vulnerabilities";
		$this->client = new Client($this->license_key);

		add_action( 'wp_ajax_wtitan_get_vulners', array( $this, 'showVulnerabilities' ) );
	}

	/**
	 * @return Vulnerabilities
	 * @since  1.0
	 */
	public static function app() {
		return self::$app;
	}

	/**
	 * @return array
	 */
	public function getWordpress() {
		$wps = maybe_serialize( $this->plugin->getOption('vulnerabilities_wordpress', array()));
		$wps = maybe_unserialize( $wps);
		$this->wordpress = $wps;
		return $this->wordpress;
	}

	/**
	 * @return array
	 */
	public function getPlugins() {
		$plugs = maybe_serialize( $this->plugin->getOption('vulnerabilities_plugins', array()));
		$plugs = maybe_unserialize( $plugs);
		$this->plugins = $plugs;
		return $this->plugins;
	}

	/**
	 * @return array
	 */
	public function getThemes() {
		$themes = maybe_serialize( $this->plugin->getOption('vulnerabilities_themes', array()));
		$themes = maybe_unserialize( $themes);
		$this->themes = $themes;
		return $this->themes;
	}

	/**
	 * Get wordpress's vulnerabilities
	 *
	 * @return void
	 */
	public function get_wordpress_vulners() {
		global $wp_version;
		$this->wordpress = $this->validate($this->client->get_vuln_cms( $wp_version));
		$this->plugin->updateOption( "vulnerabilities_wordpress", $this->wordpress);
	}

	/**
	 * Get plugin's vulnerabilities
	 *
	 * @return void
	 */
	public function get_plugins_vulners() {
		$plugins_wp = get_plugins();
		$params = new VulnerabilityPlugin();
		foreach ( $plugins_wp as $key => $plugin ) {
			$tmp = explode('/', $key);
			if( isset($tmp[0]) && count($tmp) >= 2) {
				$slug = $tmp[0];
				$plugins_wp[$slug] = $key;
			}
			else break;
			$params->add_plugin( $slug, (string)$plugin['Version']);
		}
		$this->plugins = $this->validate($this->client->get_vuln_plugin( $params));
		foreach ( $this->plugins as $key => $plug ) {
			foreach ( $plug as $k => $vuln ) {
				$this->plugins[$key][$k]->path = $plugins_wp[ $vuln->slug ];
			}
		}
		$this->plugin->updateOption( "vulnerabilities_plugins", $this->plugins);
	}

	/**
	 * Get theme's vulnerabilities
	 *
	 * @return void
	 */
	public function get_themes_vulners() {
		$themes_wp = wp_get_themes();
		$params = new VulnerabilityTheme();
		foreach ( $themes_wp as $key => $theme ) {
			if(empty($theme['Version'])) continue;
			$params->add_theme( $key, (string)$theme['Version']);
		}
		$this->themes = $this->validate( $this->client->get_vuln_theme( $params));
		$this->plugin->updateOption( "vulnerabilities_themes", $this->themes);
	}

	/**
	 * Get all vulnerabilities
	 *
	 * @return void
	 */
	public function get_vulnerabilities() {
		$this->get_wordpress_vulners();
		$this->get_plugins_vulners();
		$this->get_themes_vulners();
	}

	/**
	 * Validate response
	 *
	 * @param array $response
	 *
	 * @return array
	 */
	public function validate($response) {
		$result = array();
		foreach ( $response as $key => $item ) {
			foreach ( $item as $k => $vuln ) {
				if( empty( $vuln->description ) ) continue;
				$vuln->description = wp_strip_all_tags( $vuln->description );
				$result[$key][$k]          = $vuln;
			}
		}

		return $result;
	}

	/**
	 * Show page content
	 */
	public function showPageContent() {
		$this->get_vulnerabilities();

		echo $this->render_template( 'vulnerabilities');
	}

	/**
	 * {@inheritdoc}
	 */
	public function showVulnerabilities() {
		check_ajax_referer('get_vulners');

		if(isset($_POST['target'])) {
			$target  = $_POST['target'];
			switch ($target)
			{
				case 'plugin':
					$this->get_plugins_vulners();
					echo $this->render_template( 'plugins-table', $this->plugins);
					break;
				case 'theme':
					$this->get_themes_vulners();
					echo $this->render_template( 'themes-table', $this->themes);
					break;
				case 'wp':
					$this->get_wordpress_vulners();
					echo $this->render_template( 'wordpress-table', $this->wordpress);
					break;
				default:
					$this->get_vulnerabilities();
					$args = array(
						'wordpress' => $this->wordpress,
						'plugins' => $this->plugins,
						'themes' => $this->themes,
					);
					echo $this->render_template( 'all-table', $args);
					break;
			}
			die();
		}
	}

}
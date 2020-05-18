<?php

namespace WBCR\Titan;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		$this->module_dir = WTITAN_PLUGIN_DIR . "/includes/vulnerabilities";
		$this->module_url = WTITAN_PLUGIN_URL . "/includes/vulnerabilities";
		$this->client     = new Client( $this->license_key );

		$this->getWordpress();
		$this->getPlugins();
		$this->getThemes();

		add_action( 'wp_ajax_wtitan_get_vulners', array( $this, 'showVulnerabilities' ) );
	}

	/**
	 * @return Vulnerabilities
	 * @since  7.0
	 */
	public static function app() {
		return self::$app;
	}

	/**
	 * @return array
	 */
	public function getWordpress() {
		$this->wordpress = get_option( $this->plugin->getPrefix() . 'vulnerabilities_wordpress', false );

		return $this->wordpress;
	}

	/**
	 * @return array
	 */
	public function getPlugins() {
		$this->plugins = get_option( $this->plugin->getPrefix() . 'vulnerabilities_plugins', false );

		return $this->plugins;
	}

	/**
	 * @return array
	 */
	public function getThemes() {
		$this->themes = get_option( $this->plugin->getPrefix() . 'vulnerabilities_themes', false );

		return $this->themes;
	}

	/**
	 * Get all vulnerabilities
	 *
	 * @return void
	 */
	public function get_vulnerabilities() {
		$this->getWordpress();
		$this->getPlugins();
		$this->getThemes();
	}

	/**
	 * @return int
	 */
	public function get_count() {
		$plugin_vulner_count = 0;
		$theme_vulner_count  = 0;
		$wp_vulner_count     = 0;
		if ( is_array( $this->plugins ) ) {
			foreach ( $this->plugins as $plugin ) {
				foreach ( $plugin as $vulner ) {
					$plugin_vulner_count ++;
				}
			}
		}
		if ( is_array( $this->themes ) ) {
			foreach ( $this->themes as $theme ) {
				foreach ( $theme as $vulner ) {
					$theme_vulner_count ++;
				}
			}
		}
		if ( is_array( $this->wordpress ) ) {
			$wp_vulner_count = count( $this->wordpress );
		}

		return $wp_vulner_count + $plugin_vulner_count + $theme_vulner_count;
	}

	/**
	 * Get wordpress's vulnerabilities
	 *
	 * @return void
	 */
	public function get_wordpress_vulners() {
		global $wp_version;
		$this->wordpress = $this->validate( $this->client->get_vuln_cms( $wp_version ) );
		update_option( $this->plugin->getPrefix() . "vulnerabilities_wordpress", $this->wordpress, 'no' );
	}

	/**
	 * Get plugin's vulnerabilities
	 *
	 * @return void
	 */
	public function get_plugins_vulners() {
		$plugins_wp = get_plugins();
		$params     = new VulnerabilityPlugin();
		foreach ( $plugins_wp as $key => $plugin ) {
			$tmp = explode( '/', $key );
			if ( isset( $tmp[0] ) && count( $tmp ) >= 2 ) {
				$slug                = $tmp[0];
				$plugins_wp[ $slug ] = $key;
			} else {
				break;
			}
			$params->add_plugin( $slug, (string) $plugin['Version'] );
		}
		$this->plugins = $this->validate( $this->client->get_vuln_plugin( $params ) );
		foreach ( $this->plugins as $key => $plug ) {
			foreach ( $plug as $k => $vuln ) {
				$this->plugins[ $key ][ $k ]->path = $plugins_wp[ $vuln->slug ];
			}
		}
		update_option( $this->plugin->getPrefix() . "vulnerabilities_plugins", $this->plugins, 'no' );
	}

	/**
	 * Get theme's vulnerabilities
	 *
	 * @return void
	 */
	public function get_themes_vulners() {
		$themes_wp = wp_get_themes();
		$params    = new VulnerabilityTheme();
		foreach ( $themes_wp as $key => $theme ) {
			if ( empty( $theme['Version'] ) ) {
				continue;
			}
			$params->add_theme( $key, (string) $theme['Version'] );
		}
		$this->themes = $this->validate( $this->client->get_vuln_theme( $params ) );
		update_option( $this->plugin->getPrefix() . "vulnerabilities_themes", $this->themes, 'no' );
	}

	/**
	 * Get all vulnerabilities
	 *
	 * @return void
	 */
	public function get_all_vulners() {
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
	public function validate( $response ) {
		$result = array();
		foreach ( $response as $key => $item ) {
			foreach ( $item as $k => $vuln ) {
				if ( empty( $vuln->description ) ) {
					continue;
				}
				$vuln->description    = wp_strip_all_tags( $vuln->description );
				$result[ $key ][ $k ] = $vuln;
			}
		}

		return $result;
	}

	/**
	 * Show page content
	 */
	public function showPageContent() {
		$this->get_vulnerabilities();

		echo $this->render_template( 'vulnerabilities' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function showVulnerabilities() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 2 );
		} else {
			check_ajax_referer( 'get_vulners' );

			if ( isset( $_POST['target'] ) ) {
				$target = $_POST['target'];
				switch ( $target ) {
					case 'plugin':
						$this->get_plugins_vulners();
						echo $this->render_template( 'plugins-table', $this->plugins );
						break;
					case 'theme':
						$this->get_themes_vulners();
						echo $this->render_template( 'themes-table', $this->themes );
						break;
					case 'wp':
						$this->get_wordpress_vulners();
						echo $this->render_template( 'wordpress-table', $this->wordpress );
						break;
					default:
						$this->get_all_vulners();
						$args = array(
							'wordpress' => $this->wordpress,
							'plugins'   => $this->plugins,
							'themes'    => $this->themes,
						);
						echo $this->render_template( 'all-table', $args );
						break;
				}
				die();
			}
		}
	}

}
<?php
namespace WBCR\Titan;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The file contains a short help info.
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Antispam extends Module_Base {

	/**
	 * @see self::app()
	 * @var Antispam
	 */
	private static $app;

	/**
	 * Vulnerabilities constructor.
	 *
	 */
	public function __construct() {
		parent::__construct();
		self::$app = $this;

		$this->module_dir = WTITAN_PLUGIN_DIR."/includes/antispam";
		$this->module_url = WTITAN_PLUGIN_URL."/includes/antispam";
	}

	/**
	 * @return Antispam
	 * @since  7.0
	 */
	public static function app() {
		return self::$app;
	}

	/**
	 *
	 * @since  7.0
	 */
	public function showPageContent() {
	}

}
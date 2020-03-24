<?php

namespace WBCR\Titan\Cert;

use WBCR\Titan\Module_Base;

/**
 * Class CertInfo
 * @package WBCR\Titan\Cert
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @version 1.0.0
 */
class CertInfo extends Module_Base {

	public function __construct() {
		parent::__construct();
		$this->module_url = WTITAN_PLUGIN_URL . "/includes/cert-info";
		$this->module_dir = WTITAN_PLUGIN_DIR . "/includes/cert-info";
	}

	public function showPageContent() {
		echo $this->render_template( 'result', [
			'cert' => Cert::get_instance(),
		] );
	}
}
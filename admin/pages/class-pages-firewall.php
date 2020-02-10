<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The file contains a short help info.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 2019 Webraftic Ltd
 * @version       1.0
 */
class Firewall extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'firewall';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-tagcloud';

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_position = 0;

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $internal = false;

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $add_link_to_plugin_actions = true;

	/**
	 * Logs constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->menu_title                  = __( 'Firewall', 'anti-spam' );
		$this->page_menu_short_description = __( 'Stops Complex Attacks', 'anti-spam' );

		parent::__construct( $plugin );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 1.1.4
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/firewall-dashboard.css' );
		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/circular-progress.js', [ 'jquery' ] );
	}


	/**
	 * {@inheritdoc}
	 */
	public function showPageContent() {
		?>
        <div class="wbcr-content-section">
            <div class="wbcr-factory-page-group-header" style="margin:0">
                <strong>Web Application Firewall (WAF)</strong>. <p>
                    The Wordfence Web Application Firewall is a PHP based, application level firewall that filters out
                    malicious requests to your site.</p>
            </div>


            <div id="wantispam-firewall-dashboard-top-section">
                <table>
                    <tr>
                        <td>
                            <div id="circle-waf-coverage" class="wf-status-circular"></div>
                            <script>
								jQuery(document).ready(function($) {
									$('#circle-waf-coverage').wfCircularProgress({
										endPercent: 0.22,
										color: '#fcb214',
										inactiveColor: '#ececec',
										strokeWidth: 3,
										diameter: 100,
									});
								});
                            </script>
                            <h4>Web Application Firewall</h4>
                            <p>Stops Complex Attacks</p>
                        </td>
                        <td>
                            <div id="circle-waf-rules" class="wf-status-circular"></div>
                            <script>
								jQuery(document).ready(function($) {
									$('#circle-waf-rules').wfCircularProgress({
										endPercent: 0.70,
										color: '#fcb214',
										inactiveColor: '#ececec',
										strokeWidth: 3,
										diameter: 100,
									});
								});
                            </script>
                            <h4>Firewall Rules: Community</h4>
                            <p>Rule updates delayed by 30 days</p>
                        </td>
                        <td>
                            <div id="circle-waf-blacklist" class="wf-status-circular"></div>
                            <script>
								jQuery(document).ready(function($) {
									$('#circle-waf-blacklist').wfCircularProgress({
										endPercent: 0,
										color: '#ececec',
										inactiveColor: '#ececec',
										strokeWidth: 3,
										diameter: 100
									});
								});
                            </script>
                            <h4>Real-Time IP Blacklist: Disabled</h4>
                            <p>Blocks requests from known malicious IPs</p>
                        </td>
                        <td>
                            <div id="circle-waf-brute" class="wf-status-circular"></div>
                            <script>
								jQuery(document).ready(function($) {
									$('#circle-waf-brute').wfCircularProgress({
										endPercent: 1,
										color: '#16bc9b',
										inactiveColor: '#ececec',
										strokeWidth: 3,
										diameter: 100,
									});
								});
                            </script>
                            <h4>Brute Force Protection</h4>
                            <p>Stops Password Guessing Attacks</p>
                        </td>
                    </tr>
                </table>
            </div>
            <br>
            <div id="wantispam-firewall-dashboard-top-section">
                <table>
                    <tr>
                        <td>
                            <h4>Web Application Firewall Status</h4>
                            <p>Enabled and Protecting: In this mode, the Wordfence Web Application Firewall is actively
                                blocking requests matching known attack patterns and is actively protecting your site
                                from attackers.</p>
                            <select name="wafStatus" tabindex="-1" aria-hidden="true" style="width: 200px;">
                                <option selected="" class="wafStatus-enabled" value="enabled">Enabled and Protecting
                                </option>
                                <option class="wafStatus-learning-mode" value="learning-mode">Learning Mode</option>
                                <option class="wafStatus-disabled" value="disabled">Disabled</option>
                            </select>
                        </td>
                        <td>
                            <h4>Protection Level</h4>
                            <p>Extended Protection: All PHP requests will be processed by the firewall prior to running.

                                If you're moving to a new host or a new installation location, you may need to
                                temporarily disable extended protection to avoid any file not found errors. Use this
                                action to remove the configuration changes that enable extended protection mode or you
                                can remove them manually.</p>
                        </td>
                        <td>
                            <h4>Real-Time IP Blacklist</h4>
                            <p>Premium Feature: This feature blocks all traffic from IPs with a high volume of recent
                                malicious activity using Wordfence's real-time blacklist.</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
		<?php
	}

}

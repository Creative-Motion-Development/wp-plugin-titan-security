<?php
if ( is_array( $data ) ) {
	extract( $data );
}
/**
 * @var array $data
 * @var bool $scanner_started
 * @var \WBCR\Titan\Vulnerabilities $vulnerabilities
 * @var \WBCR\Titan\Audit $audit
 * @var \WBCR\Titan\SiteChecker $sites
 * @var array $scanner
 * @var Wbcr_Factory000_Plugin $this_plugin
 */


if ( isset( $firewall ) ) {
	extract( $firewall );
}
/**
 * @var string $firewall_mode
 * @var string $firewall_status_percent
 * @var string $firewall_status_color
 * @var string $firewall_mode
 */
if ( isset( $scanner ) ) {
	extract( $scanner );
}
/**
 * @var string $scanner_started
 * @var string $matched
 * @var string $progress
 * @var string $suspicious
 * @var string $cleaned
 */
?>
<div class="wbcr-content-section">
    <div class="wt-scanner-container wt-scanner-block-scan">
        <table>
            <tr>
                <td>
                    <h4><?php echo __( 'Quick start', 'titan-security' ); ?></h4>
                    <button class="btn btn-primary"
                            id="wt-quickstart-scan"><?php echo __( 'Start scan', 'titan-security' ); ?></button>
					<?php if ( $data['scanner_started'] ): ?>
                        <button type="button" id="scan" data-action="stop_scan" class="wt-malware-scan-button"
                                style="display: none;">Malware scan
                        </button>
					<?php else: ?>
                        <button type="button" id="scan" data-action="start_scan" class="wt-malware-scan-button"
                                style="display: none;">Malware scan
                        </button>
					<?php endif; ?>
                    <div class="wt-scan-icon-loader" data-status="" style="display: none"></div>
                </td>
                <td>
                    <p><?php echo __( 'Full scan your site.', 'titan-security' ); ?></p>
                </td>
            </tr>
        </table>
    </div>
    <div id="wt-dashboard-section" class="wt-dashboard-container">
        <table class="wt-dashboard-table">
            <thead>
            <tr>
                <td>
                    <div class="wt-caption-block"><h4><?php echo __( 'Firewall', 'titan-security' ); ?></h4></div>
                    <div class="wt-manage-link-block"><a
                                href="<?php echo $this_plugin->getPluginPageUrl( 'firewall' ); ?>"
                                class="btn btn-secondary"><?php echo __( 'Manage Firewall', 'titan-security' ); ?></a>
                    </div>
                </td>
                <td>
                    <div class="wt-caption-block"><h4><?php echo __( 'Security audit', 'titan-security' ); ?></h4></div>
                    <div class="wt-manage-link-block"><a href="<?php echo $this_plugin->getPluginPageUrl( 'check' ); ?>"
                                                         class="btn btn-secondary"><?php echo __( 'View more details', 'titan-security' ); ?></a>
                    </div>
                </td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div class="wt-left-block">
                        <div class="wtitan-status-block wtitan-status--enabled"
                             style="display: <?php echo( "enabled" === $firewall_mode ? 'block' : 'none' ) ?>;">
                            <span class="wt-firewall-icon-ok"></span>
                            <h4><?php _e( 'Titan Firewall Activated', 'titan' ); ?></h4>
                        </div>
                        <div class="wtitan-status-block wtitan-status--learning-mode"
                             style="display: <?php echo( "learning-mode" === $firewall_mode ? 'block' : 'none' ) ?>;">
                            <span class="wt-firewall-icon-clock"></span>
                            <h4><?php _e( 'Titan Firewall in Learning Mode', 'titan' ); ?></h4>
                        </div>
                        <div class="wtitan-status-block wtitan-status--disabled"
                             style="display: <?php echo( "disabled" === $firewall_mode ? 'block' : 'none' ) ?>;">
                            <span class="wt-firewall-icon-dissmiss"></span>
                            <h4 style="color:#9c3926"><?php _e( 'Titan Firewall Deactivated', 'titan' ); ?></h4>
                        </div>
                    </div>
                    <div class="wt-right-block">
                        <div id="wtitan-circle-firewall-coverage" class="wtitan-status-circular">
                            <svg viewBox="0 0 100 100"
                                 style="display: inline-block; width: 100px; height: 100px; opacity: 1;"
                                 viewbox="0 0 100 100">
                                <path class="wtitan-status-circular-inactive-path"
                                      d="M 50,50 m 0,-48 a 48,48 0 1 1 0,96 a 48,48 0 1 1 0,-96" stroke="#ececec"
                                      stroke-width="1" fill-opacity="0"></path>
                                <path class="wtitan-status-circular-active-path"
                                      d="M 50,50 m 0,-48 a 48,48 0 1 1 0,96 a 48,48 0 1 1 0,-96" stroke="#5d05b7"
                                      stroke-width="1" stroke-dasharray="301.59289474462014,301.59289474462014"
                                      stroke-dashoffset="-301.59289474462014" fill-opacity="0"
                                      style="stroke-dashoffset: -301.593px;"></path>
                                <path class="wtitan-status-circular-terminator"
                                      d="M 50,2 m 0,-1 a 1,1 0 1 1 0,2 a 1,1 0 1 1 0,-2" stroke="#5d05b7"
                                      stroke-width="1" fill="#ffffff"></path>
                            </svg>
                            <div class="wtitan-status-circular-text" style="opacity: 1;">0%</div>
                        </div>
                        <script>
                            jQuery(document).ready(function ($) {
                                $('#wtitan-circle-firewall-coverage').wfCircularProgress({
                                    endPercent: <?php echo $firewall_status_percent; ?>,
                                    color: '<?php echo $firewall_status_color; ?>',
                                    inactiveColor: '#ececec',
                                    strokeWidth: 1,
                                    diameter: 100,
                                    css_display: 'inline-block',
                                    pendingOverlay: false,
                                    pendingMessage: '',
                                });
                            });
                        </script>
                        <h4>Web Application Firewall</h4>
                    </div>

                </td>
                <td>
                    <div class="wt-full-block">
                        <div class="wt-left-block">
                            <h4>
                                <span class="dashicons dashicons-plugins-checked"></span><?php echo __( 'Security audit', 'titan-security' ); ?>
                                (<?php echo $audit->get_count(); ?>)</h4>
                            <div class="wt-block-span"><?php if ( $audit->get_count() ) {
									echo __( 'Security issues detected!', 'titan-security' );
								} ?></div>
                        </div>
                        <div class="wt-right-block">
                            <h4>
                                <span class="dashicons dashicons-buddicons-replies"></span><?php echo __( 'Vulnerabilities', 'titan-security' ); ?>
                                (<?php echo $vulnerabilities->get_count(); ?>)</h4>
                            <div class="wt-block-span-count">
								<?php if ( count( $vulnerabilities->wordpress ) ) {
									echo "<div>" . count( $vulnerabilities->wordpress ) . "</div> " . __( 'Wordpress', 'titan-security' ) . "<br>";
								} ?>
								<?php if ( count( $vulnerabilities->plugins ) ) {
									echo "<div>" . count( $vulnerabilities->plugins ) . "</div> " . __( 'Plugins', 'titan-security' ) . "<br>";
								} ?>
								<?php if ( count( $vulnerabilities->themes ) ) {
									echo "<div>" . count( $vulnerabilities->themes ) . "</div> " . __( 'Themes', 'titan-security' ) . "";
								} ?>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="wt-dashboard-table">
            <thead>
            <tr>
                <td>
                    <div class="wt-caption-block"><h4><?php echo __( 'Scanner', 'titan-security' ); ?></h4></div>
                    <div class="wt-manage-link-block"><a
                                href="<?php echo $this_plugin->getPluginPageUrl( 'scanner' ); ?>"
                                class="btn btn-secondary"><?php echo __( 'Manage scanner', 'titan-security' ); ?></a>
                    </div>
                </td>
                <td>
                    <div class="wt-caption-block"><h4><?php echo __( 'Site checker', 'titan-security' ); ?></h4></div>
                    <div class="wt-manage-link-block"><a
                                href="<?php echo $this_plugin->getPluginPageUrl( 'sitechecker' ); ?>"
                                class="btn btn-secondary"><?php echo __( 'Manage site checker', 'titan-security' ); ?></a>
                    </div>
                </td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div class="wt-full-block">
                        <div class="wt-left-block">
                            <div class="wio-chart-container wio-overview-chart-container">
                                <canvas id="wtitan-scan-chart" width="100" height="100"
                                        data-cleaned="<?php echo $cleaned ?>"
                                        data-suspicious="<?php echo $suspicious ?>"
                                        style="display: block;">
                                </canvas>
                                <div id="wt-total-percent-chart" class="wio-chart-percent">
									<?php echo round( $progress, 1 ) ?><span>%</span>
                                </div>
                                <p class="wio-global-optim-phrase wio-clear">
                                    Scanned <span class="wio-total-percent" id="wt-total-percent">
	                                <?php echo round( $progress, 1 ) ?>%
	                                </span>
                                    of your website's files
                                </p>
                            </div>
                        </div>
                        <div class="wt-right-block" style="transform: translate(0%, 50%);">
                            <div id="wio-overview-chart-legend">
                                <ul class="wio-doughnut-legend">
                                    <li>
                                        <span style="background-color:#5d05b7">&nbsp;</span>
                                        Cleaned -
										<?php echo $cleaned ?>
                                    </li>
                                    <li>
                                        <span style="background-color:#f1b1b6">&nbsp;</span>
                                        Suspicious -
										<?php echo $suspicious ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                </td>
                <td>
                    <div class="wt-full-block">
                        <table class="wt-sitechecker-block-table">
                            <thead>
                            <tr>
                                <td><h4><?php echo __( "URL's", 'titan-security' ); ?></h4></td>
                                <td><h4><?php echo __( "Frequency", 'titan-security' ); ?></h4></td>
                                <td><h4><?php echo __( "Uptime", 'titan-security' ); ?></h4></td>
                                <td><h4><?php echo __( "Push", 'titan-security' ); ?></h4></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><?php echo $sites->get_count(); ?></td>
                                <td>5<span>min</span></td>
                                <td class="wt-pink"><?php echo $sites->get_average_uptime(); ?><span>%</span></td>
                                <td><span class="wt-push-status"></span></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

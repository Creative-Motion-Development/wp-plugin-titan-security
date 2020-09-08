<?php
/* @var array $data
 * @var WBCR\Titan\Page\Firewall $this_firewall
 */

$firewall_mode           = $data['firewall_mode'];
$firewall_status_percent = $data['firewall_status_percent'];
$firewall_status_color   = $data['firewall_status_color'];
?>
<div class="wbcr-content-section wtitan-section-disabled">
    <div class="wbcr-factory-page-group-header" style="margin:0">
        <strong><?php _e( 'Web Application Firewall (WAF)', 'titan-security' ); ?></strong>. <p>
			<?php _e( 'The Titan Web Application Firewall is a PHP based, application level firewall that filters out
					malicious requests to your site.', 'titan-security' ); ?></p>
    </div>

    <div id="wtitan-firewall-dashboard-top-section">
        <table>
            <tr>
                <td>
                    <div class="wtitan-status-block wtitan-status--enabled"
                         style="display: <?php echo( "enabled" === $firewall_mode ? 'block' : 'none' ) ?>;">
                        <span class="dashicons dashicons-yes-alt"
                              style="font-size:100px;width: 100px;height:100px;color:#1fa02fc9;"></span>
                        <h4><?php _e( 'Titan Firewall Activated', 'titan-security' ); ?></h4>
                    </div>
                    <div class="wtitan-status-block wtitan-status--learning-mode"
                         style="display: <?php echo( "learning-mode" === $firewall_mode ? 'block' : 'none' ) ?>;">
                        <span class="dashicons dashicons-clock"
                              style="font-size:100px;width: 100px;height:100px;color:#fcb214;"></span>
                        <h4><?php _e( 'Titan Firewall in Learning Mode', 'titan-security' ); ?></h4>
                    </div>
                    <div class="wtitan-status-block wtitan-status--disabled"
                         style="display: <?php echo( "disabled" === $firewall_mode ? 'block' : 'none' ) ?>;">
                        <span class="dashicons dashicons-dismiss"
                              style="font-size:100px;width: 100px;height:100px;color:#f59888;"></span>
                        <h4 style="color:#9c3926"><?php _e( 'Titan Firewall Deactivated', 'titan-security' ); ?></h4>
                    </div>
                </td>
                <td>
	                <div id="wtitan-circle-firewall-coverage" class="wtitan-status-circular"></div>
	                <script>
		                jQuery(document).ready(function($) {
			                $('#wtitan-circle-firewall-coverage').wfCircularProgress({
				                endPercent: <?php echo $firewall_status_percent; ?>,
				                color: '<?php echo $firewall_status_color; ?>',
				                inactiveColor: '#ececec',
				                strokeWidth: 1,
				                diameter: 100,
			                });
		                });
	                </script>
	                <h4><?php _e('Web Application Firewall', 'titan-security'); ?></h4>
	                <p><?php _e('Stops Complex Attacks', 'titan-security'); ?></p>
	                <div id="wtitan-status-tooltip" style="display: none">
		                <strong><?php _e('How do I get to 100%?', 'titan-security'); ?></strong>
		                <ul>
			                <li><?php _e('30% Enable the Titan Firewall.', 'titan-security'); ?></li>
			                <li><?php _e('70% Optimize the Titan Firewall.', 'titan-security'); ?></li>
			                <!--<li>30% Disable learning mode.</li>
							<li>35% Enable Real-Time IP Blacklist.</li>-->
			                <li><a href="#"><?php _e('How does Titan determine this?', 'titan-security'); ?></a></li>
		                </ul>
	                </div>
                </td>
            </tr>
        </table>
    </div>
    <div id="wtitan-firewall-dashboard-top-section">
        <table>
            <tr>
                <td>
                    <h4><?php _e( 'Web Application Firewall Status', 'titan-security' ); ?></h4>
                    <p><?php _e( 'Enabled and Protecting: In this mode, the Titan Web Application Firewall is actively
								blocking requests matching known attack patterns and is actively protecting your site
								from attackers.', 'titan-security' ); ?></p>

                    <select id="js-wtitan-firewall-mode" name="wafStatus" tabindex="-1" aria-hidden="true"
                            style="width: 200px;">
                        <option selected="" class="wafStatus-enabled" value="enabled">
							<?php _e( 'Enabled and Protecting', 'titan-security' ); ?>
                        </option>
                        <option class="wafStatus-learning-mode" value="learning-mode">
							<?php _e( 'Learning Mode', 'titan-security' ); ?>
                        </option>
                        <option class="wafStatus-disabled" value="disabled">
							<?php _e( 'Disabled', 'titan-security' ); ?>
                        </option>
                    </select>
                </td>
                <td>
                    <h4><?php _e( 'Protection Level', 'titan-security' ); ?></h4>
                    <p class="wf-no-top">
                        <strong><?php _e( 'Basic WordPress Protection:', 'titan-security' ); ?></strong> <?php _e( 'The plugin will load as a regular plugin after WordPress has been loaded, and while it can block many malicious requests, some vulnerable plugins or WordPress itself may run vulnerable code before all plugins are loaded.', 'titan-security' ); ?>
                    </p>
                    <p>
                        <a class="btn btn-primary" href="#"
                           id="js-wtitan-optimize-firewall-protection"><?php _e( 'Optimize the Titan Firewall', 'titan-security' ); ?></a>
                    </p>

                </td>
            </tr>
        </table>
    </div>
</div>

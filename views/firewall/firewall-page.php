<?php
/* @var array $data
 * @var WBCR\Titan\Page\Firewall $this_firewall
 */

$this_firewall = $data['this-firewall'];
$firewall_mode = $data['firewall_mode'];
$firewall_status_percent = $data['firewall_status_percent'];
$firewall_status_color = $data['firewall_status_color'];
?>
<div class="wbcr-content-section">
        <div class="wbcr-factory-page-group-header" style="margin:0">
            <strong><?php _e('Web Application Firewall (WAF)', 'titan'); ?></strong>. <p>
				<?php _e('The Titan Web Application Firewall is a PHP based, application level firewall that filters out
					malicious requests to your site.', 'titan'); ?></p>
        </div>


        <div id="wtitan-firewall-dashboard-top-section">
            <table>
                <tr>
                    <td>
                        <div class="wtitan-status-block wtitan-status--enabled" style="display: <?php echo("enabled" === $firewall_mode ? 'block' : 'none') ?>;">
                            <h4><?php _e('Titan Firewall Activated', 'titan'); ?></h4>
                            <span class="dashicons dashicons-yes-alt" style="font-size:80px;width: 80px;height:80px;color:#1fa02fc9;"></span>
                        </div>
                        <div class="wtitan-status-block wtitan-status--learning-mode" style="display: <?php echo("learning-mode" === $firewall_mode ? 'block' : 'none') ?>;">
                            <h4><?php _e('Titan Firewall in Learning Mode', 'titan'); ?></h4>
                            <span style="font-size:80px;width: 80px;height:80px;color:#fcb214;" class="dashicons dashicons-clock"></span>
                        </div>
                        <div class="wtitan-status-block wtitan-status--disabled" style="display: <?php echo("disabled" === $firewall_mode ? 'block' : 'none') ?>;">
                            <h4 style="color:#9c3926"><?php _e('Titan Firewall Deactivated', 'titan'); ?></h4>
                            <span class="dashicons dashicons-dismiss" style="font-size:80px;width: 80px;height:80px;color:#f59888;"></span>
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
                        <h4><?php _e('Web Application Firewall', 'titan'); ?></h4>
                        <p><?php _e('Stops Complex Attacks', 'titan'); ?></p>
                        <div id="wtitan-status-tooltip" style="display: none">
                            <strong><?php _e('How do I get to 100%?', 'titan'); ?></strong>
                            <ul>
                                <li><?php _e('30% Enable the Titan Firewall.', 'titan'); ?></li>
                                <li><?php _e('70% Optimize the Titan Firewall.', 'titan'); ?></li>
                                <!--<li>30% Disable learning mode.</li>
								<li>35% Enable Real-Time IP Blacklist.</li>-->
                                <li><a href="#"><?php _e('How does Titan determine this?', 'titan'); ?></a></li>
                            </ul>
                    </td>
                </tr>
            </table>
        </div>
        <div id="wtitan-firewall-dashboard-top-section">
            <table>
                <tr>
                    <td>
                        <h4><?php _e('Web Application Firewall Status', 'titan'); ?></h4>
                        <p><?php _e('Enabled and Protecting: In this mode, the Titan Web Application Firewall is actively
								blocking requests matching known attack patterns and is actively protecting your site
								from attackers.', 'titan'); ?></p>

                        <select id="js-wtitan-firewall-mode" data-nonce="<?php echo wp_create_nonce('wtitan_change_firewall_mode') ?>" name="wafStatus" tabindex="-1" aria-hidden="true" style="width: 200px;">
                            <option selected="" class="wafStatus-enabled" value="enabled"<?php selected("enabled", $firewall_mode) ?>>
								<?php _e('Enabled and Protecting', 'titan'); ?>
                            </option>
                            <option class="wafStatus-learning-mode" value="learning-mode"<?php selected("learning-mode", $firewall_mode) ?>>
								<?php _e('Learning Mode', 'titan'); ?>
                            </option>
                            <option class="wafStatus-disabled" value="disabled"<?php selected("disabled", $firewall_mode) ?>>
								<?php _e('Disabled', 'titan'); ?>
                            </option>
                        </select>
                    </td>
                    <td>
                        <h4><?php _e('Protection Level', 'titan'); ?></h4>
						<?php if( $this_firewall->protectionMode() == \WBCR\Titan\Model\Firewall::PROTECTION_MODE_EXTENDED && !$this_firewall->isSubDirectoryInstallation() ): ?>
                            <p class="wf-no-top">
                                <strong><?php _e('Extended Protection:', 'titan'); ?></strong> <?php _e('All PHP requests will be processed by the firewall prior to running.', 'titan'); ?>
                            </p>
                            <p><?php printf(__('If you\'re moving to a new host or a new installation location, you may need to temporarily disable extended protection to avoid any file not found errors. Use this action to remove the configuration changes that enable extended protection mode or you can <a href="%s" target="_blank" rel="noopener noreferrer">remove them manually</a>.', 'titan'), '#'); ?></p>
                            <p class="wf-no-top">
                                <a class="button button-default" href="#" id="js-wtitan-firewall-uninstall"><?php _e('Remove Extended Protection', 'titan'); ?></a>
                            </p>
						<?php elseif( $this_firewall->isSubDirectoryInstallation() ): ?>
                            <p class="wf-no-top">
                                <strong><?php _e('Existing WAF Installation Detected:', 'titan'); ?></strong> <?php _e('You are currently running the Titan Web Application Firewall from another WordPress installation. Please configure the firewall to run correctly on this site.', 'titan'); ?>
                            </p>
                            <p>
                                <a class="button button-primary" href="#" id="js-wtitan-optimize-firewall-protection"><?php _e('Optimize the Titan Firewall', 'titan'); ?></a>
                            </p>
						<?php else: ?>
                            <p class="wf-no-top">
                                <strong><?php _e('Basic WordPress Protection:', 'titan'); ?></strong> <?php _e('The plugin will load as a regular plugin after WordPress has been loaded, and while it can block many malicious requests, some vulnerable plugins or WordPress itself may run vulnerable code before all plugins are loaded.', 'titan'); ?>
                            </p>
                            <p>
                                <a class="button button-primary" href="#" id="js-wtitan-optimize-firewall-protection"><?php _e('Optimize the Titan Firewall', 'titan'); ?></a>
                            </p>
						<?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

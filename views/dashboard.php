<?php
if(is_array($data)) extract($data);
/**
 * @var array                       $data
 * @var bool                        $scanner_started
 * @var \WBCR\Titan\Vulnerabilities $vulnerabilities
 * @var \WBCR\Titan\Audit           $audit
 * @var \WBCR\Titan\SiteChecker     $sites
 * @var array                       $scanner
 * @var Wbcr_Factory000_Plugin      $this_plugin
 */


if(isset($firewall)) extract($firewall);
/**
 * @var string $firewall_mode
 * @var string $firewall_status_percent
 * @var string $firewall_status_color
 * @var string $firewall_mode
 */
if(isset($scanner)) extract($scanner);
/**
 * @var string $scanner_started
 * @var string $matched
 * @var string $progress
 * @var string $suspicious
 * @var string $cleaned
 */
?>
<div class="wbcr-content-section">

    <div class="wt-dashboard-container">
        <div class="wt-row">
            <!-- FIREWALL -->
            <div class="col-md-6 wt-block-gutter">
                <div class="wt-dashboard-block">
                    <div class="row">
                        <div class="col-md-12 wt-dashboard-block-header"><h4><?php _e('Firewall', 'titan-security'); ?></h4></div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 wt-dashboard-block-content">
                            <label for="js-wtitan-firewall-mode"><?php _e('Web Application Firewall Status', 'titan-security'); ?></label>
                            <select id="js-wtitan-firewall-mode" data-nonce="<?php echo wp_create_nonce('wtitan_change_firewall_mode') ?>" name="wafStatus" tabindex="-1" aria-hidden="true" style="width: 93%;">
                                <option selected="" class="wafStatus-enabled" value="enabled"<?php selected("enabled", $firewall_mode) ?>>
	                                <?php _e('Enabled and Protecting', 'titan-security'); ?>
                                </option>
                                <option class="wafStatus-learning-mode" value="learning-mode"<?php selected("learning-mode", $firewall_mode) ?>>
	                                <?php _e('Learning Mode', 'titan-security'); ?>
                                </option>
                                <option class="wafStatus-disabled" value="disabled"<?php selected("disabled", $firewall_mode) ?>>
	                                <?php _e('Disabled', 'titan-security'); ?>
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 wt-dashboard-block-content">
                            <div class="wtitan-status-block wtitan-status--loading" style="display: none;">
                                <span class="wt-dashboard-icon-loader"></span>
                            </div>
                            <div class="wtitan-status-block wtitan-status--enabled" style="display: <?php echo("enabled" === $firewall_mode ? 'block' : 'none') ?>;">
                                <span class="wt-firewall-icon-ok"></span>
                            </div>
                            <div class="wtitan-status-block wtitan-status--learning-mode" style="display: <?php echo("learning-mode" === $firewall_mode ? 'block' : 'none') ?>;">
                                <span class="wt-firewall-icon-clock"></span>
                            </div>
                            <div class="wtitan-status-block wtitan-status--disabled" style="display: <?php echo("disabled" === $firewall_mode ? 'block' : 'none') ?>;">
                                <span class="wt-firewall-icon-dissmiss"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-11 wt-dashboard-block-content wt-block-description">
                            <?php _e('Enabled and Protecting: In this mode, the Titan Web Application Firewall is actively
                                blocking requests matching known attack patterns and is actively protecting your site
                                from attackers.', 'titan-security'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ANTISPAM -->
            <div class="col-md-6 wt-block-gutter">
                <div class="wt-dashboard-block">
                    <div class="row">
                        <div class="col-md-12 wt-dashboard-block-header"><h4>Anti-spam</h4></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 wt-dashboard-block-content">
                            <label for="wt-antispam-status"><?php _e('Anti-spam Status', 'titan-security'); ?></label>
                            <div class="factory-checkbox factory-buttons-way btn-group wt-checkbox">
                                <button type="button" class="btn factory-off " data-value="0">&nbsp;</button>
                                <button type="button" class="btn factory-on active">&nbsp;</button>

                                <input type="checkbox" style="display: none" id="wt-antispam-status" class="factory-result" name="wt-antispam-status" value="1" checked="checked">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wt-dashboard-container">
        <div class="wt-row">
            <!-- SCANNER -->
            <div class="col-md-12 wt-block-gutter">
                <div class="wt-dashboard-block">3</div>
            </div>
        </div>
    </div>
</div>

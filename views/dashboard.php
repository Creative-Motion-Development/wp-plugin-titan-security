<?php
if( is_array($data) ) {
	extract($data);
}
/**
 * @var array $data
 * @var bool $scanner_started
 * @var string $scanner_speed
 * @var array $scanner_speeds
 * @var string $schedule
 * @var array $schedules
 * @var \WBCR\Titan\Vulnerabilities $vulnerabilities
 * @var \WBCR\Titan\Audit $audit
 * @var \WBCR\Titan\SiteChecker $sites
 * @var \WBCR\Titan\Antispam $antispam
 * @var array $scanner
 * @var Wbcr_Factory000_Plugin $this_plugin
 * @var string $check_content
 */

if( isset($firewall) ) {
	extract($firewall);
}
/**
 * @var string $firewall_mode
 * @var bool $firewall_pro_activated
 */
if( isset($scanner) ) {
	extract($scanner);
}
/**
 * @var bool   $is_premium
 * @var string $scanner_started
 * @var string $matched
 * @var array  $progress
 * @var string $suspicious
 * @var string $cleaned
 * @var string $notverified
 * @var string $files_count
 * @var string $scanned
 */
$pro_class = $is_premium ? '' : 'factory-checkbox--disabled wtitan-control-premium-label';
//$statistic_data = $antispam->get_statistic_data();
?>
<div class="wbcr-content-section">
	
	<div class="wt-dashboard-container">
		<div class="wt-row">
			<!-- FIREWALL -->
			<div class="col-md-6 wt-block-gutter">
				<div class="wt-dashboard-block">
					<div class="row">
						<div class="col-md-12 wt-dashboard-block-header">
							<h4><?php _e('Firewall', 'titan-security'); ?> <?php if( !$firewall_pro_activated ): ?>
									<span class="wt-dashboard-pro-span">PRO</span><?php endif; ?></h4>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9 wt-dashboard-block-content">
							<label for="js-wtitan-firewall-mode"><?php _e('Web Application Firewall Status', 'titan-security'); ?></label>
							<select id="js-wtitan-firewall-mode" data-nonce="<?php echo wp_create_nonce('wtitan_change_firewall_mode') ?>" name="wafStatus" tabindex="-1" aria-hidden="true" style="width: 93%;"<?php echo($firewall_pro_activated ? '' : 'disabled') ?>>
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
							<div class="wtitan-status-block wtitan-status--disabled wt-firewall-icon-dissmiss" style="display: <?php echo("disabled" === $firewall_mode ? 'block' : 'none') ?>;">
							
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
						<div class="col-md-6 wt-dashboard-block-content">
							<?php
							if($is_premium) {
								$count = $antispam->get_statistic_data()->total;
								echo __( 'Spam blocked: ', 'titan-security' );
								echo "<span class='wt-magenta-text'>{$count}</span>";
							}
							?>
						</div>
						<div class="col-md-6 wt-dashboard-block-content-right">
							<label for="wt-antispam-status"><?php _e('Anti-spam status', 'titan-security'); ?></label>
							<div class="factory-checkbox factory-buttons-way btn-group wt-checkbox" id="wt-antispam-status-block">
								<button type="button" class="btn factory-off <?php echo $antispam->mode ? '' : 'active'; ?>" data-value="0">
									Off
								</button>
								<button type="button" class="btn factory-on  <?php echo $antispam->mode ? 'active' : ''; ?>" data-value="1">
									On
								</button>
								<input type="checkbox" style="display: none" id="wt-antispam-status" class="factory-result" name="wt-antispam-status" value="<?php echo $antispam->mode; ?>" checked="checked" data-nonce="<?php echo wp_create_nonce('wtitan_change_antispam_mode') ?>">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 wt-dashboard-block-content">
							<div id="wt-antispam-chart-div"></div>
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
				<div class="wt-dashboard-block">
					<div class="row">
						<div class="col-md-12 wt-dashboard-block-header">
							<h4><?php _e('Scanner', 'titan-security'); ?></h4></div>
					</div>
					<div class="row">
						<div class="col-md-6 wt-dashboard-block-content" style="line-height: 34px;">
							<?php
							echo __('Scanned: ', 'titan-security');
							$counter = 0;
							if( $scanned > 0 ) {
								$counter = "{$scanned} / {$files_count}";
							}
							echo "<span class='wt-magenta-text' id='wtitan-files-num'>{$counter}</span>&nbsp;" . __('files', 'titan-security');
							?>
						</div>
						<div class="col-md-6 wt-dashboard-block-content-right">
                            <div class="wt-dashboard-scan-button-loader" style="display: none;"></div>
							<?php if( $scanner_started ): ?>
								<button class="btn btn-primary wt-dashboard-scan-button" id="scan" data-action="stop_scan"><?php echo __('Stop scanning', 'titan-security'); ?></button>
							<?php else: ?>
								<button class="btn btn-primary wt-dashboard-scan-button" id="scan" data-action="start_scan"><?php echo __('Start scan', 'titan-security'); ?></button>
							<?php endif; ?>

						</div>
					</div>
					<div class="row">
						<div class="wt-scanner-chart">
							<div class="wt-scanner-chart-clean" style="width: <?php echo $progress[0]; ?>%;"></div>
							<div class="wt-scanner-chart-suspicious" style="width: <?php echo $progress[1];?>%;"></div>
							<div class="wt-scanner-chart-notverified" style="width: <?php echo $progress[2];?>%;"></div>
						</div>
					</div>
					<div class="row">
						<div class="wt-scanner-legend">
							<table>
								<tbody>
								<tr>
									<td><span class="wt-scanner-chart-clean wt-legend-item"></span></td>
									<td>Cleaned - <span id="wtitan-cleaned-num"><?php echo $cleaned ?></span></td>

									<td><span class="wt-scanner-chart-suspicious wt-legend-item"></span></td>
									<td>Suspicious - <span id="wtitan-suspicious-num"><?php echo $suspicious ?></span>
									</td>

									<td><span class="wt-scanner-chart-notverified wt-legend-item"></span></td>
									<td>Not verified -
										<span id="wtitan-notverified-num"><?php echo $notverified ?></span></td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 wt-dashboard-block-content" style="text-align: left;">
							<div class="form-group form-group-dropdown  factory-control-scanner_speed">
								<div class="control-group col-sm-12">
									<div class="factory-dropdown factory-from-control-dropdown factory-buttons-way <?= $pro_class; ?>" data-way="buttons">
										<div class="wt-dashboard-form-label"><?= __('Scheduled scan', 'titan-security'); ?></div>
										<div class="btn-group factory-buttons-group">
											<?php foreach($schedules as $sched) : ?>
												<button type="button" class="btn btn-default btn-small wt-scanner-schedule-button factory-<?= $sched[0]; ?> <?php echo $sched[0] == $schedule ? 'active' : ''; ?>" data-value="<?= $sched[0]; ?>"><?= $sched[1]; ?></button>
											<?php endforeach; ?>
											<input type="hidden" id="titan_scanner_speed" class="factory-result" name="titan_scanner_speed" value="<?= $schedule; ?>">
										</div>
										<div class="factory-hints" style="">
											<?php foreach($schedules as $sched) : ?>
												<div class="factory-hint factory-hint-<?= $sched[0]; ?>" style="display: <?php echo $sched[0] == $schedule ? '' : 'none'; ?>;"><?= $sched[2]; ?></div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 wt-dashboard-block-content" style="text-align: left;">
							<div class="form-group form-group-dropdown  factory-control-scanner_speed">
								<div class="control-group col-sm-12">
									<div class="factory-dropdown factory-from-control-dropdown factory-buttons-way <?= $pro_class; ?>" data-way="buttons">
										<div class="wt-dashboard-form-label"><?= __('Scanning speed', 'titan-security'); ?></div>
										<div class="btn-group factory-buttons-group">
											<?php foreach($scanner_speeds as $speeds) : ?>
												<button type="button" class="btn btn-default btn-small wt-scanner-speed-button factory-<?= $speeds[0]; ?> <?php echo $speeds[0] == $scanner_speed ? 'active' : ''; ?>" data-value="<?= $speeds[0]; ?>"><?= $speeds[1]; ?></button>
											<?php endforeach; ?>
											<input type="hidden" id="titan_scanner_speed" class="factory-result" name="titan_scanner_speed" value="<?= $scanner_speed; ?>">
										</div>
										<div class="factory-hints" style="">
											<?php foreach($scanner_speeds as $speeds) : ?>
												<div class="factory-hint factory-hint-<?= $speeds[0]; ?>" style="display: <?php echo $speeds[0] == $scanner_speed ? '' : 'none'; ?>;"><?= $speeds[2]; ?></div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">&nbsp;</div>
					</div>
				</div>
			</div>
		</div>
		<div class="wt-dashboard-container">
			<div class="wt-row">
				<!-- AUDIT -->
				<div class="col-md-12 wt-block-gutter">
					<div class="wt-dashboard-block">
						<div class="row">
							<div class="col-md-6 wt-dashboard-block-header">
								<h4><?php _e('Security audit', 'titan-security'); ?></h4></div>
							<div class="col-md-6 wt-dashboard-block-header-right">
								<div class="wt-scan-icon-loader" data-status="" style="display: none;"></div>
								<button class="btn btn-primary wt-dashboard-audit-button" id="wt-checker-check"><?php echo __('Check now', 'titan-security'); ?></button>
							</div>
						</div>
						<div class="row">
							<?php echo $check_content; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>

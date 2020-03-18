<?php
/**
 * @var array $data
 * @var bool $scanner_started
 */
?>
<div class="wbcr-content-section">
	<div class="wt-scanner-container wt-scanner-block-scan">
		<table>
			<tr>
				<td>
					<button class="btn btn-primary" id="wt-quickstart-scan"><?php echo __('Start scan','titan-security'); ?></button>
					<?php if ( $data['scanner_started'] ): ?>
                        <button type="button" id="scan" data-action="stop_scan" class="wt-malware-scan-button" style="display: none;">Malware scan</button>
                    <?php else: ?>
                        <button type="button" id="scan" data-action="start_scan" class="wt-malware-scan-button" style="display: none;">Malware scan</button>
                    <?php endif; ?>
					<div class="wt-scan-icon-loader" data-status="" style="display: none"></div>
				</td>
				<td>
					<h4><?php echo __('Description','titan-security'); ?></h4>
					<p><?php echo __('Full scan your site.','titan-security'); ?>
					</p>
				</td>
			</tr>
		</table>
	</div>
</div>

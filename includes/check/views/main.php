<?php
// !! Обязательно, чтобы редактор знал что такая переменная тут существует
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template username
 */
?>
<div class="wbcr-content-section">
    <div class="wt-scanner-container wt-scanner-block-scan">
        <table>
            <tr>
                <td>
                    <h4><?php _e( 'Security audit', 'titan-security' ); ?></h4>
                    <button class="btn btn-primary wt-scanner-scanbutton"
                            id="wt-checker-check"><?php _e( 'Check now', 'titan-security' ); ?></button>
                    <div class="wt-scan-icon-loader" data-status="" style="display: none"></div>
                </td>
                <td>
                    <h4><?php _e( 'Description', 'titan-security' ); ?></h4>
                    <p><?php _e( 'After launching, the it will check your site for vulnerabilities and doing security audit. After you solve the detected security problems , you need to run the check again.', 'titan-security' ); ?>
                    </p>
                </td>
            </tr>
        </table>
        <!--
        <div class="wt-scan-progress">
	        <?php if ( isset( $args['modules'] ) ): ?>
            <ul class="wt-scan-progress-ul">
	            <?php foreach ( $args['modules'] as $key => $module ) {
			if ( "hided" == $key ) {
				continue;
			}
			$icon = 'none';
			if ( ! empty( $module['content'] ) ) {
				$icon = 'warning';
			} else {
				$icon = 'ok';
			}
			?>
                <li class="wt-scan-progress-li" id="wt-scan-progress-<?php echo $key; ?>">
                    <div class="wt-scan-step-icon wt-scan-step-icon-<?php echo $icon; ?>"></div>
                    <div class="wt-scan-step-title"><?php echo $module['name']; ?></div>
                </li>
	            <?php } ?>
            </ul>
	        <?php endif; ?>
        </div>
        -->
    </div>
    <!-- ############################### -->
    <div class="wbcr-factory-page-group-header wtitan-page-group-header">
        <strong>Results</strong>
        <p>Find malware and viruses</p>
    </div>
	<?php echo $this->render_template( 'check', $args ); ?>
    <!-- ############################### -->
</div>

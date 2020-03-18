<?php
// !! Обязательно, чтобы редактор знал что такая переменная тут существует
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template username
 */
$scanner_menu    = "";
$scanner_content = "";
$count = "";
foreach ( $args['modules'] as $key => $module ) {
	$active = "";
    if(isset($module['active']) && !empty($module['active'])) $active = $module['active'];
    if(isset($module['count'])) $count = " ({$module['count']})";
	$scanner_menu    .= "<li class='{$active}'><a href='#wtitan-{$key}'><span class='dashicons {$module['icon']}'></span> {$module['name']}{$count}</a></li>\n";
	$scanner_content .= "<div class='wtitan-tab-table-container tab-pane {$active}' id='wtitan-{$key}'>{$module['content']}</div>\n";
}
?>
<div class="wbcr-content-section">
	<div class="wt-scanner-container wt-scanner-block-scan">
        <table>
            <tr>
                <td>
                    <h4><?php echo __('Security audit','titan-security'); ?></h4>
                    <button class="btn btn-primary wt-scanner-scanbutton" id="wt-scanner-scan"><?php echo __('Check now','titan-security'); ?></button>
	                <div class="wt-scan-icon-loader" data-status="" style="display: none"></div>
                </td>
                <td>
                    <h4><?php echo __('Description','titan-security'); ?></h4>
                    <p><?php echo __('After launching, the it will check your site for vulnerabilities and doing security audit. After you solve the detected security problems , you need to run the check again.','titan-security'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <!--
        <div class="wt-scan-progress">
	        <?php if(isset($args['modules'])): ?>
            <ul class="wt-scan-progress-ul">
	            <?php foreach ( $args['modules'] as $key => $module ) {
	            if("hided" == $key) continue;
	            $icon = 'none';
	            if(!empty($module['content'])) $icon = 'warning';
	            else $icon = 'ok';
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
	<div class="wt-scanner-tabs-container" style="margin-top: 0;">
        <ul class="nav nav-tabs" id="wtitan-scanner-tabs">
			<?php echo $scanner_menu;?>
        </ul>

		<div class="tab-content">
			<?php echo $scanner_content;?>
        </div>
	</div>
	<!-- ############################### -->
</div>
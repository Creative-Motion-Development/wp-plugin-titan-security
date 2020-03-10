<?php
// !! Обязательно, чтобы редактор знал что такая переменная тут существует
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template username
 */
$active = 'active';
$scanner_menu = "";
$scanner_content = "";
foreach ( $args['modules'] as $key => $module ) {
	//if(!in_array( $module, $args['active_modules'])) continue;
	$scanner_menu    .= "<li class='{$active}'><a href='#wtitan-{$key}'><span class='dashicons dashicons-buddicons-replies'></span> {$module['name']}</a></li>\n";
	$scanner_content .= "<div class='wtitan-tab-table-container tab-pane {$active}' id='wtitan-{$key}'>{$module['content']}</div>\n";
	$active = '';
}
?>
<div class="wbcr-content-section">
	<div class="wt-scanner-container wt-scanner-block-scan">
        <table>
            <tr>
                <td>
                    <h4>Scaning</h4>
                    <button class="button button-primary wt-scanner-scanbutton" id="wt-scanner-scan">Scan now</button>
                </td>
                <td>
                    <h4>Description</h4>
                    <p>После запуска сканер проверит всё, что у вас выбрано в настройках Сканера
                    </p>
                </td>
            </tr>
        </table>
        <div class="wt-scan-progress">
	        <?php if(isset($args['modules'])): ?>
            <ul class="wt-scan-progress-ul">
	            <?php foreach ( $args['modules'] as $key => $module ) {
	            if(!empty($module['name'])) $icon = 'ok';
	            if(in_array( $key, $args['active_modules'])) $icon = 'none';
	            else $icon = 'off';
	            ?>
                <li class="wt-scan-progress-li" id="wt-scan-progress-<?php echo $key; ?>">
                    <div class="wt-scan-step-icon wt-scan-step-icon-<?php echo $icon; ?>"></div>
                    <div class="wt-scan-step-title"><?php echo $module['name']; ?></div>
                </li>
	            <?php } ?>
            </ul>
	        <?php endif; ?>

        </div>
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
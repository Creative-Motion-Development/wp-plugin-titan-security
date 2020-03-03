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
	            <?php foreach ( $args['modules'] as $module => $name ) {
	            if(in_array( $module, $args['active_modules'])) $icon = 'none';
	            else $icon = 'off';
	            ?>
                <li class="wt-scan-progress-li" id="wt-scan-progress-<?php echo $module; ?>">
                    <div class="wt-scan-step-icon wt-scan-step-icon-<?php echo $icon; ?>"></div>
                    <div class="wt-scan-step-title"><?php echo $name; ?></div>
                </li>
	            <?php } ?>
            </ul>
	        <?php endif; ?>

        </div>
	</div>
	<!-- ############################### -->
	<div class="wbcr-factory-page-group-header" style="margin:0">
		<strong>Scanner</strong>
		<p>Find malware and viruses</p>
	</div>
	<div class="wt-scanner-container">
		111
	</div>
	<!-- ############################### -->
</div>
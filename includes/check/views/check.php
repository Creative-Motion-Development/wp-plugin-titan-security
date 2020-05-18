<?php
// !! Обязательно, чтобы редактор знал что такая переменная тут существует
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template username
 */
$scanner_menu    = "";
$scanner_content = "";
$count           = "";
foreach ( $args['modules'] as $key => $module ) {
	$active = "";
	if ( isset( $module['active'] ) && ! empty( $module['active'] ) ) {
		$active = $module['active'];
	}
	if ( isset( $module['count'] ) ) {
		$count = " ({$module['count']})";
	}
	$scanner_menu    .= "<li class='{$active}{$module['style']}'><a href='#wtitan-{$key}'><span class='dashicons {$module['icon']}'></span> {$module['name']}{$count}</a></li>\n";
	$scanner_content .= "<div class='wtitan-tab-table-container tab-pane {$active}' id='wtitan-{$key}'>{$module['content']}</div>\n";
}
?>
<div class="wt-scanner-tabs-container" style="margin-top: 0;">
    <ul class="nav nav-tabs" id="wtitan-scanner-tabs">
		<?php echo $scanner_menu; ?>
    </ul>

    <div class="tab-content">
		<?php echo $scanner_content; ?>
    </div>
</div>

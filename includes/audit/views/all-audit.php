<?php
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template
 */

if( is_array($args) && !empty($args) ) {
	$audit = $args['results'];
	if( $audit === false ) {
		?>
		<div class="wtitan-audit-empty-container">
			<?php echo sprintf(__('Click %1s to perform a security audit', 'titan-security'), '<span class="btn btn-primary wt-nobutton">' . __('Check now', 'titan-security') . '</span>'); ?>
		</div>
		<?php
	} else if( !empty($audit) ) {
		?>
		<div class="wtitan-scanner-vulner-table-container">
			<table class="table table-striped table-hover table-responsive" width="100%">
				<thead>
				<tr>
					<th class="wtitan-vulner-table__th wtitan-vulner-table-slim wtitan-vulner-table-first-col"></th>
					<th class="wtitan-vulner-table__th wtitan-vulner-table-name">Title</th>
					<th class="wtitan-vulner-table__th wtitan-vulner-table-description">Description</th>
					<th class="wtitan-vulner-table__th wtitan-vulner-table-slim">Time</th>
					<th class="wtitan-vulner-table__th wtitan-vulner-table-slim">Actions</th>
				</tr>

				</thead>
				<tbody>
				<?php
				foreach($audit as $key => $result) {
					/* @var \WBCR\Titan\AuditResult $result */
					if( empty($result->description) ) {
						$result->description = '&nbsp';
					}
					?>
					<tr>
						<td class="wtitan-vulner-table__td wt-severity-<?php echo esc_attr($result->severity); ?> wtitan-vulner-table-first-col"></td>
						<td class="wtitan-vulner-table__td wtitan-vulner-table__title"><?php echo esc_html($result->title); ?></td>
						<td class="wtitan-vulner-table__td wtitan-vulner-table__description"><?php echo esc_html($result->description); ?></td>
						<td class="wtitan-vulner-table__td"><?php echo date_i18n('d.m.Y H:i', $result->timestamp); ?></td>
						<td class="wtitan-vulner-table__td">
							<a class="button button-default wt-scanner-hide-button"
							   data-id="<?php echo esc_attr($key); ?>"
							   data-type="audit">Hide it</a>
							<?php if( empty($result->fix) ): ?>
							<?php elseif( $result->fix == "js" ): ?>
								<a class="button button-primary wt-audit-fix-button"
								   data-id="<?php echo esc_attr($key); ?>"><?php _e('Fix it', 'titan-security') ?></a>
							<?php else: ?>
								<a href="<?php echo esc_url(add_query_arg('wtitan_fixing_issue_id', $key, $result->fix)); ?>"
								   class="button button-primary" target="_blank"><?php _e('Fix it', 'titan-security') ?></a>
							<?php endif; ?>
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
		<?php
	} else {
		?>
		<div class="wtitan-audit-empty-container">
			<?php _e('No security issues', 'titan-security'); ?>
		</div>
		<?php
	}
}
?>
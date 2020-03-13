<?php
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template
 */

if( is_array($args) && !empty($args)) {
	$audit = $args['results'];
	if(!empty($audit)) {
		?>
        <div class="wtitan-scanner-vulner-table-container">
            <table class="table table-striped table-hover table-responsive" width="100%">
                <thead>
                <tr>
                    <td class="wtitan-vulner-table-slim"></td>
                    <td class="wtitan-vulner-table-name">Title</td>
                    <td class="wtitan-vulner-table-description">Description</td>
                    <td class="wtitan-vulner-table-slim">Time</td>
                    <td class="wtitan-vulner-table-slim">Actions</td>
                </tr>

                </thead>
                <tbody>
                    <?php
					foreach ( $audit as $key => $result ) {
						/* @var \WBCR\Titan\AuditResult $result */
						if ( empty( $result->description ) ) $result->description = '&nbsp';
						?>
                        <tr>
                            <td class="wt-severity-<?php echo $result->severity; ?>"></td>
                            <td><?php echo $result->title; ?></td>
                            <td class="wtitan-vulner-table-description"><?php echo $result->description; ?></td>
                            <td><?php echo wp_date('d.m.Y H:i',$result->timestamp); ?></td>
                            <td>
                                <button class="button button-secondary wt-scanner-hide-button"
                                   data-id="<?php echo $key; ?>"
                                   data-type="audit">Hide it</button>
                                <?php if(empty($result->fix)): ?>
	                            <?php elseif($result->fix == "js"): ?>
                                    <button target="_blank"
                                       class="button button-primary wt-audit-fix-button">Fix it</button>
	                            <?php else: ?>
                                    <a href="<?php echo $result->fix; ?>" target="_blank"
                                       class="button button-primary">Fix it</a>
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
	}
}
?>
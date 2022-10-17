<?php
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template
 */

if ( is_array( $args ) && isset( $args['results'] ) ) {
	$hided = $args['results'];
	if ( ! empty( $hided ) ) {
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
				foreach ( $hided as $type => $hides ) {
					?>
                    <tr>
                    <td colspan="5" class="wtitan-vulner-table-section">
						<?php
						switch ( $type ) {
							case 'audit':
								_e( 'Security audit', 'titan-security' );
								break;
							case 'malware':
								_e( 'Malware', 'titan-security' );
								break;
						}
						?>
                    </td>
                    </tr><?php
					foreach ( $hides as $key => $result ) {
						/* @var \WBCR\Titan\AuditResult $result */
						if ( empty( $result->description ) ) {
							$result->description = '&nbsp';
						}
						?>
                        <tr>
                            <td class="wt-severity-<?php echo $result->severity; ?>"></td>
                            <td><?php echo $result->title; ?></td>
                            <td class="wtitan-vulner-table-description"><?php echo $result->description; ?></td>
                            <td><?php echo date_i18n( 'd.m.Y H:i', $result->timestamp ); ?></td>
                            <td>
								<?php if ( empty( $result->fix ) ): ?>
								<?php elseif ( $result->fix == "js" ): ?>
                                    <a class="btn btn-primary wt-audit-fix-button">Fix it</a>
								<?php else: ?>
                                    <a href="<?php echo $result->fix; ?>" class="btn btn-primary">Fix it</a>
								<?php endif; ?>
                            </td>
                        </tr>
						<?php
					}
				}
				?>
                </tbody>
            </table>
        </div>
		<?php
	}
}
?>
<?php
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template
 */

if ( is_array( $args ) && ! empty( $args ) ) {
	$audit = $args['results'];
	if ( $audit === false ) {
		?>
        <div class="wtitan-audit-empty-container">
			<?= sprintf( __( 'Click %1s to perform a security audit', 'titan-security' ), '<span class="btn btn-primary wt-nobutton">' . __( 'Check now', 'titan-security' ) . '</span>' ); ?>
        </div>
		<?php
	} else if ( ! empty( $audit ) ) {
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
                            <a class="btn btn-default wt-scanner-hide-button"
                               data-id="<?php echo $key; ?>"
                               data-type="audit">Hide it</a>
							<?php if ( empty( $result->fix ) ): ?>
							<?php elseif ( $result->fix == "js" ): ?>
                                <a class="btn btn-primary wt-audit-fix-button"
                                   data-id="<?php echo esc_attr( $key ); ?>">Fix
                                    it</a>
							<?php else: ?>
                                <a href="<?php echo esc_url( add_query_arg( 'wtitan_fixing_issue_id', $key, $result->fix ) ); ?>"
                                   class="btn btn-primary">Fix
                                    it</a>
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
			<?= __( 'No security issues', 'titan-security' ); ?>
        </div>
		<?php

	}
}
?>
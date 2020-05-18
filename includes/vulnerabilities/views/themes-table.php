<?php
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template username
 */
if ( ! empty( $args ) ) {
	?>
    <table class="table table-striped table-hover table-responsive">
        <tbody>
        <tr class="wtitan-vulner-table-first-tr">
            <td class="wtitan-vulner-table-name">Name</td>
            <td class="wtitan-vulner-table-description">Description</td>
            <td class="wtitan-vulner-table-slim">Affected version</td>
            <td class="wtitan-vulner-table-slim">Safe version</td>
            <td class="wtitan-vulner-table-slim">Actions</td>
        </tr>
		<?php
		foreach ( $args as $key => $vulner ) {
			if ( empty( $vulner->description ) ) {
				$vulner->description = __( 'No description of the vulnerability', 'titan-security' );
			}
			?>
            <tr>
                <td><?php echo $vulner->name; ?></td>
                <td class="wtitan-vulner-table-description"><?php echo $vulner->description; ?></td>
                <td><?php echo $vulner->max_affected_version == '0.0.0.0.1' ? '0.0' : $vulner->max_affected_version; ?></td>
                <td><?php echo $vulner->safe_version; ?></td>
                <td>
					<?php if ( ! empty( $vulner->safe_version ) ) : ?>
                        <a href="<?php echo admin_url( "update-core.php" ) ?>" target="_blank"
                           class="btn btn-primary" id="wtitan-update-theme-button"
                           data-slug="<?php echo $vulner->slug; ?>">
                            Update
                        </a>
                        <span class="wtitan-spinner wtitan-hide"
                              id="wtitan-update-spinner-<?php echo $vulner->slug; ?>"></span>
                        <span class="wtitan-icon-ok wtitan-hide"
                              id="wtitan-icon-ok-<?php echo $vulner->slug; ?>"></span>
					<?php endif; ?>
                </td>
            </tr>
			<?php
		}
		?>
        </tbody>
    </table>
	<?php
} else {
	?>
    <div class="wtitan-vulner-container">No vulnerabilities</div>
	<?php
}
?>
<?php
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template username
 */

if ( is_array( $args ) && ! empty( $args ) ) {
	$wordpress = $args['wordpress'];
	$plugins   = $args['plugins'];
	$themes    = $args['themes'];

	if ( ! $this->plugin->is_premium() ) {
		$this->plugin->view->print_template( 'pro-version' );
	} else if ( $wordpress === false && $plugins === false && $themes === false ) {
		?>
        <div class="wtitan-audit-empty-container">
			<?php echo sprintf( __( 'Click %1s to search for vulnerabilities', 'titan-security' ), '<span class="btn btn-primary wt-nobutton">' . __( 'Check now', 'titan-security' ) . '</span>' ); ?>
        </div>
		<?php

	} else if ( ! empty( $wordpress ) || ! empty( $plugins ) || ! empty( $themes ) ) {
		?>
        <div class="wtitan-scanner-vulner-table-container">
            <table class="table table-striped table-hover table-responsive" width="100%">
                <thead>
                <tr>
                    <td class="wtitan-vulner-table-name">Name</td>
                    <td class="wtitan-vulner-table-description">Description</td>
                    <td class="wtitan-vulner-table-slim">Affected version</td>
                    <td class="wtitan-vulner-table-slim">Safe version</td>
                    <td class="wtitan-vulner-table-slim">Actions</td>
                </tr>

                </thead>
                <tbody>
				<?php
				if ( ! empty( $wordpress ) ) {
					?>
                    <tr>
                    <td colspan="5"
                        class="wtitan-vulner-table-section"><?php _e( 'Wordpress', 'titan-security' ); ?></td>
                    </tr><?php
					foreach ( $wordpress as $vulner ) {
						if ( empty( $vulner->description ) ) {
							$vulner->description = __( 'No description of the vulnerability', 'titan-security' );
						}
						if ( empty( $vulner->safe_version ) ) {
							continue;
						}
						?>
                        <tr>
                            <td>Wordpress</td>
                            <td class="wtitan-vulner-table-description"><?php echo wp_strip_all_tags( $vulner->description ); ?></td>
                            <td><?php echo $vulner->min_affected_version; ?></td>
                            <td><?php echo $vulner->safe_version; ?></td>
                            <td>
								<?php if ( ! empty( $vulner->safe_version ) ) : ?>
                                    <a href="<?php echo admin_url( "update-core.php" ) ?>" target="_blank"
                                       class="btn btn-primary">Fix it</a>
								<?php endif; ?>
                            </td>

                        </tr>
						<?php
					}
				}
				if ( ! empty( $plugins ) ) {
					?>
                    <tr>
                    <td colspan="5"
                        class="wtitan-vulner-table-section"><?php _e( 'Plugins', 'titan-security' ); ?></td>
                    </tr><?php
					foreach ( $plugins as $plug ) {
						foreach ( $plug as $vulner ) {
							if ( empty( $vulner->description ) ) {
								$vulner->description = __( 'No description of the vulnerability', 'titan-security' );
							}
							?>
                            <tr>
                                <td><?php echo $vulner->name; ?></td>
                                <td class="wtitan-vulner-table-description"><?php echo $vulner->description; ?></td>
                                <td><?php echo $vulner->min_affected_version; ?></td>
                                <td><?php echo $vulner->safe_version; ?></td>
                                <td>
									<?php if ( ! empty( $vulner->safe_version ) ) : ?>
                                        <a href="<?php echo admin_url( "update-core.php" ) ?>" target="_blank"
                                           class="btn btn-primary" id="wtitan-update-plugin-button"
                                           data-plugin="<?php echo $vulner->path; ?>"
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
						<?php }
					}
				}
				if ( ! empty( $themes ) ) {
					?>
                    <tr>
                    <td colspan="5"
                        class="wtitan-vulner-table-section"><?php _e( 'Themes', 'titan-security' ); ?></td>
                    </tr><?php
					foreach ( $themes as $theme ) {
						foreach ( $theme as $vulner ) {
							if ( empty( $vulner->description ) ) {
								$vulner->description = __( 'No description of the vulnerability', 'titan-security' );
							}
							?>
                            <tr>
                                <td><?php echo $vulner->name; ?></td>
                                <td class="wtitan-vulner-table-description"><?php echo $vulner->description; ?></td>
                                <td><?php echo $vulner->min_affected_version; ?></td>
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
					}
				}

				?>
                </tbody>
            </table>
        </div>
		<?php
	} else {
		?>
        <div class="wtitan-audit-empty-container">
			<?php _e( 'No vulnerabilities', 'titan-security' ); ?>
        </div>
		<?php

	}
}
?>
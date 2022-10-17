<?php

use WBCR\Titan\Plugin;

if( is_array($data) ) {
	extract($data);
}
/**
 * @var array $components
 */
?>
<div class="wbcr-factory-page-group-header"><?php

	_e('<strong>Plugin Components</strong>.', 'titan-security') ?>
	<p>
		<?php _e('These are components of the plugin bundle. When you activate the plugin, all the components turned on by default. If you don’t need some function, you can easily turn it off on this page.', 'titan-security') ?>
	</p>
</div>
<div class="wbcr-clearfy-components">
	<?php
	/**
	 * @since 7.0.3
	 */
	do_action('wtitan/components/custom_plugins_card', $components);
	?>

	<?php foreach((array)$components as $component): ?>
		<?php

		$slug = $component['name'];

		if( $component['type'] == 'wordpress' ) {
			$slug = $component['base_path'];
		}

		$install_button = Plugin::app()->getInstallComponentsButton($component['type'], $slug);

		$status_class = '';
		if( !$install_button->isPluginActivate() ) {
			$status_class = ' plugin-status-deactive';
		}

		$install_button->addClass('install-now');

		// Delete button
		$delete_button = Plugin::app()->getDeleteComponentsButton($component['type'], $slug);
		$delete_button->addClass('delete-now');

		?>
		<div class="plugin-card<?php echo esc_attr($status_class) ?>">
			<?php if( isset($component['build']) ): ?>
				<div class="plugin-card-<?php echo esc_attr($component['build']) ?>-ribbon"><?php echo ucfirst(esc_html($component['build'])) ?></div>
			<?php endif; ?>
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="<?php echo esc_url($component['url']) ?>" class="open-plugin-details-modal">
							<?php echo esc_html($component['title']) ?>
							<img src="<?php echo esc_attr($component['icon']) ?>" class="plugin-icon"
							     alt="<?php echo esc_attr($component['title']) ?>">
						</a>
					</h3>
				</div>
				<div class="desc column-description">
					<p><?php echo esc_html($component['description']); ?></p>
				</div>
			</div>
			<div class="plugin-card-bottom">
				<?php if( 'premium' === $component['build'] && !(Plugin::app()->premium->is_activate() && Plugin::app()->premium->is_install_package()) ): ?>
					<a target="_blank" href="<?php echo esc_url($component['url']) ?>"
					   class="button button-default install-now"><?php _e('Read more', 'titan-security'); ?></a>
				<?php else:
					$delete_button->renderButton();

					if( $install_button->isPluginActivate() ) {
						$display = '';
					} else {
						$display = 'style="display: none;"';
					}
					?>
					<?php if( !empty($component['settings_url']) ): ?>
					<a href="<?php echo esc_url($component['settings_url']) ?>"
					   class="button button-primary settings-button" <?php echo esc_attr($display); ?>><?php _e('Settings', 'titan-security'); ?></a>
				<?php endif;
					$install_button->renderButton();
				endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="clearfix"></div>
</div>

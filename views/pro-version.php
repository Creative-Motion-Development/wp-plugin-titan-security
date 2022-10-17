<div class="wtitan-audit-empty-container">
	<?php _e( 'Please activate your license to use this plugin feature.', 'titan-security' ) ?>
    <a href="<?php echo esc_url(\WBCR\Titan\Plugin::app()->getPluginPageUrl( 'license' )); ?>"
       class="btn btn-primary"><?php _e( 'Activate license', 'titan-security' ) ?></a>
    <a href="https://titansitescanner.com/pricing/"
       class="btn btn-gold"><?php _e( 'Buy license', 'titan-security' ) ?></a>
</div>
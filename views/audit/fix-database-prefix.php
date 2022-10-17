<?php
/**
 * @var \WBCR\Titan\Views $this
 * @var array $data
 */
?>
<div class="wbcr-content-section">
    <div class="wbcr-factory-page-group-header" style="margin:0">
        <strong><?php _e( 'Fixing database prefix', 'titan-security' ); ?></strong>. <p>
			<?php _e( 'With the use of this tweak you can easily replce your database prefix to other keyword and you don’t need to change it manyally.', 'titan-security' ); ?></p>
    </div>

    <form method="post" action="" style="padding:20px;">
        <p>
            <label for="wtitan-new-prefix"><?php _e( 'Please enter new prefix' ) ?></label>
            <input type="text" id="wtitan-new-prefix" name="wtitan_new_prefix"
                   value="<?php echo $data['random_prefix'] ?>">
        </p>
        <p>
            <strong><?php _e( 'You current table prefix is', 'titan-security' ); ?>:</strong>
			<?php echo $data['current_prefix'] ?>
        </p>
		<?php if ( ! is_null( $data['random_prefix'] ) ): ?>
            <input type="hidden" name="wtitan_fixing_issue_id"
                   value="<?php echo esc_attr( $data['fixing_issue_id'] ) ?>">
		<?php endif; ?>
		<?php wp_nonce_field( 'wtitan_change_database_prefix' ); ?>
        <p class="submit">
            <input type="submit" name="wtitan_save_prefix" class="button button-primary"
                   value="<?php _e( 'Save Changes' ) ?>">
        </p>
    </form>
</div>
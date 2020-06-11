<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var \WBCR\Titan\Views $this
 * @var array $data [
 *  bool activate True if the Firewall's auto_prepend_file is active and not because of a subdirectory install.
 * ]
 */
?>

<h2 class="wtitan-modal__title">
	<?php if ( $data['activate'] ): ?>
		<?php _e( 'Success', 'titan-security' ) ?>
	<?php else: ?>
		<?php _e( 'Fail', 'titan-security' ) ?>
	<?php endif; ?>
</h2>

<div class="wtitan-modal__content">
	<?php if ( $data['activate'] ): ?>
        <h4><?php _e( 'Nice work! The firewall is now optimized.', 'titan-security' ); ?></h4>
	<?php else: ?>
        <p><?php _e( 'The changes have not yet taken effect. If you are using LiteSpeed or IIS as your web server or CGI/FastCGI interface, you may need to wait a few minutes for the changes to take effect since the configuration files are sometimes cached. You also may need to select a different server configuration in order to complete this step, but wait for a few minutes before trying. You can try refreshing this page.', 'titan-security' ); ?></p>
	<?php endif; ?>
</div>
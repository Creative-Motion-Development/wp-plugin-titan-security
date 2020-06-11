<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var \WBCR\Titan\Views $this
 *
 * @var array $data [
 *  bool uninstallation_waiting
 *  bool activate True if the Titan Firewall's auto_prepend_file is active and not because of a subdirectory install.
 *  bool subdirectory True if the Titan Firewall's auto_prepend_file is active because of a subdirectory install.
 * ]
 */
?>

<h2 class="wtitan-modal__title">
	<?php if ( empty( $data['uninstallation_waiting'] ) || ! $data['uninstallation_waiting'] ): ?>
		<?php _e( 'Uninstallation was successful!', 'titan-security' ); ?>
	<?php else: ?>
		<?php _e( 'Uninstall Titan Firewall. Please wait...', 'titan-security' ) ?>
	<?php endif; ?>
</h2>

<div class="wtitan-modal__content">
    <ul class="wf-flex-horizontal">
        <li></li>
        <li style="font-size: 16px;">
			<?php if ( isset( $data['uninstallation_waiting'] ) && $data['uninstallation_waiting'] ): ?>
                <div style="text-align: center;">
                    <p><?php _e( 'The auto_prepend_file setting has been successfully removed from .htaccess and .user.ini. Once this change takes effect, Extended Protection Mode will be disabled.', 'titan-security' ); ?></p>

                    <p><?php printf( __( 'Waiting for it to take effect. This may take up to %s.', 'titan-security' ), $data['timeout'] ); ?></p>
                    <img src="<?php echo WTITANP_PLUGIN_URL . '/admin/assets/img/firewall-modal-preloader.gif'; ?>"
                         alt="">
                </div>
			<?php else: ?>
				<?php if ( isset( $data['activate'] ) && $data['activate'] ): ?>
                    <p><?php _e( 'Uninstallation from this site was successful! The Titan Firewall is still active because it is installed in another WordPress installation.', 'titan-security' ); ?></p>
				<?php else: ?>
                    <p><?php _e( 'The changes have not yet taken effect. If you are using LiteSpeed or IIS as your web server or CGI/FastCGI interface, you may need to wait a few minutes for the changes to take effect since the configuration files are sometimes cached. You also may need to select a different server configuration in order to complete this step, but wait for a few minutes before trying. You can try refreshing this page.', 'titan-security' ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
        </li>
    </ul>
</div>

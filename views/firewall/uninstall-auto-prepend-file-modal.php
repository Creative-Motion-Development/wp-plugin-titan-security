<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
<div class="wtitan-uninstall-auto-prepend-modal-content" style="display: none">
	<?php
	$currentAutoPrependFile = ini_get( 'auto_prepend_file' );
	?>
    <p><?php _e( 'Extended Protection Mode of the Titan Web Application Firewall uses the PHP ini setting called <code>auto_prepend_file</code> in order to ensure it runs before any potentially vulnerable code runs. This PHP setting currently refers to the Titan file at:', 'wordfence' ); ?></p>
    <pre class='wtitan-pre'><?php echo esc_html( $currentAutoPrependFile ); ?></pre>
	<?php
	$contents    = file_get_contents( $currentAutoPrependFile );
	$refersToWAF = preg_match( '/define\s*\(\s*(["\'])WFWAF_LOG_PATH\1\s*,\s*(["\']).+?\2\s*\)\s*/', $contents );

	if ( ! $refersToWAF ):
		?>
        <p><?php printf( __( 'Automatic uninstallation cannot be completed, but you may still be able to <a href="%s" target="_blank" rel="noopener noreferrer">manually uninstall extended protection</a>.', 'wordfence' ), "#" ); ?></p>
	<?php else: ?>
        <p><?php _e( 'Before this file can be deleted, the configuration for the <code>auto_prepend_file</code> setting needs to be removed.', 'wordfence' ); ?></p>
		<?php
		$serverInfo = \WBCR\Titan\Server\Info::createFromEnvironment();
		$dropdown   = array(
			array(
				"apache-mod_php",
				__( 'Apache + mod_php', 'titan-security' ),
				$serverInfo->isApacheModPHP(),
				\WBCR\Titan\Server\Helper::instance( 'apache-mod_php' )->getFilesNeededForBackup()
			),
			array(
				"apache-suphp",
				__( 'Apache + suPHP', 'titan-security' ),
				$serverInfo->isApacheSuPHP(),
				\WBCR\Titan\Server\Helper::instance( 'apache-suphp' )->getFilesNeededForBackup()
			),
			array(
				"cgi",
				__( 'Apache + CGI/FastCGI', 'titan-security' ),
				$serverInfo->isApache() && ! $serverInfo->isApacheSuPHP() && ( $serverInfo->isCGI() || $serverInfo->isFastCGI() ),
				\WBCR\Titan\Server\Helper::instance( 'cgi' )->getFilesNeededForBackup()
			),
			array(
				"litespeed",
				__( 'LiteSpeed/lsapi', 'titan-security' ),
				$serverInfo->isLiteSpeed(),
				\WBCR\Titan\Server\Helper::instance( 'litespeed' )->getFilesNeededForBackup()
			),
			array(
				"nginx",
				__( 'NGINX', 'titan-security' ),
				$serverInfo->isNGINX(),
				\WBCR\Titan\Server\Helper::instance( 'nginx' )->getFilesNeededForBackup()
			),
			array(
				"iis",
				__( 'Windows (IIS)', 'titan-security' ),
				$serverInfo->isIIS(),
				\WBCR\Titan\Server\Helper::instance( 'iis' )->getFilesNeededForBackup()
			),
			array( "manual", __( 'Manual Configuration', 'titan-security' ), false, array() ),
		);

		$hasRecommendedOption = false;
		$wafPrependOptions    = '';
		foreach ( $dropdown as $option ) {
			list( $optionValue, $optionText, $selected ) = $option;
			$wafPrependOptions .= "<option value=\"{$optionValue}\"" . ( $selected ? ' selected' : '' ) . ">{$optionText}" . ( $selected ? ' (recommended based on our tests)' : '' ) . "</option>\n";
			if ( $selected ) {
				$hasRecommendedOption = true;
			}
		}

		if ( ! $hasRecommendedOption ): ?>
            <p><?php _e( 'If you know your web server\'s configuration, please select it from the list below.', 'titan-security' ); ?></p>
		<?php else: ?>
            <p><?php _e( 'We\'ve preselected your server configuration based on our tests, but if you know your web server\'s configuration, please select it now. You can also choose "Manual Configuration" for alternate installation details.', 'titan-security' ); ?></p>
		<?php endif; ?>
        <select name='serverConfiguration' id='wtitan-server-config'>
			<?php echo $wafPrependOptions; ?>
        </select>
		<?php
		$adminURL = network_admin_url( 'admin.php' );
		$wfnonce  = wp_create_nonce( 'titan_auto_prepend' );
		foreach ( $dropdown as $option ):
			list( $optionValue, $optionText, $selected ) = $option;
			$class           = preg_replace( '/[^a-z0-9\-]/i', '', $optionValue );
			$helper          = new \WBCR\Titan\Server\Helper( $optionValue, null );
			$backups         = $helper->getFilesNeededForBackup();
			$filteredBackups = array();
			foreach ( $backups as $index => $backup ) {
				if ( ! file_exists( $backup ) ) {
					continue;
				}

				$filteredBackups[ $index ] = $backup;
			}
			$jsonBackups = json_encode( array_map( 'basename', $filteredBackups ) );
			?>
            <div class="wtitan-backups wtitan-backups-<?php echo $class; ?>" style="display: none;"
                 data-backups="<?php echo esc_attr( $jsonBackups ); ?>">
				<?php if ( count( $filteredBackups ) ): ?>
                    <p><?php _e( 'Please download a backup of the following files before we make the necessary changes:', 'titan-security' ); ?></p><?php endif; ?>
                <ul class="wtitan-backup-file-list">
					<?php
					foreach ( $filteredBackups as $index => $backup ) {
						$download_backup_url = add_query_arg( array(
							'page'                 => 'firewall-' . \WBCR\Titan\Plugin::app()->getPluginName(),
							'action'               => 'download-backup',
							'backup_index'         => $index,
							'server_configuration' => $helper->getServerConfig(),
							'_wpnonce'             => $wfnonce,
						), $adminURL );
						echo '<li><a class="button button-default wtitan-backup-download" data-backup-index="' . $index . '" href="' . esc_url( $download_backup_url ) . '">' . sprintf( __( 'Download %s', 'titan-security' ), esc_html( basename( $backup ) ) ) . '</a></li>';
					}
					?>
                </ul>
            </div>
		<?php endforeach; ?>
	<?php endif; ?>

    <div class="wtitan-modal-footer">
        <ul class="wtitan-flex-horizontal wtitan-flex-full-width">
            <li class="wtitan-waf-download-instructions"><?php _e( 'Once you have downloaded the files, click Continue to complete uninstallation.', 'wordfence' ); ?></li>
        </ul>
    </div>
</div>
<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
} ?>

<div class="wtitan-install-auto-prepend-modal-content" style="display: none">
	<?php
	$currentAutoPrependFile = ini_get('auto_prepend_file');
	if( empty($currentAutoPrependFile) ):
		?>
		<p><?php _e('To make your site as secure as possible, the T Web Application Firewall is designed to run via a PHP setting called <code>auto_prepend_file</code>, which ensures it runs before any potentially vulnerable code runs.', 'titan-security'); ?></p>
	<?php else: ?>
		<p><?php _e('To make your site as secure as possible, the Titan Web Application Firewall is designed to run via a PHP setting called <code>auto_prepend_file</code>, which ensures it runs before any potentially vulnerable code runs. This PHP setting is currently in use, and is including this file:', 'titan-security'); ?></p>
		<pre class='wtitan-pre'><?php echo esc_html($currentAutoPrependFile); ?></pre>
		<p><?php _e('If you don\'t recognize this file, please <a href="https://wordpress.org/support/plugin/wordfence" target="_blank" rel="noopener noreferrer">contact us on the
					WordPress support forums</a> before proceeding.', 'titan-security'); ?></p>
		<p><?php _e('You can proceed with the installation and we will include this from within our <code>titan-firewall.php</code> file which should maintain compatibility with your site, or you can opt to override the existing PHP setting.', 'titan-security'); ?></p>
		<ul id="wtitan-include-prepend" class="wtitan-switch">
			<li class="wtitan-active" data-option-value="include"><?php _e('Include', 'titan-security'); ?></li>
			<li data-option-value="override"><?php _e('Override', 'titan-security'); ?></li>
		</ul>
	<?php endif; ?>
	<div class="wtitan-notice">
		<strong><?php _e('NOTE:', 'titan-security'); ?></strong> <?php _e('If you have separate WordPress installations with Titan installed within a subdirectory of this site, it is recommended that you perform the Firewall installation procedure on those sites before this one.', 'titan-security'); ?>
	</div>
	<?php
	$serverInfo = \WBCR\Titan\Server\Info::createFromEnvironment();
	$dropdown = array(
		array(
			"apache-mod_php",
			__('Apache + mod_php', 'titan-security'),
			$serverInfo->isApacheModPHP(),
			\WBCR\Titan\Server\Helper::instance('apache-mod_php')->getFilesNeededForBackup()
		),
		array(
			"apache-suphp",
			__('Apache + suPHP', 'titan-security'),
			$serverInfo->isApacheSuPHP(),
			\WBCR\Titan\Server\Helper::instance('apache-suphp')->getFilesNeededForBackup()
		),
		array(
			"cgi",
			__('Apache + CGI/FastCGI', 'titan-security'),
			$serverInfo->isApache() && !$serverInfo->isApacheSuPHP() && ($serverInfo->isCGI() || $serverInfo->isFastCGI()),
			\WBCR\Titan\Server\Helper::instance('cgi')->getFilesNeededForBackup()
		),
		array(
			"litespeed",
			__('LiteSpeed/lsapi', 'titan-security'),
			$serverInfo->isLiteSpeed(),
			\WBCR\Titan\Server\Helper::instance('litespeed')->getFilesNeededForBackup()
		),
		array(
			"nginx",
			__('NGINX', 'titan-security'),
			$serverInfo->isNGINX(),
			\WBCR\Titan\Server\Helper::instance('nginx')->getFilesNeededForBackup()
		),
		array(
			"iis",
			__('Windows (IIS)', 'titan-security'),
			$serverInfo->isIIS(),
			\WBCR\Titan\Server\Helper::instance('iis')->getFilesNeededForBackup()
		),
		array("manual", __('Manual Configuration', 'titan-security'), false, array()),
	);

	$hasRecommendedOption = false;
	$wafPrependOptions = '';
	foreach($dropdown as $option) {
		list($optionValue, $optionText, $selected) = $option;
		$wafPrependOptions .= "<option value=\"{$optionValue}\"" . ($selected ? ' selected' : '') . ">{$optionText}" . ($selected ? ' (recommended based on our tests)' : '') . "</option>\n";
		if( $selected ) {
			$hasRecommendedOption = true;
		}
	}

	if( !$hasRecommendedOption ): ?>
		<p><?php _e('If you know your web server\'s configuration, please select it from the list below.', 'titan-security'); ?></p>
	<?php else: ?>
		<p><?php _e('We\'ve preselected your server configuration based on our tests, but if you know your web server\'s configuration, please select it now. You can also choose "Manual Configuration" for alternate installation details.', 'titan-security'); ?></p>
	<?php endif; ?>
	<select name='serverConfiguration' id='wtitan-server-config'>
		<?php echo $wafPrependOptions; ?>
	</select>
	<div class="wtitan-notice wtitan-nginx-waf-config" style="display: none;"><?php printf(__('Part of the Firewall configuration procedure for NGINX depends on creating a <code>%s</code> file in the root of your WordPress installation. This file can contain sensitive information and public access to it should be restricted. We have <a href="%s" target="_blank" rel="noreferrer noopener">instructions on our documentation site</a> on what directives to put in your nginx.conf to fix this.', 'titan-security'), esc_html(ini_get('user_ini.filename') ? ini_get('user_ini.filename') : __('(.user.ini)', 'titan-security')), 'http://site.com'); ?></div>
	<div class="wtitan-manual-waf-config" style="display: none;">
		<p><?php printf(__('If you are using a web server not listed in the dropdown or if file permissions prevent the installer from completing successfully, you will need to perform the change manually. Click Continue below to create the required file and view manual installation instructions.', 'titan-security')); ?></p>
	</div>
	<?php
	$adminURL = network_admin_url('admin.php');
	$wfnonce = wp_create_nonce('titan_auto_prepend');
	foreach($dropdown as $option):
		list($optionValue, $optionText, $selected) = $option;
		$class = preg_replace('/[^a-z0-9\-]/i', '', $optionValue);
		$helper = new \WBCR\Titan\Server\Helper($optionValue, null);
		$backups = $helper->getFilesNeededForBackup();
		$filteredBackups = array();
		foreach($backups as $index => $backup) {
			if( !file_exists($backup) ) {
				continue;
			}

			$filteredBackups[$index] = $backup;
		}
		$jsonBackups = json_encode(array_map('basename', $filteredBackups));
		?>
		<div class="wtitan-backups wtitan-backups-<?php echo $class; ?>" style="display: none;" data-backups="<?php echo esc_attr($jsonBackups); ?>">
			<?php if( count($filteredBackups) ): ?>
				<p><?php _e('Please download a backup of the following files before we make the necessary changes:', 'titan-security'); ?></p><?php endif; ?>
			<ul class="wtitan-backup-file-list">
				<?php
				foreach($filteredBackups as $index => $backup) {
					$download_backup_url = add_query_arg(array(
						'page' => 'firewall-' . \WBCR\Titan\Plugin::app()->getPluginName(),
						'action' => 'download-backup',
						'backup_index' => $index,
						'server_configuration' => $helper->getServerConfig(),
						'_wpnonce' => $wfnonce,
					), $adminURL);
					echo '<li><a class="button button-default wtitan-backup-download" data-backup-index="' . $index . '" href="' . esc_url($download_backup_url) . '">' . sprintf(__('Download %s', 'titan-security'), esc_html(basename($backup))) . '</a></li>';
				}
				?>
			</ul>
		</div>
	<?php endforeach; ?>

	<div class="wtitan-modal-footer">
		<ul class="wtitan-flex-horizontal wtitan-flex-full-width">
			<li class="wtitan-download-instructions"><?php _e('Once you have downloaded the files, click Continue to complete the setup.', 'titan-security'); ?></li>

		</ul>
	</div>
</div>
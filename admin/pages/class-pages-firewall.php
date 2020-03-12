<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
use WBCR\Titan\Views;

if( !defined('ABSPATH') ) {
	exit;
}

/**
 * The file contains a short help info.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 2019 Webraftic Ltd
 * @version       1.0
 */
class Firewall extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'firewall';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-tagcloud';

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_position = 0;

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var string
	 */
	public $menu_target = 'options-general.php';

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $internal = false;

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $add_link_to_plugin_actions = true;

	/**
	 * @var object|\WBCR\Titan\Views
	 */
	public $view;

	/**
	 * @var object|\WBCR\Titan\Model\Firewall
	 */
	public $firewall;

	/**
	 * Logs constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->plugin = $plugin;

		$this->menu_title = __('Titan security', 'anti-spam');
		$this->page_menu_short_description = __('Stops Complex Attacks', 'anti-spam');

		$this->view = $this->plugin->view();
		$this->firewall = new \WBCR\Titan\Model\Firewall();

		parent::__construct($plugin);

		add_action('admin_footer', [$this, 'print_confirmation_modal_tpl']);
	}

	public function getPageTitle()
	{
		return __('Firewall', 'anti-spam');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/firewall-dashboard.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/libs/circular-progress.js', ['jquery']);

		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/libs/sweetalert2.css');
		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/sweetalert-custom.css');

		$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/libs/sweetalert3.min.js');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/libs/popover.min.js');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/firewall-dashboard.js');
	}


	/**
	 * {@inheritdoc}
	 */
	public function showPageContent()
	{

		$firewall_mode = $this->plugin->getPopulateOption('firewall_mode');
		$firewall_status_percent = $this->firewall->wafStatus();
		if( $firewall_status_percent > 0.70 ) {
			$firewall_status_color = "#1fa02fc9";
		} else {
			$firewall_status_color = "#ff5722";
		}
		?>
		<div class="wbcr-content-section">
			<div class="wbcr-factory-page-group-header" style="margin:0">
				<strong><?php _e('Web Application Firewall (WAF)', 'titan'); ?></strong>. <p>
					<?php _e('The Wordfence Web Application Firewall is a PHP based, application level firewall that filters out
					malicious requests to your site.', 'titan'); ?></p>
			</div>


			<div id="wtitan-firewall-dashboard-top-section">
				<table>
					<tr>
						<td>
							<div class="wtitan-status-block wtitan-status--enabled" style="display: <?php echo("enabled" === $firewall_mode ? 'block' : 'none') ?>;">
								<h4><?php _e('Titan Firewall Activated', 'titan'); ?></h4>
								<span class="dashicons dashicons-yes-alt" style="font-size:80px;width: 80px;height:80px;color:#1fa02fc9;"></span>
							</div>
							<div class="wtitan-status-block wtitan-status--learning-mode" style="display: <?php echo("learning-mode" === $firewall_mode ? 'block' : 'none') ?>;">
								<h4><?php _e('Titan Firewall in Learning Mode', 'titan'); ?></h4>
								<span style="font-size:80px;width: 80px;height:80px;color:#fcb214;" class="dashicons dashicons-clock"></span>
							</div>
							<div class="wtitan-status-block wtitan-status--disabled" style="display: <?php echo("disabled" === $firewall_mode ? 'block' : 'none') ?>;">
								<h4 style="color:#9c3926"><?php _e('Titan Firewall Deactivated', 'titan'); ?></h4>
								<span class="dashicons dashicons-dismiss" style="font-size:80px;width: 80px;height:80px;color:#f59888;"></span>
							</div>
						</td>
						<td>
							<div id="wtitan-circle-firewall-coverage" class="wtitan-status-circular"></div>
							<script>
								jQuery(document).ready(function($) {
									$('#wtitan-circle-firewall-coverage').wfCircularProgress({
										endPercent: <?php echo $firewall_status_percent; ?>,
										color: '<?php echo $firewall_status_color; ?>',
										inactiveColor: '#ececec',
										strokeWidth: 1,
										diameter: 100,
									});
								});
							</script>
							<h4><?php _e('Web Application Firewall', 'titan'); ?></h4>
							<p><?php _e('Stops Complex Attacks', 'titan'); ?></p>
							<div id="wtitan-status-tooltip" style="display: none">
								<strong><?php _e('How do I get to 100%?', 'titan'); ?></strong>
								<ul>
									<li><?php _e('30% Enable the Titan Firewall.', 'titan'); ?></li>
									<li><?php _e('70% Optimize the Titan Firewall.', 'titan'); ?></li>
									<!--<li>30% Disable learning mode.</li>
									<li>35% Enable Real-Time IP Blacklist.</li>-->
									<li><a href="#"><?php _e('How does Titan determine this?', 'titan'); ?></a></li>
								</ul>
						</td>
					</tr>
				</table>
			</div>
			<div id="wtitan-firewall-dashboard-top-section">
				<table>
					<tr>
						<td>
							<h4><?php _e('Web Application Firewall Status', 'titan'); ?></h4>
							<p><?php _e('Enabled and Protecting: In this mode, the Titan Web Application Firewall is actively
								blocking requests matching known attack patterns and is actively protecting your site
								from attackers.', 'titan'); ?></p>

							<select id="js-wtitan-firewall-mode" data-nonce="<?php echo wp_create_nonce('wtitan_change_firewall_mode') ?>" name="wafStatus" tabindex="-1" aria-hidden="true" style="width: 200px;">
								<option selected="" class="wafStatus-enabled" value="enabled"<?php selected("enabled", $firewall_mode) ?>>
									<?php _e('Enabled and Protecting', 'titan'); ?>
								</option>
								<option class="wafStatus-learning-mode" value="learning-mode"<?php selected("learning-mode", $firewall_mode) ?>>
									<?php _e('Learning Mode', 'titan'); ?>
								</option>
								<option class="wafStatus-disabled" value="disabled"<?php selected("disabled", $firewall_mode) ?>>
									<?php _e('Disabled', 'titan'); ?>
								</option>
							</select>
						</td>
						<td>
							<h4><?php _e('Protection Level', 'titan'); ?></h4>
							<?php if( $this->firewall->protectionMode() == \WBCR\Titan\Model\Firewall::PROTECTION_MODE_EXTENDED && !$this->firewall->isSubDirectoryInstallation() ): ?>
								<p class="wf-no-top">
									<strong><?php _e('Extended Protection:', 'titan'); ?></strong> <?php _e('All PHP requests will be processed by the firewall prior to running.', 'titan'); ?>
								</p>
								<p><?php printf(__('If you\'re moving to a new host or a new installation location, you may need to temporarily disable extended protection to avoid any file not found errors. Use this action to remove the configuration changes that enable extended protection mode or you can <a href="%s" target="_blank" rel="noopener noreferrer">remove them manually</a>.', 'titan'), '#'); ?></p>
								<p class="wf-no-top">
									<a class="button button-default" href="#" id="js-wtitan-firewall-uninstall"><?php _e('Remove Extended Protection', 'titan'); ?></a>
								</p>
							<?php elseif( $this->firewall->isSubDirectoryInstallation() ): ?>
								<p class="wf-no-top">
									<strong><?php _e('Existing WAF Installation Detected:', 'titan'); ?></strong> <?php _e('You are currently running the Titan Web Application Firewall from another WordPress installation. Please configure the firewall to run correctly on this site.', 'titan'); ?>
								</p>
								<p>
									<a class="button button-primary" href="#" id="js-wtitan-optimize-firewall-protection"><?php _e('Optimize the Titan Firewall', 'titan'); ?></a>
								</p>
							<?php else: ?>
								<p class="wf-no-top">
									<strong><?php _e('Basic WordPress Protection:', 'titan'); ?></strong> <?php _e('The plugin will load as a regular plugin after WordPress has been loaded, and while it can block many malicious requests, some vulnerable plugins or WordPress itself may run vulnerable code before all plugins are loaded.', 'titan'); ?>
								</p>
								<p>
									<a class="button button-primary" href="#" id="js-wtitan-optimize-firewall-protection"><?php _e('Optimize the Titan Firewall', 'titan'); ?></a>
								</p>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 6.5.2
	 */
	public function print_confirmation_modal_tpl()
	{
		$firewall_mode = $this->plugin->getPopulateOption('firewall_mode');
		?>
		<script type="text/html" id="wtitan-tmpl-default-modal">
			<?php if( $this->firewall->protectionMode() == \WBCR\Titan\Model\Firewall::PROTECTION_MODE_EXTENDED && !$this->firewall->isSubDirectoryInstallation() ): ?>
				<div class="wtitan-uninstall-auto-prepend-modal-content" style="display: none">
					<?php echo $this->view->print_template('firewall/uninstall-auto-prepend-file-modal'); ?>
				</div>
			<?php else: ?>
				<div class="wtitan-install-auto-prepend-modal-content" style="display: none">
					<?php echo $this->view->print_template('firewall/auto-prepend-file-modal'); ?>
				</div>
			<?php endif; ?>
		</script>
		<?php
	}


	public function downloadBackupAction()
	{
		//if( !WFWAF_AUTO_PREPEND || WFWAF_SUBDIRECTORY_INSTALL ) { //Not yet installed

		check_admin_referer('titan_auto_prepend');
		if( isset($_GET['server_configuration']) && \WBCR\Titan\Server\Helper::isValidServerConfig($_GET['server_configuration']) ) {
			$helper = new \WBCR\Titan\Server\Helper($_GET['server_configuration']);

			$helper->downloadBackups(isset($_GET['backup_index']) ? absint($_GET['backup_index']) : 0);
		}
		//}
		/*	else { //Already installed
				if (isset($_GET['action']) && $_GET['action'] == 'removeAutoPrepend') {
					check_admin_referer('wfWAFRemoveAutoPrepend', 'wfnonce');
					if (isset($_GET['serverConfiguration']) && wfWAFAutoPrependHelper::isValidServerConfig($_GET['serverConfiguration'])) {
						$helper = new wfWAFAutoPrependHelper($_GET['serverConfiguration']);
						if (isset($_GET['downloadBackup'])) {
							$helper->downloadBackups(isset($_GET['backupIndex']) ? absint($_GET['backupIndex']) : 0);
						}
					}
				}
			}*/
	}

}

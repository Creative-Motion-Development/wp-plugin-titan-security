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
		if( $this->plugin->is_premium() )
		{
			$data = array();
			$data['this-firewall'] = $this->firewall;
			$data['firewall_mode'] = $this->plugin->getPopulateOption('firewall_mode');
			$data['firewall_status_percent'] = $this->firewall->wafStatus();
			if( $data['firewall_status_percent'] > 0.70 ) {
				$data['firewall_status_color'] = "#1fa02fc9";
			} else {
				$data['firewall_status_color'] = "#ff5722";
			}

			$this->view->print_template('firewall/firewall-page', $data);
		}
		else
			require_once WTITAN_PLUGIN_DIR."/admin/view.nolicense.php";
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

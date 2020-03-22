<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Страница общих настроек для этого плагина.
 *
 * Не поддерживает режим работы с мультисаймами.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 2019 Webraftic Ltd
 * @version       1.0
 */
class Firewall_Blocking extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var string
	 */
	public $id = "firewall-blocking";

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var string
	 */
	public $type = 'page';

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var string
	 */
	public $page_menu_dashicon = 'dashicons-testimonial';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public $page_parent_page = "firewall";

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * @var object|\WBCR\Titan\Views
	 */
	public $view;

	/**
	 * WBCR\Page\Settings constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->menu_title = __('Blocking', 'anti-spam');
		$this->page_menu_short_description = __('Firewall blocking', 'anti-spam');

		parent::__construct($plugin);

		$this->plugin = $plugin;
		$this->view = $this->plugin->view();

		if( $this->plugin->is_premium() ) {
			add_action('admin_footer', [$this, 'print_modal_tpl']);
		}
	}

	/**
	 * Enqueue page assets
	 *
	 * @return void
	 * @since 6.2
	 * @see   Wbcr_FactoryPages000_AdminPage
	 *
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		if( $this->plugin->is_premium() ) {
			$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/firewall-ips-blocking.css');
			$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/libs/sweetalert2.css');
			$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/sweetalert-custom.css');

			$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/libs/sweetalert3.min.js');
			$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/firewall-block-ip.js');
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 6.5.2
	 */
	public function print_modal_tpl()
	{
		if( !(isset($_GET['page']) && $_GET['page'] === $this->getResultId()) ) {
			return;
		}

		echo $this->view->print_template('firewall/modal-template-default', [
			'id' => 'block-ips-modal',
			'title' => __('Block Ip Address'),
			'content' => $this->view->get_template('firewall/modal-content-block-ips')
		]);
	}

	public function showPageContent()
	{
		if( !$this->plugin->is_premium() ) {
			$this->plugin->view->print_template('require-license-activate');

			return;
		}

		?>
		<div class="wbcr-factory-page-group-header">
			<strong>Blocking Ip.</strong>
			<p>Blocking Ip.</p>
		</div>
		<div class="wtitan-ips-blocking">
			<ul class="wtitan-ips-blocking__controls">
				<li class="wtitan-ips-blocking__controls-left">
					<input type="text" placeholder="<?php _e('Filter by Type, Detail, or Reason', 'titan-security') ?>" style="width:200px;">
					<a href="#" id="wf-blocks-apply-filter" class="btn btn-default"><?php _e('Filter', 'titan-security') ?></a>
				</li>
				<li class="wtitan-ips-blocking__controls-right">
					<a href="#" id="wtitan-blocks-ips" data-nonce="<?php echo wp_create_nonce('wtitan_block_ip') ?>" class="btn btn-primary">Block
						<?php _e('Ip Address', 'titan-security') ?>
					</a>
					<a href="#" id="blocks-bulk-unblock" class="btn btn-default disabled">
						<?php _e('Unblock', 'titan-security') ?>
					</a>&nbsp; &nbsp;
					<a href="#" id="blocks-bulk-make-permanent" class="btn btn-default disabled">
						<?php _e('Make Permanent', 'titan-security') ?>
					</a>&nbsp;
					&nbsp;
					<a href="" id="blocks-export-ips" class="btn btn-default disabled">Export All IPs</a>
				</li>
			</ul>

			<table class="wtitan-ips-blocking__table">
				<thead>
				<tr class="wf-blocks-columns">
					<th style="width: 2%;text-align: center">
						<input type="checkbox">
					</th>
					<th data-column="type"><?php _e('Block Type', 'titan-security') ?></th>
					<th data-column="detail"><?php _e('Detail', 'titan-security') ?></th>
					<th data-column="ruleAdded"><?php _e('Rule Added', 'titan-security') ?></th>
					<th data-column="reason"><?php _e('Reason', 'titan-security') ?></th>
					<th data-column="expiration"><?php _e('Expiration', 'titan-security') ?></th>
					<th data-column="blockCount"><?php _e('Block Count', 'titan-security') ?></th>
					<th data-column="lastAttempt"><?php _e('Last Attempt', 'titan-security') ?></th>
				</tr>
				</thead>
				<tbody></tbody>
				<tfoot></tfoot>
			</table>
		</div>
		<?php
	}

}

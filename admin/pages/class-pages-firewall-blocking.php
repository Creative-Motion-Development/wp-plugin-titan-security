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

		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/firewall-settings.css');
	}

	/**
	 * Permalinks options.
	 *
	 * @return mixed[]
	 * @since 6.2
	 */
	public function getPageOptions()
	{
		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __('Create a Blocking Rule', 'anti-spam') . '</strong></div>'
		];

		$options[] = [
			'type' => 'dropdown',
			'name' => 'brute_force_breach_passwds',
			'way' => 'buttons',
			'title' => __('Block Type	
', 'clearfy'),
			'data' => [
				['ip', __('Ip address', 'clearfy')],
				['country', __('Country', 'clearfy')],
				[
					'custom_pattern',
					__('Custom pattern', 'clearfy')
				]
			],
			'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'grey'],
			'hint' => __('In some cases, you need to disable the floating top admin panel. You can disable this panel.', 'clearfy') . '<br><b>Clearfy</b>: ' . __('Disable admin top bar.', 'clearfy'),

			'default' => 'ip',
		];

		$options[] = [
			'type' => 'html',
			'html' => [$this, 'get_add_blocking_rule_section']
		];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __('Current blocks for antispam.loc', 'anti-spam') . '</strong></div>'
		];

		/*$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'save_spam_comments',
			'title'   => __( 'Save spam comments', 'anti-spam' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'Save spam comments into spam section. Useful for testing how the plugin works.', 'anti-spam' ),
			'default' => true
		];*/

		$options[] = [
			'type' => 'html',
			'html' => [$this, 'get_current_blocks_section']
		];

		$form_options = [];

		$form_options[] = [
			'type' => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters('wantispam/settings_form/options', $form_options, $this);
	}

	public function showPageContent()
	{
		?>
		<!--<div class="wtitan-ip-blocking-form">
			<table class="wtitan-ip-blocking-form__table">
				<tbody>
				<tr>
					<td>
						<input type="text" placeholder="Enter an IP address">
					</td>
				</tr>
				<tr>
					<td>
						<input type="text" placeholder="e.g., 192.168.200.200 - 192.168.200.220 or 192.168.200.0/24">
					</td>
				</tr>
				<tr>
					<td>
						<textarea placeholder="Enter a reason" maxlength="250"></textarea>
					</td>
				</tr>
				</tbody>
			</table>
		</div>-->

		<div class="wbcr-factory-page-group-header">
			<strong>Blocking Ip.</strong>
			<p>Blocking Ip.</p>
		</div>
		<div class="wtitan-ip-blocking">
			<ul class="wtitan-ip-blocking__controls">
				<li class="wtitan-ip-blocking__controls-left">
					<input type="text" placeholder="Filter by Type, Detail, or Reason" style="width:200px;">
					<a href="#" id="wf-blocks-apply-filter" class="btn btn-default">Filter</a>
				</li>
				<li class="wtitan-ip-blocking__controls-right">
					<a href="" id="blocks-ips" class="btn btn-default">Block Ip Address</a>
					<a href="#" id="blocks-bulk-unblock" class="btn btn-default disabled">Unblock</a>&nbsp;
					&nbsp;
					<a href="#" id="blocks-bulk-make-permanent" class="btn btn-default disabled">
						Make Permanent
					</a>&nbsp;
					&nbsp;
					<a href="" id="blocks-export-ips" class="btn btn-default disabled">Export All IPs</a>
				</li>
			</ul>

			<table class="wtitan-ip-blocking__table">
				<thead>
				<tr class="wf-blocks-columns">
					<th style="width: 2%;text-align: center">
						<input type="checkbox">
					</th>
					<th data-column="type">Block Type</th>
					<th data-column="detail">Detail</th>
					<th data-column="ruleAdded">Rule Added</th>
					<th data-column="reason">Reason</th>
					<th data-column="expiration">Expiration</th>
					<th data-column="blockCount">Block Count</th>
					<th data-column="lastAttempt">Last Attempt</th>
				</tr>
				</thead>
				<tbody></tbody>
				<tfoot></tfoot>
			</table>
			<!--<a href="#" class="button button-default">Block Ip Address</a>-->
		</div>


		<?php
	}

}

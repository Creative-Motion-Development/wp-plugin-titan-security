<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
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
class Firewall_Login_Attempts extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'firewall-activity-log';

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
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;


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
	public function __construct($plugin)
	{
		$this->plugin = $plugin;

		$this->menu_title = __('Login Attempts', 'titan-security');
		$this->page_menu_short_description = __('Login Attempts', 'titan-security');

		$this->view = \WBCR\Titan\Plugin::app()->view();

		parent::__construct($plugin);
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

		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/firewall/firewall-attacks-log.css');
	}


	/**
	 * {@inheritdoc}
	 */
	public function showPageContent()
	{

		?>
		<div class="wbcr-factory-page-group-header">
			<strong><?php _e('Login Attempts', 'titan-security') ?></strong>
			<p>
				<?php _e('In this table, you can see the attacks on your site that the Titan firewall repelled.', 'titan-security') ?>
			</p>
		</div>
		
		<div class="wtitan-attacks-log wtitan-section-disabled">

			<table class="wtitan-attacks-log__table wp-list-table widefat striped plugins wp-list-table__plugins">
				<thead>
				<tr>
					<th class='wtitan-attacks-log__table-column'>
						<strong><?php _e('IP', 'titan-security'); ?></strong></th>
					<th class='wtitan-attacks-log__table-column'>
						<strong><?php _e('Username', 'titan-security'); ?></strong>
					</th>
					<th class='wtitan-attacks-log__table-column'>
						<strong><?php _e('Success', 'titan-security'); ?></strong>
					</th>
					<th class='wtitan-attacks-log__table-column'>
						<strong><?php _e('Date', 'titan-security'); ?></strong>
					</th>
				</tr>
				</thead>
				<tbody id="the-list">

				</tbody>
			</table>

		</div>
		<?php
	}
}

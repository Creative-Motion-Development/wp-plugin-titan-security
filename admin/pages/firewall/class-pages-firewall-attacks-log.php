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
class Firewall_Attacks_Log extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'firewall-attack-log';

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
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->plugin = $plugin;

		$this->menu_title = __('Attacks log', 'anti-spam');
		$this->page_menu_short_description = __('Attacks log', 'anti-spam');

		$this->view = $this->plugin->view();

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

		if( $this->plugin->is_premium() ) {
			$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/firewall-attacks-log.css');
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function showPageContent()
	{
		if( !$this->plugin->is_premium() ) {
			$this->view->print_template('require-license-activate');

			return;
		}

		global $wpdb;
		$current_page = $this->plugin->request->get('pagenum', 1, 'intval');
		$limit = 20;
		$offset = ($current_page - 1) * $limit;
		$total = $wpdb->get_var("SELECT COUNT(`id`) FROM {$wpdb->prefix}wfhits");
		$num_of_pages = (int)ceil($total / $limit);
		$hits = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wfhits ORDER BY attackLogTime DESC LIMIT %d, %d", $offset, $limit));

		?>
		<div class="wbcr-factory-page-group-header">
			<strong><?php _e('Attack list', 'titan-security') ?></strong>
			<p>
				<?php _e('In this table, you can see the attacks on your site that the Titan firewall repelled.', 'titan-security') ?>
			</p>
		</div>

		<div class="wtitan-attacks-log">
			<!--<p>
				<select name="wbcr_upm_bulk_actions" id="wbcr_upm_bulk_actions">
					<option value="0"><?php /*_e('Bulk actions', 'titan-security'); */ ?></option>
				</select>
				<input type="submit" name="wbcr_upm_apply" id="wbcr_upm_apply" class='button button-alt' value='<?php /*_e("Apply", "webcraftic-updates-manager"); */ ?>'>
			</p>-->
			<?php $page_links = paginate_links(array(
				'base' => add_query_arg('pagenum', '%#%'),
				'format' => '',
				'prev_text' => __('«', 'titan-servurity'),
				'next_text' => __('»', 'titan-servurity'),
				'total' => $num_of_pages,
				'current' => $current_page
			));
			?>
			<?php if( $page_links ): ?>
				<div class="wtitan-attacks-log__nav">
					<div class="wtitan-attacks-log__pages"><?php echo $page_links; ?></div>
				</div>
			<?php endif; ?>
			<table class="wtitan-attacks-log__table wp-list-table widefat striped plugins wp-list-table__plugins">
				<thead>
				<tr>
					<th class='wtitan-attacks-log__table-column'>
						<strong><?php _e('IP', 'titan-security'); ?></strong></th>
					<th class='wtitan-attacks-log__table-column'>
						<strong><?php _e('Event', 'titan-security'); ?></strong>
					</th>
					<th class='wtitan-attacks-log__table-column'>
						<strong><?php _e('...', 'titan-security'); ?></strong>
					</th>

					<th class='wtitan-attacks-log__table-column'>
						<strong><?php _e('Attack time', 'titan-security'); ?></strong>
					</th>
				</tr>
				</thead>
				<tbody id="the-list">
				<?php if( !empty($hits) ): ?>
					<?php foreach($hits as $hit): ?>
						<tr>
							<td class="wtitan-attacks-log__table-column">
								<?php echo esc_html(\WBCR\Titan\Firewall\Utils::inet_ntop($hit->IP)) ?>
							</td>
							<td class="wtitan-attacks-log__table-column wtitan-attacks-log__table-column-event">
								<ul>
									<li>
										<span class="wtitan-attacks-log__table-label--red"><?php echo esc_html($hit->actionDescription) ?></span>
									</li>
									<li><?php echo esc_html($hit->URL) ?></li>
									<li><strong>Status Code: <?php echo esc_html($hit->statusCode) ?></strong></li>
								</ul>
							</td>
							<!-- отключить все обновления -->
							<td class="wtitan-attacks-log__table-column">
								<?php //echo esc_html($hit->IP) ?>
							</td>
							<!-- отключить обновления переводов -->
							<td class="wtitan-attacks-log__table-column">
								<?php echo esc_html(date("d.m.Y H:i:s", $hit->attackLogTime)) ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
			<?php if( $page_links ): ?>
				<div class="wtitan-attacks-log__nav">
					<div class="wtitan-attacks-log__pages"><?php echo $page_links; ?></div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}

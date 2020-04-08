<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Base class for Titan pages
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Base extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * Scanner page constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		parent::__construct($plugin);
		$this->menuIcon = WTITAN_PLUGIN_URL . '/admin/assets/img/titan-icon.png';
	}

	/**
	 * Add assets
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets($scripts, $styles)
	{
		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/titan-security.css');

		parent::assets($scripts, $styles);
	}

	public function getPluginTitle() {
		return "<span class='wt-plugin-header-logo'>&nbsp;</span>".__( 'Titan Anti-spam & Security', 'titan-security' );
	}
	/**
	 * Создает html разметку виджета поддержки
	 *
	 * @since  7.0.6
	 */
	public function showSupportWidget() {
		$free_support_url = $this->plugin->get_support()->get_contacts_url();
		$hot_support_url  = $this->plugin->get_support()->get_site_url() . '/support';

		?>
		<div id="wbcr-clr-support-widget" class="wbcr-factory-sidebar-widget">
			<p><strong><?php _e( 'Having Issues?', 'titan-security' ); ?></strong></p>
			<div class="wbcr-clr-support-widget-body">
				<p>
					<?php _e( 'We provide free support for this plugin. If you are pushed with a problem, just create a new ticket. We will definitely help you!', 'titan-security' ); ?>
				</p>
				<ul>
					<li><span class="dashicons dashicons-sos"></span>
						<a href="<?= $free_support_url ?>" target="_blank"
						   rel="noopener"><?php _e( 'Get starting free support', 'titan-security' ); ?></a>
					</li>
					<li style="margin-top: 15px;background: #fff4f1;padding: 10px;color: #a58074;">
						<span class="dashicons dashicons-warning"></span>
						<?php printf( __( 'If you find a php error or a vulnerability in plugin, you can <a href="%s" target="_blank" rel="noopener">create ticket</a> in hot support that we responded instantly.', 'titan-security' ), $hot_support_url ); ?>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Создает html разметку виджета рейтинга
	 *
	 * @param array $args
	 *
	 * @since  7.0.6
	 */
	public function showRatingWidget( array $args ) {
		if ( ! isset( $args[0] ) || empty( $args[0] ) ) {
			$page_url = "https://wordpress.org/support/plugin/anti-spam/reviews/#new-post";
		} else {
			$page_url = $args[0];
		}

		$page_url = apply_filters( 'wbcr_factory_pages_000_imppage_rating_widget_url', $page_url, $this->plugin->getPluginName(), $this->getResultId() );

		?>
		<div class="wbcr-factory-sidebar-widget">
			<p>
				<strong><?php _e( 'Do you want the plugin to improved and update?', 'titan-security' ); ?></strong>
			</p>
			<p><?php _e( 'Help the author, leave a review on wordpress.org. Thanks to feedback, we will know that the plugin is really useful to you and is needed.', 'titan-security' ); ?></p>
			<p><?php _e( 'And also write your ideas on how to extend or improve the plugin.', 'titan-security' ); ?></p>
			<p>
			<span class="wporg-ratings" title="5 out of 5 stars" style="color:#ffb900;">
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
			</span>
			<a href="<?= $page_url ?>" title="Go rate us" target="_blank">
					<strong><?php _e( 'Go rate us and push ideas', 'titan-security' ); ?></strong>
				</a>
			</p>
		</div>
		<?php
	}

}

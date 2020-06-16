<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Страница лицензирования плагина.
 *
 * Поддерживает режим работы с мультисаймами. Вы можете увидеть эту страницу в панели настройки сети.
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 *
 * @copyright (c) 2018 Webraftic Ltd
 */
class Components_License extends Base {

	/**
	 * {@inheritdoc}
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  7.0.2
	 * @var string
	 */
	public $id = 'titan_components_license';

	/**
	 * {@inheritdoc}
	 * @since  7.0.2
	 * @var string
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  7.0.2
	 * @var string
	 */
	public $page_parent_page;

	/**
	 * @since  7.0.2
	 * @var int
	 */
	public $page_menu_position = 0;

	/**
	 * {@inheritdoc}
	 * @since  7.0.2
	 * @var bool
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * WCL_LicensePage constructor.
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @since  7.0.2
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->menu_title = __('License', 'titan-security');
		$this->page_menu_short_description = __('Product activation', 'titan-security');
		$this->plan_name = __('Titan Premium Components', 'titan-security');

		parent::__construct($plugin);
	}

	/**
	 * Requests assets (js and css) for the page.
	 *
	 * @return void
	 * @since 7.0.2
	 * @see   FactoryPages000_AdminPage
	 *
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/components.css');

		/**
		 * @param \Wbcr_Factory000_StyleList $styles
		 * @param \Wbcr_Factory000_ScriptList $scripts
		 *
		 * @since 7.0.2
		 *
		 */
		do_action('wbcr/clearfy/components/page_assets', $scripts, $styles);
	}

	/**
	 * This method simply show contents of the component page.
	 * @throws \Exception
	 * @since  7.0.2
	 */
	public function showPageContent()
	{

		/**
		 * @since 7.0.2
		 *
		 */
		$components = apply_filters('wbcr/titan/license/list_components', []);

		?>
		<div class="wbcr-factory-page-group-header"><?php _e('<strong>Products activation</strong>.', 'clearfy') ?>
			<p>
				<?php _e('These are components of the plugin bundle. When you activate the plugin, all the components turned on by default. If you don’t need some function, you can easily turn it off on this page.', 'clearfy') ?>
			</p>
		</div>
		<div class="wbcr-clearfy-components">
			<?php
			/**
			 * @param array $components
			 *
			 * @since  7.0.2
			 */
			do_action('wbcr/titan/license/custom_plugins_card', $components);
			?>

			<?php foreach((array)$components as $component): ?>
				<div class="plugin-card">
					<div class="plugin-card-top">
						<div class="name column-name">
							<h3>
								<a href="<?php echo esc_url($component['url']) ?>"
								   class="thickbox open-plugin-details-modal">
									You use <?php echo esc_html($component['title']) ?>
									<img src="<?php echo esc_attr($component['icon']) ?>" class="plugin-icon"
									     alt="<?php echo esc_attr($component['title']) ?>">
								</a>
							</h3>
						</div>
						<div class="desc column-description">
							<?php if( 'premium' === $component['build'] ): ?>
								<ul>
									<?php if( !empty($component['key']) ): ?>
										<li>
											<strong><?php _e('License key', 'clearfy') ?>:
											</strong> <?php echo esc_html($component['key']) ?>
										</li>
									<?php endif; ?>
									<?php if( !empty($component['plan']) ): ?>
										<li>
											<strong><?php _e('Plan', 'clearfy') ?>:</strong>
											<?php echo esc_html($component['plan']) ?>
										</li>
									<?php endif; ?>
									<?php if( !empty($component['subscription']) ): ?>
										<li>
											<strong><?php _e('Type', 'clearfy') ?>:</strong>
											<?php echo esc_html($component['subscription']) ?>
										</li>
									<?php endif; ?>
									<?php if( !empty($component['plan']) ): ?>
										<li>
											<strong><?php _e('Expired', 'clearfy') ?>:
											</strong> <?php echo esc_html($component['expiration_days']) ?> <?php _e('days remained', 'clearfy') ?>
										</li>
									<?php endif; ?>
								</ul>
							<?php else: ?>
								<p><?php echo esc_html($component['description']); ?></p>
							<?php endif; ?>
						</div>
					</div>
					<div class="plugin-card-bottom">
						<a href="<?php echo $this->getBaseUrl($component['license_page_id']); ?>"
						   class="button">
							<?php if( 'premium' === $component['build'] ): ?>
								<?php _e('License details') ?>
							<?php else: ?>
								<?php _e('Activate Premium License') ?>
							<?php endif; ?>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
			<div class="clearfix"></div>
		</div>
		<?php
	}

}
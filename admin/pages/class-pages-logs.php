<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Страница журнала ошибок
 *
 * Не поддерживает режим работы с мультисаймами.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 2019 Webraftic Ltd
 * @version       1.0
 */
class Logs extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'logs';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-admin-tools';

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_position = 1;


	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * Logs constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{

		$this->menu_title = __('Error Log', 'titan-security');
		$this->page_menu_short_description = __('Plugin debug report', 'titan-security');

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

		$this->styles->add(WTITAN_PLUGIN_URL . '/includes/logger/assets/css/base.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/includes/logger/assets/js/base.js', ['jquery']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function showPageContent()
	{
		require_once(WTITAN_PLUGIN_DIR . '/includes/logger/class-logger-reader.php');
		?>
		<div class="wbcr-factory-page-group-header">
			<strong><?php _e('Error Log', 'titan-security') ?></strong>
			<p>
				<?php _e('In this section, you can track image optimization errors. Sending this log to us, will help in solving possible optimization issues.', 'titan-security') ?>
			</p>
		</div>
		<div class="wbcr-factory-page-group-body">
			<div class="btn-group">
				<a href="<?php echo wp_nonce_url($this->getPageUrl() . 'action=export') ?>"
				   class="btn btn-default"><?php _e('Export Debug Information', 'titan-security') ?></a>
				<a href="#"
				   data-working="<?php echo esc_attr__('Working...', 'titan-security') ?>"
				   data-nonce="<?php echo wp_create_nonce('wlogger_clean_logs') ?>"
				   class="btn btn-default js-wlogger-export-debug-report"><?php echo sprintf(__('Clean-up Logs (<span id="js-wlogger-size">%s</span>)', 'titan-security'), $this->get_log_size_formatted()) ?></a>
			</div>
			<div class="wlogger-viewer" id="js-wlogger-viewer">
				<?php echo \WBCR\Titan\Logger\Reader::prettify() ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Processing log export action in form of ZIP archive.
	 *
	 * @since  6.0
	 */
	public function exportAction()
	{
		require_once(WTITAN_PLUGIN_DIR . '/includes/logger/class-logger-export.php');
		$export = new \WBCR\Titan\Logger\Export();

		if( $export->prepare() ) {
			$export->download(true);
		}
	}

	/**
	 * Get log size formatted.
	 *
	 * @return false|string
	 * @since  6.0
	 */
	private function get_log_size_formatted()
	{

		try {
			return size_format(\WBCR\Titan\Logger\Writter::get_total_size());
		} catch( \Exception $exception ) {
			\WBCR\Titan\Logger\Writter::error(sprintf('Failed to get total log size as exception was thrown: %s', $exception->getMessage()));
		}

		return '';
	}
}

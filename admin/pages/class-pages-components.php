<?php

namespace WBCR\Titan\Page;
/**
 * This file is the add-ons page.
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @since         7.0.3
 * @copyright (c) 2020, Creative Motion
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WBCR\Titan\Plugin;
use WBCR\Titan\Views;

class Components extends Base {

	/**
	 * The id of the page in the admin menu.
	 *
	 * Mainly used to navigate between pages.
	 *
	 * @since 7.0.3
	 * @see   FactoryPages000_AdminPage
	 *
	 * @var string
	 */
	public $id = "components";

	public $page_menu_dashicon = 'dashicons-admin-plugins';

	public $type = 'page';

	public $show_right_sidebar_in_options = false;

	public $page_menu_position = 1;

	/**
	 * Заголовок страницы, также использует в меню, как название закладки
	 *
	 * @var bool
	 */
	public $show_page_title = true;

	/**
	 * @var object|\WBCR\Titan\Views
	 */
	public $view;

	/**
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->menu_title                  = __( 'Components', 'titan-security' );
		$this->page_menu_short_description = __( 'More features for plugin', 'titan-security' );

		$this->view = $this->plugin->view();

		parent::__construct( $plugin );
	}

	/**
	 * Requests assets (js and css) for the page.
	 *
	 * @return void
	 * @since 7.0.2
	 * @see   FactoryPages000_AdminPage
	 *
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/components.css' );

		/**
		 * @param \Wbcr_Factory000_StyleList $styles
		 * @param \Wbcr_Factory000_ScriptList $scripts
		 *
		 * @since 7.0.3
		 *
		 */
		do_action( 'wtitan/components/page_assets', $scripts, $styles );
	}

	/**
	 * We register notifications for some actions
	 *
	 * @param                        $notices
	 * @param Plugin $plugin
	 *
	 * @return array
	 * @see 'libs\factory\pages\templates\FactoryPages000_ImpressiveThemplate'
	 */
	public function getActionNotices( $notices ) {
		$notices[] = [
			'conditions' => [
				'wbcr-force-update-components-success' => 1
			],
			'type'       => 'success',
			'message'    => __( 'Components have been successfully updated to the latest version.', 'titan-security' )
		];

		$notices[] = [
			'conditions' => [
				'wbcr-force-update-components-error' => 'inactive_licence'
			],
			'type'       => 'danger',
			'message'    => __( 'To use premium components, you need activate a license!', 'titan-security' ) . '<a href="' . $this->plugin->getPluginPageUrl( 'license' ) . '" class="btn btn-gold">' . __( 'Activate license', 'titan-security' ) . '</a>'
		];

		$notices[] = [
			'conditions' => [
				'wbcr-force-update-components-error' => 'unknown_error'
			],
			'type'       => 'danger',
			'message'    => __( 'An unknown error occurred while updating plugin components. Please contact the plugin support team to resolve this issue.', 'hide_my_wp' )
		];

		return $notices;
	}

	/**
	 * This method simply sorts the list of components.
	 *
	 * @param $components
	 *
	 * @return array
	 */
	public function order( $components ) {
		$deactivate_components = Plugin::app()->getPopulateOption( 'deactive_preinstall_components', [] );

		$ordered_components = [
			'premium_active'   => [],
			'premium_deactive' => [],
			'other'            => []
		];

		foreach ( (array) $components as $component ) {

			if ( ( 'premium' === $component['build'] || 'freemium' === $component['build'] ) && 'internal' === $component['type'] ) {
				if ( in_array( $component['name'], $deactivate_components ) ) {
					// free component is deactivated
					$order_key = 'premium_deactive';
				} else {
					// free component activated
					$order_key = 'premium_active';
				}
			} else {
				$order_key = 'other';
			}

			$ordered_components[ $order_key ][] = $component;
		}

		return array_merge( $ordered_components['premium_active'], $ordered_components['premium_deactive'], $ordered_components['other'] );
	}

	/**
	 * This method simply show contents of the component page.
	 *
	 * @throws \Exception
	 */
	public function showPageContent() {
		$default_image = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzNjAiIGhlaWdodD0iMzYwIiB2aWV3Ym94PSIwIDAgMzYwIDM2MCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+PHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0icmdiKDcwLCA4MSwgOTMpIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iIzIyMiIgZmlsbC1vcGFjaXR5PSIwLjE1IiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLCAwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiNkZGQiIGZpbGwtb3BhY2l0eT0iMC4wNTQ2NjY2NjY2NjY2NjciIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDYwLCAwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiMyMjIiIGZpbGwtb3BhY2l0eT0iMC4wNDYiIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDEyMCwgMCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjZGRkIiBmaWxsLW9wYWNpdHk9IjAuMDIiIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE4MCwgMCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjZGRkIiBmaWxsLW9wYWNpdHk9IjAuMDU0NjY2NjY2NjY2NjY3IiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyNDAsIDApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iIzIyMiIgZmlsbC1vcGFjaXR5PSIwLjAyODY2NjY2NjY2NjY2NyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMzAwLCAwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiNkZGQiIGZpbGwtb3BhY2l0eT0iMC4xMDY2NjY2NjY2NjY2NyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCwgNjApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iIzIyMiIgZmlsbC1vcGFjaXR5PSIwLjA5OCIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNjAsIDYwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiMyMjIiIGZpbGwtb3BhY2l0eT0iMC4xMTUzMzMzMzMzMzMzMyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTIwLCA2MCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjMjIyIiBmaWxsLW9wYWNpdHk9IjAuMDYzMzMzMzMzMzMzMzMzIiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxODAsIDYwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiNkZGQiIGZpbGwtb3BhY2l0eT0iMC4wMzczMzMzMzMzMzMzMzMiIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDI0MCwgNjApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iI2RkZCIgZmlsbC1vcGFjaXR5PSIwLjE0MTMzMzMzMzMzMzMzIiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgzMDAsIDYwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiNkZGQiIGZpbGwtb3BhY2l0eT0iMC4wMzczMzMzMzMzMzMzMzMiIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAsIDEyMCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjZGRkIiBmaWxsLW9wYWNpdHk9IjAuMDg5MzMzMzMzMzMzMzMzIiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg2MCwgMTIwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiNkZGQiIGZpbGwtb3BhY2l0eT0iMC4wODkzMzMzMzMzMzMzMzMiIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDEyMCwgMTIwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiMyMjIiIGZpbGwtb3BhY2l0eT0iMC4wODA2NjY2NjY2NjY2NjciIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE4MCwgMTIwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiMyMjIiIGZpbGwtb3BhY2l0eT0iMC4xMzI2NjY2NjY2NjY2NyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjQwLCAxMjApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iIzIyMiIgZmlsbC1vcGFjaXR5PSIwLjE1IiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgzMDAsIDEyMCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjMjIyIiBmaWxsLW9wYWNpdHk9IjAuMDk4IiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLCAxODApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iIzIyMiIgZmlsbC1vcGFjaXR5PSIwLjA2MzMzMzMzMzMzMzMzMyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNjAsIDE4MCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjZGRkIiBmaWxsLW9wYWNpdHk9IjAuMDIiIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDEyMCwgMTgwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiNkZGQiIGZpbGwtb3BhY2l0eT0iMC4wMzczMzMzMzMzMzMzMzMiIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE4MCwgMTgwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiMyMjIiIGZpbGwtb3BhY2l0eT0iMC4xMTUzMzMzMzMzMzMzMyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjQwLCAxODApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iIzIyMiIgZmlsbC1vcGFjaXR5PSIwLjA2MzMzMzMzMzMzMzMzMyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMzAwLCAxODApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iI2RkZCIgZmlsbC1vcGFjaXR5PSIwLjA1NDY2NjY2NjY2NjY2NyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCwgMjQwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiNkZGQiIGZpbGwtb3BhY2l0eT0iMC4xMDY2NjY2NjY2NjY2NyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNjAsIDI0MCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjZGRkIiBmaWxsLW9wYWNpdHk9IjAuMDcyIiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMjAsIDI0MCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjMjIyIiBmaWxsLW9wYWNpdHk9IjAuMTE1MzMzMzMzMzMzMzMiIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE4MCwgMjQwKSIgLz48cG9seWxpbmUgcG9pbnRzPSIxOS44LDAsNDAuMiwwLDYwLDE5LjgsNjAsNDAuMiw0MC4yLDYwLDE5LjgsNjAsMCw0MC4yLDAsMTkuOCwxOS44LDAiIGZpbGw9IiMyMjIiIGZpbGwtb3BhY2l0eT0iMC4xMzI2NjY2NjY2NjY2NyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjQwLCAyNDApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iIzIyMiIgZmlsbC1vcGFjaXR5PSIwLjA4MDY2NjY2NjY2NjY2NyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMzAwLCAyNDApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iIzIyMiIgZmlsbC1vcGFjaXR5PSIwLjEzMjY2NjY2NjY2NjY3IiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLCAzMDApIiAvPjxwb2x5bGluZSBwb2ludHM9IjE5LjgsMCw0MC4yLDAsNjAsMTkuOCw2MCw0MC4yLDQwLjIsNjAsMTkuOCw2MCwwLDQwLjIsMCwxOS44LDE5LjgsMCIgZmlsbD0iI2RkZCIgZmlsbC1vcGFjaXR5PSIwLjAzNzMzMzMzMzMzMzMzMyIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utb3BhY2l0eT0iMC4wMiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNjAsIDMwMCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjZGRkIiBmaWxsLW9wYWNpdHk9IjAuMTI0IiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMjAsIDMwMCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjMjIyIiBmaWxsLW9wYWNpdHk9IjAuMDI4NjY2NjY2NjY2NjY3IiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxODAsIDMwMCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjZGRkIiBmaWxsLW9wYWNpdHk9IjAuMDcyIiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyNDAsIDMwMCkiIC8+PHBvbHlsaW5lIHBvaW50cz0iMTkuOCwwLDQwLjIsMCw2MCwxOS44LDYwLDQwLjIsNDAuMiw2MCwxOS44LDYwLDAsNDAuMiwwLDE5LjgsMTkuOCwwIiBmaWxsPSIjMjIyIiBmaWxsLW9wYWNpdHk9IjAuMDI4NjY2NjY2NjY2NjY3IiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjAyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgzMDAsIDMwMCkiIC8+PC9zdmc+';
		$response      = [];

		$response = array_merge( $response, [
			[
				'name'         => 'hide_login_page',
				'title'        => __( 'Hide login page', 'titan-security' ),
				'url'          => 'https://wordpress.org/plugins/hide-login-page/',
				'type'         => 'wordpress',
				'build'        => 'free',
				'base_path'    => 'hide-login-page/hide-login-page.php',
				'icon'         => 'https://ps.w.org/hide-login-page/assets/icon-256x256.png',
				'description'  => __( 'Hide Login Page is a very light plugin that lets you easily and safely change the url of the login form page to anything you want. The settings are located in the Tweaks tab.', 'titan-security' ),
				'settings_url' => admin_url( 'admin.php?page=hlp_hide_login-' . $this->plugin->getPluginName() )
			],
		] );

		/**
		 * @param array $response
		 *
		 * @since 7.0.3
		 *
		 */
		$response = apply_filters( 'wtitan/components/items_list', $response );

		$components = $this->order( $response );

		$data = [
			'components' => $components
		];
		$this->plugin->view->print_template( 'components', $data );
	}
}



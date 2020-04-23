<?php
/**
 * Uninstall addon button
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @since         7.0.3
 * @copyright (c) 2020, Creative Motion
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WTITAN_DeletePluginsButton extends WTITAN_InstallPluginsButton {

	/**
	 * @throws \Exception
	 */
	protected function build_wordpress() {
		parent::build_wordpress();

		$this->action = 'delete';
		$this->addData( 'plugin-action', $this->action );
		$this->removeClass( 'button-primary' );
	}

	protected function build_internal() {
		// nothing
	}

	/**
	 * @param bool $echo
	 *
	 * @return string|void
	 */
	public function getButton() {
		$button = '<a href="#" class="' . implode( ' ', $this->get_classes() ) . '" ' . implode( ' ', $this->get_data() ) . '><span class="dashicons dashicons-trash"></span></a>';

		if ( $this->type == 'internal' || ! $this->isPluginInstall() || $this->isPluginActivate() ) {
			$button = '';
		}

		return $button;
	}
}


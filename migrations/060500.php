<?php #comp-page builds: premium

/**
 * Updates for altering the table used to store statistics data.
 * Adds new columns and renames existing ones in order to add support for the new social buttons.
 */
class WANTISPAMUpdate060500 extends Wbcr_Factory000_Update {

	public function install() {
		if ( $this->plugin->isNetworkAdmin() ) {
			update_site_option( $this->plugin->getOptionName( 'what_is_new_64' ), 1 );
		} else {
			update_option( $this->plugin->getOptionName( 'what_is_new_64' ), 1 );
		}

		$settings = $this->get_settings();
		if ( ! empty( $settings['save_spam_comments'] ) && $settings['save_spam_comments'] ) {
			update_option( $this->plugin->getOptionName( 'save_spam_comments' ), 1 );
		}
	}

	private function get_settings() {
		$antispam_settings = (array) get_option( 'antispam_settings' );
		$default_settings  = $this->default_settings();
		$antispam_settings = array_merge( $default_settings, $antispam_settings ); // set empty options with default values

		return $antispam_settings;
	}

	private function default_settings() {
		$settings = [
			'save_spam_comments' => 0
		];

		return $settings;
	}
}
<?php #comp-page builds: premium

/**
 *
 * Adds new columns and renames existing ones in order.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WTITANUpdate070000 extends Wbcr_Factory000_Update {

	public function install() {
		if ( is_multisite() ) {
			global $wpdb, $wp_version;

			$wpdb->query( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE 'wbcr_clearfy_%';" );

			$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $id ) {
					switch_to_blog( $id );
					$this->migrate_prefix();
					restore_current_blog();
				}
			}
		} else {
			$this->migrate_prefix();
		}
	}

	public function migrate_prefix()
	{
		global $wpdb;

		$request = $wpdb->get_results( "SELECT option_id, option_name, option_value FROM {$wpdb->prefix}options WHERE option_name LIKE 'wantispam_%'" );
		if ( ! empty( $request ) ) {
			foreach ( $request as $option ) {
				$option_new_name = str_replace( 'wantispam', WBCR\Titan\Plugin::app()->getPrefix(), $option->option_name );
				if ( ! get_option( $option_new_name, false ) ) {
					$wpdb->query( "UPDATE {$wpdb->prefix}options SET option_name='$option_new_name' WHERE option_id='{$option->option_id}'" );
				} else {
					delete_option( $option->option_name );
				}
			}
		}
	}
}
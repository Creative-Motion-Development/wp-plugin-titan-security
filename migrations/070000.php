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
		if('no' === $this->plugin->getOption( 'titan_extra_menu', 'no')) {
			$this->plugin->updateOption( 'titan_extra_menu', true);
		}
		if('no' === $this->plugin->getOption( 'antispam_mode', 'no')) {
			$this->plugin->updateOption( 'antispam_mode', true);
		}

		if ( is_multisite() ) {
			global $wpdb;

			$this->migrate_prefix_sitemeta();

			$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $id ) {
					switch_to_blog( $id );
					$this->migrate_prefix_options();
					restore_current_blog();
				}
			}
		} else {
			$this->migrate_prefix_options();
		}
	}

	public function migrate_prefix_options()
	{
		global $wpdb;

		$request = $wpdb->get_results( "SELECT option_id, option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'wantispam_%'" );
		if ( ! empty( $request ) ) {
			foreach ( $request as $option ) {
				$option_new_name = str_replace( 'wantispam_', WBCR\Titan\Plugin::app()->getPrefix(), $option->option_name );
				if ( ! get_option( $option_new_name, false ) ) {
					$wpdb->query( "UPDATE {$wpdb->options} SET option_name='$option_new_name' WHERE option_id='{$option->option_id}'" );
				} else {
					delete_option( $option->option_name );
				}
			}
		}
	}

	public function migrate_prefix_sitemeta()
	{
		global $wpdb;

		$request = $wpdb->get_results( "SELECT meta_id, site_id, meta_key, meta_value FROM {$wpdb->sitemeta} WHERE meta_key LIKE 'wantispam_%'" );
		if ( ! empty( $request ) ) {
			foreach ( $request as $meta ) {
				$meta_new_name = str_replace( 'wantispam_', WBCR\Titan\Plugin::app()->getPrefix(), $meta->meta_key );

				$smeta = $wpdb->get_var( "SELECT meta_id FROM {$wpdb->sitemeta} WHERE site_id={$meta->site_id} AND meta_key={$meta_new_name}" );
				if ( is_null($smeta) ) {
					$wpdb->query( "UPDATE {$wpdb->sitemeta} SET meta_key='$meta_new_name' WHERE meta_id='{$meta->meta_id}'" );
				} else {
					delete_site_meta( $meta->site_id,$meta->meta_key );
				}
			}
		}
	}
}
<?php
// @formatter:off
// if uninstall.php is not called by WordPress, die
if( !defined('WP_UNINSTALL_PLUGIN') ) {
	die;
}

/**
 * Удаление кеша и опций
 */
function uninstall()
{
	// remove plugin options
	global $wpdb;

	$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'titan_%';");
}

if(get_option( 'titan_complete_uninstall', false)) {
	if ( is_multisite() ) {
		global $wpdb, $wp_version;

		$wpdb->query("DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE 'titan_%';");

		$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

		if ( ! empty( $blogs ) ) {
			foreach ( $blogs as $id ) {

				switch_to_blog( $id );

				uninstall();

				restore_current_blog();
			}
		}
	} else {
		uninstall();
	}
}

//require_once dirname(__FILE__) . '/includes/firewall/class-database-schema.php';
//$db_schema = new \WBCR\Titan\Database\Schema();
//$db_schema->dropAll();

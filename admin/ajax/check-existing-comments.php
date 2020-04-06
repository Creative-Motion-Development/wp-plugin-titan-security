<?php
/**
 * Ajax action to check existing comments
 *
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 *
 * @copyright (c) 2019 Webcraftic Ltd
 * @version 1.0
 * @since 6.2
 */

if( ! defined('ABSPATH' ) ) {
    exit;
}

add_action('wp_ajax_waspam-check-existing-comments', 'waspam_checking_existing_comments');

/**
 * Checking existing comment
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
function waspam_checking_existing_comments() {
    check_admin_referer('waspam-check-existing-comments');

    if( ! current_user_can( 'manage_options' ) ) {
        wp_die( -1 );
    }

    if( list($status, $remaining) = waspam_check_existing_comments() ) {
        if($status) {
            wp_send_json_success([
                'remaining' => $remaining
            ]);
        }
    }

    wp_send_json_error([
        'message' => 'Empty AntiSpam API response'
    ], 500);
}
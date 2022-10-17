<?php
/**
 * Helper functions
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 12.12.2019, Webcraftic
 * @version       1.0
 */

/**
 * Gets honeypot fields.
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  6.5.3
 */
function wantispam_get_honeypot_fields() {
	$rn   = "\r\n"; // .chr(13).chr(10)
	$html = '';

	$html .= '<div class="wantispam-group wantispam-group-q" style="clear: both;">
					<label>Current ye@r <span class="required">*</span></label>
					<input type="hidden" name="wantispam_a" class="wantispam-control wantispam-control-a" value="' . date( 'Y' ) . '" />
					<input type="text" name="wantispam_q" class="wantispam-control wantispam-control-q" value="' . \WBCR\Titan\Plugin::app()->getPluginVersion() . '" autocomplete="off" />
				  </div>' . $rn; // question (hidden with js)
	$html .= '<div class="wantispam-group wantispam-group-e" style="display: none;">
					<label>Leave this field empty</label>
					<input type="text" name="wantispam_e_email_url_website" class="wantispam-control wantispam-control-e" value="" autocomplete="off" />
				  </div>' . $rn; // empty field (hidden with css); trap for spammers because many bots will try to put email or url here

	return $html;
}

/**
 * Gets required fields into the comment form on the page.
 *
 * @param string $html
 *
 * @return string
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  6.5.3
 *
 */
function wantispam_get_required_fields( $render_honeypot_fields = true ) {
	$html = '<!-- Anti-spam plugin wordpress.org/plugins/anti-spam/ -->';
	$html .= '<div class="wantispam-required-fields">';
	$html .= '<input type="hidden" name="wantispam_t" class="wantispam-control wantispam-control-t" value="' . time() . '" />'; // Start time of form filling
	if ( $render_honeypot_fields ) {
		$html .= wantispam_get_honeypot_fields();
	}
	$html .= '</div>';
	$html .= '<!--\End Anti-spam plugin -->';

	return $html;
}

/**
 * Controls the display of a privacy related notice underneath the comment form.
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  6.5.3
 */
function wantispam_display_comment_form_privacy_notice( $echo = false ) {
	if ( ! \WBCR\Titan\Plugin::app()->getPopulateOption( 'comment_form_privacy_notice' ) ) {
		return '';
	}

	$output = '<p class="wantispam-comment-form-privacy-notice" style="margin-top:10px;">' . sprintf( __( 'This site uses Antispam to reduce spam. <a href="%s" target="_blank" rel="nofollow noopener">Learn how your comment data is processed</a>.', 'titan-security' ), 'https://anti-spam.space/antispam-privacy/' ) . '</p>';

	if ( $echo === false ) {
		return $output;
	}

	echo esc_html($output);
}

/**
 * Return premium widget markup
 *
 * @return string
 * @since  6.5.3
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 */
function wantispam_get_sidebar_premium_widget() {
	ob_start();
	?>
    <div class="wbcr-factory-sidebar-widget">
        <p>
            <a href="https://titansitescanner.com/pricing/" target="_blank" rel="noopener nofollow">
                <img style="width: 100%;"
                     src="https://api.cm-wp.com/wp-content/uploads/2019/12/baner_antispam_vertical.jpg" alt="">
            </a>
        </p>
    </div>
	<?php
	return ob_get_clean();
}

/**
 * Should show a page about the plugin or not.
 *
 * @return bool
 * @since  6.5.3
 */
function wantispam_is_need_show_about_page() {
	if ( \WBCR\Titan\Plugin::app()->isNetworkActive() ) {
		$need_show_about = (int) get_site_option( \WBCR\Titan\Plugin::app()->getOptionName( 'what_is_new_64' ) );
	} else {
		$need_show_about = (int) get_option( \WBCR\Titan\Plugin::app()->getOptionName( 'what_is_new_64' ) );
	}

	$is_ajax = wantispam_doing_ajax();
	$is_cron = wantispam_doing_cron();
	$is_rest = wantispam_doing_rest_api();

	if ( $need_show_about && ! $is_ajax && ! $is_cron && ! $is_rest ) {
		return true;
	}

	return false;
}

/**
 * Checks if the current request is a WP REST API request.
 *
 * Case #1: After WP_REST_Request initialisation
 * Case #2: Support "plain" permalink settings
 * Case #3: URL Path begins with wp-json/ (your REST prefix)
 *          Also supports WP installations in subfolders
 *
 * @author matzeeable https://wordpress.stackexchange.com/questions/221202/does-something-like-is-rest-exist
 * @since  2.1.0
 * @return boolean
 */
function wantispam_doing_rest_api() {
	$prefix     = rest_get_url_prefix();
	$rest_route = \WBCR\Titan\Plugin::app()->request->get( 'rest_route', null );
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST // (#1)
	     || ! is_null( $rest_route ) // (#2)
	        && strpos( trim( $rest_route, '\\/' ), $prefix, 0 ) === 0 ) {
		return true;
	}

	// (#3)
	$rest_url    = wp_parse_url( site_url( $prefix ) );
	$current_url = wp_parse_url( add_query_arg( [] ) );

	return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
}

/**
 * @return bool
 * @since  6.5.3
 */
function wantispam_doing_ajax() {
	if ( function_exists( 'wp_doing_ajax' ) ) {
		return wp_doing_ajax();
	}

	return defined( 'DOING_AJAX' ) && DOING_AJAX;
}

/**
 * @return bool
 * @since  6.5.3
 */
function wantispam_doing_cron() {
	if ( function_exists( 'wp_doing_cron' ) ) {
		return wp_doing_cron();
	}

	return defined( 'DOING_CRON' ) && DOING_CRON;
}

/**
 * Checks whether the license is activated for plugin.
 *
 * @return bool
 * @since  6.5.4
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 */
function wantispam_is_titan_license_activate() {
	if ( class_exists( '\WBCR\Titan\Plugin' ) ) {
		return \WBCR\Titan\Plugin::app()->premium->is_activate();
	}

	return false;
}

/**
 * Checks whether the license is activated for the plugin or not. If the plugin is installed
 * in priorities checks its license.
 *
 * @return bool
 * @since  6.5.4
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 */
function wantispam_is_license_activate() {
	return wantispam_is_titan_license_activate() || \WBCR\Titan\Plugin::app()->premium->is_activate();
}

/**
 * Checks active (not expired!) License for plugin or not. If the plugin is installed
 * checks its license in priorities.
 *
 * @return bool
 * @since  6.5.4
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 */
function wantispam_is_license_active() {
	if ( wantispam_is_titan_license_activate() ) {
		return \WBCR\Titan\Plugin::app()->premium->is_active();
	}

	return \WBCR\Titan\Plugin::app()->premium->is_activate() && \WBCR\Titan\Plugin::app()->premium->is_active();
}

/**
 * Allows you to get a license key. If the Clearfy plugin is installed, it will be prioritized
 * return it key.
 *
 * @return string|null
 * @since  6.5.4
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 */
function wantispam_get_license_key() {
	if ( ! wantispam_is_license_activate() ) {
		return null;
	}

	if ( wantispam_is_titan_license_activate() ) {
		return \WBCR\Titan\Plugin::app()->premium->get_license()->get_key();
	}

	return \WBCR\Titan\Plugin::app()->premium->get_license()->get_key();
}

/**
 * @return number|null
 * @since  6.5.4
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 */
function wantispam_get_freemius_plugin_id() {
	if ( wantispam_is_titan_license_activate() ) {
		return \WBCR\Titan\Plugin::app()->premium->get_setting( 'plugin_id' );
	}

	return \WBCR\Titan\Plugin::app()->premium->get_setting( 'plugin_id' );
}

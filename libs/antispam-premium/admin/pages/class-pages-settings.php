<?php

namespace WBCR\Antispam\Premium\Page;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Страница общих настроек для этого плагина.
 *
 * Не поддерживает режим работы с мультисаймами.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 2019 Webraftic Ltd
 * @version       1.0
 */
class Settings extends \WBCR\Antispam\Page\Settings {

	/**
	 * Enqueue page assets
	 *
	 * @since 1.0.0
	 * @return void
	 * @see   Wbcr_FactoryPages000_AdminPage
	 *
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		if ( isset( $_GET['action'] ) && "check-existing-comments" === $_GET['action'] ) {
			$this->scripts->add( WANTISPAMP_PLUGIN_URL . '/admin/assets/js/check-existing-comments.js', [
				'jquery',
				'wantispam-settings'
			] );
		}
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  7.0.0
	 */
	public function checkExistingCommentsAction() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to view page!' );
		}

		check_admin_referer( 'wantispam_checking_unapproved_comments' );

		$count_comments = wantispamp_get_unchecked_comments_count();

		if ( ! $count_comments ) {
			$this->redirectToAction( 'index' );
		}

		$wp_nonce = wp_create_nonce( 'waspam-check-existing-comments' );
		$redirect_url = admin_url( 'edit-comments.php?comment_status=spam_checking' );

		ob_start();
		?>
        <style>
            #wantispam-check-existing-comments {
                padding: 30px;
            }

            #wantispam-check-existing-comments__progress-bar {
                background-color: #f3f3f3;
                border: 1px solid #cacaca;
                width: 400px;
                height: 30px;
            }

            .wantispam-check-existing-comments__left-comments {
                display: none;
                padding: 5px 0;
            }
        </style>
        <div id="wantispam-check-existing-comments" data-step="<?php echo esc_attr( \WBCR\Titan\Plugin::COUNT_TO_CHECK ) ?>" data-nonce="<?php echo esc_attr( $wp_nonce ); ?>" data-redirect-url="<?php echo esc_attr( $redirect_url ); ?>">
            <h4><?php _e( 'Please wait! Checking comments...', 'titan-security' ) ?></h4>
            <progress id="wantispam-check-existing-comments__progress-bar" value="0" max="100"></progress>
            <div class="wantispam-check-existing-comments__left-comments"><?php printf( __( 'It remains to check %s comments.', 'titan-security' ), '<span></span>' ); ?></div>
        </div>
		<?php

		$this->showPage( ob_get_clean() );
	}

}

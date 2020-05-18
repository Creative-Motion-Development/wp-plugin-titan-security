<?php

namespace WBCR\Titan\Tweaks;

final class Strong_Passwords {

	const STRENGTH_KEY = 'titan-password-strength';

	public function __construct() {

		add_action( 'titan_register_password_requirements', array( $this, 'register_requirements' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
		add_action( 'resetpass_form', array( $this, 'add_scripts_to_wp_login' ) );
		add_action( 'titan_password_requirements_change_form', array( $this, 'add_scripts_to_wp_login' ) );
	}

	/**
	 * Register the Strong Passwords requirement.
	 */
	public function register_requirements() {
		Password_Requirements_Base::register( 'strength', array(
			'evaluate'                => array( $this, 'evaluate' ),
			'validate'                => array( $this, 'validate' ),
			'reason'                  => array( $this, 'reason' ),
			'meta'                    => self::STRENGTH_KEY,
			'evaluate_if_not_enabled' => true,
			'defaults'                => array( 'role' => 'administrator' ),
			'settings_config'         => array( $this, 'get_settings_config' ),
		) );
	}

	/**
	 * Enqueue script to hide the acknowledge weak password checkbox.
	 *
	 * @return void
	 */
	public function add_scripts() {

		global $pagenow;

		if ( 'profile.php' !== $pagenow ) {
			return;
		}

		if ( ! Password_Requirements_Base::is_requirement_enabled( 'strength' ) ) {
			return;
		}

		$settings = Password_Requirements_Base::get_requirement_settings( 'strength' );
		$role     = isset( $settings['role'] ) ? $settings['role'] : 'administrator';

		require_once( WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/class-canonical-roles.php' );

		if ( Canonical_Roles::is_user_at_least( $role ) ) {
			wp_enqueue_script( 'titan_strong_passwords', WTITAN_PLUGIN_URL . '/includes/tweaks/password-requirements/assets/js/script.js', array( 'jquery' ), \WBCR\Titan\Plugin::app()->getPluginVersion() );
		}
	}

	/**
	 * On the reset password and login interstitial form, render the Strong Passwords JS to hide the acknowledge weak password checkbox.
	 *
	 * We have to do this in these late actions so we have access to the correct user data.
	 *
	 * @param \WP_User $user
	 */
	public function add_scripts_to_wp_login( $user ) {

		if ( ! Password_Requirements_Base::is_requirement_enabled( 'strength' ) ) {
			return;
		}

		$settings = Password_Requirements_Base::get_requirement_settings( 'strength' );
		$role     = isset( $settings['role'] ) ? $settings['role'] : 'administrator';

		require_once( WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/class-canonical-roles.php' );

		if ( Canonical_Roles::is_user_at_least( $role, $user ) ) {
			wp_enqueue_script( 'titan_strong_passwords', WTITAN_PLUGIN_URL . '/includes/tweaks/password-requirements/assets/js/script.js', array( 'jquery' ), \WBCR\Titan\Plugin::app()->getPluginVersion() );
		}
	}

	/**
	 * Provide the reason string displayed to users on the change password form.
	 *
	 * @param $evaluation
	 *
	 * @return string
	 */
	public function reason( $evaluation ) {
		return esc_html__( 'Due to site rules, a strong password is required for your account. Please choose a new password that rates as strong on the meter.', 'titan-security' );
	}

	/**
	 * Evaluate the strength of a password.
	 *
	 * @param string $password
	 * @param \WP_User $user
	 *
	 * @return int
	 */
	public function evaluate( $password, $user ) {
		return $this->get_password_strength( $user, $password );
	}

	/**
	 * Validate whether a password strength is acceptable for a given user.
	 *
	 * @param int $strength
	 * @param \WP_User|\stdClass $user
	 * @param array $settings
	 * @param array $args
	 *
	 * @return bool
	 */
	public function validate( $strength, $user, $settings, $args ) {

		if ( (int) $strength === 4 ) {
			return true;
		}

		require_once( WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/class-canonical-roles.php' );

		$role = isset( $args['canonical'] ) ? $args['canonical'] : Canonical_Roles::get_user_role( $user );

		if ( ! Canonical_Roles::is_canonical_role_at_least( $settings['role'], $role ) ) {
			return true;
		}

		return $this->make_error_message();
	}

	public function get_settings_config() {
		return array(
			'label'       => esc_html__( 'Strong Passwords', 'titan-security' ),
			'description' => esc_html__( 'Force users to use strong passwords as rated by the WordPress password meter.', 'titan-security' ),
			'render'      => array( $this, 'render_settings' ),
			'sanitize'    => array( $this, 'sanitize_settings' ),
		);
	}

	/**
	 * Render the Settings Page.
	 *
	 * @param \ITSEC_Form $form
	 */
	public function render_settings( $form ) {

		$href = 'http://codex.wordpress.org/Roles_and_Capabilities';
		$link = '<a href="' . $href . '" target="_blank" rel="noopener noreferrer">' . $href . '</a>';
		?>
        <tr>
            <th scope="row">
                <label for="titan-password-requirements-requirement_settings-strength-role">
					<?php esc_html_e( 'Minimum Role', 'titan-security' ); ?>
                </label>
            </th>
            <td>
				<?php $form->add_canonical_roles( 'role' ); ?>
                <br/>
                <label for="titan-password-requirements-requirement_settings-strength-role"><?php _e( 'Minimum role at which a user must choose a strong password.', 'titan-security' ); ?></label>
                <p class="description"><?php printf( __( 'For more information on WordPress roles and capabilities please see %s.', 'titan-security' ), $link ); ?></p>
                <p class="warningtext description"><?php _e( 'Warning: If your site invites public registrations setting the role too low may annoy your members.', 'titan-security' ); ?></p>
            </td>
        </tr>
		<?php
	}

	/**
	 * Get a list of the sanitizer rules to apply.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function sanitize_settings( $settings ) {
		return array(
			array( 'string', 'role', esc_html__( 'Minimum Role for Strong Passwords', 'titan-security' ) ),
			array( 'canonical-roles', 'role', esc_html__( 'Minimum Role for Strong Passwords', 'titan-security' ) ),
		);
	}

	/**
	 * Get the strong password error message according to the given context.
	 *
	 * @return string
	 */
	private function make_error_message() {
		$message = __( '<strong>Error</strong>: Due to site rules, a strong password is required. Please choose a new password that rates as <strong>Strong</strong> on the meter.', 'titan-security' );

		return wp_kses( $message, array( 'strong' => array() ) );
	}

	/**
	 * Calculate the strength of a password.
	 *
	 * @param \WP_User $user
	 * @param string $password
	 *
	 * @return int
	 */
	private function get_password_strength( $user, $password ) {

		$penalty_strings = array(
			get_site_option( 'admin_email' )
		);
		$user_properties = array(
			'user_login',
			'first_name',
			'last_name',
			'nickname',
			'display_name',
			'user_email',
			'user_url',
			'description'
		);

		foreach ( $user_properties as $user_property ) {
			if ( isset( $user->$user_property ) ) {
				$penalty_strings[] = $user->$user_property;
			}
		}

		$results = self::get_password_strength_results( $password, $penalty_strings );

		return $results->score;
	}

	/**
	 * Evaluate a password's strength.
	 *
	 * @param string $password
	 * @param array $penalty_strings Additional strings that if found within the password, will decrease the strength.
	 *
	 * @return \ITSEC_Zxcvbn_Results
	 */
	public static function get_password_strength_results( $password, $penalty_strings = array() ) {
		if ( ! isset( $GLOBALS['titan_zxcvbn'] ) ) {
			require_once( WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/libs/zxcvbn-php/zxcvbn.php' );
			$GLOBALS['titan_zxcvbn'] = new \ITSEC_Zxcvbn();
		}

		return $GLOBALS['titan_zxcvbn']->test_password( $password, $penalty_strings );
	}
}

new Strong_Passwords();

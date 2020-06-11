<?php

namespace WBCR\Titan\Tweaks;

/**
 * Class \WBCR\Titan\Tweaks\Password_Requirements_Base
 */
class Password_Requirements_Base {

	/** @var array[] */
	private static $requirements;

	/**
	 * Get all registered password requirements.
	 *
	 * @return array
	 */
	public static function get_registered() {
		if ( null === self::$requirements ) {
			self::$requirements = array();

			/**
			 * Fires when password requirements should be registered.
			 */
			do_action( 'titan_register_password_requirements' );
		}

		return self::$requirements;
	}

	/**
	 * Register a password requirement.
	 *
	 * @param string $reason_code
	 * @param array $opts
	 */
	public static function register( $reason_code, $opts ) {
		$merged = wp_parse_args( $opts, array(
			'evaluate'                => null,
			'validate'                => null,
			'flag_check'              => null,
			'reason'                  => null,
			'defaults'                => null,
			'settings_config'         => null, // Callable returning label, description, render & sanitize callbacks.
			'meta'                    => "_titan_password_evaluation_{$reason_code}",
			'evaluate_if_not_enabled' => false,
		) );

		if ( ( array_key_exists( 'validate', $opts ) || array_key_exists( 'evaluate', $opts ) ) && ( ! is_callable( $merged['validate'] ) || ! is_callable( $merged['evaluate'] ) ) ) {
			return;
		}

		if ( array_key_exists( 'flag_check', $opts ) && ! is_callable( $merged['flag_check'] ) ) {
			return;
		}

		if ( array_key_exists( 'defaults', $opts ) ) {
			if ( ! is_array( $merged['defaults'] ) ) {
				return;
			}

			if ( ! array_key_exists( 'settings_config', $opts ) ) {
				return;
			}
		}

		if ( array_key_exists( 'settings_config', $opts ) && ! is_callable( $merged['settings_config'] ) ) {
			return;
		}

		self::$requirements[ $reason_code ] = $merged;
	}

	/**
	 * Get a message indicating to the user why a password change is required.
	 *
	 * @param \WP_User $user
	 *
	 * @return string
	 */
	public static function get_message_for_password_change_reason( $user ) {

		if ( ! $reason = self::password_change_required( $user ) ) {
			return '';
		}

		$message = '';

		$registered = self::get_registered();

		if ( isset( $registered[ $reason ] ) ) {
			$settings = self::get_requirement_settings( $reason );
			$message  = call_user_func( $registered[ $reason ]['reason'], get_user_meta( $user->ID, $registered[ $reason ]['meta'], true ), $settings );
		}

		/**
		 * Retrieve a human readable description as to why a password change has been required for the current user.
		 *
		 * Modules MUST HTML escape their reason strings before returning them with this filter.
		 *
		 * @param string $message
		 * @param \WP_User $user
		 */
		$message = apply_filters( "titan_password_change_requirement_description_for_{$reason}", $message, $user );

		if ( $message ) {
			return $message;
		}

		return esc_html__( 'A password change is required for your account.', 'titan-security' );
	}

	/**
	 * Validate a user's password.
	 *
	 * @param \WP_User|\stdClass|int $user
	 * @param string $new_password
	 * @param array $args
	 *
	 * @return \WP_Error Error object with new errors.
	 */
	public static function validate_password( $user, $new_password, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'error'   => new \WP_Error(),
			'context' => '',
		) );

		/** @var \WP_Error $error */
		$error = $args['error'];
		$user  = $user instanceof \stdClass ? $user : self::get_user( $user );

		if ( ! $user ) {
			$error->add( 'invalid_user', esc_html__( 'Invalid User', 'titan-security' ) );

			return $error;
		}

		if ( ! empty( $user->ID ) && wp_check_password( $new_password, get_userdata( $user->ID )->user_pass, $user->ID ) ) {
			$message = wp_kses( __( '<strong>ERROR</strong>: The password you have chosen appears to have been used before. You must choose a new password.', 'titan-security' ), array( 'strong' => array() ) );
			$error->add( 'pass', $message );

			return $error;
		}

		require_once( WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/class-canonical-roles.php' );

		if ( isset( $args['role'] ) && $user instanceof \WP_User ) {
			$canonical = \WBCR\Titan\Tweaks\Canonical_Roles::get_canonical_role_from_role_and_user( $args['role'], $user );
		} elseif ( isset( $args['role'] ) ) {
			$canonical = \WBCR\Titan\Tweaks\Canonical_Roles::get_canonical_role_from_role( $args['role'] );
		} elseif ( empty( $user->ID ) || ! is_numeric( $user->ID ) ) {
			$canonical = \WBCR\Titan\Tweaks\Canonical_Roles::get_canonical_role_from_role( get_option( 'default_role', 'subscriber' ) );
		} else {
			$canonical = \WBCR\Titan\Tweaks\Canonical_Roles::get_user_role( $user );
		}

		$args['canonical'] = $canonical;

		/**
		 * Fires when modules should validate a password according to their rules.
		 *
		 * @param \WP_Error $error
		 * @param \WP_User|\stdClass $user
		 * @param string $new_password
		 * @param array $args
		 *
		 * @since 3.9.0
		 *
		 */
		do_action( 'titan_validate_password', $error, $user, $new_password, $args );

		return $error;
	}

	/**
	 * Flag that a password change is required for a user.
	 *
	 * @param \WP_User|int $user
	 * @param string $reason
	 */
	public static function flag_password_change_required( $user, $reason ) {
		$user = self::get_user( $user );

		if ( $user ) {
			update_user_meta( $user->ID, 'titan_password_change_required', $reason );
		}
	}

	/**
	 * Check if a password change is required for the given user.
	 *
	 * @param \WP_User|int $user
	 *
	 * @return string|false Either the reason code a change is required, or false.
	 */
	public static function password_change_required( $user ) {
		$user = self::get_user( $user );

		if ( ! $user ) {
			return false;
		}

		$reason = get_user_meta( $user->ID, 'titan_password_change_required', true );

		if ( ! $reason ) {
			return false;
		}

		$registered = self::get_registered();

		if ( isset( $registered[ $reason ] ) ) {
			return self::is_requirement_enabled( $reason ) ? $reason : false;
		}

		if ( ! has_filter( "titan_password_change_requirement_description_for_{$reason}" ) ) {
			return false;
		}

		return $reason;
	}

	/**
	 * Globally clear all required password changes with a particular reason code.
	 *
	 * @param string $reason
	 */
	public static function global_clear_required_password_change( $reason ) {
		delete_metadata( 'user', 0, 'titan_password_change_required', $reason, true );
	}

	/**
	 * Get the GMT time the user's password has last been changed.
	 *
	 * @param \WP_User|int $user
	 *
	 * @return int
	 */
	public static function password_last_changed( $user ) {

		$user = self::get_user( $user );

		if ( ! $user ) {
			return 0;
		}

		$changed    = (int) get_user_meta( $user->ID, 'titan_last_password_change', true );
		$deprecated = (int) get_user_meta( $user->ID, 'titan-password-updated', true );

		if ( $deprecated > $changed ) {
			return $deprecated;
		}

		if ( ! $changed ) {
			return strtotime( $user->user_registered );
		}

		return $changed;
	}

	/**
	 * Is a password requirement enabled.
	 *
	 * @param string $requirement
	 *
	 * @return bool
	 */
	public static function is_requirement_enabled( $requirement ) {

		$requirements = self::get_registered();

		if ( ! isset( $requirements[ $requirement ] ) ) {
			return false;
		}

		// If the requirement does not have any settings, than it is always enabled.
		if ( null === $requirements[ $requirement ]['settings_config'] ) {
			return true;
		}

		//$enabled = ITSEC_Modules::get_setting('password-requirements', 'enabled_requirements');

		//if( !empty($enabled[$requirement]) ) {
		//return true;
		//}

		//return false;

		return true;
	}

	/**
	 * Get requirement settings.
	 *
	 * @param string $requirement
	 *
	 * @return array|false
	 */
	public static function get_requirement_settings( $requirement ) {

		$requirements = self::get_registered();

		if ( ! isset( $requirements[ $requirement ] ) ) {
			return false;
		}

		if ( null === $requirements[ $requirement ]['settings_config'] ) {
			return false;
		}

		//$all_settings = ITSEC_Modules::get_setting('password-requirements', 'requirement_settings');
		$all_settings = array(
			'strength' => array(
				'role' => 'administrator',
			),
		);;
		$settings = isset( $all_settings[ $requirement ] ) ? $all_settings[ $requirement ] : array();

		return wp_parse_args( $settings, $requirements[ $requirement ]['defaults'] );
	}

	/**
	 * Get a WordPress user object.
	 *
	 * @param int|string|\WP_User|bool $user Either the user ID ( must be an int ), the username, a WP_User object,
	 *                                      or false to retrieve the currently logged-in user.
	 *
	 * @return \WP_User|false
	 */
	public static function get_user( $user = false ) {
		if ( $user instanceof \WP_User ) {
			return $user;
		}

		if ( false === $user ) {
			$user = wp_get_current_user();
		} elseif ( is_int( $user ) ) {
			$user = get_user_by( 'id', $user );
		} elseif ( is_string( $user ) ) {
			$user = get_user_by( 'login', $user );
		} elseif ( is_object( $user ) && isset( $user->ID ) ) {
			$user = get_user_by( 'id', $user->ID );
		} else {
			if ( is_object( $user ) ) {
				$type = 'object(' . get_class( $user ) . ')';
			} else {
				$type = gettype( $user );
			}

			error_log( 'self::get_user() called with an invalid $user argument. Received $user variable of type: ' . $type );

			wp_die( 'Internal Server Error' );
		}

		if ( $user instanceof \WP_User ) {
			return $user;
		}

		return false;
	}
}
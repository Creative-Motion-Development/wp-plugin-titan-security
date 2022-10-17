<?php

namespace WBCR\Titan\Bruteforce;

/**
 * Class Limit_Login_Attempts
 */
class Limit_Login_Attempts {

	public $default_options = array(
		'bruteforce_gdpr'    => 0,

		/* Are we behind a proxy? */
		'client_type'        => WTITAN_BRUTEFORCE_DIRECT_ADDR,

		/* Lock out after this many tries */
		'allowed_retries'    => 4,

		/* Lock out for this many seconds */
		'minutes_lockout'    => 1200, // 20 minutes

		/* Long lock out after this many lockouts */
		'allowed_lockouts'   => 4,

		/* Long lock out for this many seconds */
		'long_duration'      => 86400, // 24 hours,

		/* Reset failed attempts after this many seconds */
		'valid_duration'     => 43200, // 12 hours

		/* Also limit malformed/forged cookies? */
		'cookies'            => true,

		/* Notify on lockout. Values: '', 'log', 'email', 'log,email' */
		'lockout_notify'     => 'log',

		/* If notify by email, do so after this number of lockouts */
		'notify_email_after' => 4,

		'whitelist_ips'       => array(),
		'whitelist_usernames' => array(),
		'blacklist_ips'       => array(),
		'blacklist_usernames' => array(),
	);

	/**
	 * Errors messages
	 *
	 * @var array
	 */
	public $_errors = array();

	public function __construct() {
		$this->plugin = \WBCR\Titan\Plugin::app();
		$this->hooks_init();
	}

	/**
	 * Register wp hooks and filters
	 */
	public function hooks_init() {
		add_action( 'plugins_loaded', array( $this, 'setup' ), 9999 );
		add_action( 'after_password_reset', array( $this, 'after_password_reset' ) );
		add_filter( 'titan_limit_login_whitelist_ip', array( $this, 'check_whitelist_ips' ), 10, 2 );
		add_filter( 'titan_limit_login_whitelist_usernames', array( $this, 'check_whitelist_usernames' ), 10, 2 );
		add_filter( 'titan_limit_login_blacklist_ip', array( $this, 'check_blacklist_ips' ), 10, 2 );
		add_filter( 'titan_limit_login_blacklist_usernames', array( $this, 'check_blacklist_usernames' ), 10, 2 );
		add_filter( 'illegal_user_logins', array( $this, 'register_user_blacklist' ), 999 );
	}

	/**
	 * Hook 'plugins_loaded'
	 */
	public function setup() {

		// Setup default plugin options
		//$this->sanitize_options();

		add_action( 'wp_login_failed', array( $this, 'limit_login_failed' ) );
		add_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999, 2 );

		add_filter( 'shake_error_codes', array( $this, 'failure_shake' ) );
		add_action( 'login_head', array( $this, 'add_error_message' ) );
		add_action( 'login_errors', array( $this, 'fixup_error_messages' ) );

		// Add notices for XMLRPC request
		add_filter( 'xmlrpc_login_error', array( $this, 'xmlrpc_error_messages' ) );

		// Add notices to woocommerce login page
		add_action( 'wp_head', array( $this, 'add_wc_notices' ) );

		/*
		* This action should really be changed to the 'authenticate' filter as
		* it will probably be deprecated. That is however only available in
		* later versions of WP.
		*/
		add_action( 'wp_authenticate', array( $this, 'track_credentials' ), 10, 2 );
		add_action( 'authenticate', array( $this, 'authenticate_filter' ), 5, 3 );

		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			add_action( 'init', array( $this, 'check_xmlrpc_lock' ) );
		}

		add_action( 'wp_ajax_limit-login-unlock', array( $this, 'ajax_unlock' ) );
	}

	/**
	 * @param $user \Wp_User
	 */
	public function after_password_reset( $user ) {

		$lockouts     = $this->plugin->getOption( 'bruteforce_lockouts' );
		$lockouts_log = $this->plugin->getOption( 'bruteforce_logged' );

		if ( $user->has_cap( 'administrator' ) ) {

			if ( $this->is_ip_blacklisted() ) {

				$black_list_ips = $this->plugin->getOption( 'bruteforce_blacklist_ips' );

				if ( ! empty( $black_list_ips ) ) {

					foreach ( $black_list_ips as $key => $ip ) {

						if ( $ip === $this->get_address() ) {

							unset( $black_list_ips[ $key ] );
						}
					}
				}

				$this->plugin->updateOption( 'bruteforce_blacklist_ips', $black_list_ips );
			}

			if ( $this->is_username_blacklisted( $user->data->user_login ) ) {

				$black_list_usernames = $this->plugin->getOption( 'bruteforce_blacklist_usernames' );

				if ( ! empty( $black_list_usernames ) ) {

					foreach ( $black_list_usernames as $key => $login ) {

						if ( $login === $user->data->user_login ) {

							unset( $black_list_usernames[ $key ] );
						}
					}
				}

				$this->plugin->updateOption( 'bruteforce_blacklist_usernames', $black_list_usernames );
			}

			$admin_ip = $this->get_address();
			$admin_ip = ( $this->plugin->getOption( 'bruteforce_gdpr' ) ? $this->getHash( $admin_ip ) : $admin_ip );

			if ( is_array( $lockouts ) && isset( $lockouts[ $admin_ip ] ) ) {

				unset( $lockouts[ $admin_ip ] );

				$this->plugin->updateOption( 'bruteforce_lockouts', $lockouts );

				if ( is_array( $lockouts_log ) && isset( $lockouts_log[ $admin_ip ] ) ) {

					foreach ( $lockouts_log[ $admin_ip ] as $user_login => &$data ) {

						$data['unlocked'] = true;
					}

					$this->plugin->updateOption( 'bruteforce_logged', $lockouts_log );
				}
			}

			$valid = $this->plugin->getOption( 'bruteforce_retries_valid' );

			if ( is_array( $valid ) && isset( $valid[ $admin_ip ] ) ) {

				unset( $valid[ $admin_ip ] );

				$this->plugin->updateOption( 'bruteforce_retries_valid', $valid );
			}

			$retries = $this->plugin->getOption( 'bruteforce_retries' );

			if ( is_array( $retries ) && isset( $retries[ $admin_ip ] ) ) {

				unset( $retries[ $admin_ip ] );

				$this->plugin->updateOption( 'bruteforce_retries', $retries );
			}
		} else {

			$user_ip = $this->get_address();
			$user_ip = ( $this->plugin->getOption( 'bruteforce_gdpr' ) ? $this->getHash( $user_ip ) : $user_ip );

			if ( isset( $lockouts_log[ $user_ip ] ) && is_array( $lockouts_log[ $user_ip ] ) ) {

				$last_unlocked_time = 0;
				foreach ( $lockouts_log[ $user_ip ] as $user_login => $data ) {

					if ( ! isset( $data['unlocked'] ) || ! $data['unlocked'] ) {
						continue;
					}

					if ( $data['date'] > $last_unlocked_time ) {
						$last_unlocked_time = $data['date'];
					}
				}

				if ( is_array( $lockouts ) && isset( $lockouts[ $user_ip ] ) && ( $last_unlocked_time === 0 || ( ( time() - $last_unlocked_time ) ) > ( $this->plugin->getOption( 'bruteforce_minutes_lockout' ) ) ) ) {

					unset( $lockouts[ $user_ip ] );

					if ( is_array( $lockouts_log ) && isset( $lockouts_log[ $user_ip ] ) ) {

						foreach ( $lockouts_log[ $user_ip ] as $user_login => &$data ) {

							$data['unlocked'] = true;
						}

						$this->plugin->updateOption( 'bruteforce_logged', $lockouts_log );
					}

					$this->plugin->updateOption( 'bruteforce_lockouts', $lockouts );
				}
			}
		}
	}

	public function check_xmlrpc_lock() {
		if ( is_user_logged_in() || $this->is_ip_whitelisted() ) {
			return;
		}

		if ( $this->is_ip_blacklisted() || ! $this->is_limit_login_ok() ) {
			header( 'HTTP/1.0 403 Forbidden' );
			exit;
		}
	}

	public function check_whitelist_ips( $allow, $ip ) {
		return $this->ip_in_range( $ip, (array) $this->plugin->getOption( 'bruteforce_whitelist_ips' ) );
	}

	public function check_whitelist_usernames( $allow, $username ) {
		return in_array( $username, (array) $this->plugin->getOption( 'bruteforce_whitelist_usernames' ) );
	}

	public function check_blacklist_ips( $allow, $ip ) {
		return $this->ip_in_range( $ip, (array) $this->plugin->getOption( 'bruteforce_blacklist_ips' ) );
	}

	public function check_blacklist_usernames( $allow, $username ) {
		return in_array( $username, (array) $this->plugin->getOption( 'bruteforce_blacklist_usernames' ) );
	}

	public function ip_in_range( $ip, $list ) {
		foreach ( $list as $range ) {
			$range = array_map( 'trim', explode( '-', $range ) );
			if ( count( $range ) == 1 ) {
				if ( (string) $ip === (string) $range[0] ) {
					return true;
				}
			} else {
				$low    = ip2long( $range[0] );
				$high   = ip2long( $range[1] );
				$needle = ip2long( $ip );

				if ( $low === false || $high === false || $needle === false ) {
					continue;
				}

				$low    = (float) sprintf( "%u", $low );
				$high   = (float) sprintf( "%u", $high );
				$needle = (float) sprintf( "%u", $needle );

				if ( $needle >= $low && $needle <= $high ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param $blacklist
	 *
	 * @return array|null
	 */
	public function register_user_blacklist( $blacklist ) {

		$black_list_usernames = $this->plugin->getOption( 'bruteforce_blacklist_usernames' );

		if ( ! empty( $black_list_usernames ) && is_array( $black_list_usernames ) ) {
			$blacklist += $black_list_usernames;
		}

		return $blacklist;
	}

	/**
	 * @param $error \IXR_Error
	 *
	 * @return \IXR_Error
	 */
	public function xmlrpc_error_messages( $error ) {

		if ( ! class_exists( 'IXR_Error' ) ) {
			return $error;
		}

		if ( ! $this->is_limit_login_ok() ) {
			return new \IXR_Error( 403, $this->error_msg() );
		}

		$ip      = $this->get_address();
		$retries = $this->plugin->getOption( 'bruteforce_retries' );
		$valid   = $this->plugin->getOption( 'bruteforce_retries_valid' );

		/* Should we show retries remaining? */

		if ( ! is_array( $retries ) || ! is_array( $valid ) ) {
			/* no retries at all */
			return $error;
		}
		if ( ( ! isset( $retries[ $ip ] ) && ! isset( $retries[ $this->getHash( $ip ) ] ) ) || ( ! isset( $valid[ $ip ] ) && ! isset( $valid[ $this->getHash( $ip ) ] ) ) || ( time() > $valid[ $ip ] && time() > $valid[ $this->getHash( $ip ) ] )

		) {
			/* no: no valid retries */
			return $error;
		}
		if ( ( ( ( isset( $retries[ $ip ] ) ? $retries[ $ip ] : 0 ) + ( isset( $retries[ $this->getHash( $ip ) ] ) ? $retries[ $this->getHash( $ip ) ] : 0 ) ) % $this->plugin->getOption( 'bruteforce_allowed_retries' ) ) == 0 ) {
			//* no: already been locked out for these retries */
			return $error;
		}

		$remaining = max( ( $this->plugin->getOption( 'bruteforce_allowed_retries' ) - ( ( ( isset( $retries[ $ip ] ) ? $retries[ $ip ] : 0 ) + ( isset( $retries[ $this->getHash( $ip ) ] ) ? $retries[ $this->getHash( $ip ) ] : 0 ) ) % $this->plugin->getOption( 'bruteforce_allowed_retries' ) ) ), 0 );

		return new \IXR_Error( 403, sprintf( _n( "<strong>%d</strong> attempt remaining.", "<strong>%d</strong> attempts remaining.", $remaining, 'titan-security' ), $remaining ) );
	}

	/**
	 * Errors on WooCommerce account page
	 */
	public function add_wc_notices() {

		global $limit_login_just_lockedout, $limit_login_nonempty_credentials, $limit_login_my_error_shown;

		if ( ! function_exists( 'is_account_page' ) || ! function_exists( 'wc_add_notice' ) ) {
			return;
		}

		/*
		* During lockout we do not want to show any other error messages (like
		* unknown user or empty password).
		*/
		if ( empty( $_POST ) && ! $this->is_limit_login_ok() && ! $limit_login_just_lockedout ) {
			if ( is_account_page() ) {
				wc_add_notice( $this->error_msg(), 'error' );
			}
		}
	}

	/**
	 * @param $user
	 * @param $username
	 * @param $password
	 *
	 * @return \WP_Error | \WP_User
	 */
	public function authenticate_filter( $user, $username, $password ) {

		if ( ! empty( $username ) && ! empty( $password ) ) {

			$ip = $this->get_address();

			// Check if username is blacklisted
			if ( ! $this->is_username_whitelisted( $username ) && ! $this->is_ip_whitelisted( $ip ) && ( $this->is_username_blacklisted( $username ) || $this->is_ip_blacklisted( $ip ) ) ) {

				remove_filter( 'login_errors', array( $this, 'fixup_error_messages' ) );
				remove_filter( 'login_head', array( $this, 'add_error_message' ) );
				remove_filter( 'wp_login_failed', array( $this, 'limit_login_failed' ) );
				remove_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999 );
				remove_filter( 'login_head', array( $this, 'add_error_message' ) );
				remove_filter( 'login_errors', array( $this, 'fixup_error_messages' ) );

				remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
				remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );

				$user = new \WP_Error();
				$user->add( 'username_blacklisted', "<strong>ERROR:</strong> Too many failed login attempts." );
			} elseif ( $this->is_username_whitelisted( $username ) || $this->is_ip_whitelisted( $ip ) ) {

				remove_filter( 'wp_login_failed', array( $this, 'limit_login_failed' ) );
				remove_filter( 'wp_authenticate_user', array( $this, 'wp_authenticate_user' ), 99999 );
				remove_filter( 'login_head', array( $this, 'add_error_message' ) );
				remove_filter( 'login_errors', array( $this, 'fixup_error_messages' ) );
			}
		}

		return $user;
	}

	/**
	 * Check if it is ok to login
	 *
	 * @return bool
	 */
	public function is_limit_login_ok() {

		$ip = $this->get_address();

		/* Check external whitelist filter */
		if ( $this->is_ip_whitelisted( $ip ) ) {
			return true;
		}

		/* lockout active? */
		$lockouts = $this->plugin->getOption( 'bruteforce_lockouts' );

		$a = $this->checkKey( $lockouts, $ip );
		$b = $this->checkKey( $lockouts, $this->getHash( $ip ) );

		return ( ! is_array( $lockouts ) || ( ! isset( $lockouts[ $ip ] ) && ! isset( $lockouts[ $this->getHash( $ip ) ] ) ) || ( time() >= $a && time() >= $b ) );
	}

	/**
	 * Action when login attempt failed
	 *
	 * Increase nr of retries (if necessary). Reset valid value. Setup
	 * lockout if nr of retries are above threshold. And more!
	 *
	 * A note on external whitelist: retries and statistics are still counted and
	 * notifications done as usual, but no lockout is done.
	 *
	 * @param $username
	 */
	public function limit_login_failed( $username ) {

		$ip     = $this->get_address();
		$ipHash = $this->getHash( $this->get_address() );

		/* if currently locked-out, do not add to retries */
		$lockouts = $this->plugin->getOption( 'bruteforce_lockouts' );

		if ( ! is_array( $lockouts ) ) {
			$lockouts = array();
		}

		if ( ( isset( $lockouts[ $ip ] ) && time() < $lockouts[ $ip ] ) || ( isset( $lockouts[ $ipHash ] ) && time() < $lockouts[ $ipHash ] ) ) {
			return;
		}

		/* Get the arrays with retries and retries-valid information */
		$retries = $this->plugin->getOption( 'bruteforce_retries' );
		$valid   = $this->plugin->getOption( 'bruteforce_retries_valid' );

		if ( ! is_array( $retries ) ) {
			$retries = array();
			$this->plugin->updateOption( 'bruteforce_retries', $retries );
		}

		if ( ! is_array( $valid ) ) {
			$valid = array();
			$this->plugin->updateOption( 'bruteforce_retries_valid', $valid );
		}

		$gdpr = $this->plugin->getOption( 'bruteforce_gdpr' );
		$ip   = ( $gdpr ? $ipHash : $ip );
		/* Check validity and add one to retries */
		if ( isset( $retries[ $ip ] ) && isset( $valid[ $ip ] ) && time() < $valid[ $ip ] ) {
			$retries[ $ip ] ++;
		} else {
			$retries[ $ip ] = 1;
		}
		$valid[ $ip ] = time() + $this->plugin->getOption( 'bruteforce_valid_duration' );

		/* lockout? */
		if ( $retries[ $ip ] % $this->plugin->getOption( 'bruteforce_allowed_retries' ) != 0 ) {
			/*
			* Not lockout (yet!)
			* Do housecleaning (which also saves retry/valid values).
			*/
			$this->cleanup( $retries, null, $valid );

			return;
		}

		/* lockout! */
		$whitelisted  = $this->is_ip_whitelisted( $ip );
		$retries_long = $this->plugin->getOption( 'bruteforce_allowed_retries' ) * $this->default_options['allowed_lockouts'];

		/*
		* Note that retries and statistics are still counted and notifications
		* done as usual for whitelisted ips , but no lockout is done.
		*/
		if ( $whitelisted ) {
			if ( $retries[ $ip ] >= $retries_long ) {
				unset( $retries[ $ip ] );
				unset( $valid[ $ip ] );
			}
		} else {
			global $limit_login_just_lockedout;
			$limit_login_just_lockedout = true;
			$gdpr                       = $this->plugin->getOption( 'bruteforce_gdpr' );
			$index                      = ( $gdpr ? $ipHash : $ip );

			/* setup lockout, reset retries as needed */
			if ( ( isset( $retries[ $ip ] ) ? $retries[ $ip ] : 0 ) >= $retries_long || ( isset( $retries[ $ipHash ] ) ? $retries[ $ipHash ] : 0 ) >= $retries_long ) {
				/* long lockout */
				$lockouts[ $index ] = time() + $this->default_options['long_duration'];
				unset( $retries[ $index ] );
				unset( $valid[ $index ] );
			} else {
				/* normal lockout */
				$lockouts[ $index ] = time() + $this->plugin->getOption( 'bruteforce_minutes_lockout' );
			}
		}

		/* do housecleaning and save values */
		$this->cleanup( $retries, $lockouts, $valid );

		/* do any notification */
		$this->notify_log( $username );

		/* increase statistics */
		$total = $this->plugin->getOption( 'bruteforce_lockouts_total' );
		if ( $total === false || ! is_numeric( $total ) ) {
			$this->plugin->updateOption( 'bruteforce_lockouts_total', 1 );
		} else {
			$this->plugin->updateOption( 'bruteforce_lockouts_total', $total + 1 );
		}
	}

	/**
	 * Logging of lockout (if configured)
	 *
	 * @param $user_login
	 *
	 * @internal param $user
	 */
	public function notify_log( $user_login ) {

		if ( ! $user_login ) {
			return;
		}

		$log = $option = $this->plugin->getOption( 'bruteforce_logged' );
		if ( ! is_array( $log ) ) {
			$log = array();
		}
		$ip = $this->get_address();

		$index = ( $this->plugin->getOption( 'bruteforce_gdpr' ) ? $this->getHash( $ip ) : $ip );
		/* can be written much simpler, if you do not mind php warnings */
		if ( ! isset( $log[ $index ] ) ) {
			$log[ $index ] = array();
		}

		if ( ! isset( $log[ $index ][ $user_login ] ) ) {
			$log[ $index ][ $user_login ] = array( 'counter' => 0 );
		} elseif ( ! is_array( $log[ $index ][ $user_login ] ) ) {
			$log[ $index ][ $user_login ] = array(
				'counter' => $log[ $index ][ $user_login ],
			);
		}

		$log[ $index ][ $user_login ]['counter'] ++;
		$log[ $index ][ $user_login ]['date'] = time();

		if ( isset( $_POST['woocommerce-login-nonce'] ) ) {
			$gateway = 'WooCommerce';
		} elseif ( isset( $GLOBALS['wp_xmlrpc_server'] ) && is_object( $GLOBALS['wp_xmlrpc_server'] ) ) {
			$gateway = 'XMLRPC';
		} else {
			$gateway = 'WP Login';
		}

		$log[ $index ][ $user_login ]['gateway'] = $gateway;

		if ( $option === false ) {
			$this->plugin->updateOption( 'bruteforce_logged', $log );
		} else {
			$this->plugin->updateOption( 'bruteforce_logged', $log );
		}
	}

	/**
	 * Check if IP is whitelisted.
	 *
	 * This function allow external ip whitelisting using a filter. Note that it can
	 * be called multiple times during the login process.
	 *
	 * Note that retries and statistics are still counted and notifications
	 * done as usual for whitelisted ips , but no lockout is done.
	 *
	 * Example:
	 * function my_ip_whitelist($allow, $ip) {
	 *    return ($ip == 'my-ip') ? true : $allow;
	 * }
	 * add_filter('titan_limit_login_whitelist_ip', 'my_ip_whitelist', 10, 2);
	 *
	 * @param null $ip
	 *
	 * @return bool
	 */
	public function is_ip_whitelisted( $ip = null ) {

		if ( is_null( $ip ) ) {
			$ip = $this->get_address();
		}

		$whitelisted = apply_filters( 'titan_limit_login_whitelist_ip', false, $ip );

		return ( $whitelisted === true );
	}

	public function is_username_whitelisted( $username ) {

		if ( empty( $username ) ) {
			return false;
		}

		$whitelisted = apply_filters( 'titan_limit_login_whitelist_usernames', false, $username );

		return ( $whitelisted === true );
	}

	public function is_ip_blacklisted( $ip = null ) {

		if ( is_null( $ip ) ) {
			$ip = $this->get_address();
		}

		$whitelisted = apply_filters( 'titan_limit_login_blacklist_ip', false, $ip );

		return ( $whitelisted === true );
	}

	public function is_username_blacklisted( $username ) {

		if ( empty( $username ) ) {
			return false;
		}

		$whitelisted = apply_filters( 'titan_limit_login_blacklist_usernames', false, $username );

		return ( $whitelisted === true );
	}

	/**
	 * Filter: allow login attempt? (called from wp_authenticate())
	 *
	 * @param $user \WP_User
	 * @param $password
	 *
	 * @return \WP_Error
	 */
	public function wp_authenticate_user( $user, $password ) {

		if ( is_wp_error( $user ) || $this->check_whitelist_ips( false, $this->get_address() ) || $this->check_whitelist_usernames( false, $user->user_login ) || $this->is_limit_login_ok() ) {

			return $user;
		}

		$error = new \WP_Error();

		global $limit_login_my_error_shown;
		$limit_login_my_error_shown = true;

		if ( $this->is_username_blacklisted( $user->user_login ) || $this->is_ip_blacklisted( $this->get_address() ) ) {
			$error->add( 'username_blacklisted', "<strong>ERROR:</strong> Too many failed login attempts." );
		} else {
			// This error should be the same as in "shake it" filter below
			$error->add( 'too_many_retries', $this->error_msg() );
		}

		return $error;
	}

	/**
	 * Filter: add this failure to login page "Shake it!"
	 *
	 * @param $error_codes
	 *
	 * @return array
	 */
	public function failure_shake( $error_codes ) {
		$error_codes[] = 'too_many_retries';
		$error_codes[] = 'username_blacklisted';

		return $error_codes;
	}

	/**
	 * Keep track of if user or password are empty, to filter errors correctly
	 *
	 * @param $user
	 * @param $password
	 */
	public function track_credentials( $user, $password ) {
		global $limit_login_nonempty_credentials;

		$limit_login_nonempty_credentials = ( ! empty( $user ) && ! empty( $password ) );
	}

	/**
	 * Should we show errors and messages on this page?
	 *
	 * @return bool
	 */
	public function login_show_msg() {
		if ( isset( $_GET['key'] ) ) {
			/* reset password */
			return false;
		}

		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

		return ( $action != 'lostpassword' && $action != 'retrievepassword' && $action != 'resetpass' && $action != 'rp' && $action != 'register' );
	}

	/**
	 * Construct informative error message
	 *
	 * @return string
	 */
	public function error_msg() {
		$ip       = $this->get_address();
		$lockouts = $this->plugin->getOption( 'bruteforce_lockouts' );
		$a        = $this->checkKey( $lockouts, $ip );
		$b        = $this->checkKey( $lockouts, $this->getHash( $ip ) );

		$msg = __( '<strong>ERROR</strong>: Too many failed login attempts.', 'titan-security' ) . ' ';

		if ( ! is_array( $lockouts ) || ( ! isset( $lockouts[ $ip ] ) && ! isset( $lockouts[ $this->getHash( $ip ) ] ) ) || ( time() >= $a && time() >= $b ) ) {
			/* Huh? No timeout active? */
			$msg .= __( 'Please try again later.', 'titan-security' );

			return $msg;
		}

		$when = ceil( ( ( $a > $b ? $a : $b ) - time() ) / 60 );
		if ( $when > 60 ) {
			$when = ceil( $when / 60 );
			$msg  .= sprintf( _n( 'Please try again in %d hour.', 'Please try again in %d hours.', $when, 'titan-security' ), $when );
		} else {
			$msg .= sprintf( _n( 'Please try again in %d minute.', 'Please try again in %d minutes.', $when, 'titan-security' ), $when );
		}

		$msg .= '<br><br>' . sprintf( __( 'You can also try <a href="%s">resetting your password</a> and that should help you to log in.', 'titan-security' ), wp_lostpassword_url() );

		return $msg;
	}

	/**
	 * Add a message to login page when necessary
	 */
	public function add_error_message() {
		global $error, $limit_login_my_error_shown;

		if ( ! $this->login_show_msg() || $limit_login_my_error_shown ) {
			return;
		}

		$msg = $this->get_message();

		if ( $msg != '' ) {
			$limit_login_my_error_shown = true;
			$error                      .= $msg;
		}

		return;
	}

	/**
	 * Fix up the error message before showing it
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function fixup_error_messages( $content ) {
		global $limit_login_just_lockedout, $limit_login_nonempty_credentials, $limit_login_my_error_shown;

		if ( ! $this->login_show_msg() ) {
			return $content;
		}

		/*
		* During lockout we do not want to show any other error messages (like
		* unknown user or empty password).
		*/
		if ( ! $this->is_limit_login_ok() && ! $limit_login_just_lockedout ) {
			return $this->error_msg();
		}

		/*
		* We want to filter the messages 'Invalid username' and
		* 'Invalid password' as that is an information leak regarding user
		* account names (prior to WP 2.9?).
		*
		* Also, if more than one error message, put an extra <br /> tag between
		* them.
		*/
		$msgs = explode( "<br />\n", $content );

		if ( strlen( end( $msgs ) ) == 0 ) {
			/* remove last entry empty string */
			array_pop( $msgs );
		}

		$count         = count( $msgs );
		$my_warn_count = $limit_login_my_error_shown ? 1 : 0;

		if ( $limit_login_nonempty_credentials && $count > $my_warn_count ) {

			/* Replace error message, including ours if necessary */
			if ( ! empty( $_REQUEST['log'] ) && is_email( $_REQUEST['log'] ) ) {
				$content = __( '<strong>ERROR</strong>: Incorrect email address or password.', 'titan-security' ) . "<br />\n";
			} else {
				$content = __( '<strong>ERROR</strong>: Incorrect username or password.', 'titan-security' ) . "<br />\n";
			}

			if ( $limit_login_my_error_shown || $this->get_message() ) {
				$content .= "<br />\n" . $this->get_message() . "<br />\n";
			}

			return $content;
		} elseif ( $count <= 1 ) {
			return $content;
		}

		$new = '';
		while ( $count -- > 0 ) {
			$new .= array_shift( $msgs ) . "<br />\n";
			if ( $count > 0 ) {
				$new .= "<br />\n";
			}
		}

		return $new;
	}

	public function fixup_error_messages_wc( \WP_Error $error ) {
		$error->add( 1, __( 'WC Error' ) );
	}

	/**
	 * Return current (error) message to show, if any
	 *
	 * @return string
	 */
	public function get_message() {
		/* Check external whitelist */
		if ( $this->is_ip_whitelisted() ) {
			return '';
		}

		/* Is lockout in effect? */
		if ( ! $this->is_limit_login_ok() ) {
			return $this->error_msg();
		}

		return $this->retries_remaining_msg();
	}

	/**
	 * Construct retries remaining message
	 *
	 * @return string
	 */
	public function retries_remaining_msg() {
		$ip      = $this->get_address();
		$retries = $this->plugin->getOption( 'bruteforce_retries' );
		$valid   = $this->plugin->getOption( 'bruteforce_retries_valid' );
		$a       = $this->checkKey( $retries, $ip );
		$b       = $this->checkKey( $retries, $this->getHash( $ip ) );
		$c       = $this->checkKey( $valid, $ip );
		$d       = $this->checkKey( $valid, $this->getHash( $ip ) );

		/* Should we show retries remaining? */
		if ( ! is_array( $retries ) || ! is_array( $valid ) ) {
			/* no retries at all */
			return '';
		}
		if ( ( ! isset( $retries[ $ip ] ) && ! isset( $retries[ $this->getHash( $ip ) ] ) ) || ( ! isset( $valid[ $ip ] ) && ! isset( $valid[ $this->getHash( $ip ) ] ) ) || ( time() > $c && time() > $d ) ) {
			/* no: no valid retries */
			return '';
		}
		if ( ( $a % $this->plugin->getOption( 'bruteforce_allowed_retries' ) ) == 0 && ( $b % $this->plugin->getOption( 'bruteforce_allowed_retries' ) ) == 0 ) {
			/* no: already been locked out for these retries */
			return '';
		}

		$remaining = max( ( $this->plugin->getOption( 'bruteforce_allowed_retries' ) - ( ( $a + $b ) % $this->plugin->getOption( 'bruteforce_allowed_retries' ) ) ), 0 );

		return sprintf( _n( "<strong>%d</strong> attempt remaining.", "<strong>%d</strong> attempts remaining.", $remaining, 'titan-security' ), $remaining );
	}

	/**
	 * Get correct remote address
	 *
	 * @return string
	 *
	 */
	public function get_address() {

		$trusted_ip_origins = $this->default_options['client_type']; //$this->plugin->getOption('trusted_ip_origins');

		if ( empty( $trusted_ip_origins ) || ! is_array( $trusted_ip_origins ) ) {

			$trusted_ip_origins = array();
		}

		if ( ! in_array( 'REMOTE_ADDR', $trusted_ip_origins ) ) {

			$trusted_ip_origins[] = 'REMOTE_ADDR';
		}

		$ip = '';
		foreach ( $trusted_ip_origins as $origin ) {

			if ( isset( $_SERVER[ $origin ] ) && ! empty( $_SERVER[ $origin ] ) ) {

				$ip = sanitize_text_field($_SERVER[ $origin ]);
				break;
			}
		}

		$ip = preg_replace( '/^(\d+\.\d+\.\d+\.\d+):\d+$/', '\1', $ip );

		return $ip;
	}

	/**
	 * Clean up old lockouts and retries, and save supplied arrays
	 *
	 * @param null $retries
	 * @param null $lockouts
	 * @param null $valid
	 */
	public function cleanup( $retries = null, $lockouts = null, $valid = null ) {
		$now      = time();
		$lockouts = ! is_null( $lockouts ) ? $lockouts : $this->plugin->getOption( 'bruteforce_lockouts' );

		$log = $this->plugin->getOption( 'bruteforce_logged' );

		/* remove old lockouts */
		if ( is_array( $lockouts ) ) {
			foreach ( $lockouts as $ip => $lockout ) {
				if ( $lockout < $now ) {
					unset( $lockouts[ $ip ] );

					if ( is_array( $log ) && isset( $log[ $ip ] ) ) {
						foreach ( $log[ $ip ] as $user_login => &$data ) {

							$data['unlocked'] = true;
						}
					}
				}
			}
			$this->plugin->updateOption( 'bruteforce_lockouts', $lockouts );
		}

		$this->plugin->updateOption( 'bruteforce_logged', $log );

		/* remove retries that are no longer valid */
		$valid   = ! is_null( $valid ) ? $valid : $this->plugin->getOption( 'bruteforce_retries_valid' );
		$retries = ! is_null( $retries ) ? $retries : $this->plugin->getOption( 'bruteforce_retries' );
		if ( ! is_array( $valid ) || ! is_array( $retries ) ) {
			return;
		}

		foreach ( $valid as $ip => $lockout ) {
			if ( $lockout < $now ) {
				unset( $valid[ $ip ] );
				unset( $retries[ $ip ] );
			}
		}

		/* go through retries directly, if for some reason they've gone out of sync */
		foreach ( $retries as $ip => $retry ) {
			if ( ! isset( $valid[ $ip ] ) ) {
				unset( $retries[ $ip ] );
			}
		}

		$this->plugin->updateOption( 'bruteforce_retries', $retries );
		$this->plugin->updateOption( 'bruteforce_retries_valid', $valid );
	}

	public function ajax_unlock() {
		check_ajax_referer( 'limit-login-unlock', 'sec' );
		$ip = sanitize_text_field($_POST['ip']);

		$lockouts = (array) $this->plugin->getOption( 'bruteforce_lockouts' );

		if ( isset( $lockouts[ $ip ] ) ) {
			unset( $lockouts[ $ip ] );
			$this->plugin->updateOption( 'bruteforce_lockouts', $lockouts );
		}

		//save to log
		$user_login = sanitize_text_field($_POST['username']);
		$log        = $this->plugin->getOption( 'bruteforce_logged' );

		if ( @$log[ $ip ][ $user_login ] ) {
			if ( ! is_array( $log[ $ip ][ $user_login ] ) ) {
				$log[ $ip ][ $user_login ] = array(
					'counter' => $log[ $ip ][ $user_login ],
				);
			}
			$log[ $ip ][ $user_login ]['unlocked'] = true;

			$this->plugin->updateOption( 'bruteforce_logged', $log );
		}

		header( 'Content-Type: application/json' );
		echo 'true';
		exit;
	}

	/**
	 * Show error message
	 *
	 * @param $msg
	 */
	public function show_error( $msg ) {
		Helpers::show_error( $msg );
	}

	/**
	 * returns IP with its md5 value
	 */
	private function getHash( $str ) {
		return md5( $str );
	}

	/**
	 * @param $arr - array
	 * @param $k - key
	 *
	 * @return int array value at given index or zero
	 */
	private function checkKey( $arr, $k ) {
		return isset( $arr[ $k ] ) ? $arr[ $k ] : 0;
	}
}

new Limit_Login_Attempts();
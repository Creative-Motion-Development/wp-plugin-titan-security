<?php

namespace WBCR\Titan\Bruteforce;

/***************************************************************************************
 * Different ways to get remote address: direct & behind proxy
 **************************************************************************************/
define('WTITAN_BRUTEFORCE_DIRECT_ADDR', 'REMOTE_ADDR');
define('WTITAN_BRUTEFORCE_PROXY_ADDR', 'HTTP_X_FORWARDED_FOR');

/**
 * Class Limit_Login_Attempts
 */
class Limit_Login_Attempts {

	public $default_options = array(
		'gdpr' => 0,

		/* Are we behind a proxy? */
		'client_type' => WTITAN_BRUTEFORCE_DIRECT_ADDR,

		/* Lock out after this many tries */
		'allowed_retries' => 4,

		/* Lock out for this many seconds */
		'lockout_duration' => 1200, // 20 minutes

		/* Long lock out after this many lockouts */
		'allowed_lockouts' => 4,

		/* Long lock out for this many seconds */
		'long_duration' => 86400, // 24 hours,

		/* Reset failed attempts after this many seconds */
		'valid_duration' => 43200, // 12 hours

		/* Also limit malformed/forged cookies? */
		'cookies' => true,

		/* Notify on lockout. Values: '', 'log', 'email', 'log,email' */
		'lockout_notify' => 'log',

		/* If notify by email, do so after this number of lockouts */
		'notify_email_after' => 4,

		'whitelist' => array(),
		'whitelist_usernames' => array(),
		'blacklist' => array(),
		'blacklist_usernames' => array(),
	);

	/**
	 * Errors messages
	 *
	 * @var array
	 */
	public $_errors = array();

	public function __construct()
	{
		$this->hooks_init();
	}

	/**
	 * Register wp hooks and filters
	 */
	public function hooks_init()
	{
		add_action('plugins_loaded', array($this, 'setup'), 9999);
		add_action('admin_enqueue_scripts', array($this, 'enqueue'));
		add_action('after_password_reset', array($this, 'after_password_reset'));
		add_filter('limit_login_whitelist_ip', array($this, 'check_whitelist_ips'), 10, 2);
		add_filter('limit_login_whitelist_usernames', array($this, 'check_whitelist_usernames'), 10, 2);
		add_filter('limit_login_blacklist_ip', array($this, 'check_blacklist_ips'), 10, 2);
		add_filter('limit_login_blacklist_usernames', array($this, 'check_blacklist_usernames'), 10, 2);
		add_filter('illegal_user_logins', array($this, 'register_user_blacklist'), 999);
	}

	/**
	 * Hook 'plugins_loaded'
	 */
	public function setup()
	{

		// Setup default plugin options
		//$this->sanitize_options();

		add_action('wp_login_failed', array($this, 'limit_login_failed'));
		add_filter('wp_authenticate_user', array($this, 'wp_authenticate_user'), 99999, 2);

		add_filter('shake_error_codes', array($this, 'failure_shake'));
		add_action('login_head', array($this, 'add_error_message'));
		add_action('login_errors', array($this, 'fixup_error_messages'));

		// Add notices for XMLRPC request
		add_filter('xmlrpc_login_error', array($this, 'xmlrpc_error_messages'));

		// Add notices to woocommerce login page
		add_action('wp_head', array($this, 'add_wc_notices'));

		/*
		* This action should really be changed to the 'authenticate' filter as
		* it will probably be deprecated. That is however only available in
		* later versions of WP.
		*/
		add_action('wp_authenticate', array($this, 'track_credentials'), 10, 2);
		add_action('authenticate', array($this, 'authenticate_filter'), 5, 3);

		if( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {
			add_action('init', array($this, 'check_xmlrpc_lock'));
		}

		add_action('wp_ajax_limit-login-unlock', array($this, 'ajax_unlock'));
	}

	/**
	 * @param $user \Wp_User
	 */
	public function after_password_reset($user)
	{

		$lockouts = \WBCR\Titan\Plugin::app()->getOption('lockouts');
		$lockouts_log = \WBCR\Titan\Plugin::app()->getOption('logged');

		if( $user->has_cap('administrator') ) {

			if( $this->is_ip_blacklisted() ) {

				$black_list_ips = \WBCR\Titan\Plugin::app()->getOption('blacklist');

				if( !empty($black_list_ips) ) {

					foreach($black_list_ips as $key => $ip) {

						if( $ip === $this->get_address() ) {

							unset($black_list_ips[$key]);
						}
					}
				}

				\WBCR\Titan\Plugin::app()->updateOption('blacklist', $black_list_ips);
			}

			if( $this->is_username_blacklisted($user->data->user_login) ) {

				$black_list_usernames = \WBCR\Titan\Plugin::app()->getOption('blacklist_usernames');

				if( !empty($black_list_usernames) ) {

					foreach($black_list_usernames as $key => $login) {

						if( $login === $user->data->user_login ) {

							unset($black_list_usernames[$key]);
						}
					}
				}

				\WBCR\Titan\Plugin::app()->updateOption('blacklist_usernames', $black_list_usernames);
			}

			$admin_ip = $this->get_address();
			$admin_ip = (\WBCR\Titan\Plugin::app()->getOption('gdpr') ? $this->getHash($admin_ip) : $admin_ip);

			if( is_array($lockouts) && isset($lockouts[$admin_ip]) ) {

				unset($lockouts[$admin_ip]);

				\WBCR\Titan\Plugin::app()->updateOption('lockouts', $lockouts);

				if( is_array($lockouts_log) && isset($lockouts_log[$admin_ip]) ) {

					foreach($lockouts_log[$admin_ip] as $user_login => &$data) {

						$data['unlocked'] = true;
					}

					\WBCR\Titan\Plugin::app()->updateOption('logged', $lockouts_log);
				}
			}

			$valid = \WBCR\Titan\Plugin::app()->getOption('retries_valid');

			if( is_array($valid) && isset($valid[$admin_ip]) ) {

				unset($valid[$admin_ip]);

				\WBCR\Titan\Plugin::app()->updateOption('retries_valid', $valid);
			}

			$retries = \WBCR\Titan\Plugin::app()->getOption('retries');

			if( is_array($retries) && isset($retries[$admin_ip]) ) {

				unset($retries[$admin_ip]);

				\WBCR\Titan\Plugin::app()->updateOption('retries', $retries);
			}
		} else {

			$user_ip = $this->get_address();
			$user_ip = (\WBCR\Titan\Plugin::app()->getOption('gdpr') ? $this->getHash($user_ip) : $user_ip);

			if( isset($lockouts_log[$user_ip]) && is_array($lockouts_log[$user_ip]) ) {

				$last_unlocked_time = 0;
				foreach($lockouts_log[$user_ip] as $user_login => $data) {

					if( !isset($data['unlocked']) || !$data['unlocked'] ) {
						continue;
					}

					if( $data['date'] > $last_unlocked_time ) {
						$last_unlocked_time = $data['date'];
					}
				}

				if( is_array($lockouts) && isset($lockouts[$user_ip]) && ($last_unlocked_time === 0 || ((time() - $last_unlocked_time)) > (\WBCR\Titan\Plugin::app()->getOption('lockout_duration'))) ) {

					unset($lockouts[$user_ip]);

					if( is_array($lockouts_log) && isset($lockouts_log[$user_ip]) ) {

						foreach($lockouts_log[$user_ip] as $user_login => &$data) {

							$data['unlocked'] = true;
						}

						\WBCR\Titan\Plugin::app()->updateOption('logged', $lockouts_log);
					}

					\WBCR\Titan\Plugin::app()->updateOption('lockouts', $lockouts);
				}
			}
		}
	}

	public function check_xmlrpc_lock()
	{
		if( is_user_logged_in() || $this->is_ip_whitelisted() ) {
			return;
		}

		if( $this->is_ip_blacklisted() || !$this->is_limit_login_ok() ) {
			header('HTTP/1.0 403 Forbidden');
			exit;
		}
	}

	public function check_whitelist_ips($allow, $ip)
	{
		return $this->ip_in_range($ip, (array)\WBCR\Titan\Plugin::app()->getOption('whitelist'));
	}

	public function check_whitelist_usernames($allow, $username)
	{
		return in_array($username, (array)\WBCR\Titan\Plugin::app()->getOption('whitelist_usernames'));
	}

	public function check_blacklist_ips($allow, $ip)
	{
		return $this->ip_in_range($ip, (array)\WBCR\Titan\Plugin::app()->getOption('blacklist'));
	}

	public function check_blacklist_usernames($allow, $username)
	{
		return in_array($username, (array)\WBCR\Titan\Plugin::app()->getOption('blacklist_usernames'));
	}

	public function ip_in_range($ip, $list)
	{
		foreach($list as $range) {
			$range = array_map('trim', explode('-', $range));
			if( count($range) == 1 ) {
				if( (string)$ip === (string)$range[0] ) {
					return true;
				}
			} else {
				$low = ip2long($range[0]);
				$high = ip2long($range[1]);
				$needle = ip2long($ip);

				if( $low === false || $high === false || $needle === false ) {
					continue;
				}

				$low = (float)sprintf("%u", $low);
				$high = (float)sprintf("%u", $high);
				$needle = (float)sprintf("%u", $needle);

				if( $needle >= $low && $needle <= $high ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param $blacklist
	 * @return array|null
	 */
	public function register_user_blacklist($blacklist)
	{

		$black_list_usernames = \WBCR\Titan\Plugin::app()->getOption('blacklist_usernames');

		if( !empty($black_list_usernames) && is_array($black_list_usernames) ) {
			$blacklist += $black_list_usernames;
		}

		return $blacklist;
	}

	/**
	 * @param $error \IXR_Error
	 *
	 * @return \IXR_Error
	 */
	public function xmlrpc_error_messages($error)
	{

		if( !class_exists('IXR_Error') ) {
			return $error;
		}

		if( !$this->is_limit_login_ok() ) {
			return new \IXR_Error(403, $this->error_msg());
		}

		$ip = $this->get_address();
		$retries = \WBCR\Titan\Plugin::app()->getOption('retries');
		$valid = \WBCR\Titan\Plugin::app()->getOption('retries_valid');

		/* Should we show retries remaining? */

		if( !is_array($retries) || !is_array($valid) ) {
			/* no retries at all */
			return $error;
		}
		if( (!isset($retries[$ip]) && !isset($retries[$this->getHash($ip)])) || (!isset($valid[$ip]) && !isset($valid[$this->getHash($ip)])) || (time() > $valid[$ip] && time() > $valid[$this->getHash($ip)])

		) {
			/* no: no valid retries */
			return $error;
		}
		if( (((isset($retries[$ip]) ? $retries[$ip] : 0) + (isset($retries[$this->getHash($ip)]) ? $retries[$this->getHash($ip)] : 0)) % \WBCR\Titan\Plugin::app()->getOption('allowed_retries')) == 0 ) {
			//* no: already been locked out for these retries */
			return $error;
		}

		$remaining = max((\WBCR\Titan\Plugin::app()->getOption('allowed_retries') - (((isset($retries[$ip]) ? $retries[$ip] : 0) + (isset($retries[$this->getHash($ip)]) ? $retries[$this->getHash($ip)] : 0)) % \WBCR\Titan\Plugin::app()->getOption('allowed_retries'))), 0);

		return new \IXR_Error(403, sprintf(_n("<strong>%d</strong> attempt remaining.", "<strong>%d</strong> attempts remaining.", $remaining, 'limit-login-attempts-reloaded'), $remaining));
	}

	/**
	 * Errors on WooCommerce account page
	 */
	public function add_wc_notices()
	{

		global $limit_login_just_lockedout, $limit_login_nonempty_credentials, $limit_login_my_error_shown;

		if( !function_exists('is_account_page') || !function_exists('wc_add_notice') ) {
			return;
		}

		/*
		* During lockout we do not want to show any other error messages (like
		* unknown user or empty password).
		*/
		if( empty($_POST) && !$this->is_limit_login_ok() && !$limit_login_just_lockedout ) {
			if( is_account_page() ) {
				wc_add_notice($this->error_msg(), 'error');
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
	public function authenticate_filter($user, $username, $password)
	{

		if( !empty($username) && !empty($password) ) {

			$ip = $this->get_address();

			// Check if username is blacklisted
			if( !$this->is_username_whitelisted($username) && !$this->is_ip_whitelisted($ip) && ($this->is_username_blacklisted($username) || $this->is_ip_blacklisted($ip)) ) {

				remove_filter('login_errors', array($this, 'fixup_error_messages'));
				remove_filter('login_head', array($this, 'add_error_message'));
				remove_filter('wp_login_failed', array($this, 'limit_login_failed'));
				remove_filter('wp_authenticate_user', array($this, 'wp_authenticate_user'), 99999);
				remove_filter('login_head', array($this, 'add_error_message'));
				remove_filter('login_errors', array($this, 'fixup_error_messages'));

				remove_filter('authenticate', 'wp_authenticate_username_password', 20);
				remove_filter('authenticate', 'wp_authenticate_email_password', 20);

				$user = new \WP_Error();
				$user->add('username_blacklisted', "<strong>ERROR:</strong> Too many failed login attempts.");
			} elseif( $this->is_username_whitelisted($username) || $this->is_ip_whitelisted($ip) ) {

				remove_filter('wp_login_failed', array($this, 'limit_login_failed'));
				remove_filter('wp_authenticate_user', array($this, 'wp_authenticate_user'), 99999);
				remove_filter('login_head', array($this, 'add_error_message'));
				remove_filter('login_errors', array($this, 'fixup_error_messages'));
			}
		}

		return $user;
	}

	/**
	 * Setup main options
	 */
	public function sanitize_options()
	{
		$simple_int_options = array(
			'allowed_retries',
			'lockout_duration',
			'valid_duration',
			'allowed_lockouts',
			'long_duration',
			'notify_email_after'
		);
		foreach($simple_int_options as $option) {
			$val = \WBCR\Titan\Plugin::app()->getOption($option);
			if( (int)$val != $val || (int)$val <= 0 ) {
				\WBCR\Titan\Plugin::app()->updateOption($option, 1);
			}
		}
		if( \WBCR\Titan\Plugin::app()->getOption('notify_email_after') > \WBCR\Titan\Plugin::app()->getOption('allowed_lockouts') ) {
			\WBCR\Titan\Plugin::app()->updateOption('notify_email_after', \WBCR\Titan\Plugin::app()->getOption('allowed_lockouts'));
		}

		$args = explode(',', \WBCR\Titan\Plugin::app()->getOption('lockout_notify'));
		$args_allowed = explode(',', LLA_LOCKOUT_NOTIFY_ALLOWED);
		$new_args = array_intersect($args, $args_allowed);

		\WBCR\Titan\Plugin::app()->updateOption('lockout_notify', implode(',', $new_args));

		$ctype = \WBCR\Titan\Plugin::app()->getOption('client_type');
		if( $ctype != WTITAN_BRUTEFORCE_DIRECT_ADDR && $ctype != WTITAN_BRUTEFORCE_PROXY_ADDR ) {
			\WBCR\Titan\Plugin::app()->updateOption('client_type', WTITAN_BRUTEFORCE_DIRECT_ADDR);
		}
	}

	/**
	 * Check if it is ok to login
	 *
	 * @return bool
	 */
	public function is_limit_login_ok()
	{

		$ip = $this->get_address();

		/* Check external whitelist filter */
		if( $this->is_ip_whitelisted($ip) ) {
			return true;
		}

		/* lockout active? */
		$lockouts = \WBCR\Titan\Plugin::app()->getOption('lockouts');

		$a = $this->checkKey($lockouts, $ip);
		$b = $this->checkKey($lockouts, $this->getHash($ip));

		return (!is_array($lockouts) || (!isset($lockouts[$ip]) && !isset($lockouts[$this->getHash($ip)])) || (time() >= $a && time() >= $b));
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
	public function limit_login_failed($username)
	{

		$ip = $this->get_address();
		$ipHash = $this->getHash($this->get_address());

		/* if currently locked-out, do not add to retries */
		$lockouts = \WBCR\Titan\Plugin::app()->getOption('lockouts');

		if( !is_array($lockouts) ) {
			$lockouts = array();
		}

		if( (isset($lockouts[$ip]) && time() < $lockouts[$ip]) || (isset($lockouts[$ipHash]) && time() < $lockouts[$ipHash]) ) {
			return;
		}

		/* Get the arrays with retries and retries-valid information */
		$retries = \WBCR\Titan\Plugin::app()->getOption('retries');
		$valid = \WBCR\Titan\Plugin::app()->getOption('retries_valid');

		if( !is_array($retries) ) {
			$retries = array();
			\WBCR\Titan\Plugin::app()->updateOption('retries', $retries);
		}

		if( !is_array($valid) ) {
			$valid = array();
			\WBCR\Titan\Plugin::app()->updateOption('retries_valid', $valid);
		}

		$gdpr = \WBCR\Titan\Plugin::app()->getOption('gdpr');
		$ip = ($gdpr ? $ipHash : $ip);
		/* Check validity and add one to retries */
		if( isset($retries[$ip]) && isset($valid[$ip]) && time() < $valid[$ip] ) {
			$retries[$ip]++;
		} else {
			$retries[$ip] = 1;
		}
		$valid[$ip] = time() + \WBCR\Titan\Plugin::app()->getOption('valid_duration');

		/* lockout? */
		if( $retries[$ip] % \WBCR\Titan\Plugin::app()->getOption('allowed_retries') != 0 ) {
			/*
			* Not lockout (yet!)
			* Do housecleaning (which also saves retry/valid values).
			*/
			$this->cleanup($retries, null, $valid);

			return;
		}

		/* lockout! */
		$whitelisted = $this->is_ip_whitelisted($ip);
		$retries_long = \WBCR\Titan\Plugin::app()->getOption('allowed_retries') * \WBCR\Titan\Plugin::app()->getOption('allowed_lockouts');

		/*
		* Note that retries and statistics are still counted and notifications
		* done as usual for whitelisted ips , but no lockout is done.
		*/
		if( $whitelisted ) {
			if( $retries[$ip] >= $retries_long ) {
				unset($retries[$ip]);
				unset($valid[$ip]);
			}
		} else {
			global $limit_login_just_lockedout;
			$limit_login_just_lockedout = true;
			$gdpr = \WBCR\Titan\Plugin::app()->getOption('gdpr');
			$index = ($gdpr ? $ipHash : $ip);

			/* setup lockout, reset retries as needed */
			if( (isset($retries[$ip]) ? $retries[$ip] : 0) >= $retries_long || (isset($retries[$ipHash]) ? $retries[$ipHash] : 0) >= $retries_long ) {
				/* long lockout */
				$lockouts[$index] = time() + \WBCR\Titan\Plugin::app()->getOption('long_duration');
				unset($retries[$index]);
				unset($valid[$index]);
			} else {
				/* normal lockout */
				$lockouts[$index] = time() + \WBCR\Titan\Plugin::app()->getOption('lockout_duration');
			}
		}

		/* do housecleaning and save values */
		$this->cleanup($retries, $lockouts, $valid);

		/* do any notification */ //$this->notify($username);

		/* increase statistics */
		$total = \WBCR\Titan\Plugin::app()->getOption('lockouts_total');
		if( $total === false || !is_numeric($total) ) {
			$this->add_option('lockouts_total', 1);
		} else {
			\WBCR\Titan\Plugin::app()->updateOption('lockouts_total', $total + 1);
		}
	}

	/**
	 * Handle notification in event of lockout
	 *
	 * @param $user
	 */
	//public function notify($user)
	//{
	//	$args = explode(',', \WBCR\Titan\Plugin::app()->getOption('lockout_notify'));
	//
	//	if( empty($args) ) {
	//		return;
	//	}
	//
	//	foreach($args as $mode) {
	//		switch( trim($mode) ) {
	//			case 'email':
	//				$this->notify_email($user);
	//				break;
	//			case 'log':
	//				$this->notify_log($user);
	//				break;
	//		}
	//	}
	//}

	/**
	 * Email notification of lockout to admin (if configured)
	 *
	 * @param $user
	 */
	//public function notify_email($user)
	//{
	//	$ip = $this->get_address();
	//	$whitelisted = $this->is_ip_whitelisted($ip);
	//
	//	$retries = \WBCR\Titan\Plugin::app()->getOption('retries');
	//	if( !is_array($retries) ) {
	//		$retries = array();
	//	}
	//
	//	/* check if we are at the right nr to do notification */
	//	if( (isset($retries[$ip]) || isset($retries[$this->getHash($ip)])) && ((intval($retries[$ip] + $retries[$this->getHash($ip)]) / \WBCR\Titan\Plugin::app()->getOption('allowed_retries')) % \WBCR\Titan\Plugin::app()->getOption('notify_email_after')) != 0 ) {
	//		return;
	//	}
	//
	//	/* Format message. First current lockout duration */
	//	if( !isset($retries[$ip]) && !isset($retries[$this->getHash($ip)]) ) {
	//		/* longer lockout */
	//		$count = \WBCR\Titan\Plugin::app()->getOption('allowed_retries') * \WBCR\Titan\Plugin::app()->getOption('allowed_lockouts');
	//		$lockouts = \WBCR\Titan\Plugin::app()->getOption('allowed_lockouts');
	//		$time = round(\WBCR\Titan\Plugin::app()->getOption('long_duration') / 3600);
	//		$when = sprintf(_n('%d hour', '%d hours', $time, 'limit-login-attempts-reloaded'), $time);
	//	} else {
	//		/* normal lockout */
	//		$count = $retries[$ip] + $retries[$this->getHash($ip)];
	//		$lockouts = floor(($count) / \WBCR\Titan\Plugin::app()->getOption('allowed_retries'));
	//		$time = round(\WBCR\Titan\Plugin::app()->getOption('lockout_duration') / 60);
	//		$when = sprintf(_n('%d minute', '%d minutes', $time, 'limit-login-attempts-reloaded'), $time);
	//	}
	//
	//	$blogname = $this->use_local_options ? get_option('blogname') : get_site_option('site_name');
	//	$blogname = htmlspecialchars_decode($blogname, ENT_QUOTES);
	//
	//	if( $whitelisted ) {
	//		$subject = sprintf(__("[%s] Failed login attempts from whitelisted IP", 'limit-login-attempts-reloaded'), $blogname);
	//	} else {
	//		$subject = sprintf(__("[%s] Too many failed login attempts", 'limit-login-attempts-reloaded'), $blogname);
	//	}
	//
	//	$message = sprintf(__("%d failed login attempts (%d lockout(s)) from IP: %s", 'limit-login-attempts-reloaded') . "\r\n\r\n", $count, $lockouts, $ip);
	//	if( $user != '' ) {
	//		$message .= sprintf(__("Last user attempted: %s", 'limit-login-attempts-reloaded') . "\r\n\r\n", $user);
	//	}
	//	if( $whitelisted ) {
	//		$message .= __("IP was NOT blocked because of external whitelist.", 'limit-login-attempts-reloaded');
	//	} else {
	//		$message .= sprintf(__("IP was blocked for %s", 'limit-login-attempts-reloaded'), $when);
	//	}
	//
	//	if( $custom_admin_email = \WBCR\Titan\Plugin::app()->getOption('admin_notify_email') ) {
	//
	//		$admin_email = $custom_admin_email;
	//	} else {
	//
	//		$admin_email = $this->use_local_options ? get_option('admin_email') : get_site_option('admin_email');
	//	}
	//
	//	@wp_mail($admin_email, $subject, $message);
	//}

	/**
	 * Logging of lockout (if configured)
	 *
	 * @param $user_login
	 *
	 * @internal param $user
	 */
	public function notify_log($user_login)
	{

		if( !$user_login ) {
			return;
		}

		$log = $option = \WBCR\Titan\Plugin::app()->getOption('logged');
		if( !is_array($log) ) {
			$log = array();
		}
		$ip = $this->get_address();

		$index = (\WBCR\Titan\Plugin::app()->getOption('gdpr') ? $this->getHash($ip) : $ip);
		/* can be written much simpler, if you do not mind php warnings */
		if( !isset($log[$index]) ) {
			$log[$index] = array();
		}

		if( !isset($log[$index][$user_login]) ) {
			$log[$index][$user_login] = array('counter' => 0);
		} elseif( !is_array($log[$index][$user_login]) ) {
			$log[$index][$user_login] = array(
				'counter' => $log[$index][$user_login],
			);
		}

		$log[$index][$user_login]['counter']++;
		$log[$index][$user_login]['date'] = time();

		if( isset($_POST['woocommerce-login-nonce']) ) {
			$gateway = 'WooCommerce';
		} elseif( isset($GLOBALS['wp_xmlrpc_server']) && is_object($GLOBALS['wp_xmlrpc_server']) ) {
			$gateway = 'XMLRPC';
		} else {
			$gateway = 'WP Login';
		}

		$log[$index][$user_login]['gateway'] = $gateway;

		if( $option === false ) {
			$this->add_option('logged', $log);
		} else {
			\WBCR\Titan\Plugin::app()->updateOption('logged', $log);
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
	 * add_filter('limit_login_whitelist_ip', 'my_ip_whitelist', 10, 2);
	 *
	 * @param null $ip
	 *
	 * @return bool
	 */
	public function is_ip_whitelisted($ip = null)
	{

		if( is_null($ip) ) {
			$ip = $this->get_address();
		}

		$whitelisted = apply_filters('limit_login_whitelist_ip', false, $ip);

		return ($whitelisted === true);
	}

	public function is_username_whitelisted($username)
	{

		if( empty($username) ) {
			return false;
		}

		$whitelisted = apply_filters('limit_login_whitelist_usernames', false, $username);

		return ($whitelisted === true);
	}

	public function is_ip_blacklisted($ip = null)
	{

		if( is_null($ip) ) {
			$ip = $this->get_address();
		}

		$whitelisted = apply_filters('limit_login_blacklist_ip', false, $ip);

		return ($whitelisted === true);
	}

	public function is_username_blacklisted($username)
	{

		if( empty($username) ) {
			return false;
		}

		$whitelisted = apply_filters('limit_login_blacklist_usernames', false, $username);

		return ($whitelisted === true);
	}

	/**
	 * Filter: allow login attempt? (called from wp_authenticate())
	 *
	 * @param $user \WP_User
	 * @param $password
	 *
	 * @return \WP_Error
	 */
	public function wp_authenticate_user($user, $password)
	{

		if( is_wp_error($user) || $this->check_whitelist_ips(false, $this->get_address()) || $this->check_whitelist_usernames(false, $user->user_login) || $this->is_limit_login_ok() ) {

			return $user;
		}

		$error = new \WP_Error();

		global $limit_login_my_error_shown;
		$limit_login_my_error_shown = true;

		if( $this->is_username_blacklisted($user->user_login) || $this->is_ip_blacklisted($this->get_address()) ) {
			$error->add('username_blacklisted', "<strong>ERROR:</strong> Too many failed login attempts.");
		} else {
			// This error should be the same as in "shake it" filter below
			$error->add('too_many_retries', $this->error_msg());
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
	public function failure_shake($error_codes)
	{
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
	public function track_credentials($user, $password)
	{
		global $limit_login_nonempty_credentials;

		$limit_login_nonempty_credentials = (!empty($user) && !empty($password));
	}

	/**
	 * Should we show errors and messages on this page?
	 *
	 * @return bool
	 */
	public function login_show_msg()
	{
		if( isset($_GET['key']) ) {
			/* reset password */
			return false;
		}

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

		return ($action != 'lostpassword' && $action != 'retrievepassword' && $action != 'resetpass' && $action != 'rp' && $action != 'register');
	}

	/**
	 * Construct informative error message
	 *
	 * @return string
	 */
	public function error_msg()
	{
		$ip = $this->get_address();
		$lockouts = \WBCR\Titan\Plugin::app()->getOption('lockouts');
		$a = $this->checkKey($lockouts, $ip);
		$b = $this->checkKey($lockouts, $this->getHash($ip));

		$msg = __('<strong>ERROR</strong>: Too many failed login attempts.', 'limit-login-attempts-reloaded') . ' ';

		if( !is_array($lockouts) || (!isset($lockouts[$ip]) && !isset($lockouts[$this->getHash($ip)])) || (time() >= $a && time() >= $b) ) {
			/* Huh? No timeout active? */
			$msg .= __('Please try again later.', 'limit-login-attempts-reloaded');

			return $msg;
		}

		$when = ceil((($a > $b ? $a : $b) - time()) / 60);
		if( $when > 60 ) {
			$when = ceil($when / 60);
			$msg .= sprintf(_n('Please try again in %d hour.', 'Please try again in %d hours.', $when, 'limit-login-attempts-reloaded'), $when);
		} else {
			$msg .= sprintf(_n('Please try again in %d minute.', 'Please try again in %d minutes.', $when, 'limit-login-attempts-reloaded'), $when);
		}

		$msg .= '<br><br>' . sprintf(__('You can also try <a href="%s">resetting your password</a> and that should help you to log in.', 'limit-login-attempts-reloaded'), wp_lostpassword_url());

		return $msg;
	}

	/**
	 * Add a message to login page when necessary
	 */
	public function add_error_message()
	{
		global $error, $limit_login_my_error_shown;

		if( !$this->login_show_msg() || $limit_login_my_error_shown ) {
			return;
		}

		$msg = $this->get_message();

		if( $msg != '' ) {
			$limit_login_my_error_shown = true;
			$error .= $msg;
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
	public function fixup_error_messages($content)
	{
		global $limit_login_just_lockedout, $limit_login_nonempty_credentials, $limit_login_my_error_shown;

		if( !$this->login_show_msg() ) {
			return $content;
		}

		/*
		* During lockout we do not want to show any other error messages (like
		* unknown user or empty password).
		*/
		if( !$this->is_limit_login_ok() && !$limit_login_just_lockedout ) {
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
		$msgs = explode("<br />\n", $content);

		if( strlen(end($msgs)) == 0 ) {
			/* remove last entry empty string */
			array_pop($msgs);
		}

		$count = count($msgs);
		$my_warn_count = $limit_login_my_error_shown ? 1 : 0;

		if( $limit_login_nonempty_credentials && $count > $my_warn_count ) {

			/* Replace error message, including ours if necessary */
			if( !empty($_REQUEST['log']) && is_email($_REQUEST['log']) ) {
				$content = __('<strong>ERROR</strong>: Incorrect email address or password.', 'limit-login-attempts-reloaded') . "<br />\n";
			} else {
				$content = __('<strong>ERROR</strong>: Incorrect username or password.', 'limit-login-attempts-reloaded') . "<br />\n";
			}

			if( $limit_login_my_error_shown || $this->get_message() ) {
				$content .= "<br />\n" . $this->get_message() . "<br />\n";
			}

			return $content;
		} elseif( $count <= 1 ) {
			return $content;
		}

		$new = '';
		while( $count-- > 0 ) {
			$new .= array_shift($msgs) . "<br />\n";
			if( $count > 0 ) {
				$new .= "<br />\n";
			}
		}

		return $new;
	}

	public function fixup_error_messages_wc(\WP_Error $error)
	{
		$error->add(1, __('WC Error'));
	}

	/**
	 * Return current (error) message to show, if any
	 *
	 * @return string
	 */
	public function get_message()
	{
		/* Check external whitelist */
		if( $this->is_ip_whitelisted() ) {
			return '';
		}

		/* Is lockout in effect? */
		if( !$this->is_limit_login_ok() ) {
			return $this->error_msg();
		}

		return $this->retries_remaining_msg();
	}

	/**
	 * Construct retries remaining message
	 *
	 * @return string
	 */
	public function retries_remaining_msg()
	{
		$ip = $this->get_address();
		$retries = \WBCR\Titan\Plugin::app()->getOption('retries');
		$valid = \WBCR\Titan\Plugin::app()->getOption('retries_valid');
		$a = $this->checkKey($retries, $ip);
		$b = $this->checkKey($retries, $this->getHash($ip));
		$c = $this->checkKey($valid, $ip);
		$d = $this->checkKey($valid, $this->getHash($ip));

		/* Should we show retries remaining? */
		if( !is_array($retries) || !is_array($valid) ) {
			/* no retries at all */
			return '';
		}
		if( (!isset($retries[$ip]) && !isset($retries[$this->getHash($ip)])) || (!isset($valid[$ip]) && !isset($valid[$this->getHash($ip)])) || (time() > $c && time() > $d) ) {
			/* no: no valid retries */
			return '';
		}
		if( ($a % \WBCR\Titan\Plugin::app()->getOption('allowed_retries')) == 0 && ($b % \WBCR\Titan\Plugin::app()->getOption('allowed_retries')) == 0 ) {
			/* no: already been locked out for these retries */
			return '';
		}

		$remaining = max((\WBCR\Titan\Plugin::app()->getOption('allowed_retries') - (($a + $b) % \WBCR\Titan\Plugin::app()->getOption('allowed_retries'))), 0);

		return sprintf(_n("<strong>%d</strong> attempt remaining.", "<strong>%d</strong> attempts remaining.", $remaining, 'limit-login-attempts-reloaded'), $remaining);
	}

	/**
	 * Get correct remote address
	 *
	 * @return string
	 *
	 */
	public function get_address()
	{

		$trusted_ip_origins = \WBCR\Titan\Plugin::app()->getOption('trusted_ip_origins');

		if( empty($trusted_ip_origins) || !is_array($trusted_ip_origins) ) {

			$trusted_ip_origins = array();
		}

		if( !in_array('REMOTE_ADDR', $trusted_ip_origins) ) {

			$trusted_ip_origins[] = 'REMOTE_ADDR';
		}

		$ip = '';
		foreach($trusted_ip_origins as $origin) {

			if( isset($_SERVER[$origin]) && !empty($_SERVER[$origin]) ) {

				$ip = $_SERVER[$origin];
				break;
			}
		}

		$ip = preg_replace('/^(\d+\.\d+\.\d+\.\d+):\d+$/', '\1', $ip);

		return $ip;
	}

	/**
	 * Clean up old lockouts and retries, and save supplied arrays
	 *
	 * @param null $retries
	 * @param null $lockouts
	 * @param null $valid
	 */
	public function cleanup($retries = null, $lockouts = null, $valid = null)
	{
		$now = time();
		$lockouts = !is_null($lockouts) ? $lockouts : \WBCR\Titan\Plugin::app()->getOption('lockouts');

		$log = \WBCR\Titan\Plugin::app()->getOption('logged');

		/* remove old lockouts */
		if( is_array($lockouts) ) {
			foreach($lockouts as $ip => $lockout) {
				if( $lockout < $now ) {
					unset($lockouts[$ip]);

					if( is_array($log) && isset($log[$ip]) ) {
						foreach($log[$ip] as $user_login => &$data) {

							$data['unlocked'] = true;
						}
					}
				}
			}
			\WBCR\Titan\Plugin::app()->updateOption('lockouts', $lockouts);
		}

		\WBCR\Titan\Plugin::app()->updateOption('logged', $log);

		/* remove retries that are no longer valid */
		$valid = !is_null($valid) ? $valid : \WBCR\Titan\Plugin::app()->getOption('retries_valid');
		$retries = !is_null($retries) ? $retries : \WBCR\Titan\Plugin::app()->getOption('retries');
		if( !is_array($valid) || !is_array($retries) ) {
			return;
		}

		foreach($valid as $ip => $lockout) {
			if( $lockout < $now ) {
				unset($valid[$ip]);
				unset($retries[$ip]);
			}
		}

		/* go through retries directly, if for some reason they've gone out of sync */
		foreach($retries as $ip => $retry) {
			if( !isset($valid[$ip]) ) {
				unset($retries[$ip]);
			}
		}

		\WBCR\Titan\Plugin::app()->updateOption('retries', $retries);
		\WBCR\Titan\Plugin::app()->updateOption('retries_valid', $valid);
	}

	/**
	 * Render admin options page
	 */
	public function options_page()
	{
		$this->use_local_options = !is_network_admin();
		$this->cleanup();

		if( !empty($_POST) ) {
			check_admin_referer('limit-login-attempts-options');

			if( is_network_admin() ) {
				\WBCR\Titan\Plugin::app()->updateOption('allow_local_options', !empty($_POST['allow_local_options']));
			} elseif( $this->network_mode ) {
				\WBCR\Titan\Plugin::app()->updateOption('use_local_options', empty($_POST['use_global_options']));
			}

			/* Should we support GDPR */
			if( isset($_POST['gdpr']) ) {
				\WBCR\Titan\Plugin::app()->updateOption('gdpr', 1);
			} else {
				\WBCR\Titan\Plugin::app()->updateOption('gdpr', 0);
			}

			/* Should we clear log? */
			if( isset($_POST['clear_log']) ) {
				\WBCR\Titan\Plugin::app()->updateOption('logged', '');
				$this->show_error(__('Cleared IP log', 'limit-login-attempts-reloaded'));
			}

			/* Should we reset counter? */
			if( isset($_POST['reset_total']) ) {
				\WBCR\Titan\Plugin::app()->updateOption('lockouts_total', 0);
				$this->show_error(__('Reset lockout count', 'limit-login-attempts-reloaded'));
			}

			/* Should we restore current lockouts? */
			if( isset($_POST['reset_current']) ) {
				\WBCR\Titan\Plugin::app()->updateOption('lockouts', array())
				$this->show_error(__('Cleared current lockouts', 'limit-login-attempts-reloaded'));
			}

			/* Should we update options? */
			if( isset($_POST['update_options']) ) {
				\WBCR\Titan\Plugin::app()->updateOption('allowed_retries', (int)$_POST['allowed_retries']);
				\WBCR\Titan\Plugin::app()->updateOption('lockout_duration', (int)$_POST['lockout_duration'] * 60);
				\WBCR\Titan\Plugin::app()->updateOption('valid_duration', (int)$_POST['valid_duration'] * 3600);
				\WBCR\Titan\Plugin::app()->updateOption('allowed_lockouts', (int)$_POST['allowed_lockouts']);
				\WBCR\Titan\Plugin::app()->updateOption('long_duration', (int)$_POST['long_duration'] * 3600);
				\WBCR\Titan\Plugin::app()->updateOption('notify_email_after', (int)$_POST['email_after']);

				\WBCR\Titan\Plugin::app()->updateOption('admin_notify_email', sanitize_email($_POST['admin_notify_email']));

				$white_list_ips = (!empty($_POST['lla_whitelist_ips'])) ? explode("\n", str_replace("\r", "", stripslashes($_POST['lla_whitelist_ips']))) : array();

				if( !empty($white_list_ips) ) {
					foreach($white_list_ips as $key => $ip) {
						if( '' == $ip ) {
							unset($white_list_ips[$key]);
						}
					}
				}
				\WBCR\Titan\Plugin::app()->updateOption('whitelist', $white_list_ips);

				$white_list_usernames = (!empty($_POST['lla_whitelist_usernames'])) ? explode("\n", str_replace("\r", "", stripslashes($_POST['lla_whitelist_usernames']))) : array();

				if( !empty($white_list_usernames) ) {
					foreach($white_list_usernames as $key => $ip) {
						if( '' == $ip ) {
							unset($white_list_usernames[$key]);
						}
					}
				}
				\WBCR\Titan\Plugin::app()->updateOption('whitelist_usernames', $white_list_usernames);

				$black_list_ips = (!empty($_POST['lla_blacklist_ips'])) ? explode("\n", str_replace("\r", "", stripslashes($_POST['lla_blacklist_ips']))) : array();

				if( !empty($black_list_ips) ) {
					foreach($black_list_ips as $key => $ip) {
						$range = array_map('trim', explode('-', $ip));
						if( count($range) > 1 && (float)sprintf("%u", ip2long($range[0])) > (float)sprintf("%u", ip2long($range[1])) ) {
							$this->show_error(__('The "' . $ip . '" IP range is invalid', 'limit-login-attempts-reloaded'));
						}
						if( '' == $ip ) {
							unset($black_list_ips[$key]);
						}
					}
				}
				\WBCR\Titan\Plugin::app()->updateOption('blacklist', $black_list_ips);

				$black_list_usernames = (!empty($_POST['lla_blacklist_usernames'])) ? explode("\n", str_replace("\r", "", stripslashes($_POST['lla_blacklist_usernames']))) : array();

				if( !empty($black_list_usernames) ) {
					foreach($black_list_usernames as $key => $ip) {
						if( '' == $ip ) {
							unset($black_list_usernames[$key]);
						}
					}
				}
				\WBCR\Titan\Plugin::app()->updateOption('blacklist_usernames', $black_list_usernames);

				$trusted_ip_origins = (!empty($_POST['lla_trusted_ip_origins'])) ? array_map('trim', explode(',', sanitize_text_field($_POST['lla_trusted_ip_origins']))) : array();

				if( !in_array('REMOTE_ADDR', $trusted_ip_origins) ) {

					$trusted_ip_origins[] = 'REMOTE_ADDR';
				}

				\WBCR\Titan\Plugin::app()->updateOption('trusted_ip_origins', $trusted_ip_origins);

				$notify_methods = array();
				if( isset($_POST['lockout_notify_log']) ) {
					$notify_methods[] = 'log';
				}
				if( isset($_POST['lockout_notify_email']) ) {
					$notify_methods[] = 'email';
				}
				\WBCR\Titan\Plugin::app()->updateOption('lockout_notify', implode(',', $notify_methods));

				$this->sanitize_options();

				$this->show_error(__('Options saved.', 'limit-login-attempts-reloaded'));
			}
		}

		include_once(LLA_PLUGIN_DIR . '/views/options-page.php');
	}

	public function ajax_unlock()
	{
		check_ajax_referer('limit-login-unlock', 'sec');
		$ip = (string)@$_POST['ip'];

		$lockouts = (array)\WBCR\Titan\Plugin::app()->getOption('lockouts');

		if( isset($lockouts[$ip]) ) {
			unset($lockouts[$ip]);
			\WBCR\Titan\Plugin::app()->updateOption('lockouts', $lockouts);
		}

		//save to log
		$user_login = @(string)$_POST['username'];
		$log = \WBCR\Titan\Plugin::app()->getOption('logged');

		if( @$log[$ip][$user_login] ) {
			if( !is_array($log[$ip][$user_login]) ) {
				$log[$ip][$user_login] = array(
					'counter' => $log[$ip][$user_login],
				);
			}
			$log[$ip][$user_login]['unlocked'] = true;

			\WBCR\Titan\Plugin::app()->updateOption('logged', $log);
		}

		header('Content-Type: application/json');
		echo 'true';
		exit;
	}

	/**
	 * Show error message
	 *
	 * @param $msg
	 */
	public function show_error($msg)
	{
		Helpers::show_error($msg);
	}

	/**
	 * returns IP with its md5 value
	 */
	private function getHash($str)
	{
		return md5($str);
	}

	/**
	 * @param $arr - array
	 * @param $k - key
	 * @return int array value at given index or zero
	 */
	private function checkKey($arr, $k)
	{
		return isset($arr[$k]) ? $arr[$k] : 0;
	}
}
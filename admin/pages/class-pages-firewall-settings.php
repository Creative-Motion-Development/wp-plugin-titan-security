<?php

namespace WBCR\Titan\Page;

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
class Firewall_Settings extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var string
	 */
	public $id = "firewall-settings";

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public $page_parent_page = "firewall";

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var string
	 */
	public $page_menu_dashicon = 'dashicons-testimonial';

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * WBCR\Page\Settings constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->menu_title                  = __( 'Settings', 'anti-spam' );
		$this->page_menu_short_description = __( 'Firewall settings', 'anti-spam' );

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}

	/**
	 * Enqueue page assets
	 *
	 * @return void
	 * @since 6.2
	 * @see   Wbcr_FactoryPages000_AdminPage
	 *
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->scripts->request( [
			'control.checkbox',
			'control.dropdown',
			'bootstrap.tooltip',
			'holder.more-link'
		], 'bootstrap' );

		$this->styles->request( [
			'bootstrap.core',
			'bootstrap.form-group',
			'holder.more-link',
			'bootstrap.separator',
			'control.dropdown',
			'control.checkbox'
		], 'bootstrap' );
	}

	/**
	 * Permalinks options.
	 *
	 * @return mixed[]
	 * @since 6.2
	 */
	public function getPageOptions() {

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Advanced Firewall Options.', 'anti-spam' ) . '</strong>' . '<p>' . __( 'Additional modules to spam protect.', 'anti-spam' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'      => 'checkbox',
			'way'       => 'buttons',
			'name'      => 'disable_wafip_blocking',
			'title'     => __( 'Delay IP and Country blocking until after WordPress and plugins have loaded (only process firewall rules early)', 'anti-spam' ),
			'layout'    => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'      => __( 'When the Titan Firewall is optimized, the Firewall loads before the WordPress environment loads. This is desired behavior, as it increases security and gives the Firewall a performance boost. But if your server has a conflict with blocking by IP, country, or other advanced blocking settings before WordPress has loaded, you can turn on this option to allow WordPress to load first. We do not recommend enabling this option except for testing purposes.', 'anti-spam' ),
			'default'   => true,
			'eventsOn'  => [
				'show' => '.factory-control-whitelisted, .wantispam-disable-wafip-blocking-separator'
			],
			'eventsOff' => [
				'hide' => '.factory-control-whitelisted, .wantispam-disable-wafip-blocking-separator'
			]
		];

		$options[] = [
			'type'    => 'textarea',
			'name'    => 'whitelisted',
			'title'   => __( 'Whitelisted IP addresses that bypass all rules', 'anti-spam' ),
			//'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'Whitelisted IPs must be separated by commas or placed on separate lines. You can specify ranges using the following formats: 127.0.0.1/24, 127.0.0.[1-100], or 127.0.0.1-127.0.1.100. Titan automatically whitelists private networks because these are not routable on the public Internet.', 'anti-spam' ),
			'default' => ''
		];

		$options[] = [
			'type'     => 'separator',
			'cssClass' => 'wantispam-disable-wafip-blocking-separator'
		];

		$options[] = [
			'type'    => 'list',
			'way'     => 'checklist',
			'name'    => 'disable_comments_for_post_types',
			'title'   => __( 'Whitelisted services', 'comments-plus' ),
			'data'    => [
				[ 'sucuri', 'Sucuri' ],
				[ 'facebook', 'Facebook' ],
				[ 'uptime_robot', 'Uptime Robot' ],
				[ 'status_cake', 'StatusCake' ],
				[ 'managewp', 'ManageWP' ],
				[ 'seznam', 'Seznam Search Engine' ],
			],
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'    => __( 'Select the post types for which comments will be disabled', 'comments-plus' ),
			'default' => 'sucuri,facebook,uptime_robots'
		];

		$options[] = [
			'type'    => 'textarea',
			'name'    => 'banned_urls',
			'title'   => __( 'Immediately block IPs that access these URLs', 'anti-spam' ),
			//'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'Separate multiple URLs with commas or place them on separate lines. Asterisks are wildcards, but use with care. If you see an attacker repeatedly probing your site for a known vulnerability you can use this to immediately block them. All URLs must start with a "/" without quotes and must be relative. e.g. /badURLone/, /bannedPage.html, /dont-access/this/URL/, /starts/with-*', 'anti-spam' ),
			'default' => ''
		];

		$options[] = [
			'type'    => 'textarea',
			'name'    => 'waf_alert_whitelist',
			'title'   => __( 'Ignored IP addresses for Wordfence Web Application Firewall alerting', 'anti-spam' ),
			//'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'Ignored IPs must be separated by commas or placed on separate lines. These addresses will be ignored from any alerts about increased attacks and can be used to ignore things like standalone website security scanners.', 'anti-spam' ),
			'default' => ''
		];

		$options[] = [
			'type' => 'html',
			'html' => [ $this, 'get_rules_control' ]
		];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Brute Force Protection.', 'anti-spam' ) . '</strong>' . '<p>' . __( 'A Brute Force Attack consists of a large amount of repeated attempts at guessing your username and password to gain access to your WordPress admin. These attacks are automated, and the usernames and passwords used for guessing typically originate from big data leaks. Limiting the amount of login attempts that your site allows and blocking users who try an invalid username are two ways of protecting yourself against this type of attack. See full options below.', 'anti-spam' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'enable_brute_force_protection',
			'title'   => __( 'Enable brute force protection', 'anti-spam' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'This option enables all "Brute Force Protection" options, including strong password enforcement and invalid login throttling. You can modify individual options below.', 'anti-spam' ),
			'default' => true
		];

		$options[] = [
			'type'    => 'dropdown',
			'name'    => 'brute_force_max_failures',
			'title'   => __( 'Lock out after how many login failures ', 'anti-spam' ),
			'data'    => [
				[ '2', '2' ],
				[ '3', '3' ],
				[ '4', '4' ],
				[ '5', '5' ],
				[ '6', '6' ],
				[ '7', '7' ],
				[ '8', '8' ],
				[ '9', '9' ],
				[ '10', '10' ],
				[ '20', '20' ],
				[ '30', '30' ],
				[ '40', '40' ],
				[ '50', '50' ],
				[ '60', '60' ],
				[ '70', '70' ],
				[ '80', '80' ],
				[ '90', '90' ],
				[ '100', '100' ],
				[ '200', '200' ],
				[ '300', '300' ],
				[ '400', '400' ],
				[ '500', '500' ]
			],
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'This will lock out an IP address for a specified amount of time if that visitor generates the specified number of login failures. Note that it is common for real users to forget their passwords and generate up to 5 or more login attempts while trying to remember their username and/or password. So we recommend you set this to 20 which gives real users plenty of opportunity to sign in, but will block a brute force attack after 20 attempts.', 'anti-spam' ),
			'default' => '500'
		];

		$options[] = [
			'type'    => 'dropdown',
			'name'    => 'brute_force_max_forgot_passwd',
			'title'   => __( 'Lock out after how many forgot password attempts', 'anti-spam' ),
			'data'    => [
				[ '1', '1' ],
				[ '2', '2' ],
				[ '3', '3' ],
				[ '4', '4' ],
				[ '5', '5' ],
				[ '6', '6' ],
				[ '7', '7' ],
				[ '8', '8' ],
				[ '9', '9' ],
				[ '10', '10' ],
				[ '20', '20' ],
				[ '30', '30' ],
				[ '40', '40' ],
				[ '50', '50' ],
				[ '60', '60' ],
				[ '70', '70' ],
				[ '80', '80' ],
				[ '90', '90' ],
				[ '100', '100' ],
				[ '200', '200' ],
				[ '300', '300' ],
				[ '400', '400' ],
				[ '500', '500' ]
			],
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'This limits the number of times the “Forgot password?” form can be used. This protects you against having your “Forgot password?” form used to flood a real user with password reset emails, and prevents attackers trying to guess user accounts on your system. Setting this to 5 should be sufficient for most sites.', 'anti-spam' ),
			'default' => '20'
		];

		$options[] = [
			'type'    => 'dropdown',
			'name'    => 'brute_force_count_fail_mins',
			'title'   => __( 'Count failures over what time period', 'anti-spam' ),
			'data'    => [
				[ 5 * MINUTE_IN_SECONDS, '5 minutes' ],
				[ 10 * MINUTE_IN_SECONDS, '10 minutes' ],
				[ 30 * MINUTE_IN_SECONDS, '30 minutes' ],
				[ HOUR_IN_SECONDS, '1 hour' ],
				[ 2 * HOUR_IN_SECONDS, '2 hours' ],
				[ 4 * HOUR_IN_SECONDS, '4 hours' ],
				[ 6 * HOUR_IN_SECONDS, '6 hours' ],
				[ 12 * HOUR_IN_SECONDS, '12 hours' ],
				[ 24 * HOUR_IN_SECONDS, '1 day' ],
			],
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'This specifies the time frame over which we count failures . So if you specify 5 minutes and 20 failures, then if someone fails to sign in 20 times during a 5-minute period, they will be locked out from login. Brute force attacks usually send one login attempt every few seconds. So if you have set the number of login failures to 20, then 5 minutes is plenty of time to catch a brute force hack attempt. You do have the option to set it higher.', 'anti-spam' ),
			'default' => 4 * HOUR_IN_SECONDS
		];

		$options[] = [
			'type'    => 'dropdown',
			'name'    => 'brute_force_lockout_mins',
			'title'   => __( 'Amount of time a user is locked out ', 'anti-spam' ),
			'data'    => [
				[ 5 * MINUTE_IN_SECONDS, '5 minutes' ],
				[ 10 * MINUTE_IN_SECONDS, '10 minutes' ],
				[ 30 * MINUTE_IN_SECONDS, '30 minutes' ],
				[ HOUR_IN_SECONDS, '1 hour' ],
				[ 2 * HOUR_IN_SECONDS, '2 hours' ],
				[ 4 * HOUR_IN_SECONDS, '4 hours' ],
				[ 6 * HOUR_IN_SECONDS, '6 hours' ],
				[ 12 * HOUR_IN_SECONDS, '12 hours' ],
				[ DAY_IN_SECONDS, '1 day' ],
				[ 2 * DAY_IN_SECONDS, '2 day' ],
				[ 5 * DAY_IN_SECONDS, '5 day' ],
				[ 10 * DAY_IN_SECONDS, '10 days' ],
				[ 20 * DAY_IN_SECONDS, '20 days' ],
				[ MONTH_IN_SECONDS, '1 month' ],
				[ 2 * MONTH_IN_SECONDS, '2 months' ],
			],
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'This specifies how long an IP address is locked out for when Titan brute force protection locks them out. Remember, the goal is to prevent a remote attack from having many opportunities to guess your website’s usernames and passwords. If you have reasonably strong passwords, then it will take thousands of guesses to guess your password correctly.

So if you have your failure count set to 20, your time period set to 5 minutes and you set this option to 5 minutes, then an attacker will only get 20 guesses every 5 minutes and then they have to wait 5 minutes while they’re locked out. So the effect is that they only get 20 guesses every 10 minutes or 2880 guesses per day, assuming they realize that they can restart their attack exactly 5 minutes after being locked out. If you feel this is not long enough, then you can increase the lock-out time to 60 minutes, which drastically reduces the number of daily attempts at guesses an attacker has.', 'anti-spam' ),
			'default' => 4 * HOUR_IN_SECONDS
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'brute_force_lock_invalid_users',
			'title'   => __( 'Immediately lock out invalid usernames', 'anti-spam' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'This option will immediately lock out someone who attempts to log in with an invalid username. Please note that your real users may mistype their usernames. This will cause them to get locked out, which is an inconvenience. We recommend enabling this feature for sites that have a low number of users such as 1-2 admins and/or a possibly a few editors. If a legitimate user is locked out you can find and delete any currently active block on the Firewall > Blocking page..', 'anti-spam' ),
			'default' => true
		];

		$options[] = [
			'type'    => 'textarea',
			'name'    => 'brute_force_user_black_list',
			'title'   => __( 'Immediately block the IP of users who try to sign in as these usernames', 'anti-spam' ),
			//'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'Hit enter to add a username', 'anti-spam' ),
			'default' => ''
		];

		$options[] = [
			'type'    => 'dropdown',
			'name'    => 'brute_force_breach_passwds',
			'way'     => 'buttons',
			'title'   => __( 'Prevent the use of passwords leaked in data breaches', 'clearfy' ),
			'data'    => [
				[ 'no', __( 'No', 'clearfy' ) ],
				[ 'for_admins_only', __( 'For admins only', 'clearfy' ) ],
				[
					'for_all_users',
					__( 'For all users with "publish posts" capability', 'clearfy' )
				]
			],
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'    => __( 'In some cases, you need to disable the floating top admin panel. You can disable this panel.', 'clearfy' ) . '<br><b>Clearfy</b>: ' . __( 'Disable admin top bar.', 'clearfy' ),
			'default' => 'no',
		];

		$advanced_options[] = [
			'type'     => 'separator',
			'cssClass' => 'wantispam-brute-force-breach-passwds-mode'
		];

		$advanced_options[] = [
			'type'      => 'checkbox',
			'way'       => 'buttons',
			'name'      => 'brute_force_strong_passwds',
			'title'     => __( 'Enforce strong passwords', 'anti-spam' ),
			'layout'    => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'      => __( 'You can use this to either “Force admins and publishers” or “force all members” to use strong passwords. We recommend you force admins and publishers to use strong passwords. When a user on your WordPress site changes their password, Titan will check the password against an algorithm to make sure it is strong enough to give you a good level of protection. If the password fails this check then it’s rejected and the user must enter a stronger password. Titan checks that the password is a minimum length, that it doesn’t match any known obvious passwords and it then uses a point system to allocate points based on things like whether it contains a number, if it has upper and lower case letters and so on. If the point score does not exceed a required level, then it will reject the password the user entered.', 'anti-spam' ),
			'default'   => true,
			'eventsOn'  => [
				'show' => '.factory-control-brute_force_breach_passwds_mode, .wantispam-brute-force-breach-passwds-mode'
			],
			'eventsOff' => [
				'hide' => '.factory-control-brute_force_breach_passwds_mode, .wantispam-brute-force-breach-passwds-mode'
			]
		];

		$advanced_options[] = [

			'type'  => 'dropdown',
			'name'  => 'brute_force_breach_passwds_mode',
			'title' => __( 'Prevent the use of passwords leaked in data breaches', 'clearfy' ),
			'data'  => [
				[
					'for_admins_and_publishers',
					__( 'Force admins and publishers to use strong passwords (recommended)', 'clearfy' )
				],
				[
					'for_all_users',
					__( 'Force all members to use strong passwords', 'clearfy' )
				]
			],

			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'    => __( 'In some cases, you need to disable the floating top admin panel. You can disable this panel.', 'clearfy' ) . '<br><b>Clearfy</b>: ' . __( 'Disable admin top bar.', 'clearfy' ),
			'default' => 'for_admins_and_publishers',

		];

		$advanced_options[] = [
			'type'     => 'separator',
			'cssClass' => 'wantispam-brute-force-breach-passwds-mode'
		];

		$advanced_options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'brute_force_mask_login_errors',
			'title'   => __( "Don't let WordPress reveal valid users in login errors", 'anti-spam' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'If you disable and remove the ‘admin’ account in WordPress and you have the option “Anyone can register” enabled in WordPress “General” settings next to “membership,” then it is possible for users to register an account with the username “admin,” which can cause confusion on your system and may allow those users to persuade other users of your system to disclose sensitive data. Enabling this feature prevents the above from happening. We recommend you enable this option. Prevent discovery of usernames through ‘/?author=N’ scans, the oEmbed API, and the WordPress REST API. On a WordPress system, it’s possible to discover valid usernames by visiting a specially crafted URL that looks like one of these: example.com/?author=2, example.com/wp-json/oembed/1.0/embed?url=http%3A%2F%2Fexample.com%2Fhello-world%2F, example.com/wp-json/wp/v2/users. Enabling this option prevents hackers from being able to discover usernames using these methods. This includes finding the author in the post data provided publicly by the oEmbed API and the WordPress REST API “users” URL that was introduced in WordPress 4.7. Note that some themes can leak usernames and we can’t prevent username discovery when a theme does this. We recommend that you keep this option enabled.', 'anti-spam' ),
			'default' => true
		];

		$advanced_options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'brute_force_block_admin_reg',
			'title'   => __( "Prevent users registering 'admin' username if it doesn't exist", 'anti-spam' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'By default, when you enter a valid username with an incorrect password, WordPress will tell you that you entered a good username but the password is wrong. If you enter a bad username and bad password, WordPress will tell you that the username does not exist. This is a serious security problem because it lets users easily find out which users exist on your WordPress site and target those for attacks. This option gives a generic message of: “The username or password you entered is incorrect.” thereby protecting your usernames and not revealing if the hacker guessed a valid user. It’s strongly recommended that you enable this feature.', 'anti-spam' ),
			'default' => true
		];

		$advanced_options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'brute_force_block_admin_reg',
			'title'   => __( "Prevent discovery of usernames through '/?author=N' scans, the oEmbed API, and the WordPress REST API", 'anti-spam' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'On a WordPress system, it’s possible to discover valid usernames by visiting a specially crafted URL that looks like one of these: example.com/?author=2, example.com/wp-json/oembed/1.0/embed?url=http%3A%2F%2Fexample.com%2Fhello-world%2F, example.com/wp-json/wp/v2/users. Enabling this option prevents hackers from being able to discover usernames using these methods. This includes finding the author in the post data provided publicly by the oEmbed API and the WordPress REST API “users” URL that was introduced in WordPress 4.7. Note that some themes can leak usernames and we can’t prevent username discovery when a theme does this. We recommend that you keep this option enabled.', 'anti-spam' ),
			'default' => true
		];

		$advanced_options[] = [
			'type'     => 'separator',
			'cssClass' => 'wantispam-brute-force-block-bad-post-separator'
		];

		$advanced_options[] = [
			'type'      => 'checkbox',
			'way'       => 'buttons',
			'name'      => 'brute_force_block_bad_post',
			'title'     => __( "Block IPs who send POST requests with blank User-Agent and Referer", 'anti-spam' ),
			'layout'    => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'      => __( 'Many badly written brute force hacking scripts send login attempts and comment spam attempts using a blank user agent (in other words, they don’t specify which browser they are) and blank referer headers (in other words, they don’t specify which URL they arrived from). Enabling this option will not only prevent requests like this from reaching your site, but it will also immediately block the IP address the request originated from. Note that both User-Agent and Referer must be missing from the request for this blocking rule to take effect. ', 'anti-spam' ),
			'default'   => true,
			'eventsOn'  => [
				'show' => '.factory-control-brute_force_block_custom_text, .wantispam-brute-force-block-bad-post-separator'
			],
			'eventsOff' => [
				'hide' => '.factory-control-brute_force_block_custom_text, .wantispam-brute-force-block-bad-post-separator'
			]
		];

		$advanced_options[] = [
			'type'    => 'textarea',
			'name'    => 'brute_force_block_custom_text',
			'title'   => __( 'Custom text shown on block pages ', 'anti-spam' ),
			//'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'HTML tags will be stripped prior to output and line breaks will be converted into the appropriate tags.', 'anti-spam' ),
			'default' => ''
		];

		$advanced_options[] = [
			'type'     => 'separator',
			'cssClass' => 'wantispam-brute-force-block-bad-post-separator'
		];

		$advanced_options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'brute_force_check_password_strength_on_update',
			'title'   => __( "Check password strength on profile update", 'anti-spam' ),
			'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'    => __( 'If you enable this option, it will not alert a user that they have a weak password, unlike the “force strong passwords” feature above. However, it will send the site admin an email alert telling that admin that a user has specified a weak password during a profile update. It simply lets you know who is using a weak password so that you can contact them and let them know that they may want to improve the password strength. If you do contact one of your users or customers, make sure that you are clear that you do not actually know what their password is. You are only alerted that the password does not meet your site’s password strength requirements. That way they know you have not violated any reasonable expectation of privacy that they may have.', 'anti-spam' ),
			'default' => true
		];

		$options[] = [
			'type'  => 'more-link',
			'id'    => 'wantispam-brute-force-advanced-options',
			'title' => __( 'Additional Options', 'anti-spam' ),
			'count' => 6,
			'items' => $advanced_options
		];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Advanced Firewall Options.', 'anti-spam' ) . '</strong>' . '<p>' . __( 'Additional modules to spam protect.', 'anti-spam' ) . '</p>' . '</div>'
		];

		$form_options = [];

		$form_options[] = [
			'type'  => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters( 'wantispam/settings_form/options', $form_options, $this );
	}

	/**
	 * Adds an html warning notification html markup.
	 */
	public function get_rules_control() {
		?>
        <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="control-group col-sm-8">
                <h4>Rules</h4>
                <table class="wf-striped-table">
                    <thead>
                    <tr>
                        <th style="width: 5%"></th>
                        <th>Category</th>
                        <th>Description</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr data-rule-id="1" data-original-value="1">
                        <td style="text-align: center">
                            <input type="checkbox" value="whitelist">
                        </td>
                        <td>whitelist</td>
                        <td>Whitelisted URL</td>
                    </tr>
                    <tr data-rule-id="2" data-original-value="1">
                        <td style="text-align: center">
                           <input type="checkbox" value="whitelist">
                        </td>
                        <td>lfi</td>
                        <td>Slider Revolution: Local File Inclusion</td>
                    </tr>
                    <tr data-rule-id="3" data-original-value="1">
                        <td style="text-align: center">
                           <input type="checkbox" value="whitelist">
                        </td>
                        <td>sqli</td>
                        <td>SQL Injection</td>
                    </tr>

                    </tbody>
                    <tfoot>
                    <tr id="waf-show-all-rules">
                        <td class="wf-center" colspan="4"><a href="#" id="waf-show-all-rules-button">SHOW ALL RULES</a></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
		<?php
	}
}

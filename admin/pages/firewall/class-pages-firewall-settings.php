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
class Firewall_Settings extends Base {

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
	public function __construct( $plugin ) {
		$this->menu_title                  = __( 'Settings', 'titan-security' );
		$this->page_menu_short_description = __( 'Firewall settings', 'titan-security' );

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}

	public $rules = array();

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

		$this->styles->add( WTITAN_PLUGIN_URL . '/admin/assets/css/firewall/firewall-settings.css' );
		$this->scripts->add( WTITAN_PLUGIN_URL . '/admin/assets/js/firewall/firewall-settings.js' );

		add_action( 'wbcr/factory/update_option', [ $this, 'before_save' ] );
	}

	/**
	 * Permalinks options.
	 *
	 * @return mixed[]
	 * @since 6.2
	 */
	public function getPageOptions() {
		$is_premium = \WBCR\Titan\Plugin::app()->premium->is_active() && \WBCR\Titan\Plugin::app()->premium->is_install_package();

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Base Options.', 'titan-security' ) . '</strong>' . '<p>' . __( 'Additional modules to spam protect.', 'titan-security' ) . '</p>' . '</div>'
		];
		$options[] = [
			'type'         => 'dropdown',
			'name'         => 'howget_ip',
			'way'          => 'buttons',
			'title'        => __( 'How does Titan get IPs ', 'titan-security' ),
			'data'         => [
				[
					'',
					__( 'Default', 'titan-security' ),
					__( 'Let Titan use the most secure method to get visitor IP addresses. Prevents spoofing and works with most sites. (Recommended)', 'titan-security' )
				],
				[
					'REMOTE_ADDR',
					__( 'REMOTE_ADDR', 'titan-security' ),
					__( 'Use PHP\'s built in REMOTE_ADDR and don\'t use anything else. Very secure if this is compatible with your site.', 'titan-security' )
				],
				[
					'HTTP_X_FORWARDED_FOR',
					__( 'HTTP_X_FORWARDED_FOR', 'titan-security' ),
					__( 'Use the X-Forwarded-For HTTP header. Only use if you have a front-end proxy or spoofing may result.', 'titan-security' )
				],
				[
					'HTTP_X_REAL_IP',
					__( 'HTTP_X_REAL_IP', 'titan-security' ),
					__( 'Use the X-Real-IP HTTP header. Only use if you have a front-end proxy or spoofing may result.', 'titan-security' )
				],
				[
					'HTTP_CF_CONNECTING_IP',
					__( 'HTTP_CF_CONNECTING_IP', 'titan-security' ),
					__( 'Use the Cloudflare "CF-Connecting-IP" HTTP header to get a visitor IP. Only use if you\'re using Cloudflare.', 'titan-security' )
				]
			],
			'layout'       => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'         => __( 'Titan needs to determine each visitor’s IP address to provide security functions on your website. The Titan default configuration works just fine for most websites, but it’s important that this configuration is correct. For example, if Titan is not receiving IP addresses correctly and thinks an external visitor originates from a private address, it will whitelist that visitor and bypass security protocols. You can read more about which addresses Titan considers private here.

The Titan scanner has an option to “Scan for misconfigured How does Titan get IPs”. This scan feature can help you detect if the wrong option has been selected for “How does Titan get IPs.”

Another way of determining if Titan is getting IPs correctly is to check the “IPs” section in the Titan Tools > Diagnostics.

Let Titan use the most secure method to get visitor IP addresses. Prevents spoofing and works with most sites.
This is the default mode of operation for Titan. Titan will try to get a valid IP address from PHP and if that doesn’t work, it will look at data that a firewall or reverse proxy sends in case your website uses this configuration.

This option provides a good balance between security and compatibility.

Use PHP’s built in REMOTE_ADDR and don’t use anything else. Very secure if this is compatible with your site.
If you know that you definitely don’t use a reverse proxy, cache, Cloudflare, CDN or anything else in front of your web server that “proxies” traffic to your website, and if you are sure that your website is just a standalone PHP web server, then using this option will work and is the most secure in a non-proxy or load balancer configuration.

You may also want to select this option for other reasons – for example to force Titan to use the $_SERVER[‘REMOTE_ADDR’] variable in PHP.

Use the X-Forwarded-For HTTP header. Only use if you have a front-end proxy or spoofing may result.
If you are using Nginx or another load balancer as a front-end-proxy or load balancer in front of your web server, and the front-end server sends IP addresses to the web server that runs WordPress using the HTTP X-Forwarded-For header, then you should enable this option.

Be careful about enabling this option if you do not have a front-end-proxy, load balancer, or CDN configuration, because it will then allow visitors to spoof their IP address and you will also miss many hits that should have been logged.

Use the X-Real-IP HTTP header. Only use if you have a front-end proxy or spoofing may result.
As with the X-Forwarded-For option above, only use this option if you are sure that you want Titan to retrieve the visitor IP address from the X-Real-IP HTTP header, and do not enable this if you don’t have a front-end proxy or load balancer that is sending visits to your real web server and adding the X-Real-IP header.

Use the Cloudflare “CF-Connecting-IP” HTTP header to get a visitor IP. Only use if you’re using Cloudflare.
Titan is fully compatible with CloudFlare, and in some configurations Cloudflare will send the real visitor IP address to your web server using the CF-Connecting-IP HTTP header. If the CloudFlare support personnel have advised you that this is the case, then enable this option on Titan to ensure that Titan is able to get your visitor IP address.

Note that Cloudflare has several configurations including their own web server module that takes care of detecting the visitor IP address, so be sure to work with their technical support staff and read their documentation to determine which configuration you’re using.

Multiple IPs detected
If your host requires using the X-Forwarded-For header, there may be multiple IP addresses detected. If your own IP address does not appear where it shows “Your IP with this setting,” you may need to add trusted proxies.

If you do not know whether your host uses more than one proxy address, contact your host or the reverse-proxy service that you use. If you know there is only one proxy address, it should be the last address in the “Detected IPs” field.

Once you know which proxies to trust, click the + Edit trusted proxies link below the detected IPs.
In the Trusted proxies field that appears, enter the IP addresses of the proxies. You can enter a single IP like 10.0.0.15. You can also enter a “CIDR” range like 10.0.0.0/24. Note that your host’s trusted IPs should not be the same addresses in these examples.
Click Save Options to save the changes, and check that your IP appears correctly in the “Your IP with this setting” field.', 'titan-security' ) . '<br><b>Clearfy</b>: ' . __( 'Disable admin top bar.', 'titan-security' ),
			'default'      => '',
			'filter_value' => [ $this, 'filter_howget_ip_option' ],
			'cssClass'     => ! $is_premium ? [ 'factory-control--disabled factory-control-premium-label' ] : [],
		];

		$options[] = [
			'type'     => 'textarea',
			'name'     => 'howget_ips_trusted_proxies',
			'title'    => __( 'Trusted Proxies', 'titan-security' ),
			//'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'     => __( 'These IPs (or CIDR ranges) will be ignored when determining the requesting IP via the X-Forwarded-For HTTP header. Enter one IP or CIDR range per line.', 'titan-security' ),
			'default'  => '',
			//'filter_value' => [$this, 'filter_howget_ips_trusted_proxies_option'],
			'cssClass' => ! $is_premium ? [ 'factory-control--disabled factory-control-premium-label' ] : [],

		];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Advanced Firewall Options.', 'titan-security' ) . '</strong>' . '<p>' . __( 'Additional modules to spam protect.', 'titan-security' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'      => 'checkbox',
			'way'       => 'buttons',
			'name'      => 'disable_wafip_blocking',
			'title'     => __( 'Delay IP and Country blocking until after WordPress and plugins have loaded (only process firewall rules early)', 'titan-security' ),
			'layout'    => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'      => __( 'When the Titan Firewall is optimized, the Firewall loads before the WordPress environment loads. This is desired behavior, as it increases security and gives the Firewall a performance boost. But if your server has a conflict with blocking by IP, country, or other advanced blocking settings before WordPress has loaded, you can turn on this option to allow WordPress to load first. We do not recommend enabling this option except for testing purposes.', 'titan-security' ),
			'default'   => true,
			'eventsOn'  => [
				'show' => '.factory-control-whitelisted, .wtitan-disable-wafip-blocking-separator'
			],
			'eventsOff' => [
				'hide' => '.factory-control-whitelisted, .wtitan-disable-wafip-blocking-separator'
			],
			'cssClass'  => ! $is_premium ? [ 'factory-control--disabled factory-control-premium-label' ] : [],
		];

		$options[] = [
			'type'         => 'textarea',
			'name'         => 'whitelisted',
			'title'        => __( 'Whitelisted IP addresses that bypass all rules', 'titan-security' ),
			//'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'         => __( 'Whitelisted IPs must be separated by commas or placed on separate lines. You can specify ranges using the following formats: 127.0.0.1/24, 127.0.0.[1-100], or 127.0.0.1-127.0.1.100. Titan automatically whitelists private networks because these are not routable on the public Internet.', 'titan-security' ),
			'default'      => '',
			'filter_value' => [ $this, 'filter_whitelisted_option' ],
			'cssClass'     => ! $is_premium ? [ 'factory-control--disabled factory-control-premium-label' ] : [],
		];

		$options[] = [
			'type'     => 'separator',
			'cssClass' => 'wtitan-disable-wafip-blocking-separator'
		];

		$options[] = [
			'type'     => 'list',
			'way'      => 'checklist',
			'name'     => 'whitelisted_services',
			'title'    => __( 'Whitelisted services', 'comments-plus' ),
			'data'     => [
				[ 'sucuri', 'Sucuri' ],
				[ 'facebook', 'Facebook' ],
				[ 'uptime_robot', 'Uptime Robot' ],
				[ 'status_cake', 'StatusCake' ],
				[ 'managewp', 'ManageWP' ],
				[ 'seznam', 'Seznam Search Engine' ],
			],
			'layout'   => [ 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ],
			'hint'     => __( 'Select the post types for which comments will be disabled', 'comments-plus' ),
			//'filter_value' => [$this, 'filter_whitelisted_services_option'],
			'default'  => 'sucuri,facebook,uptime_robots',
			'cssClass' => ! $is_premium ? [ 'factory-control--disabled factory-control-premium-label' ] : [],
		];

		$options[] = [
			'type'     => 'textarea',
			'name'     => 'banned_urls',
			'title'    => __( 'Immediately block IPs that access these URLs', 'titan-security' ),
			//'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'     => __( 'Separate multiple URLs with commas or place them on separate lines. Asterisks are wildcards, but use with care. If you see an attacker repeatedly probing your site for a known vulnerability you can use this to immediately block them. All URLs must start with a "/" without quotes and must be relative. e.g. /badURLone/, /bannedPage.html, /dont-access/this/URL/, /starts/with-*', 'titan-security' ),
			'default'  => '',
			'cssClass' => ! $is_premium ? [ 'factory-control--disabled factory-control-premium-label' ] : [],
		];

		$options[] = [
			'type'     => 'textarea',
			'name'     => 'waf_alert_whitelist',
			'title'    => __( 'Ignored IP addresses for Titan Web Application Firewall alerting', 'titan-security' ),
			//'layout'  => [ 'hint-type' => 'icon', 'hint-icon-color' => 'green' ],
			'hint'     => __( 'Ignored IPs must be separated by commas or placed on separate lines. These addresses will be ignored from any alerts about increased attacks and can be used to ignore things like standalone website security scanners.', 'titan-security' ),
			'default'  => '',
			'cssClass' => ! $is_premium ? [ 'factory-control--disabled factory-control-premium-label' ] : [],
		];

		$options[] = [
			'type' => 'html',
			'html' => [ $this, 'get_rules_control' ]
		];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Whitelisted URLs.', 'titan-security' ) . '</strong>' . '<p>' . __( 'Additional modules to spam protect.', 'titan-security' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type' => 'html',
			'html' => [ $this, 'get_whitelisted_urls_section' ]
		];

		$options[] = [
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __( 'Rate Limiting.', 'titan-security' ) . '</strong>' . '<p>' . __( 'Additional modules to spam protect.', 'titan-security' ) . '</p>' . '</div>'
		];

		$options[] = [
			'type'     => 'checkbox',
			'way'      => 'buttons',
			'name'     => 'enable_advanced_blocking',
			'title'    => __( "Enable Rate Limiting and Advanced Blocking", 'titan-security' ),
			//'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'green'],
			'hint'     => __( 'NOTE: This checkbox enables ALL blocking/throttling functions including IP, country and advanced blocking, and the "Rate Limiting Rules" below.', 'titan-security' ),
			'default'  => true,
			'cssClass' => ! $is_premium ? [ 'factory-control--disabled factory-control-premium-label' ] : [],
		];

		$options[] = [
			'type' => 'separator',
			//'cssClass' => 'wtitan-brute-force-block-bad-post-separator'
		];
		$options[] = [
			'type'     => 'checkbox',
			'way'      => 'buttons',
			'name'     => 'immediately_block_fake_google',
			'title'    => __( "Enable Rate Limiting and Advanced Blocking", 'titan-security' ),
			'layout'   => [ 'hint-type' => 'icon', 'hint-icon-color' => 'red' ],
			'hint'     => __( 'If you are having a problem with people stealing your content and pretending to be Google as they crawl your site, then you can enable this option which will immediately block anyone pretending to be Google. The way this option works is that we look at the visitor User-Agent HTTP header which indicates which browser the visitor is running. If it appears to be Googlebot, then we do a reverse lookup on the visitor’s IP address to verify that the IP does belong to Google. If the IP is not a Google IP, then we block it if you have this option enabled. Be careful about using this option, because we have had reports of it blocking real site visitors, especially (for some reason) legitimate visitors from Brazil. It’s possible, although we haven’t confirmed this, that some Internet service providers in Brazil use transparent proxies that  modify their customers’ user-agent headers to pretend to be Googlebot rather than the real header. Or it may be possible that these providers are engaging in some sort of crawling activity pretending to be Googlebot using the same IP address that is the public IP for their customers. Whatever the cause is, the result is that if you enable this you may block some real visitors.', 'titan-security' ),
			'default'  => false,
			'cssClass' => ! $is_premium ? [ 'factory-control--disabled factory-control-premium-label' ] : [],
		];

		$options[] = [
			'type' => 'html',
			'html' => [ $this, 'get_rate_limit_section' ]
		];

		$form_options = [];

		$form_options[] = [
			'type'  => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters( 'wtitan/settings_form/options', $form_options, $this );
	}


	/**
	 * Adds an html warning notification html markup.
	 */
	public function get_rules_control() {
		?>
        <div class="form-group wtitan-section-disabled">
            <label class="col-sm-4 control-label"></label>
            <div class="control-group col-sm-8">
                <strong>Rules</strong>
                <div class="wtitan-excluded-rules">
                    <table>
                        <thead>
                        <tr>
                            <th style="width: 10%"></th>
                            <th style="width: 30%; text-align:left;">Category</th>
                            <th style="text-align: left;">Description</th>
                        </tr>
                        </thead>
                        <tbody>

                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" class="js-wtitan-excluded-rules__checkbox" value="119">
                            </td>
                            <td>rce</td>
                            <td>Duplicator Installer wp-config.php Overwrite</td>
                        </tr>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" class="js-wtitan-excluded-rules__checkbox" value="18">
                            </td>
                            <td>priv-esc</td>
                            <td>User Roles Manager Privilege Escalation &lt;= 4.24</td>
                        </tr>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" class="js-wtitan-excluded-rules__checkbox" value="66">
                            </td>
                            <td>dos</td>
                            <td>WordPress Core &lt;= 4.5.3 - DoS</td>
                        </tr>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" class="js-wtitan-excluded-rules__checkbox" value="117">
                            </td>
                            <td>privesc</td>
                            <td>WordPress Core: Arbitrary File Deletion</td>
                        </tr>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" class="js-wtitan-excluded-rules__checkbox" value="126"
                                       checked="checked">
                            </td>
                            <td>privesc</td>
                            <td>WordPress &lt;= 5.0 - PHP Object Injection via Meta Data &amp; Authenticated File
                                Delete
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" class="js-wtitan-excluded-rules__checkbox" value="1"
                                       checked="checked">
                            </td>
                            <td>whitelist</td>
                            <td>Whitelisted URL</td>
                        </tr>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" class="js-wtitan-excluded-rules__checkbox" value="2"
                                       checked="checked">
                            </td>
                            <td>lfi</td>
                            <td>Slider Revolution: Local File Inclusion</td>
                        </tr>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" class="js-wtitan-excluded-rules__checkbox" value="60"
                                       checked="checked">
                            </td>
                            <td>file_upload</td>
                            <td>Slider Revolution: Arbitrary File Upload</td>
                        </tr>
                        </tbody>
                    </table>
                    <input type="hidden" id="js-wtitan-excluded-rules__field" name="titan_disabled_rules" value="">
                </div>
            </div>
        </div>
		<?php
	}

	public function get_whitelisted_urls_section() {


		?>
        <div class="wtitan-whitelist">
            <ul>
                <li class="wtitan-whitelist__top-section">
                    <ul>
                        <li>
                            <strong class="wtitan-whitelist__label">Add Whitelisted URL/Param</strong>
                            <span class="wtitan-whitelist__hint">
							The URL/parameters in this table will not be tested by the firewall. They are typically
							added while the firewall is in Learning Mode or by an admin who identifies a particular
							action/request is a false positive.</span>
                        </li>
                        <li>
                            <div>
                                <div class="wtitan-whitelist__form-group">
                                    <input type="text" name="whitelistURL" id="whitelistURL" placeholder="URL" disabled>
                                </div>
                                <div class="wtitan-whitelist__form-group">
                                    <select style="width:200px;" name="whitelistParam" id="whitelistParam" tabindex="-1"
                                            aria-hidden="true" disabled>
                                        <option value="request.body">POST Body</option>
                                        <option value="request.cookies">Cookie</option>
                                        <option value="request.fileNames">File Name</option>
                                        <option value="request.headers">Header</option>
                                        <option value="request.queryString">Query String</option>
                                    </select>
                                </div>
                                <div class="wtitan-whitelist__form-group">
                                    <input style="display:inline-block;" type="text" name="whitelistParamName"
                                           id="whitelistParamName" placeholder="Param Name" disabled>
                                </div>
                                <a href="#" class="btn btn-default btn-small disabled"
                                   id="waf-whitelisted-urls-add">Add</a>
                            </div>
                        </li>
                        <li>
                            <hr>
                        </li>
                        <li class="wtitan-whitelist__table-controls">
                            <div class="wtitan-whitelist__table-controls-left">
                                <a href="#" id="whitelist-bulk-delete"
                                   class="btn btn-default btn-small disabled">Delete</a>&nbsp;&nbsp;<a href="#"
                                                                                                       id="whitelist-bulk-enable"
                                                                                                       class="disabled btn btn-default btn-small">Enable</a>&nbsp;&nbsp;<a
                                        href="#" id="whitelist-bulk-disable" class="btn btn-default btn-small disabled">Disable</a>
                            </div>
                            <div class="wtitan-whitelist__table-controls-right">
                                <select name="filterColumn" disabled>
                                    <option value="url">URL</option>
                                    <option value="param">Param</option>
                                    <option value="source">Source</option>
                                    <option value="user">User</option>
                                    <option value="ip">IP</option>
                                </select>&nbsp;
                                <input type="text" placeholder="Filter Value" name="filterValue" disabled>
                                <a href="#" id="whitelist-apply-filter" class="btn btn-default btn-small disabled">Filter</a>
                            </div>
                        </li>
                        <li>
                            <table class="wtitan-whitelist__table">
                                <thead>
                                <tr>
                                    <th style="width: 2%;text-align: center">
                                        <input type="checkbox" disabled>
                                    </th>
                                    <th style="width: 5%;">Enabled</th>
                                    <th>URL</th>
                                    <th>Param</th>
                                    <th>Created</th>
                                    <th>Source</th>
                                    <th>User</th>
                                    <th>IP</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </li>
                    </ul>
                </li>
                <li class="wtitan-whitelist__bottom-section">
                    <ul>
                        <li class="wtitan-whitelist__label">
                            <strong>Monitor background requests from an administrator's
                                web browser for false positives</strong>
                        </li>
                        <li class="wtitan-whitelist__bg-requests-controls">
                            <ul>
                                <li><label><input type="checkbox" disabled> Front-end Website</label></li>
                                <li><label><input type="checkbox" disabled> Admin Panel</label></li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
		<?php
	}

	public function get_rate_limit_section() {
		$max_global_requests_options = [
			'1'    => '1 per minute',
			'2'    => '2 per minute',
			'3'    => '3 per minute',
			'4'    => '4 per minute',
			'5'    => '5 per minute',
			'10'   => '15 per minute',
			'15'   => '15 per minute',
			'30'   => '30 per minute',
			'60'   => '60 per minute',
			'120'  => '120 per minute',
			'240'  => '240 per minute',
			'480'  => '960 per minute',
			'960'  => '960 per minute',
			'1920' => '1920 per minute',
		];
		?>
        <div class="wtitan-rate-limit-settings" style="padding:0 40px 0 0;">
            <ul>
                <li>
                    <ul class="wtitan-rate-limit-settings__control">
                        <li class="wtitan-rate-limit-settings__control-title"><span>How should we treat Google's crawlers</span>
                        </li>
                        <li>
                            <select style="width:400px;font-weight:normal;" name="titan_never_block_bg" disabled>
                                <option value="never_block_verified">
                                    Verified Google crawlers have unlimited access to this site
                                </option>
                                <option value="never_block_ua">
                                    Anyone claiming to
                                    be Google has unlimited access
                                </option>
                                <option value="treat_as_other_crawlers">
                                    Treat
                                    Google like any other Crawler
                                </option>
                            </select>
                        </li>
                    </ul>
                </li>
                <li>
                    <ul class="wtitan-rate-limit-settings__control">
                        <li class="wtitan-rate-limit-settings__control-title">If anyone's requests exceed</li>
                        <li class="wtitan-rate-limit-settings__control-fields">
                            <select name="titan_max_global_requests"
                                    class="wtitan-rate-limit-settings__control-block-time-select" disabled>
                                <option value="disabled">
                                    Unlimited
                                </option>
								<?php foreach ( $max_global_requests_options as $value => $title ): ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo $title; ?></option>
								<?php endforeach; ?>
                            </select>&nbsp;then
                            <select name="titan_max_global_requests_action"
                                    class="wtitan-rate-limit-settings__control-action-select" disabled>
                                <option value="throttle">
                                    throttle it
                                </option>
                                <option value="block">block it
                                </option>
                            </select>
                        </li>
                    </ul>

                </li>
                <li>
                    <ul class="wtitan-rate-limit-settings__control">
                        <li class="wtitan-rate-limit-settings__control-title">If a crawler's page views exceed</li>
                        <li class="wtitan-rate-limit-settings__control-fields">
                            <select name="titan_max_requests_crawlers"
                                    class="wtitan-rate-limit-settings__control-block-time-select" disabled>
                                <option value="disabled">
                                    Unlimited
                                </option>
								<?php foreach ( $max_global_requests_options as $value => $title ): ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo $title; ?></option>
								<?php endforeach; ?>
                            </select>&nbsp;then
                            <select name="titan_max_requests_crawlers_action"
                                    class="wtitan-rate-limit-settings__control-action-select" disabled>
                                <option value="throttle">
                                    throttle it
                                </option>
                                <option value="block">block
                                    it
                                </option>
                            </select>
                        </li>
                    </ul>
                </li>
                <li>
                    <ul class="wtitan-rate-limit-settings__control">
                        <li class="wtitan-rate-limit-settings__control-title">If a crawler's pages not found (404s)
                            exceed
                        </li>
                        <li class="wtitan-rate-limit-settings__control-fields">
                            <select name="titan_max404_crawlers"
                                    class="wtitan-rate-limit-settings__control-block-time-select" disabled>
                                <option value="disabled">
                                    Unlimited
                                </option>
								<?php foreach ( $max_global_requests_options as $value => $title ): ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo $title; ?></option>
								<?php endforeach; ?>
                            </select>&nbsp;then
                            <select name="titan_max404_crawlers_action"
                                    class="wtitan-rate-limit-settings__control-action-select" disabled>
                                <option value="throttle">
                                    throttle it
                                </option>
                                <option value="block">block it
                                </option>
                            </select>
                        </li>
                    </ul>
                </li>
                <li>
                    <ul class="wtitan-rate-limit-settings__control">
                        <li class="wtitan-rate-limit-settings__control-title">If a human's page views exceed</li>
                        <li class="wtitan-rate-limit-settings__control-fields">
                            <select name="titan_max_requests_humans"
                                    class="wtitan-rate-limit-settings__control-block-time-select" disabled>
                                <option value="disabled">
                                    Unlimited
                                </option>
								<?php foreach ( $max_global_requests_options as $value => $title ): ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo $title; ?></option>
								<?php endforeach; ?>
                            </select>&nbsp;then
                            <select name="titan_max_requests_humans_action"
                                    class="wtitan-rate-limit-settings__control-action-select" disabled>
                                <option value="throttle">
                                    throttle it
                                </option>
                                <option value="block">block it
                                </option>
                            </select>
                        </li>
                    </ul>
                </li>
                <li>
                    <ul class="wtitan-rate-limit-settings__control">
                        <li class="wtitan-rate-limit-settings__control-title">If a human's pages not found (404s)
                            exceed
                        </li>
                        <li class="wtitan-rate-limit-settings__control-fields">
                            <select name="titan_max404_humans"
                                    class="wtitan-rate-limit-settings__control-block-time-select" disabled>
                                <option value="disabled">
                                    Unlimited
                                </option>
								<?php foreach ( $max_global_requests_options as $value => $title ): ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo $title; ?></option>
								<?php endforeach; ?>
                            </select>&nbsp;then
                            <select name="titan_max404_humans_action"
                                    class="wtitan-rate-limit-settings__control-action-select" disabled>
                                <option value="throttle">
                                    throttle it
                                </option>
                                <option value="block">block it</option>
                            </select>
                        </li>
                    </ul>
                </li>
                <li>
                    <ul class="wtitan-rate-limit-settings__control">
                        <li class="wtitan-rate-limit-settings__control-title">How long is an IP address blocked when it
                            breaks a rule</span></li>
                        <li class="wtitan-rate-limit-settings__control-fields">
                            <select name="titan_blocked_time" disabled>
                                <option value="60">1 minute</option>
                                <option value="300">5 minutes</option>
                                <option value="1800">30 minutes</option>
                                <option value="3600">1 hour</option>
                                <option value="7200">2 hours</option>
                                <option value="21600">6 hours</option>
                                <option value="43200">12 hours</option>
                                <option value="86400">1 day</option>
                                <option value="172800">2 days</option>
                                <option value="432000">5 days</option>
                                <option value="864000">10 days</option>
                                <option value="2592000">1 month</option>
                            </select>
                        </li>
                    </ul>
                </li>
                <li>
                    <ul class="wtitan-rate-limit-settings__control">
                        <li class="wtitan-rate-limit-settings__control-title">Whitelisted 404 URLs
                            <span class="wtitan-rate-limit-settings__control-subtitle">These URL patterns will be excluded from the
							throttling rules used to limit crawlers.</span></li>

                        <li class="wtitan-rate-limit-settings__control-fields">
                            <textarea name="titan_allowed404s"
                                      class="wtitan-rate-limit-settings__control-allowed404s-textarea"
                                      disabled></textarea>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>
		<?php
	}


}

<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
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
class Tweaks extends Base {

	/**
	 * {@inheritDoc}
	 *
	 * @since  1.0
	 * @var string
	 */
	public $id = "tweaks";

	/**
	 * {@inheritDoc}
	 *
	 * @since  1.0
	 * @var string
	 */
	public $page_menu_dashicon = 'dashicons-admin-tools';

	/**
	 * {@inheritDoc}
	 *
	 * @since  1.0
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
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->menu_title = __('Tweaks', 'titan-security');
		$this->page_menu_short_description = __('Security tweaks', 'titan-security');

		parent::__construct($plugin);

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
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		if( $this->plugin->is_premium() ) {
			$this->scripts->request([
				'control.checkbox',
				'control.dropdown',
				'bootstrap.tooltip',
				'holder.more-link'
			], 'bootstrap');

			$this->styles->request([
				'bootstrap.core',
				'bootstrap.form-group',
				'holder.more-link',
				'bootstrap.separator',
				'control.dropdown',
				'control.checkbox'
			], 'bootstrap');

			add_action('wbcr/factory/update_option', [$this, 'before_save']);
		}
	}

	/**
	 * Permalinks options.
	 *
	 * @return mixed[]
	 * @since 6.2
	 */
	public function getPageOptions()
	{


		$options[] = array(
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . __('<strong>Base settings</strong>.', 'titan-security') . '<p>' . __('Basic recommended security settings.', 'titan-security') . '</p></div>'
		);

		$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'strong_password',
			'title' => __('Strong Password Requirement', 'titan-security'),
			'layout' => array('hint-type' => 'icon'),
			'hint' => __('Force users to use strong passwords as rated by the WordPress password meter.', 'titan-security') . '<br><b>Titan: </b>' . __('Sets the redirect to exclude the possibility of obtaining a login.', 'titan-security'),
			'default' => false,
			'eventsOn' => [
				'show' => '.factory-control-strong_password_min_role'
			],
			'eventsOff' => [
				'hide' => '.factory-control-strong_password_min_role'
			]
		);

		$options[] = [
			'type' => 'dropdown',
			'name' => 'strong_password_min_role',
			'title' => __('Strong Password Minimum Role', 'titan-security'),
			'data' => [
				['administrator', 'Administrator'],
				['editor', 'Editor'],
				['author', 'Author'],
				['contributor', 'Contributor'],
				['subscriber', 'Subscriber'],
			],
			'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'green'],
			'hint' => __('Minimum role at which a user must choose a strong password. For more information on WordPress roles and capabilities please see http://codex.wordpress.org/Roles_and_Capabilities. Warning: If your site invites public registrations setting the role too low may annoy your members.', 'titan-security'),
			'default' => 'administrator'
		];

		$options[] = array(
			'type' => 'separator'
		);

		$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'protect_author_get',
			'title' => __('Hide author login', 'titan-security'),
			'layout' => array('hint-type' => 'icon'),
			'hint' => __('An attacker can find out the author\'s login, using a similar request to get your site. mysite.com/?author=1', 'titan-security') . '<br><b>Titan: </b>' . __('Sets the redirect to exclude the possibility of obtaining a login.', 'titan-security'),
			'default' => false
		);

		$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'change_login_errors',
			'title' => __('Hide errors when logging into the site', 'titan-security'),
			'layout' => array('hint-type' => 'icon'),
			'hint' => __('WP by default shows whether you entered a wrong login or incorrect password, which allows attackers to understand if there is a certain user on the site, and then start searching through the passwords.', 'titan-security') . '<br><b>Titan: </b>' . __('Changes in the text of the error so that attackers could not find the login.', 'titan-security'),
			'default' => false
		);

		$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'remove_x_pingback',
			'title' => __('Disable XML-RPC', 'titan-security'),
			'layout' => array('hint-type' => 'icon', 'hint-icon-color' => 'grey'),
			'hint' => __('A pingback is basically an automated comment that gets created when another blog links to you. A self-pingback is created when you link to an article within your own blog. Pingbacks are essentially nothing more than spam and simply waste resources.', 'titan-security') . '<br><b>Titan: </b>' . __('Removes the server responses a reference to the xmlrpc file.', 'titan-security'),
			'default' => false,
			'eventsOn' => array(
				'show' => '#wbcr-clearfy-xml-rpc-danger-message'
			),
			'eventsOff' => array(
				'hide' => '#wbcr-clearfy-xml-rpc-danger-message'
			)
		);

		$options[] = array(
			'type' => 'html',
			'html' => array($this, 'xmlRpcDangerMessage')
		);

		//block_xml_rpc
		//disable_xml_rpc_auth
		//remove_xml_rpc_tag

		$options[] = array(
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header">' . __('<strong>Hide WordPress versions</strong>', 'titan-security') . '<p>' . __('WordPress itself and many plugins shows their version at the public areas of your site. An attacker received this information may be aware of the vulnerabilities found in the version of the WordPress core or plugins.', 'titan-security') . '</p></div>'
		);

		$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'remove_html_comments',
			'title' => __('Remove html comments', 'titan-security'),
			'layout' => array('hint-type' => 'icon', 'hint-icon-color' => 'grey'),
			'hint' => __('This function will remove all html comments in the source code, except for special and hidden comments. This is necessary to hide the version of installed plugins.', 'titan-security') . '<br><br><b>Titan: </b>' . __('Remove html comments in source code.', 'titan-security'),
			'default' => false
		);

		$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'remove_meta_generator',
			'title' => __('Remove meta generator', 'titan-security') . ' <span class="wbcr-clearfy-recomended-text">(' . __('Recommended', 'titan-security') . ')</span>',
			'layout' => array('hint-type' => 'icon'),
			'hint' => __('Allows attacker to learn the version of WP installed on the site. This meta tag has no useful function.', 'titan-security') . '<br><b>Titan: </b>' . sprintf(__('Removes the meta tag from the %s section', 'titan-security'), '&lt;head&gt;'),
			'default' => false
		);

		/*	$options[] = [
				'type' => 'html',
				'html' => '<div class="wbcr-factory-page-group-header">' . '<strong>' . __('Remove query strings from static resources', 'titan-security') . '</strong>' . '<p>' . __('This funcitons will remove query strings from static resources like CSS & JS files inside the HTML <head> element to improve your speed scores in services like Pingdom, GTmetrix, PageSpeed and YSlow. <b style="color:#ff5722">Important:</b> This does not work for authorized users. To avoid problems after plugins update!', 'titan-security') . '</p>' . '</div>'
			];*/

		$options[] = [
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'remove_js_version',
			'title' => __('Remove Version from Script', 'titan-security') . ' <span class="wbcr-clearfy-recomended-text">(' . __('Recommended', 'titan-security') . ')</span>',
			'layout' => ['hint-type' => 'icon'],
			'hint' => __('To make it more difficult for others to hack your website you can remove the WordPress version number from your site, your css and js. Without that number it\'s not possible to see if you run not the current version to exploit bugs from the older versions. <br><br>
					Additionally it can improve the loading speed of your site, because without query strings in the URL the css and js files can be cached.', 'titan-security') . '<br><br><b>Titan: </b>' . __('Removes wordpress version number from scripts (not logged in user only).', 'titan-security'),
			'default' => false
		];

		$options[] = [
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'remove_style_version',
			'title' => __('Remove Version from Stylesheet', 'titan-security') . ' <span class="wbcr-clearfy-recomended-text">(' . __('Recommended', 'titan-security') . ')</span>',
			'layout' => ['hint-type' => 'icon'],
			'hint' => __('To make it more difficult for others to hack your website you can remove the WordPress version number from your site, your css and js. Without that number it\'s not possible to see if you run not the current version to exploit bugs from the older versions. <br><br>
					Additionally it can improve the loading speed of your site, because without query strings in the URL the css and js files can be cached.', 'titan-security') . '<br><br><b>Titan: </b>' . __('Removes the wordpress version number from stylesheets (not logged in user only).', 'titan-security'),
			'default' => false
			/*'eventsOn' => array(
				'show' => '.factory-control-disable_remove_style_version_for_auth_users'
			),
			'eventsOff' => array(
				'hide' => '.factory-control-disable_remove_style_version_for_auth_users'
			)*/
		];

		/*$options[] = array(
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'disable_remove_style_version_for_auth_users',
			'title'   => __( 'Disable remove versions for auth users', 'titan-security' ) . ' <span class="wbcr-clearfy-recomended-text">(' . __( 'Recommended', 'titan-security' ) . ')</span>',
			'layout'  => array( 'hint-type' => 'icon' ),
			'default' => false
		);*/

		$options[] = [
			'type' => 'textarea',
			'name' => 'remove_version_exclude',
			'height' => '120',
			'title' => __('Exclude stylesheet/script file names', 'titan-security'),
			'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'grey'],
			'hint' => __('Enter Stylesheet/Script file names to exclude from version removal (each exclude file starts with a new line)', 'titan-security') . '<br><br><b>' . __('Example', 'titan-security') . ':</b>' . ' http://testwp.dev/wp-includes/js/jquery/jquery.js',
		];

		$form_options = [];

		$form_options[] = [
			'type' => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return apply_filters('wtitan/tweaks_form/options', $form_options, $this);
	}

	/**
	 * Adds an html warning notification html markup.
	 */
	public function xmlRpcDangerMessage()
	{
		?>
		<div class="form-group">
			<label class="col-sm-4 control-label"></label>
			<div class="control-group col-sm-8">
				<div id="wbcr-clearfy-xml-rpc-danger-message" class="wbcr-clearfy-danger-message">
					<?php _e('<b>Use this option carefully!</b><br> Plugins like jetpack may have problems using this option.', 'titan-security') ?>
				</div>
			</div>
		</div>
		<?php
	}
}

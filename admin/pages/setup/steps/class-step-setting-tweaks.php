<?php

namespace WBCR\Titan\Page;

/**
 * Step
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Setting_Tweaks extends \WBCR\Factory_Templates_000\Pages\Step_Form {

	protected $prev_id = 'step3';
	protected $id = 'step4';
	protected $next_id = 'step5';

	public function get_title()
	{
		return __("Setting Tweaks", "titan-security");
	}

	public function get_form_description()
	{
		return __('Tweaks are minor security fixes to your site. Enabling tweaks can prevent your site from being hacked.', 'titan-security');
	}

	public function get_form_options()
	{
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
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'protect_author_get',
			'title' => __('Hide author login', 'titan-security'),
			'layout' => array('hint-type' => 'icon'),
			'hint' => __('An attacker can find out the author\'s login, using a similar request to get your site. mysite.com/?author=1', 'titan-security') . '<br><b>Titan: </b>' . __('Sets the redirect to exclude the possibility of obtaining a login.', 'titan-security'),
			'default' => true
		);

		$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'change_login_errors',
			'title' => __('Hide errors when logging into the site', 'titan-security'),
			'layout' => array('hint-type' => 'icon'),
			'hint' => __('WP by default shows whether you entered a wrong login or incorrect password, which allows attackers to understand if there is a certain user on the site, and then start searching through the passwords.', 'titan-security') . '<br><b>Titan: </b>' . __('Changes in the text of the error so that attackers could not find the login.', 'titan-security'),
			'default' => true
		);

		$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'remove_meta_generator',
			'title' => __('Remove meta generator', 'titan-security'),
			'layout' => array('hint-type' => 'icon'),
			'hint' => __('Allows attacker to learn the version of WP installed on the site. This meta tag has no useful function.', 'titan-security') . '<br><b>Titan: </b>' . sprintf(__('Removes the meta tag from the %s section', 'titan-security'), '&lt;head&gt;'),
			'default' => true
		);

		$options[] = [
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'remove_js_version',
			'title' => __('Remove Version from Script', 'titan-security'),
			'layout' => ['hint-type' => 'icon'],
			'hint' => __('To make it more difficult for others to hack your website you can remove the WordPress version number from your site, your css and js. Without that number it\'s not possible to see if you run not the current version to exploit bugs from the older versions. <br><br>
					Additionally it can improve the loading speed of your site, because without query strings in the URL the css and js files can be cached.', 'titan-security') . '<br><br><b>Titan: </b>' . __('Removes wordpress version number from scripts (not logged in user only).', 'titan-security'),
			'default' => true
		];

		$options[] = [
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'remove_style_version',
			'title' => __('Remove Version from Stylesheet', 'titan-security'),
			'layout' => ['hint-type' => 'icon'],
			'hint' => __('To make it more difficult for others to hack your website you can remove the WordPress version number from your site, your css and js. Without that number it\'s not possible to see if you run not the current version to exploit bugs from the older versions. <br><br>
					Additionally it can improve the loading speed of your site, because without query strings in the URL the css and js files can be cached.', 'titan-security') . '<br><br><b>Titan: </b>' . __('Removes the wordpress version number from stylesheets (not logged in user only).', 'titan-security'),
			'default' => true
			/*'eventsOn' => array(
				'show' => '.factory-control-disable_remove_style_version_for_auth_users'
			),
			'eventsOff' => array(
				'hide' => '.factory-control-disable_remove_style_version_for_auth_users'
			)*/
		];

		return $options;
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
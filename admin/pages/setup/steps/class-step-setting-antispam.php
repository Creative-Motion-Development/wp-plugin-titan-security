<?php

namespace WBCR\Titan\Page;

/**
 * Step
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Setting_Antispam extends \WBCR\FactoryClearfy000\Pages\Step_Form {

	protected $prev_id = 'step4';
	protected $id = 'step5';
	protected $next_id = 'step6';

	public function get_title()
	{
		return __("Setting Antispam", "titan-security");
	}

	public function get_form_description()
	{
		return 'Recommended settings that can complement your SEO plugin.';
	}

	public function get_form_options()
	{
		$options[] = [
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'antispam_mode',
			'title' => __('Anti-spam mode', 'titan-security'),
			'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'green'],
			'hint' => __('Enable or disable anti-spam for all site.', 'titan-security'),
			'default' => true,
		];

		$options[] = [
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'save_spam_comments',
			'title' => __('Save spam comments', 'titan-security'),
			'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'green'],
			'hint' => __('Save spam comments into spam section. Useful for testing how the plugin works.', 'titan-security'),
			'default' => true
		];

		return $options;
	}
}
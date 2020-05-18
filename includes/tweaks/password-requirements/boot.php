<?php
add_action( 'plugins_loaded', function ()
{
	require_once( WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/login-interstitial/class-login-interstitial.php' );
	require_once( WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/class-password-requirements-base.php' );
	require_once( WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/class-password-requirements.php' );
	require_once( WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/class-strong-passwords.php' );

	$requirements = new \WBCR\Titan\Tweaks\Password_Requirements();
	$requirements->run();

	$login_interstitial = new \WBCR\Titan\Tweaks\Login_Interstitial();
	$login_interstitial->run();
}, - 90 );
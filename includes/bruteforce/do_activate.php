<?php

if ( ! \WBCR\Titan\Plugin::app()->getOption( 'bruteforce_set_default_options' ) ) {
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_set_default_options', 1 );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_gdpr', 0 );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_logged', '' );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_lockouts_total', 0 );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_minutes_lockout', 1200 );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_valid_duration', 43200 );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_allowed_retries', 4 );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_lockouts', array() );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_whitelist_ips', array() );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_whitelist_usernames', array() );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_blacklist_ips', array() );
	\WBCR\Titan\Plugin::app()->updateOption( 'bruteforce_blacklist_usernames', array() );
}


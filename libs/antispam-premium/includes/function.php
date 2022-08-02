<?php
/**
 * Helpers functions for the plugin
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>, Alexander Kovalev <alex.kovalevv@gmail.com>
 * @copyright (c) 26.10.2019, Webcraftic
 * @version       1.0
 */

/**
 * Masks a value with asterisks (*)
 *
 * @return string
 */
function wantispamp_obfuscate_param($value = null)
{
	if( $value && (!is_object($value) || !is_array($value)) ) {
		$length = strlen($value);
		$value = str_repeat('*', $length);
	}

	return $value;
}

function wantispamp_array($array)
{
	require_once(WANTISPAMP_PLUGIN_DIR . '/includes/class-array.php');

	return new \WBCR\Titan\Premium\Arr($array);
}

/**
 * Convert value to bool if it is string or int
 *
 * @param bool $value
 *
 * @return bool
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  6.3
 *
 */
function wantispamp_normalize_bool($value)
{
	if( is_string($value) ) {
		$value = "true" === $value ? true : false;
	} else if( is_int($value) ) {
		$value = (bool)$value;
	}

	return $value;
}

/**
 * Get database key with plugin prefix
 *
 * @param $key
 *
 * @return string
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  6.0
 *
 */
function wantispamp_db_key($key)
{
	return \WBCR\Titan\Plugin::app()->getPrefix() . str_replace('-', '_', trim($key));
}

/**
 * Generate unique ID
 *
 * @param string $salt Any text to enhance uniqueness
 *
 * @return string
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  6.0
 *
 */
function wantispamp_generate_uid($salt = '')
{
	return md5($salt . wp_generate_password(20) . time());
}

/**
 * Sends comments for verification
 *
 * @return \WP_Error|array
 *  - @since  6.2
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @var bool Check result
 *  - @var int Remaining comments
 */
function wantispamp_check_existing_comments()
{
	if( !current_user_can('manage_options') ) {
		wp_die(-1);
	}

	$comments = wantispamp_get_comment_list();

	if( empty($comments) ) {
		return [true, 0];
	}

	$items = [];
	foreach($comments as $comment) {
		$uid = wantispamp_generate_uid();
		$items[$uid] = [
			'uid' => $uid,
			'comment_ID' => $comment->comment_ID,
			'email' => $comment->comment_author_email,
			'ip' => $comment->comment_author_IP,
			'text' => $comment->comment_content,
			'js_on' => true,
			'callback_url' => rest_url('wantispam/v1/sync/')
		];
	}

	\WBCR\Titan\Logger\Writter::info(sprintf("%d comments prepared to checking!", sizeof($items)));

	$cm_api = new WBCR\Titan\Premium\Api\Request();
	$request = $cm_api->check_spam($items);

	if( is_wp_error($request) ) {
		return $request;
	}

	if( !empty($request->response) ) {
		$count_checked = sizeof($request->response);
		foreach($request->response as $result_item) {
			if( isset($items[$result_item->uid]) ) {
				$spam = ('done' === $result_item->status) ? $result_item->spam : false;
				$comment_ID = $items[$result_item->uid]['comment_ID'];
				if( wantispamp_approve_comment($result_item->uid, $comment_ID, $result_item->status, $spam) ) {
					$count_checked--;
				}
			}
		}

		if( sizeof($request->response) === $count_checked ) {
			return new WP_Error('not_one_comments_checked', 'Comments cannot be checked for spam because of error.');
		}
	}

	return [true, wantispamp_get_unchecked_comments_count()];
}

/**
 * Puts comment to spam or marks as approved.
 *
 * @param string $uid
 * @param int    $comment_ID
 * @param string $status
 * @param bool   $spam
 *
 * @return bool
 * @since  6.2
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 */
function wantispamp_approve_comment($uid, $comment_ID, $status, $spam)
{
	if( 'done' === $status ) {
		if( true === $spam ) {
			wp_set_comment_status($comment_ID, 'spam');
			\WBCR\Titan\Logger\Writter::info(sprintf("Comment #%d notices as spam!", $comment_ID));
		} else {
			wp_set_comment_status($comment_ID, 'approve');
			\WBCR\Titan\Logger\Writter::info(sprintf("Comment #%d notices as approve!", $comment_ID));
		}

		\WBCR\Titan\Logger\Writter::info(sprintf("Comment #%d has been checked [END]!", $comment_ID));
		delete_comment_meta($comment_ID, wantispamp_db_key('spam_checking'));
		add_comment_meta($comment_ID, wantispamp_db_key('comment_checked'), 1);

		return true;
	} else if( 'process' === $status ) {
		update_comment_meta($comment_ID, wantispamp_db_key('spam_checking'), $uid);
		\WBCR\Titan\Logger\Writter::info(sprintf("Comment check #%d has been delayed! The one will be after 5 min [END].", $comment_ID));

		return true;
	}

	return false;
}

/**
 * Returns unverified comments indented from the beginning of the comment list
 *
 * @return array Array of unverified comments
 * @since  6.2
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @see    https://developer.wordpress.org/reference/functions/get_comments/
 */
function wantispamp_get_comment_list()
{
	$args = [
		'status' => 'hold',
		'number' => \WBCR\Titan\Plugin::COUNT_TO_CHECK,
		'meta_query' => [

			[
				'key' => wantispamp_db_key('spam_checking'),
				'compare' => 'NOT EXISTS',
			],
			[
				'key' => wantispamp_db_key('comment_checked'),
				'compare' => 'NOT EXISTS',
			],
		],
	];

	return get_comments($args);
}

/**
 * Get count of unchecked comments
 *
 * The counter will be cached to improve performance
 *
 * @return bool|int|mixed
 * @since  6.2
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 */
function wantispamp_get_unchecked_comments_count()
{
	global $wpdb;

	$count_comments = wp_cache_get('count_unchecked_comments', 'wantispam');

	if( false === $count_comments ) {
		$count_comments = (int)$wpdb->get_var("
		SELECT COUNT(*) FROM {$wpdb->comments} c 
            LEFT JOIN {$wpdb->commentmeta} AS cm ON (c.comment_ID = cm.comment_id AND cm.meta_key = 'wantispam_spam_checking' )  
            LEFT JOIN {$wpdb->commentmeta} AS mt1 ON (c.comment_ID = mt1.comment_id AND mt1.meta_key = 'wantispam_comment_checked' ) 
         WHERE ( comment_approved = '0' ) AND (cm.comment_id IS NULL AND mt1.comment_id IS NULL) ");

		wp_cache_set('count_unchecked_comments', $count_comments, 'wantispam');
	}

	return $count_comments;
}

/**
 * Returns the request headers
 * This approach is used because the user can use any non-Apache web server
 *
 * @return array [Request headers]
 * @since  6.2
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
function wantispamp_get_all_headers()
{
	$requestHeaders = [];
	foreach($_SERVER as $name => $value) {
		if( substr($name, 0, 5) === "HTTP_" ) {
			$key = str_replace('_', ' ', substr($name, 5));
			$key = ucwords(strtolower($key));
			$key = str_replace(' ', '-', $key);
			$requestHeaders[$key] = $value;
		}
	}

	return $requestHeaders;
}

/**
 * Get the IP address
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  6.0
 */
function wantispamp_get_ip()
{
	$how_get_ip = \WBCR\Titan\Plugin::app()->getPopulateOption('get_ip_method', "recommended");

	switch( $how_get_ip ) {
		case "recommended":
		case "remote_addr";
			//Get the IP of the person registering
			return $_SERVER['REMOTE_ADDR'];
		case "x_forwarded_for":
			if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
				$http_x_headers = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

				return $http_x_headers[0];
			} else {
				return '0.0.0.0';
			}
		case "x-real-ip":
			return isset($_SERVER['X-Real-IP']) ? $_SERVER['X-Real-IP'] : '0.0.0.0';
		case "CF-Connecting-IP":
			return isset($_SERVER['CF-Connecting-IP']) ? $_SERVER['CF-Connecting-IP'] : '0.0.0.0';
		default:
			return '0.0.0.0';
	}
}
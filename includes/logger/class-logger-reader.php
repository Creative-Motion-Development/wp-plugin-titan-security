<?php

namespace WBCR\Titan\Logger;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helps to convert log file content into easy-to-read HTML.
 *
 * Usage example:
 *
 * ```php
 * $log_content = WIO_Log_Reader::prettify();
 * ```
 *
 * @author        Alexander Teshabaev <sasha.tesh@gmail.com>
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @see           \WBCR\Titan\Logger\Writter::get_content() for method which is used to get file content.
 *
 * @see           \WBCR\Titan\Logger\Writter for further information about logging.
 */
class Reader {

	/**
	 * Prettify log content.
	 *
	 * @return bool|mixed|string
	 * @see \WBCR\Titan\Logger\Writter::get_content()
	 */
	public static function prettify() {
		$content = \WBCR\Titan\Logger\Writter::get_content();

		$search = [
			"\r\n",
			"\n\r",
			"\025",
			"\n",
			"\r",
			"\t",
		];

		$replacement = [
			'<br>',
			'<br>',
			'<br>',
			'<br>',
			'<br>',
			str_repeat( '&nbsp;', 4 ),
		];

		$content = str_replace( $search, $replacement, $content );

		$color_map = [
			\WBCR\Titan\Logger\Writter::LEVEL_INFO    => [ 'color' => '#fff', 'bg' => '#52d130' ],
			\WBCR\Titan\Logger\Writter::LEVEL_ERROR   => [ 'color' => '#fff', 'bg' => '#ff5e5e' ],
			\WBCR\Titan\Logger\Writter::LEVEL_WARNING => [ 'color' => '#fff', 'bg' => '#ef910a' ],
			\WBCR\Titan\Logger\Writter::LEVEL_DEBUG   => [ 'color' => '#fff', 'bg' => '#8f8d8b' ],
		];

		/**
		 * Highlight log levels
		 */
		foreach ( $color_map as $level => $item ) {
			$content = preg_replace( "/\[([\d\w]{6})\]\[($level)\]/", "[$1][<span style=\"color: {$item['color']};background-color: {$item['bg']}\">$2</span>]", $content );
		}

		return $content;
	}
}
<?php
/**
 * Class that handles templates.
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>, Alexander Kovalev <alex.kovalevv@gmail.com>
 * @copyright (c) 05.04.2019, Webcraftic
 * @version       1.0
 */

namespace WBCR\Titan;

class Views {

	/**
	 * The single instance of the class.
	 *
	 * @since  1.3.0
	 * @access protected
	 * @var    array
	 */
	protected static $_instance = [];

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.3.0
	 * @var string
	 */
	protected $plugin_dir;

	/**
	 * \WBCR\Titan\Views constructor.
	 *
	 * @param string $plugin_dir
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct( $plugin_dir ) {
		$this->plugin_dir = $plugin_dir;
	}

	/**
	 * @param string $plugin_dir
	 *
	 * @return object|\WBCR\Titan\Views object Main instance.
	 * @since  1.3.0
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.3.6 - add instace id
	 */
	public static function get_instance( $plugin_dir ) {
		$instance_id = md5( $plugin_dir );

		if ( ! isset( self::$_instance[ $instance_id ] ) ) {
			self::$_instance[ $instance_id ] = new self( $plugin_dir );
		}

		return self::$_instance[ $instance_id ];
	}

	/**
	 * Get a template contents.
	 *
	 * @param string $template The template name.
	 * @param mixed $data Some data to pass to the template.
	 * @param \Wbcr_FactoryClearfy000_PageBase $page
	 *
	 * @return bool|string       The page contents. False if the template doesn't exist.
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.3.0
	 *
	 */
	public function get_template( $template, $data = [], \Wbcr_FactoryClearfy000_PageBase $page = null ) {
		$template = str_replace( '_', '-', $template );

		if ( false !== strpos( $template, '/' ) ) {
			$path_part_array = explode( '/', $template );
			$path            = $this->plugin_dir . '/views/' . $path_part_array[0] . '/' . $path_part_array[1] . '.php';
		} else {
			$path = $this->plugin_dir . '/views/' . $template . '.php';
		}

		if ( ! file_exists( $path ) ) {
			return false;
		}

		ob_start();
		include $path;
		$contents = ob_get_clean();

		return trim( (string) $contents );
	}

	/**
	 * Print a template.
	 *
	 * @param string $template The template name.
	 * @param mixed $data Some data to pass to the template.
	 * @param \Wbcr_FactoryClearfy000_PageBase $page
	 *
	 * @since  1.3.0
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @access public
	 *
	 */
	public function print_template( $template, $data = [], \Wbcr_FactoryClearfy000_PageBase $page = null ) {
		echo $this->get_template( $template, $data, $page );
	}
}
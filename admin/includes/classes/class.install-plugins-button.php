<?php
/**
 * Install addon button
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @since         7.0.3
 * @copyright (c) 2020, Creative Motion
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WTITAN_InstallPluginsButton {

	protected $type;
	protected $plugin_slug;

	protected $classes = [
		'button',
		'wtitan-proccess-button',
		'wtitan-update-component-button'
	];
	protected $data = [];
	protected $base_path;

	protected $action;

	protected $url;

	/**
	 * @param string $group_name
	 *
	 * @throws \Exception
	 */
	public function __construct( $type, $plugin_slug ) {
		if ( empty( $type ) || ! is_string( $plugin_slug ) ) {
			throw new \Exception( 'Empty type or plugin_slug attribute.' );
		}
		$this->type        = $type;
		$this->plugin_slug = $plugin_slug;

		if ( $this->type == 'wordpress' ) {
			if ( strpos( rtrim( trim( $this->plugin_slug ) ), '/' ) !== false ) {
				$this->base_path = $this->plugin_slug;
				$base_path_parts = explode( '/', $this->base_path );
				if ( sizeof( $base_path_parts ) === 2 ) {
					$this->plugin_slug = $base_path_parts[0];
				}
			} else {
				$this->base_path = $this->get_plugin_base_path_by_slug( $this->plugin_slug );
			}

			$this->build_wordpress();
		} else if ( $this->type == 'internal' ) {
			$this->build_internal();
		} else {
			throw new \Exception( 'Invalid button type.' );
		}

		// Set default data
		$this->addData( 'storage', $this->type );
		$this->addData( 'i18n', WBCR\Titan\Plugin\Helper::getEscapeJson( $this->get_i18n() ) );
		$this->addData( 'wpnonce', wp_create_nonce( 'updates' ) );
	}

	/**
	 * @return bool
	 */
	public function isPluginActivate() {
		if ( $this->type == 'wordpress' && $this->isPluginInstall() ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';

			return is_plugin_active( $this->base_path );
		} else if ( $this->type == 'internal' ) {
			$preinsatall_components = WBCR\Titan\Plugin::app()->getPopulateOption( 'deactive_preinstall_components', [] );

			return ! in_array( $this->plugin_slug, $preinsatall_components );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isPluginInstall() {
		if ( $this->type == 'wordpress' ) {
			if ( empty( $this->base_path ) ) {
				return false;
			}

			// Check if the function get_plugins() is registered. It is necessary for the front-end
			// usually get_plugins() only works in the admin panel.
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugins = get_plugins();

			if ( isset( $plugins[ $this->base_path ] ) ) {
				return true;
			}
		} else if ( $this->type == 'internal' ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $class
	 *
	 * @throws \Exception
	 */
	public function addClass( $class ) {
		if ( ! is_string( $class ) ) {
			throw new \Exception( 'Attribute class must be a string.' );
		}
		$this->classes[] = $class;
	}

	/**
	 * @param $class
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function removeClass( $class ) {
		if ( ! is_string( $class ) ) {
			throw new \Exception( 'Attribute class must be a string.' );
		}
		$key = array_search( $class, $this->classes );
		if ( isset( $this->classes[ $key ] ) ) {
			unset( $this->classes[ $key ] );

			return true;
		}

		return false;
	}

	/**
	 * @param $name
	 * @param $value
	 *
	 * @throws \Exception
	 */
	public function addData( $name, $value ) {
		if ( ! is_string( $name ) || ! is_string( $value ) ) {
			throw new \Exception( 'Attributes name and value must be a string.' );
		}

		$this->data[ $name ] = $value;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function removeData( $name ) {
		if ( ! is_string( $name ) ) {
			throw new \Exception( 'Attribute name must be a string.' );
		}

		if ( isset( $this->data[ $name ] ) ) {
			unset( $this->data[ $name ] );

			return true;
		}

		return false;
	}

	/**
	 * Print an install button
	 *
	 * @throws \Exception
	 * @since  1.5.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	public function renderButton() {
		echo $this->getButton();
	}

	/**
	 * @return string
	 */
	public function getButton() {
		$i18n = $this->get_i18n();

		$button = '<a href="#" class="' . implode( ' ', $this->get_classes() ) . '" ' . implode( ' ', $this->get_data() ) . '>' . $i18n[ $this->action ] . '</a>';

		return $button;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function getLink() {
		$this->removeClass( 'button' );
		$this->removeClass( 'button-default' );
		$this->removeClass( 'button-primary' );

		//$this->addClass('link');
		$this->addClass( 'button-link' );

		return $this->getButton();
	}

	/**
	 * Print an install a link
	 *
	 * @throws \Exception
	 * @since  1.5.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	public function renderLink() {
		echo $this->getLink();
	}

	/**
	 * @return array
	 * @since  1.5.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	protected function get_data() {
		$data_to_print = [];

		foreach ( (array) $this->data as $key => $value ) {
			$data_to_print[ $key ] = 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return $data_to_print;
	}

	/**
	 * @return array
	 * @since  1.5.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	protected function get_classes() {
		return array_map( 'esc_attr', $this->classes );
	}

	/**
	 * @throws \Exception
	 * @since  1.5.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	protected function build_wordpress() {
		if ( $this->type != 'wordpress' || empty( $this->base_path ) ) {
			return;
		}

		$this->action = 'install';

		if ( $this->isPluginInstall() ) {
			$this->action = 'deactivate';
			if ( ! $this->isPluginActivate() ) {
				$this->action = 'activate';
			}
		}

		$this->addData( 'plugin-action', $this->action );
		$this->addData( 'slug', $this->plugin_slug );
		$this->addData( 'plugin', $this->base_path );

		if ( $this->action == 'activate' ) {
			$this->addClass( 'button-primary' );
		} else {
			$this->addClass( 'button-default' );
		}
	}

	/**
	 * Configurate button of internal components
	 *
	 * @throws \Exception
	 * @since  1.5.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	protected function build_internal() {
		if ( $this->type != 'internal' ) {
			return;
		}

		$this->action = 'activate';

		if ( $this->isPluginActivate() ) {
			$this->action = 'deactivate';
		}

		$this->addData( 'plugin-action', $this->action );
		$this->addData( 'plugin', $this->plugin_slug );

		if ( $this->action == 'activate' ) {
			$this->addClass( 'button-primary' );
		} else {
			$this->addClass( 'button-default' );
		}
	}

	/**
	 * Internalization for action buttons
	 *
	 * @return array
	 * @since  1.5.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	protected function get_i18n() {
		return [
			'activate'    => __( 'Activate', 'titan-security' ),
			'install'     => __( 'Install', 'titan-security' ),
			'deactivate'  => __( 'Deactivate', 'titan-security' ),
			'delete'      => __( 'Delete', 'titan-security' ),
			'loading'     => __( 'Please wait...', 'titan-security' ),
			'preparation' => __( 'Preparation...', 'titan-security' ),
			'read'        => __( 'Read more', 'titan-security' )
		];
	}


	/**
	 * Allows you to get the base path to the plugin in the directory wp-content/plugins/
	 *
	 * @param $slug - slug for example "clearfy", "hide-login-page"
	 *
	 * @return int|null|string - "clearfy/clearfy.php"
	 */
	protected function get_plugin_base_path_by_slug( $slug ) {
		// Check if the function get_plugins() is registered. It is necessary for the front-end
		// usually get_plugins() only works in the admin panel.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		foreach ( $plugins as $base_path => $plugin ) {
			if ( strpos( $base_path, rtrim( trim( $slug ) ) ) !== false ) {
				return $base_path;
			}
		}

		return null;
	}
}


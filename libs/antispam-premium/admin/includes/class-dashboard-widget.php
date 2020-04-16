<?php

namespace WBCR\Titan\Premium;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A widget to display spam statistics.
 *
 * The plugin receives statistics from a remote server, the server sends data for 7 days.
 * This class inserts data into the Google chart and displays the chart in the widget.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 *
 * @since         1.1
 * @copyright (c) 2019 Webcraftic Ltd
 */
class Dashboard_Widget {

	/**
	 * Request interval in hours
	 *
	 * @since 1.1
	 */
	const DEFAULT_REQUESTS_INTERVAL = 4;

	/**
	 * Request interval in hours, if server is unavailable
	 *
	 * @since 1.1
	 */
	const SERVER_UNAVAILABLE_INTERVAL = 4;

	/**
	 * Statistic data
	 *
	 * @since  1.1
	 * @var \stdClass
	 */
	protected $statistic_data;


	/**
	 * Dashboard_Widget constructor.
	 *
	 * Call parent constructor. Registration hooks.
	 *
	 * @since 1.1 Added
	 *
	 * @param string $content
	 */
	public function __construct() {
		$this->statistic_data = $this->get_statistic_data();

		if ( is_wp_error( $this->statistic_data ) || empty( $this->statistic_data->stat ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_dashboard_widget_scripts' ] );

		if ( \WBCR\Titan\Plugin::app()->isNetworkActive() && \WBCR\Titan\Plugin::app()->isNetworkAdmin() ) {
			add_action( 'wp_network_dashboard_setup', [ $this, 'add_dashboard_widgets' ], 999 );

			return;
		}

		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widgets' ], 999 );
	}

	/**
	 * Enqueue google charts library
	 *
	 * @since  1.1
	 */
	public function enqueue_dashboard_widget_scripts( $page ) {
		if ( 'index.php' !== $page ) {
			return;
		}

		wp_enqueue_script( 'wantispam-google-chart', 'https://www.gstatic.com/charts/loader.js', false, WANTISPAMP_PLUGIN_VERSION, true );

		ob_start();
		?>
        <!-- Google chart API-->
        <script type="text/javascript">
			(function() {
				google.charts.load('current', {'packages': ['bar']});
				google.charts.setOnLoadCallback(function() {
					var data = google.visualization.arrayToDataTable([
						['<?php _e( 'Date', 'titan-security' ) ?>', '<?php _e( 'Spam attack', 'titan-security' ) ?>'],
						<?php foreach((array) $this->statistic_data->stat as $day => $number): ?>
						['<?php echo date( "d.m", strtotime( $day ) ) ?>', <?php echo (int) $number ?>],
						<?php endforeach; ?>
					]);

					var options = {
						width: 370,
						height: 300,
						chart: {
							title: '<?php _e( 'Plugin stopped spam attacks', 'titan-security' ) ?>',
							subtitle: '<?php _e( 'Show statistics for 7 days', 'titan-security' ) ?>',
						},
						legend: {position: "none"}
					};

					var chart = new google.charts.Bar(document.getElementById('wantispam-chart-div'));

					chart.draw(data, google.charts.Bar.convertOptions(options));
				});
			})();
        </script>
		<?php
		$code = ob_get_clean();
		$code = trim( preg_replace( '#<script[^>]*>(.*)</script>#is', '$1', $code ) );
		wp_add_inline_script( 'wantispam-google-chart', $code );
	}

	/**
	 * Add the News widget to the dashboard.
	 *
	 * @since 1.1 Added
	 */
	public function add_dashboard_widgets() {
		$widget_id = 'wantispam-statistic';

		wp_add_dashboard_widget( $widget_id, ' Anti-spam Pro statistic', [
			$this,
			'print_widget_content'
		] );

		$this->sort_dashboard_widgets( $widget_id );
	}

	/**
	 * Create the function to output the contents of the Dashboard Widget.
	 *
	 * @since 1.1 Added
	 */
	public function print_widget_content() {
		?>
        <div class="wordpress-news hide-if-no-js">
            <div class="rss-widget">
                <div id="wantispam-chart-div"></div>
                <p><?php printf( __( 'For all time using the plugin, %s spam attacks were stopped.', 'titan-security' ), '<strong style="color:red">' . (int) $this->statistic_data->total . '</strong>' ) ?></p>
            </div>
        </div>
		<?php

	}

	/**
	 * Sorts widgets on the dashboard page
	 *
	 * Our widget must be top than other.
	 *
	 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @author        Alexander Vitkalov <nechin.va@gmail.com>
	 *
	 * @since         1.1
	 *
	 * @param string $widget_id   Widget ID
	 */
	private function sort_dashboard_widgets( $widget_id ) {
		global $wp_meta_boxes;

		$location = \WBCR\Titan\Plugin::app()->isNetworkAdmin() ? 'dashboard-network' : 'dashboard';

		$normal_core   = $wp_meta_boxes[ $location ]['normal']['core'];
		$widget_backup = [ $widget_id => $normal_core[ $widget_id ] ];
		unset( $normal_core[ $widget_id ] );
		$sorted_core = array_merge( $widget_backup, $normal_core );

		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_core;
	}

	/**
	 * Get data from cache.
	 *
	 * If data in the cache, not empty and not expired, then get data from cache. Or get data from server.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 * @since  1.1
	 * @return mixed array
	 */
	private function get_statistic_data() {
		$key = \WBCR\Titan\Plugin::app()->getPrefix() . 'stats_transient_';

		$cached = get_transient( $key );

		if ( $cached !== false ) {
			if ( isset( $cached->error_code ) && isset( $cached->error ) ) {
				return new \WP_Error( $cached->error_code, $cached->error );
			}

			return $cached;
		}

		$api  = new \WBCR\Titan\Premium\Api\Request();
		$data = $api->get_statistic( 7 );

		if ( is_wp_error( $data ) ) {
			set_transient( $key, (object) [
				'error'      => $data->get_error_message(),
				'error_code' => $data->get_error_code()
			], self::SERVER_UNAVAILABLE_INTERVAL * HOUR_IN_SECONDS );

			return $data;
		}

		set_transient( $key, $data->response, self::DEFAULT_REQUESTS_INTERVAL * HOUR_IN_SECONDS );

		return $data->response;
	}
}

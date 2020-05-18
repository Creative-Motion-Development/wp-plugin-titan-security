<?php

namespace WBCR\Titan;

use WBCR\Titan\Client\Client;
use WBCR\Titan\Client\Entity\Signature;
use WBCR\Titan\MalwareScanner\SignaturePool;

/**
 * The file contains a short help info.
 *
 * @author        Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @version       1.0
 * @copyright (c) 2020 Creative Motion
 */
class Scanner extends Module_Base {

	/**
	 * Scanner constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->module_dir = WTITAN_PLUGIN_DIR . "/includes/scanner";

		add_action( 'wp_ajax_start_scan', [ $this, 'ajax_start_scan' ] );
		add_action( 'wp_ajax_stop_scan', [ $this, 'ajax_stop_scan' ] );
		add_action( 'wp_ajax_status_scan', [ $this, 'ajax_status_scan' ] );
	}

	public function ajax_status_scan() {
		check_ajax_referer( 'titan-status-scan' );

		if ( ! Plugin::app()->current_user_can() ) {
			wp_send_json_error( [
				'error_message' => __( "You don't have enough capability to edit this information", "titan-security" ),
			] );
		}

		$status = Plugin::app()->getOption( 'scanner_status', 'stopped' );
		if ( $status === 'stopped' ) {
			//wp_send_json_success(false);
		}

		$cleaned = $suspicious = $progress = 0;
		$scanner = get_option( Plugin::app()->getPrefix() . 'scanner' );
		if ( $scanner ) {
			$matchedCount = get_option( Plugin::app()->getPrefix() . 'scanner_malware_matched', false );
			$files_count  = Plugin::app()->getOption( 'scanner_files_count', 0 );

			$cleaned     = $scanner->cleaned_count;
			$suspicious  = $scanner->suspicious_count;
			$notfiltered = $scanner->files_count;
			$scanned     = $scanner->cleaned_count + $scanner->suspicious_count . ' / ' . $files_count;
			$progress    = [
				$files_count > 0 ? round( $scanner->cleaned_count / $files_count * 100, 1 ) : 0,
				$files_count > 0 ? round( $scanner->suspicious_count / $files_count * 100, 1 ) : 0,
				$files_count > 0 ? round( $scanner->files_count / $files_count * 100, 1 ) : 0
			];

			wp_send_json_success( compact( 'cleaned', 'suspicious', 'progress', 'notfiltered', 'scanned', 'files_count' ) );
		}
	}

	public function ajax_start_scan() {
		check_ajax_referer( 'titan-start-scan' );

		if ( ! Plugin::app()->current_user_can() ) {
			\WBCR\Titan\Logger\Writter::error( 'Scanner start action: ' . __( "You don't have enough capability to edit this information", "titan-security" ) );
			wp_send_json_error( [
				'message' => __( "You don't have enough capability to edit this information", "titan-security" ),
			] );
		}

		titan_create_scheduler_scanner();

		\WBCR\Titan\Logger\Writter::warning( 'Scanner start action: ' . __( 'Started', "titan-security" ) );
		wp_send_json_success( [
			'message' => __( 'Scanning started', 'titan-security' ),
		] );
	}

	public function ajax_stop_scan() {
		check_ajax_referer( 'titan-stop-scan' );

		if ( ! Plugin::app()->current_user_can() ) {
			\WBCR\Titan\Logger\Writter::info( 'Scanner stop action:' . __( "You don't have enough capability to edit this information", "titan-security" ) );
			wp_send_json_error( [
				'message' => __( "You don't have enough capability to edit this information", "titan-security" ),
			] );
		}

		titan_remove_scheduler_scanner();

		\WBCR\Titan\Logger\Writter::warning( 'Scanner stop action:' . __( 'Cancelled', 'titan-security' ) );
		wp_send_json_success( [
			'message' => __( 'Scanning cancelled', 'titan-security' ),
		] );
	}


	/**
	 * Get count matched
	 *
	 * @return int
	 */
	public function get_matched_count() {
		$matched = get_option( Plugin::app()->getPrefix() . 'scanner_malware_matched', [] );

		return count( $matched );
	}

	/**
	 * Current state of scanner
	 */
	public function get_current_results() {

		/** @var MalwareScanner\Scanner $scanner */
		$scanner         = get_option( Plugin::app()->getPrefix() . 'scanner' );
		$matched         = get_option( Plugin::app()->getPrefix() . 'scanner_malware_matched', false );
		$scanner_started = Plugin::app()->getOption( 'scanner_status' ) == 'started';
		$files_count     = Plugin::app()->getOption( 'scanner_files_count', 0 );
		$progress        = [
			$files_count > 0 ? floor( $scanner->cleaned_count / $files_count * 100 ) : 0,
			$files_count > 0 ? ceil( $scanner->suspicious_count / $files_count * 100 ) : 0,
			$files_count > 0 ? floor( $scanner->files_count / $files_count * 100 ) : 0
		];

		if ( ! $scanner ) {
			return [
				'scanner_started' => 0,
				'matched'         => false,
				'progress'        => [ 0, 0, 100 ],
				'cleaned'         => 0,
				'suspicious'      => 0,
				'scanned'         => 0,
				'notverified'     => 0,
				'files_count'     => 0,
			];
		}

		return [
			'scanner_started' => $scanner_started,
			'matched'         => $matched,
			'cleaned'         => $scanner->cleaned_count,
			'suspicious'      => $scanner->suspicious_count,
			'scanned'         => $scanner->cleaned_count + $scanner->suspicious_count,
			'notverified'     => $scanner->files_count,
			'files_count'     => $files_count,
			'progress'        => $progress,
		];
	}

	/**
	 * Show page content
	 */
	public function showPageContent() {
		require WTITAN_PLUGIN_DIR . '/includes/scanner/classes/scanner/boot.php';


		echo $this->render_template( 'scanner', $this->get_current_results() );

	}
}
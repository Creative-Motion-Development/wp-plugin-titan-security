<?php

define( 'WTITAN_PLUGIN_DIR', dirname( dirname( dirname( __FILE__ ) ) ) );

function loader( $class ) {
	$classMap = [
		'Page'           => WTITAN_PLUGIN_DIR . '/admin/pages',
		'Client'         => WTITAN_PLUGIN_DIR . '/libs/api-client',
		'Entity'         => WTITAN_PLUGIN_DIR . '/libs/api-client/entity',
		'Request'        => WTITAN_PLUGIN_DIR . '/libs/api-client/request',
		'Response'       => WTITAN_PLUGIN_DIR . '/libs/api-client/response',
		'Method'         => WTITAN_PLUGIN_DIR . '/libs/api-client/response/method',
		'MalwareScanner' => WTITAN_PLUGIN_DIR . '/includes/scanner/classes/scanner',
	];

	$className     = substr( $class, strrpos( $class, '\\' ) + 1 );
	$lastNamespace = explode( '\\', $class );
	$lastNamespace = $lastNamespace[ count( $lastNamespace ) - 2 ];

	$path = $classMap[ $lastNamespace ] . '/class.' . strtolower( $className ) . '.php';
	if ( ! file_exists( $path ) ) {
		$path = $classMap[ $lastNamespace ] . '/class-' . strtolower( $className ) . '.php';
	}

	if ( ! file_exists( $path ) ) {
		$path = $classMap[ $lastNamespace ] . '/' . $className . '.php';
	}

	if ( ! file_exists( $path ) ) {
		die( $path );
	}

	/** @noinspection PhpIncludeInspection */
	include $path;
}

spl_autoload_register( 'loader' );


$client = new \WBCR\Titan\Client\Client( 'sk_uR~5IArjgfpf-2giAK^hMg5kfC3-7' );

/** @var array $signatures */
$signatures = $client->get_signatures();
foreach ( $signatures as $key => $signature ) {
	$signatures[ $key ] = $signature->to_array();
}
$signature_pool = \WBCR\Titan\MalwareScanner\SignaturePool::fromArray( $signatures );

$scanner = new \WBCR\Titan\MalwareScanner\Scanner( dirname( dirname( WTITAN_PLUGIN_DIR ) ), $signature_pool );
$matches = $scanner->scan();

var_dump( $matches );
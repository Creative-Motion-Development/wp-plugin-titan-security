<?php

namespace WBCR\Titan\MalwareScanner;

/**
 * Class SignaturePool
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class SignaturePool {
	/**
	 * @var Signature[]
	 */
	private $signatures;

	/**
	 * SignaturePool constructor.
	 *
	 * @param Signature[] $signatures
	 */
	public function __construct( $signatures ) {
		$this->signatures = $signatures;
	}

	/**
	 * @return Signature[]
	 */
	public function getSignatures() {
		return $this->signatures;
	}

	/**
	 * @param File $file
	 *
	 * @return Match[]
	 */
	public function scanFile( $file ) {
		$matches = [];

		foreach ( $this->signatures as $signature ) {
			set_error_handler( function ( $_, $msg ) use ( $signature ) {
				print_r( sprintf( "%d: \"/%s/mi\" (%s)\n", $signature->getId(), $signature->getSignature(), $msg ) );
			} );
			$match = $signature->scan( $file );
			if ( ! is_null( $match ) ) {
				$matches[] = $match;
			}
		}

		set_error_handler( null );

		return $matches;
	}

	/**
	 * @param array[] $params
	 *
	 * @return SignaturePool
	 */
	public static function fromArray( $params ) {
		$signatures = [];
		foreach ( $params as $signature ) {
			$signature = Signature::fromArray( $signature );
			if ( is_null( $signature ) ) {
				continue;
			}

			$signatures[ $signature->getId() ] = $signature;
		}

		return new SignaturePool( $signatures );
	}
}
<?php

namespace WBCR\Titan\MalwareScanner;

use WBCR\Titan\Logger\Writter;

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
			$match = $signature->scan( $file );
			if ( ! is_null( $match ) ) {
				$matches[] = $match;
			}
		}

		$file->clearLoadedData();
		gc_collect_cycles();

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
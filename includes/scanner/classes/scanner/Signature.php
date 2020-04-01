<?php

namespace WBCR\Titan\MalwareScanner;

use Exception;
use WBCR\Titan\Logger\Writter;

/**
 * Class Signature
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Signature {
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $format;

	/**
	 * @var int
	 */
	protected $childId;

	/**
	 * @var string
	 */
	protected $sever;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $signature;

	/**
	 * Signature constructor.
	 *
	 * @param int $id
	 * @param string $format
	 * @param int|null $childId
	 * @param string $sever
	 * @param string $title
	 * @param string $signature
	 */
	public function __construct( $id, $format, $childId, $sever, $title, $signature ) {
		$this->id        = (int) $id;
		$this->format    = $format;
		$this->childId   = (int) $childId;
		$this->sever     = $sever;
		$this->title     = $title;
		$this->signature = $signature;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * @return int|null
	 */
	public function getChildId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getSever() {
		return $this->sever;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getSignature() {
		return $this->signature;
	}

	/**
	 * @param File $file
	 *
	 * @return Match|null
	 */
	public function scan( $file ) {
		$content = $file->loadData();

		$match = null;

		$signature = &$this;
		switch ( $this->format ) {

			case 're':
				try {

//
//
//                             Safety Pig Fenya
//                             Saves from memory leaks
//                                                       _
//                               _._ _..._ .-',     _.._(`))
//                              '-. `     '  /-._.-'    ',/
//                                 )         \            '.
//                                / _    _    |             \
//                               |  a    a    /              |
//                               \   .-.                     ;
//                                '-('' ).-'       ,'       ;
//                                   '-;           |      .'
//                                      \           \    /
//                                      | 7  .__  _.-\   \
//                                      | |  |  ``/  /`  /
//                                     /,_|  |   /,_/   /
//                                        /,_/      '`-'
//
//

					set_error_handler( function ( $_, $msg ) use ( $signature ) {
						$msg = sprintf( "Error execution regex #%d: \"/%s/mi\" (%s)\n", $signature->getId(), $signature->getSignature(), $msg );
						Writter::error( $msg );
						error_log( $msg );
					} );

					// After much observation and googling, it turned out that this function causes a memory leak
					$result = preg_match_all( "/{$this->getSignature()}/mi", $content, $matches );

					if ( $result ) {
						$match = new Match( $file, $matches[0][0] );
					}
				} catch ( Exception $e ) {
					Writter::error( sprintf( "%s:\n%s", $e->getMessage(), $e->getTraceAsString() ) );
				}
				break;

		}

		set_error_handler( null );

		return $match;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return sprintf(
			"[ID%s][child=%s sever=%s title=%s format=%s]: %s",
			$this->getId(),
			$this->getChildId(),
			$this->getSever(),
			$this->getTitle(),
			$this->getFormat(),
			$this->getSignature()
		);
	}

	/**
	 * @param array $params
	 *
	 * @return Signature|null
	 */
	public static function fromArray( $params ) {
		if ( empty( $params['id'] ) || empty( $params['format'] ) || empty( $params['severity'] ) || empty( $params['title'] )
		     || empty( $params['content'] ) ) {
			return null;
		}

		if ( ! isset( $params['child_id'] ) ) {
			$params['child_id'] = null;
		}

		return new Signature( $params['id'], $params['format'], $params['child_id'], $params['severity'], $params['title'], $params['content'] );
	}
}
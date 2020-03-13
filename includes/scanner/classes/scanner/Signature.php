<?php

namespace WBCR\Titan\MalwareScanner;

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
	 * @param int      $id
	 * @param string   $format
	 * @param int|null $childId
	 * @param string   $sever
	 * @param string   $title
	 * @param string   $signature
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
		switch ( $this->format ) {

			case 're':
				try {
					$result = preg_match_all( "/{$this->getSignature()}/mi", $file->getContent(), $matches );

					if ( $result ) {
						return new Match( $file, $matches[0][0] );
					}
				} catch ( \Exception $e ) {
					print_r( $e->getMessage() . "\n" );
					print_r( $e->getTraceAsString() . "\n" );
				}
				break;

		}

		return null;
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

		return new Signature( $params['id'], $params['format'], $params['child_id'] ?? null, $params['severity'], $params['title'], $params['content'] );
	}
}
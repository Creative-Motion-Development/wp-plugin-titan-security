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

		$fData = fopen($file->getPath(), 'r');
		if($fData === false) {
			$e = sprintf("Open error: %s", $file->getPath());
			Writter::error($e);
			error_log($e);
			return [];
		}

		if(function_exists('exif_imagetype')) {
			$type = @exif_imagetype($file->getPath());
			if($type !== false) {
				return [];
			}
		}

		$ext = explode('.', $file->getPath());
		$ext = $ext[count($ext) - 1];

		$isPHP = in_array($ext, ['php', 'phtml']);
		$isHTML = !$isPHP && in_array($ext, ['html']);
		$isJS = !$isPHP && !$isHTML && in_array($ext, ['js', 'svg']);
		$isArchive = !$isPHP && !$isHTML && !$isJS && in_array($ext, ['zip', 'tar', 'gz']);
		$isBinary = !$isPHP && !$isHTML && !$isJS && !$isArchive && in_array($ext, [
			'jpg', 'jpeg', 'mp3', 'avi', 'm4v', 'mov',
			'mp4', 'gif', 'png', 'tiff', 'svg', 'sql',
			'tbz2', 'bz2', 'xz', 'zip', 'tgz', 'gz',
			'tar', 'log', 'err'
		]);
		$isUnknown = !$isPHP && !$isHTML && !$isJS && !$isBinary;

		$chunk = 0;
		while(!feof($fData)) {
			if($isUnknown || $isArchive) {
				fclose($fData);
				return [];
			}
			$chunk++;
			$data = fread($fData, 1024 * 1024 * 1); // 1MB

			foreach($this->signatures as $signature) {

				if($signature->getType() != 'both') {
					if($isPHP && $signature->getType() != 'server') {
						continue;
					}

					if(($isHTML || $isJS) && $signature->getType() != 'browser') {
						continue;
					}
				}

				$found = preg_match("/" . $signature->getSignature() . "/mi", $data, $matched, PREG_OFFSET_CAPTURE);
				if($found) {
					$match = $matched[0];
					$matches[] = new Match($signature, $file, $match[1], $match[0]);
					break;
				}
			}
		}

		fclose($fData);

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
<?php

namespace WBCR\Titan\Client;

use ReflectionClass;

/**
 * Class Loader
 * @package       WBCR\Titan\Client\Response
 *
 * @author        Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @version       1.0.0
 * @copyright (c) 2020 Creative Motion
 */
abstract class Loader {

	/**
	 * @param array $data
	 *
	 * @return static
	 */
	public static function from_array( $data ) {
		$params = [];

		$reflect = new ReflectionClass( static::class );

		$constructor = $reflect->getConstructor();
		$phpDoc      = $constructor->getDocComment();

		$parameters = $constructor->getParameters();
		foreach ( $parameters as $parameter ) {
			$pos  = $parameter->getPosition();
			$name = $parameter->getName();

			if ( ! isset( $data[ $name ] ) ) {
				$params[ $pos ] = null;
				continue;
			}

			$pattern = "/@param ([\w\\\|]+) \\\${$name}/sm";
			preg_match( $pattern, $phpDoc, $matches );
			if ( ! isset( $matches[1] ) ) {
				$params[ $pos ] = $data[ $name ];
				continue;
			}

			$type = $matches[1];
			if ( stripos( $type, 'WBCR' ) === false ) {
				$params[ $pos ] = $data[ $name ];
				continue;
			}

			$type = explode( '|', $type );
			if ( ! isset( $type[0] ) ) {
				$params[ $pos ] = $data[ $name ];
				continue;
			}

			if ( ! class_exists( $type[0] ) || ! method_exists( $type[0], 'from_array' ) ) {
				$params[ $pos ] = $data[ $name ];
				continue;
			}

			$params[ $pos ] = $type[0]::from_array( $data[ $name ] );
		}

		return new static( ...$params );
	}
}
<?php


namespace WBCR\Titan\Client\Response;

use WBCR\Titan\Client\Loader;/**
 * Class Error
 * @package       WBCR\Titan\Client\Response
 *
 * @author        Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @version       1.0.0
 *@copyright (c) 2020 Creative Motion
 */
class Error extends Loader {
    /**
     * @var string
     */
    private $message;

    /**
     * @var string[]
     */
    private $attributes;

    /**
     * Error constructor.
     *
     * @param string   $message
     * @param string[] $attributes
     */
    public function __construct($message, $attributes) {
        $this->message = $message;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @return string[]
     */
    public function getAttributes() {
        return $this->attributes;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
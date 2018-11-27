<?php

declare(strict_types=1);

namespace App\Util;

use App\Exception\JsonException;

class Json
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public static function encode($value): string
    {
        // @todo add \JSON_THROW_ON_ERROR once PHP 7.3
        $options = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;

        $string = \json_encode($value, $options);

        if ($error = \json_last_error()) {
            throw new JsonException(\json_last_error_msg(), $error);
        }

        return $string;
    }

    /**
     * @param string $json
     *
     * @return mixed
     */
    public static function decode(string $json)
    {
        $data = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING);

        if ($error = \json_last_error()) {
            throw new JsonException(\json_last_error_msg(), $error);
        }

        return $data;
    }

    final private function __construct()
    {
    }
}

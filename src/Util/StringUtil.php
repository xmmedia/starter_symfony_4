<?php

declare(strict_types=1);

namespace App\Util;

class StringUtil
{
    /**
     * Returns a trimmed string.
     * If the value is not a string, it will return it without modification.
     *
     * @param string|mixed $string
     *
     * @return string|mixed
     */
    public static function trim($string)
    {
        if (!\is_string($string)) {
            return $string;
        }

        $trimmed = \Symfony\Component\Form\Util\StringUtil::trim($string);

        if ('' === $trimmed) {
            return null;
        }

        return $trimmed;
    }
}

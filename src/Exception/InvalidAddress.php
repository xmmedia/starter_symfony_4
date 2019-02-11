<?php

declare(strict_types=1);

namespace App\Exception;

final class InvalidAddress extends \InvalidArgumentException
{
    public static function line1(string $line1, string $message): self
    {
        return new self(
            sprintf(
                'The address line 1 ("%s") is invalid: %s',
                $line1,
                $message
            )
        );
    }

    public static function line2(string $line2, string $message): self
    {
        return new self(
            sprintf(
                'The address line 2 ("%s") is invalid: %s',
                $line2,
                $message
            )
        );
    }

    public static function city(string $city, string $message): self
    {
        return new self(
            sprintf(
                'The address city ("%s") is invalid: %s',
                $city,
                $message
            )
        );
    }
}

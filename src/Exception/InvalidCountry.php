<?php

declare(strict_types=1);

namespace App\Exception;

final class InvalidCountry extends \InvalidArgumentException
{
    public static function invalid(string $province): self
    {
        return new self(sprintf('The country "%s" is invalid.', $province));
    }
}

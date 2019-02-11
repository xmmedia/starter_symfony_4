<?php

declare(strict_types=1);

namespace App\Exception;

final class InvalidPostalCode extends \InvalidArgumentException
{
    public static function invalid(string $postalCode): self
    {
        return new self(sprintf('The postal code "%s" is invalid.', $postalCode));
    }
}

<?php

declare(strict_types=1);

namespace App\Exception;

final class InvalidProvince extends \InvalidArgumentException
{
    public static function invalid(string $province): self
    {
        return new self(sprintf('The province "%s" is invalid.', $province));
    }
}

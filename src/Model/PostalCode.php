<?php

declare(strict_types=1);

namespace App\Model;

use App\Exception\InvalidPostalCode;
use Webmozart\Assert\Assert;

/**
 * Canada: 6 alphanumeric (without spaces)
 * US: 5 or 10 numbers (5+4 digits plus dash).
 */
class PostalCode implements ValueObject
{
    public const MIN_LENGTH = 5;
    public const MAX_LENGTH = 10;

    /** @var string */
    private $postalCode;

    public static function fromString(string $postalCode): self
    {
        return new self($postalCode);
    }

    private function __construct(string $postalCode)
    {
        $postalCode = strtoupper(str_replace(' ', '', $postalCode));

        try {
            Assert::minLength($postalCode, self::MIN_LENGTH);
            Assert::maxLength($postalCode, self::MAX_LENGTH);
        } catch (\InvalidArgumentException $e) {
            throw InvalidPostalCode::invalid($postalCode);
        }

        $this->postalCode = self::format($postalCode);
    }

    public static function format(string $postalCode): string
    {
        // if first char is a letter, we're assuming it's a Canadian postal code
        if (ctype_alpha(substr($postalCode, 0, 1))) {
            $postalCode = strtoupper(str_replace([' ', '-'], '', $postalCode));

            $postalCode = sprintf(
                '%s %s',
                substr($postalCode, 0, 3),
                substr($postalCode, 3, 3)
            );
        }

        return $postalCode;
    }

    public function toString(): string
    {
        return (string) $this->postalCode;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param PostalCode|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        return $this->postalCode === $other->postalCode;
    }
}

<?php

declare(strict_types=1);

namespace App\Model;

use App\DataProvider\CountryProvider;
use App\Exception\InvalidCountry;
use Webmozart\Assert\Assert;

class Country implements ValueObject
{
    /** @var string */
    private $abbreviation;

    /** @var string */
    private $name;

    public static function fromString(string $country): self
    {
        return new self($country);
    }

    private function __construct(string $abbreviation)
    {
        $abbreviation = strtoupper($abbreviation);

        try {
            Assert::length($abbreviation, 2);
            Assert::oneOf($abbreviation, CountryProvider::abbreviations());
        } catch (\Exception $e) {
            throw InvalidCountry::invalid($abbreviation);
        }

        $this->abbreviation = $abbreviation;
        $this->name = CountryProvider::name($abbreviation);
    }

    public function abbreviation(): string
    {
        return $this->abbreviation;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toString(): string
    {
        return $this->abbreviation;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param Country|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        return get_class($this) === get_class($other) && $this->abbreviation === $other->abbreviation;
    }
}

<?php

declare(strict_types=1);

namespace App\Model;

use App\Exception\InvalidAddress;
use App\Util\StringUtil;
use Webmozart\Assert\Assert;

class Address implements ValueObject
{
    public const LINE_MIN_LENGTH = 3;
    public const LINE_MAX_LENGTH = 100;
    public const CITY_MIN_LENGTH = 2;
    public const CITY_MAX_LENGTH = 50;

    /** @var string */
    private $line1;

    /** @var string|null */
    private $line2;

    /** @var string */
    private $city;

    /** @var Province */
    private $province;

    /** @var PostalCode */
    private $postalCode;

    /** @var Country */
    private $country;

    public static function fromStrings(
        string $line1,
        ?string $line2,
        string $city,
        string $province,
        string $postalCode,
        string $country
    ): self {
        $province = Province::fromString($province);
        $postalCode = PostalCode::fromString($postalCode);
        $country = Country::fromString($country);

        return new self($line1, $line2, $city, $province, $postalCode, $country);
    }

    public static function fromArray(array $address): self
    {
        if (!$address['province'] instanceof Province) {
            $address['province'] = Province::fromString($address['province']);
        }
        if (!$address['postalCode'] instanceof PostalCode) {
            $address['postalCode'] = PostalCode::fromString($address['postalCode']);
        }
        if (!$address['country'] instanceof Country) {
            $address['country'] = Country::fromString($address['country']);
        }

        return new self(
            $address['line1'],
            $address['line2'] ?? null,
            $address['city'],
            $address['province'],
            $address['postalCode'],
            $address['country']
        );
    }

    private function __construct(
        string $line1,
        ?string $line2,
        string $city,
        Province $province,
        PostalCode $postalCode,
        Country $country
    ) {
        try {
            Assert::lengthBetween(
                $line1,
                self::LINE_MIN_LENGTH,
                self::LINE_MAX_LENGTH
            );
        } catch (\InvalidArgumentException $e) {
            throw InvalidAddress::line1($line1, $e);
        }

        $line2 = StringUtil::trim($line2);
        if (null !== $line2) {
            try {
                Assert::nullOrLengthBetween(
                    $line2,
                    self::LINE_MIN_LENGTH,
                    self::LINE_MAX_LENGTH
                );
            } catch (\InvalidArgumentException $e) {
                throw InvalidAddress::line2($line2, $e);
            }
        }

        try {
            Assert::lengthBetween(
                $city,
                self::CITY_MIN_LENGTH,
                self::CITY_MAX_LENGTH
            );
        } catch (\InvalidArgumentException $e) {
            throw InvalidAddress::city($city, $e);
        }

        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->city = $city;
        $this->province = $province;
        $this->postalCode = $postalCode;
        $this->country = $country;
    }

    public function line1(): string
    {
        return $this->line1;
    }

    public function line2(): ?string
    {
        return $this->line2;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function province(): Province
    {
        return $this->province;
    }

    public function postalCode(): PostalCode
    {
        return $this->postalCode;
    }

    public function country(): Country
    {
        return $this->country;
    }

    public function toString(): string
    {
        $str = $this->line1;
        if (null !== $this->line2) {
            $str .= "\n".$this->line2;
        }
        $str .= "\n".$this->city.', '.$this->province->name();
        $str .= '  '.$this->postalCode;
        $str .= "\n".$this->country->name();

        return $str;
    }

    public function toArray(): array
    {
        return [
            'line1'      => $this->line1,
            'line2'      => $this->line2,
            'city'       => $this->city,
            'province'   => $this->province->toString(),
            'postalCode' => $this->postalCode->toString(),
            'country'    => $this->country->toString(),
        ];
    }

    /**
     * @param Address|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (\get_class($this) !== \get_class($other)) {
            return false;
        }

        return $this->toArray() === $other->toArray();
    }
}

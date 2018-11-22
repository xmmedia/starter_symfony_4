<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ValueObject;
use Webmozart\Assert\Assert;

class Name implements ValueObject, \JsonSerializable
{
    public const NAME_MIN_LENGTH = 5;
    public const NAME_MAX_LENGTH = 50;

    /** @var string */
    private $name;

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    private function __construct(string $name)
    {
        Assert::notEmpty($name);
        Assert::minLength($name, self::NAME_MIN_LENGTH);
        Assert::maxLength($name, self::NAME_MAX_LENGTH);

        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toString(): string
    {
        return $this->name();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function jsonSerialize()
    {
        return $this->toString();
    }

    /**
     * @param Name|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        return get_class($this) === get_class($other) && $this->name === $other->name;
    }
}

<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Util\Assert;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

class Name implements ValueObject, \JsonSerializable
{
    public const MIN_LENGTH = 2;
    public const MAX_LENGTH = 50;

    /** @var string */
    private $name;

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    private function __construct(string $name)
    {
        $name = StringUtil::trim($name);

        Assert::notEmpty($name);
        Assert::lengthBetween($name, self::MIN_LENGTH, self::MAX_LENGTH);

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
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this->name === $other->name;
    }
}

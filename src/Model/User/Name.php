<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Util\Assert;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

final readonly class Name implements ValueObject, \JsonSerializable, \Stringable
{
    public const int MIN_LENGTH = 2;
    public const int MAX_LENGTH = 50;

    private string $name;

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

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (self::class !== $other::class) {
            return false;
        }

        return $this->name === $other->name;
    }
}

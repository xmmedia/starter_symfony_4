<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Util\Assert;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

class Token implements ValueObject
{
    private string $token;

    public static function fromString(string $token): self
    {
        return new self($token);
    }

    public function __construct(string $token)
    {
        $token = StringUtil::trim($token);

        Assert::notEmpty($token);

        $this->token = $token;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function toString(): string
    {
        return $this->token;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        return $this->token === $other->token;
    }
}

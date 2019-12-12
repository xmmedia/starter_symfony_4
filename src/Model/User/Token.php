<?php

declare(strict_types=1);

namespace App\Model\User;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Model\ValueObject;

class Token implements ValueObject
{
    /** @var string */
    private $token;

    public static function fromString(string $token): self
    {
        return new self($token);
    }

    public function __construct(string $token)
    {
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
        if (\get_class($this) !== \get_class($other)) {
            return false;
        }

        return $this->token === $other->token;
    }
}

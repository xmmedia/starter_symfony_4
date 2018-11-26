<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ValueObject;

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
     * @param Token|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        return get_class($this) === get_class($other) && $this->token === $other->token;
    }
}

<?php

declare(strict_types=1);

namespace App\Model;

final class Email implements ValueObject
{
    /** @var string */
    private $email;

    /** @var string|null */
    private $name;

    public static function fromString(string $email, string $name = null): self
    {
        return new self($email, $name);
    }

    private function __construct(string $email, string $name = null)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function toString(): string
    {
        return $this->email;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function withName(): string
    {
        if (null === $this->name) {
            return $this->email;
        }

        return sprintf('%s <%s>', $this->name, $this->email);
    }

    /**
     * @param Email|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        return get_class($this) === get_class($other) && strtolower($this->email) === strtolower($other->email);
    }
}

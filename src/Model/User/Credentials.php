<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ValueObject;

/**
 * For user before a user is authenticated.
 */
final class Credentials implements ValueObject
{
    public const CSRF_TOKEN_ID = 'authenticate';

    /** @var string|null */
    private $email;

    /** @var string|null */
    private $password;

    public static function build(
        ?string $email,
        ?string $password
    ): self {
        return new self($email, $password);
    }

    private function __construct(
        ?string $email,
        ?string $password
    ) {
        $this->email = null !== $email ? $email : null;
        $this->password = $password;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function password(): ?string
    {
        return $this->password;
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        return \get_class($this) === \get_class($other)
            && $this->email === $other->email
            && $this->password === $other->password;
    }
}

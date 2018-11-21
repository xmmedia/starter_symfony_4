<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ValueObject;
use Symfony\Component\Security\Csrf\CsrfToken;

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

    /** @var CsrfToken */
    private $csrfToken;

    public static function build(
        ?string $email,
        ?string $password,
        ?string $csrfToken
    ): self {
        return new self($email, $password, $csrfToken);
    }

    private function __construct(
        ?string $email,
        ?string $password,
        ?string $csrfToken
    ) {
        $this->email = null !== $email ? $email : null;
        $this->password = $password;
        $this->csrfToken = new CsrfToken(self::CSRF_TOKEN_ID, $csrfToken);
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function password(): ?string
    {
        return $this->password;
    }

    public function csrfToken(): CsrfToken
    {
        return $this->csrfToken;
    }

    /**
     * @param Credentials|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        return get_class($this) === get_class($other)
            && $this->email === $other->email
            && $this->password === $other->password
            && (string) $this->csrfToken === (string) $other->csrfToken;
    }
}

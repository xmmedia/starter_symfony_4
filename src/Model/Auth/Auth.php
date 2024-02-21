<?php

declare(strict_types=1);

namespace App\Model\Auth;

use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\Entity;

class Auth extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    private AuthId $authId;

    public static function success(
        AuthId $authId,
        UserId $userId,
        Email $email,
        string $userAgent,
        string $ipAddress,
        string $route,
    ): self {
        $self = new self();
        $self->recordThat(
            Event\UserLoggedIn::now(
                $authId,
                $userId,
                $email,
                substr($userAgent, 0, 500),
                $ipAddress,
                $route,
            ),
        );

        return $self;
    }

    public static function failure(
        AuthId $authId,
        ?string $email,
        ?string $userAgent,
        string $ipAddress,
        ?string $exceptionMessage,
        string $route,
    ): self {
        $self = new self();
        $self->recordThat(
            Event\UserFailedToLogin::now(
                $authId,
                $email,
                null !== $userAgent ? substr($userAgent, 0, 500) : null,
                $ipAddress,
                $exceptionMessage,
                $route,
            ),
        );

        return $self;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function aggregateId(): string
    {
        return $this->authId->toString();
    }

    public function authId(): AuthId
    {
        return $this->authId;
    }

    protected function whenUserLoggedIn(Event\UserLoggedIn $event): void
    {
        $this->authId = $event->authId();
    }

    protected function whenUserFailedToLogin(Event\UserFailedToLogin $event): void
    {
        $this->authId = $event->authId();
    }

    /**
     * @param Auth $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        return $this->authId->sameValueAs($other->authId);
    }
}

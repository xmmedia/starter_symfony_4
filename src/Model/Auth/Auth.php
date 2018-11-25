<?php

declare(strict_types=1);

namespace App\Model\Auth;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\AppliesAggregateChanged;
use App\Model\Auth\Event\UserFailedToLogin;
use App\Model\Auth\Event\UserLoggedIn;
use App\Model\Email;
use App\Model\Entity;
use App\Model\User\UserId;

class Auth extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    /** @var AuthId */
    private $authId;

    public static function success(
        AuthId $authId,
        UserId $userId,
        Email $email,
        string $userAgent,
        string $ipAddress
    ): self {
        $self = new self();
        $self->recordThat(
            UserLoggedIn::now(
                $authId,
                $userId,
                $email,
                $userAgent,
                $ipAddress
            )
        );

        return $self;
    }

    public static function failure(
        AuthId $authId,
        string $email,
        string $userAgent,
        string $ipAddress,
        ?string $exceptionMessage
    ): self {
        $self = new self();
        $self->recordThat(
            UserFailedToLogin::now(
                $authId,
                $email,
                $userAgent,
                $ipAddress,
                $exceptionMessage
            )
        );

        return $self;
    }

    protected function aggregateId(): string
    {
        return $this->authId->toString();
    }

    protected function whenUserLoggedIn(UserLoggedIn $event): void
    {
        $this->authId = $event->authId();
    }

    protected function whenUserFailedToLogin(UserFailedToLogin $event): void
    {
        $this->authId = $event->authId();
    }

    /**
     * @param Auth|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        return get_class($this) === get_class($other) && $this->authId->sameValueAs($other->authId);
    }
}

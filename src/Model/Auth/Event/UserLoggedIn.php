<?php

declare(strict_types=1);

namespace App\Model\Auth\Event;

use App\Model\Auth\AuthId;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Email;

class UserLoggedIn extends AggregateChanged
{
    private UserId $userId;
    private Email $email;
    private string $userAgent;
    private string $ipAddress;
    private string $route;

    public static function now(
        AuthId $authId,
        UserId $userId,
        Email $email,
        string $userAgent,
        string $ipAddress,
        string $route,
    ): self {
        $event = self::occur($authId->toString(), [
            'userId'    => $userId->toString(),
            'email'     => $email->toString(),
            'userAgent' => $userAgent,
            'ipAddress' => $ipAddress,
            'route'     => $route,
        ]);

        $event->userId = $userId;
        $event->email = $email;
        $event->userAgent = $userAgent;
        $event->ipAddress = $ipAddress;
        $event->route = $route;

        return $event;
    }

    public function authId(): AuthId
    {
        return AuthId::fromString($this->aggregateId());
    }

    public function userId(): UserId
    {
        if (!isset($this->userId)) {
            $this->userId = UserId::fromString($this->payload['userId']);
        }

        return $this->userId;
    }

    public function email(): Email
    {
        if (!isset($this->email)) {
            $this->email = Email::fromString($this->payload['email']);
        }

        return $this->email;
    }

    public function userAgent(): string
    {
        if (!isset($this->userAgent)) {
            $this->userAgent = $this->payload['userAgent'];
        }

        return $this->userAgent;
    }

    public function ipAddress(): string
    {
        if (!isset($this->ipAddress)) {
            $this->ipAddress = $this->payload['ipAddress'];
        }

        return $this->ipAddress;
    }

    public function route(): string
    {
        if (!isset($this->route)) {
            $this->route = $this->payload['route'];
        }

        return $this->route;
    }
}

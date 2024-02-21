<?php

declare(strict_types=1);

namespace App\Model\Auth\Event;

use App\Model\Auth\AuthId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class UserFailedToLogin extends AggregateChanged
{
    private ?string $email;
    private ?string $userAgent;
    private string $ipAddress;
    private ?string $exceptionMessage;
    private string $route;

    public static function now(
        AuthId $authId,
        ?string $email,
        ?string $userAgent,
        string $ipAddress,
        ?string $exceptionMessage,
        string $route,
    ): self {
        $event = self::occur($authId->toString(), [
            'email'            => $email,
            'userAgent'        => $userAgent,
            'ipAddress'        => $ipAddress,
            'exceptionMessage' => $exceptionMessage,
            'route'            => $route,
        ]);

        $event->email = $email;
        $event->userAgent = $userAgent;
        $event->ipAddress = $ipAddress;
        $event->exceptionMessage = $exceptionMessage;
        $event->route = $route;

        return $event;
    }

    public function authId(): AuthId
    {
        return AuthId::fromString($this->aggregateId());
    }

    public function email(): ?string
    {
        if (!isset($this->email)) {
            $this->email = $this->payload['email'];
        }

        return $this->email;
    }

    public function userAgent(): ?string
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

    public function exceptionMessage(): ?string
    {
        if (!isset($this->exceptionMessage)) {
            $this->exceptionMessage = $this->payload['exceptionMessage'];
        }

        return $this->exceptionMessage;
    }

    public function route(): string
    {
        if (!isset($this->route)) {
            $this->route = $this->payload['route'];
        }

        return $this->route;
    }
}

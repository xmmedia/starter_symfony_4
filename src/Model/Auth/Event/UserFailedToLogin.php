<?php

declare(strict_types=1);

namespace App\Model\Auth\Event;

use App\Model\Auth\AuthId;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class UserFailedToLogin extends AggregateChanged
{
    private ?string $email;
    private ?UserId $userId;
    private ?string $userAgent;
    private string $ipAddress;
    private ?string $exceptionMessage;
    private string $route;

    public static function now(
        AuthId $authId,
        ?string $email,
        ?UserId $userId,
        ?string $userAgent,
        string $ipAddress,
        ?string $exceptionMessage,
        string $route,
    ): self {
        $event = self::occur($authId->toString(), [
            'email'            => $email,
            'userId'           => $userId?->toString(),
            'userAgent'        => $userAgent,
            'ipAddress'        => $ipAddress,
            'exceptionMessage' => $exceptionMessage,
            'route'            => $route,
        ]);

        $event->email = $email;
        $event->userId = $userId;
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

    public function userId(): ?UserId
    {
        if (!isset($this->userId)) {
            // @todo-symfony remove array_key_exists check (& test) if this is a new project
            if (\array_key_exists('userId', $this->payload) && null !== $this->payload['userId']) {
                $this->userId = UserId::fromString($this->payload['userId']);
            } else {
                $this->userId = null;
            }
        }

        return $this->userId;
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

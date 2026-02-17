<?php

declare(strict_types=1);

namespace App\Model\Auth\Event;

use App\Model\Auth\AuthId;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class UserEndedImpersonating extends AggregateChanged
{
    private UserId $adminUserId;
    private ?string $userAgent;
    private string $ipAddress;
    private string $route;

    public static function now(
        AuthId $authId,
        UserId $adminUserId,
        ?string $userAgent,
        string $ipAddress,
        string $route,
    ): self {
        $event = self::occur($authId->toString(), [
            'adminUserId' => $adminUserId->toString(),
            'userAgent'   => $userAgent,
            'ipAddress'   => $ipAddress,
            'route'       => $route,
        ]);

        $event->adminUserId = $adminUserId;
        $event->userAgent = $userAgent;
        $event->ipAddress = $ipAddress;
        $event->route = $route;

        return $event;
    }

    public function authId(): AuthId
    {
        return AuthId::fromString($this->aggregateId());
    }

    public function adminUserId(): UserId
    {
        if (!isset($this->adminUserId)) {
            $this->adminUserId = UserId::fromString($this->payload['adminUserId']);
        }

        return $this->adminUserId;
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

    public function route(): string
    {
        if (!isset($this->route)) {
            $this->route = $this->payload['route'];
        }

        return $this->route;
    }
}

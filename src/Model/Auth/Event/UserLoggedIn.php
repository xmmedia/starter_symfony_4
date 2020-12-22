<?php

declare(strict_types=1);

namespace App\Model\Auth\Event;

use App\Model\Auth\AuthId;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Email;

class UserLoggedIn extends AggregateChanged
{
    /** @var UserId */
    private $userId;

    /** @var Email */
    private $email;

    /** @var string */
    private $userAgent;

    /** @var string */
    private $ipAddress;

    public static function now(
        AuthId $authId,
        UserId $userId,
        Email $email,
        string $userAgent,
        string $ipAddress
    ): self {
        $event = self::occur($authId->toString(), [
            'userId'    => $userId->toString(),
            'email'     => $email->toString(),
            'userAgent' => $userAgent,
            'ipAddress' => $ipAddress,
        ]);

        $event->userId = $userId;
        $event->email = $email;
        $event->userAgent = $userAgent;
        $event->ipAddress = $ipAddress;

        return $event;
    }

    public function authId(): AuthId
    {
        return AuthId::fromString($this->aggregateId());
    }

    public function userId(): UserId
    {
        if (null === $this->userId) {
            $this->userId = UserId::fromString($this->payload['userId']);
        }

        return $this->userId;
    }

    public function email(): Email
    {
        if (null === $this->email) {
            $this->email = Email::fromString($this->payload['email']);
        }

        return $this->email;
    }

    public function userAgent(): string
    {
        if (null === $this->userAgent) {
            $this->userAgent = $this->payload['userAgent'];
        }

        return $this->userAgent;
    }

    public function ipAddress(): string
    {
        if (null === $this->ipAddress) {
            $this->ipAddress = $this->payload['ipAddress'];
        }

        return $this->ipAddress;
    }
}

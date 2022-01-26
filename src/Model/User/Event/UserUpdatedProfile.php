<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\Name;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Email;

class UserUpdatedProfile extends AggregateChanged
{
    private Email $email;
    private Name $firstName;
    private Name $lastName;

    public static function now(
        UserId $userId,
        Email $email,
        Name $firstName,
        Name $lastName,
    ): self {
        $event = self::occur($userId->toString(), [
            'email'     => $email->toString(),
            'firstName' => $firstName->toString(),
            'lastName'  => $lastName->toString(),
        ]);

        $event->email = $email;
        $event->firstName = $firstName;
        $event->lastName = $lastName;

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function email(): Email
    {
        if (!isset($this->email)) {
            $this->email = Email::fromString($this->payload['email']);
        }

        return $this->email;
    }

    public function firstName(): Name
    {
        if (!isset($this->firstName)) {
            $this->firstName = Name::fromString($this->payload['firstName']);
        }

        return $this->firstName;
    }

    public function lastName(): Name
    {
        if (!isset($this->lastName)) {
            $this->lastName = Name::fromString($this->payload['lastName']);
        }

        return $this->lastName;
    }
}

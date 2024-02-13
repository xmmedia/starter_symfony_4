<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\Name;
use App\Model\User\UserData;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Email;

final class UserUpdatedProfile extends AggregateChanged
{
    private Email $email;
    private Name $firstName;
    private Name $lastName;
    private UserData $userData;

    public static function now(
        UserId $userId,
        Email $email,
        Name $firstName,
        Name $lastName,
        UserData $userData,
    ): self {
        $event = self::occur($userId->toString(), [
            'email'     => $email->toString(),
            'firstName' => $firstName->toString(),
            'lastName'  => $lastName->toString(),
            'userData'  => $userData->toArray(),
        ]);

        $event->email = $email;
        $event->firstName = $firstName;
        $event->lastName = $lastName;
        $event->userData = $userData;

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

    public function userData(): UserData
    {
        if (!isset($this->userData)) {
            $this->userData = UserData::fromArray($this->payload['userData']);
        }

        return $this->userData;
    }
}

<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserData;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Email;

final class UserWasAddedByAdmin extends AggregateChanged
{
    private Email $email;
    private string $hashedPassword;
    private Role $role;
    private bool $active;
    private Name $firstName;
    private Name $lastName;
    private bool $sendInvite;
    private UserData $userData;

    public static function now(
        UserId $userId,
        Email $email,
        string $hashedPassword,
        Role $role,
        bool $active,
        Name $firstName,
        Name $lastName,
        bool $sendInvite,
        UserData $userData,
    ): self {
        $event = self::occur($userId->toString(), [
            'email'          => $email->toString(),
            'hashedPassword' => $hashedPassword,
            'role'           => $role->getValue(),
            'active'         => $active,
            'firstName'      => $firstName->toString(),
            'lastName'       => $lastName->toString(),
            'sendInvite'     => $sendInvite,
            'userData'       => $userData->toArray(),
        ]);

        $event->email = $email;
        $event->hashedPassword = $hashedPassword;
        $event->role = $role;
        $event->active = $active;
        $event->firstName = $firstName;
        $event->lastName = $lastName;
        $event->sendInvite = $sendInvite;
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

    public function hashedPassword(): string
    {
        if (!isset($this->hashedPassword)) {
            if (\array_key_exists('hashedPassword', $this->payload)) {
                $this->hashedPassword = $this->payload['hashedPassword'];
            } else {
                $this->hashedPassword = $this->payload['encodedPassword'];
            }
        }

        return $this->hashedPassword;
    }

    public function role(): Role
    {
        if (!isset($this->role)) {
            $this->role = Role::byValue($this->payload['role']);
        }

        return $this->role;
    }

    public function active(): bool
    {
        if (!isset($this->active)) {
            $this->active = $this->payload['active'];
        }

        return $this->active;
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

    public function sendInvite(): bool
    {
        if (!isset($this->sendInvite)) {
            $this->sendInvite = $this->payload['sendInvite'];
        }

        return $this->sendInvite;
    }

    public function userData(): UserData
    {
        if (!isset($this->userData)) {
            $this->userData = UserData::fromArray($this->payload['userData']);
        }

        return $this->userData;
    }
}

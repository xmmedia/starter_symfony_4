<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Email;

class MinimalUserWasAddedByAdmin extends AggregateChanged
{
    private Email $email;
    private string $hashedPassword;
    private Role $role;
    private ?Name $firstName;
    private ?Name $lastName;
    private bool $sendInvite;

    public static function now(
        UserId $userId,
        Email $email,
        string $hashedPassword,
        Role $role,
        Name $firstName,
        Name $lastName,
        bool $sendInvite,
    ): self {
        $event = self::occur($userId->toString(), [
            'email'          => $email->toString(),
            'hashedPassword' => $hashedPassword,
            'role'           => $role->getValue(),
            'firstName'      => $firstName->toString(),
            'lastName'       => $lastName->toString(),
            'sendInvite'     => $sendInvite,
        ]);

        $event->email = $email;
        $event->hashedPassword = $hashedPassword;
        $event->role = $role;
        $event->firstName = $firstName;
        $event->lastName = $lastName;
        $event->sendInvite = $sendInvite;

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

    public function firstName(): ?Name
    {
        if (!isset($this->firstName)) {
            if (!\array_key_exists('firstName', $this->payload)) {
                $this->firstName = null;
            } else {
                $this->firstName = Name::fromString($this->payload['firstName']);
            }
        }

        return $this->firstName;
    }

    public function lastName(): ?Name
    {
        if (!isset($this->lastName)) {
            if (!\array_key_exists('lastName', $this->payload)) {
                $this->lastName = null;
            } else {
                $this->lastName = Name::fromString($this->payload['lastName']);
            }
        }

        return $this->lastName;
    }

    public function sendInvite(): bool
    {
        if (!isset($this->sendInvite)) {
            if (!\array_key_exists('sendInvite', $this->payload)) {
                $this->sendInvite = false;
            } else {
                $this->sendInvite = $this->payload['sendInvite'];
            }
        }

        return $this->sendInvite;
    }
}

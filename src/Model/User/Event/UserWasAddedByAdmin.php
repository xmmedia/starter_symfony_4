<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\EventSourcing\AggregateChanged;
use App\Model\Email;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;

class UserWasAddedByAdmin extends AggregateChanged
{
    /** @var Email */
    private $email;

    /** @var string */
    private $encodedPassword;

    /** @var Role */
    private $role;

    /** @var bool */
    private $active;

    /** @var Name */
    private $firstName;

    /** @var Name */
    private $lastName;

    /** @var bool */
    private $sendInvite;

    public static function now(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
        bool $active,
        Name $firstName,
        Name $lastName,
        bool $sendInvite
    ): self {
        $event = self::occur($userId->toString(), [
            'email'           => $email->toString(),
            'encodedPassword' => $encodedPassword,
            'role'            => $role->getValue(),
            'active'          => $active,
            'firstName'       => $firstName->toString(),
            'lastName'        => $lastName->toString(),
            'sendInvite'      => $sendInvite,
        ]);

        $event->email = $email;
        $event->encodedPassword = $encodedPassword;
        $event->role = $role;
        $event->active = $active;
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
        if (null === $this->email) {
            $this->email = Email::fromString($this->payload()['email']);
        }

        return $this->email;
    }

    public function encodedPassword(): string
    {
        if (null === $this->encodedPassword) {
            $this->encodedPassword = $this->payload()['encodedPassword'];
        }

        return $this->encodedPassword;
    }

    public function role(): Role
    {
        if (null === $this->role) {
            $this->role = Role::byValue($this->payload()['role']);
        }

        return $this->role;
    }

    public function active(): bool
    {
        if (null === $this->active) {
            $this->active = $this->payload()['active'];
        }

        return $this->active;
    }

    public function firstName(): Name
    {
        if (null === $this->firstName) {
            $this->firstName = Name::fromString($this->payload()['firstName']);
        }

        return $this->firstName;
    }

    public function lastName(): Name
    {
        if (null === $this->lastName) {
            $this->lastName = Name::fromString($this->payload()['lastName']);
        }

        return $this->lastName;
    }

    public function sendInvite(): bool
    {
        if (null === $this->sendInvite) {
            $this->sendInvite = $this->payload()['sendInvite'];
        }

        return $this->sendInvite;
    }
}

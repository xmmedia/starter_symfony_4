<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\EventSourcing\AggregateChanged;
use App\Model\Email;
use App\Model\User\UserId;
use Symfony\Component\Security\Core\Role\Role;

class UserWasCreatedByAdmin extends AggregateChanged
{
    /** @var Email */
    private $email;

    /** @var string */
    private $encodedPassword;

    /** @var Role */
    private $role;

    /** @var bool */
    private $enabled;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    public static function now(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
        bool $enabled,
        string $firstName,
        string $lastName
    ): self {
        $event = self::occur($userId->toString(), [
            'email'           => $email->toString(),
            'encodedPassword' => $encodedPassword,
            'role'            => $role->getRole(),
            'enabled'         => $enabled,
            'firstName'       => $firstName,
            'lastName'        => $lastName,
        ]);

        $event->email = $email;
        $event->encodedPassword = $encodedPassword;
        $event->role = $role;
        $event->enabled = $enabled;
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
            $this->role = new Role($this->payload()['role']);
        }

        return $this->role;
    }

    public function enabled(): bool
    {
        if (null === $this->enabled) {
            $this->enabled = $this->payload()['enabled'];
        }

        return $this->enabled;
    }

    public function firstName(): string
    {
        if (null === $this->firstName) {
            $this->firstName = $this->payload()['firstName'];
        }

        return $this->firstName;
    }

    public function lastName(): string
    {
        if (null === $this->lastName) {
            $this->lastName = $this->payload()['lastName'];
        }

        return $this->lastName;
    }
}

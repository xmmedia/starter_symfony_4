<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\Role;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Email;

class MinimalUserWasAddedByAdmin extends AggregateChanged
{
    private Email $email;
    private string $encodedPassword;
    private Role $role;

    public static function now(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
    ): self {
        $event = self::occur($userId->toString(), [
            'email'           => $email->toString(),
            'encodedPassword' => $encodedPassword,
            'role'            => $role->getValue(),
        ]);

        $event->email = $email;
        $event->encodedPassword = $encodedPassword;
        $event->role = $role;

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

    public function encodedPassword(): string
    {
        if (!isset($this->encodedPassword)) {
            $this->encodedPassword = $this->payload['encodedPassword'];
        }

        return $this->encodedPassword;
    }

    public function role(): Role
    {
        if (!isset($this->role)) {
            $this->role = Role::byValue($this->payload['role']);
        }

        return $this->role;
    }
}

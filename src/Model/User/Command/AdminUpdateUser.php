<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Messaging\Command;
use App\Model\Email;
use App\Model\User\Name;
use App\Model\User\UserId;
use Symfony\Component\Security\Core\Role\Role;
use Webmozart\Assert\Assert;

final class AdminUpdateUser extends Command
{
    public static function withData(
        UserId $userId,
        Email $email,
        Role $role,
        Name $firstName,
        Name $lastName
    ): self {
        return new self([
            'userId'          => $userId->toString(),
            'email'           => $email->toString(),
            'role'            => $role->getRole(),
            'firstName'       => $firstName->toString(),
            'lastName'        => $lastName->toString(),
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload()['userId']);
    }

    public function email(): Email
    {
        return Email::fromString($this->payload()['email']);
    }

    public function role(): Role
    {
        return new Role($this->payload()['role']);
    }

    public function firstName(): Name
    {
        return Name::fromString($this->payload()['firstName']);
    }

    public function lastName(): Name
    {
        return Name::fromString($this->payload()['lastName']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'role');

        Assert::keyExists($payload, 'firstName');

        Assert::keyExists($payload, 'lastName');

        parent::setPayload($payload);
    }
}

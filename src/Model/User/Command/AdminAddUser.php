<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Email;

final class AdminAddUser extends Command
{
    public static function with(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
        bool $active,
        Name $firstName,
        Name $lastName,
        bool $sendInvite
    ): self {
        return new self([
            'userId'          => $userId->toString(),
            'email'           => $email->toString(),
            'encodedPassword' => $encodedPassword,
            'role'            => $role->getValue(),
            'active'          => $active,
            'firstName'       => $firstName->toString(),
            'lastName'        => $lastName->toString(),
            'sendInvite'      => $sendInvite,
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

    public function encodedPassword(): string
    {
        return $this->payload()['encodedPassword'];
    }

    public function role(): Role
    {
        return Role::byValue($this->payload()['role']);
    }

    public function active(): bool
    {
        return $this->payload()['active'];
    }

    public function firstName(): Name
    {
        return Name::fromString($this->payload()['firstName']);
    }

    public function lastName(): Name
    {
        return Name::fromString($this->payload()['lastName']);
    }

    public function sendInvite(): bool
    {
        return $this->payload()['sendInvite'];
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'email');
        Assert::string($payload['email']);

        Assert::keyExists($payload, 'encodedPassword');
        Assert::notEmpty($payload['encodedPassword']);
        Assert::string($payload['encodedPassword']);

        Assert::keyExists($payload, 'role');
        Assert::string($payload['role']);

        Assert::keyExists($payload, 'active');
        Assert::boolean($payload['active']);

        Assert::keyExists($payload, 'firstName');
        Assert::string($payload['firstName']);

        Assert::keyExists($payload, 'lastName');
        Assert::string($payload['lastName']);

        parent::setPayload($payload);
    }
}

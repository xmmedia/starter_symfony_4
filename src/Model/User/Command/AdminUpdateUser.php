<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserData;
use App\Model\User\UserId;
use App\Util\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Email;

final class AdminUpdateUser extends Command
{
    public static function with(
        UserId $userId,
        Email $email,
        Role $role,
        Name $firstName,
        Name $lastName,
        UserData $userData,
    ): self {
        return new self([
            'userId'    => $userId->toString(),
            'email'     => $email->toString(),
            'role'      => $role->getValue(),
            'firstName' => $firstName->toString(),
            'lastName'  => $lastName->toString(),
            'userData'  => $userData->toArray(),
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['userId']);
    }

    public function email(): Email
    {
        return Email::fromString($this->payload['email']);
    }

    public function role(): Role
    {
        return Role::byValue($this->payload['role']);
    }

    public function firstName(): Name
    {
        return Name::fromString($this->payload['firstName']);
    }

    public function lastName(): Name
    {
        return Name::fromString($this->payload['lastName']);
    }

    public function userData(): UserData
    {
        return UserData::fromArray($this->payload['userData']);
    }

    #[\Override]
    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'email');
        Assert::string($payload['email']);

        Assert::keyExists($payload, 'role');
        Assert::string($payload['role']);

        Assert::keyExists($payload, 'firstName');
        Assert::string($payload['firstName']);

        Assert::keyExists($payload, 'lastName');
        Assert::string($payload['lastName']);

        Assert::keyExists($payload, 'userData');
        Assert::isArray($payload['userData']);

        parent::setPayload($payload);
    }
}

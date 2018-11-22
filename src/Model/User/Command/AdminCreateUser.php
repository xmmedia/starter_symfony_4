<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Messaging\Command;
use App\Model\Email;
use App\Model\User\Name;
use App\Model\User\UserId;
use Symfony\Component\Security\Core\Role\Role;
use Webmozart\Assert\Assert;

final class AdminCreateUser extends Command
{
    public static function withData(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
        bool $enabled,
        Name $firstName,
        Name $lastName
    ): self {
        return new self([
            'userId'          => $userId->toString(),
            'email'           => $email->toString(),
            'encodedPassword' => $encodedPassword,
            'role'            => $role->getRole(),
            'enabled'         => $enabled,
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

    public function encodedPassword(): string
    {
        return $this->payload()['encodedPassword'];
    }

    public function role(): Role
    {
        return new Role($this->payload()['role']);
    }

    public function enabled(): bool
    {
        return $this->payload()['enabled'];
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

        Assert::keyExists($payload, 'encodedPassword');
        Assert::notEmpty($payload['encodedPassword']);
        Assert::string($payload['encodedPassword']);

        Assert::keyExists($payload, 'role');

        Assert::keyExists($payload, 'enabled');
        Assert::boolean($payload['enabled']);

        Assert::keyExists($payload, 'firstName');

        Assert::keyExists($payload, 'lastName');

        parent::setPayload($payload);
    }
}

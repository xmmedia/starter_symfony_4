<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Messaging\Command;
use App\Model\Email;
use App\Model\User\UserId;
use Symfony\Component\Security\Core\Role\Role;
use Webmozart\Assert\Assert;

final class AdminCreateUserMinimum extends Command
{
    public static function withData(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role
    ): self {
        return new self([
            'userId'          => $userId->toString(),
            'email'           => $email->toString(),
            'encodedPassword' => $encodedPassword,
            'role'            => $role->getRole(),
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

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'encodedPassword');
        Assert::notEmpty($payload['encodedPassword']);
        Assert::string($payload['encodedPassword']);

        Assert::keyExists($payload, 'role');

        parent::setPayload($payload);
    }
}

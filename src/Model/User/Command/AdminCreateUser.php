<?php

declare(strict_types=1);

namespace App\Model\User\Command;

use App\Messaging\Command;
use App\Model\Email;
use App\Model\User\User;
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
        string $firstName,
        string $lastName
    ): self {
        return new self([
            'userId'          => $userId->toString(),
            'email'           => $email->toString(),
            'encodedPassword' => $encodedPassword,
            'role'            => $role->getRole(),
            'enabled'         => $enabled,
            'firstName'       => $firstName,
            'lastName'        => $lastName,
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

    public function firstName(): string
    {
        return $this->payload()['firstName'];
    }

    public function lastName(): string
    {
        return $this->payload()['lastName'];
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
        Assert::notEmpty($payload['firstName']);
        Assert::string($payload['firstName']);
        Assert::minLength($payload['firstName'], User::NAME_MIN_LENGTH);
        Assert::maxLength($payload['firstName'], User::NAME_MAX_LENGTH);

        Assert::keyExists($payload, 'lastName');
        Assert::notEmpty($payload['lastName']);
        Assert::string($payload['lastName']);
        Assert::minLength($payload['lastName'], User::NAME_MIN_LENGTH);
        Assert::maxLength($payload['lastName'], User::NAME_MAX_LENGTH);

        parent::setPayload($payload);
    }
}

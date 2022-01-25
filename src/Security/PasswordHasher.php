<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Model\User\Role;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordHasher
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function __invoke(Role $role, string $password): string
    {
        return $this->passwordHasher->hashPassword(
            $this->getUserForRole($role),
            $password,
        );
    }

    private function getUserForRole(Role $role): User
    {
        $user = new User();

        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user, [$role->getValue()]);

        return $user;
    }
}

<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Role\Role;

class PasswordEncoder
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(Role $role, string $password)
    {
        return $this->passwordEncoder->encodePassword(
            $this->getUserForRole($role),
            $password
        );
    }

    private function getUserForRole(Role $role): User
    {
        $user = new User();

        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user, [$role->getRole()]);

        return $user;
    }
}

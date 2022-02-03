<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Query\User;

use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordValidQuery implements QueryInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private Security $security,
    ) {
    }

    public function __invoke(string $password): array
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            throw new \RuntimeException('Must be logged in to access.');
        }

        return [
            'valid' => $this->userPasswordHasher->isPasswordValid(
                $currentUser,
                $password,
            ),
        ];
    }
}

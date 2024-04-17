<?php

declare(strict_types=1);

namespace App\GraphQl\Query\User;

use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserPasswordValidQuery implements QueryInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private Security $security,
    ) {
    }

    public function __invoke(#[\SensitiveParameter] string $password): array
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

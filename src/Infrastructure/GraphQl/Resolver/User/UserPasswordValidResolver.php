<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\User;

use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordValidResolver implements ResolverInterface
{
    /** @var UserPasswordHasherInterface */
    private $userPasswordHasher;

    /** @var Security */
    private $security;

    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
    ) {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->security = $security;
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

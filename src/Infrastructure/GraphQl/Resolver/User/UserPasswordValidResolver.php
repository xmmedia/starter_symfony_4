<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\User;

use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordValidResolver implements ResolverInterface
{
    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    /** @var Security */
    private $security;

    public function __construct(
        UserPasswordEncoderInterface $userPasswordEncoder,
        Security $security
    ) {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->security = $security;
    }

    public function __invoke(string $password): array
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            throw new \RuntimeException('Must be logged in to access.');
        }

        return [
            'valid' => $this->userPasswordEncoder->isPasswordValid(
                $currentUser,
                $password
            ),
        ];
    }
}

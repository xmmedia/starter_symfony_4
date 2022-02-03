<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Query\User;

use App\Projection\User\UserFinder;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Xm\SymfonyBundle\Model\Email;

class UserEmailUniqueQuery implements QueryInterface
{
    public function __construct(private UserFinder $userFinder, private Security $security)
    {
    }

    /**
     * Returns unique => false when the email address is not used
     * or only used by the current user.
     * As the email address is stored in lower case,
     * the email is compared in lowercase.
     */
    public function __invoke(string $email): array
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            throw new \RuntimeException('Must be logged in to access.');
        }

        $user = $this->userFinder->findOneByEmail(
            Email::fromString(mb_strtolower($email)),
        );

        if (!$user) {
            return ['unique' => true];
        }

        return [
            'unique' => $currentUser->userId()->sameValueAs($user->userId()),
        ];
    }
}

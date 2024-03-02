<?php

declare(strict_types=1);

namespace App\GraphQl\Query\User;

use App\Projection\User\UserFinder;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Xm\SymfonyBundle\Model\Email;

final readonly class UserEmailUniqueQuery implements QueryInterface
{
    public function __construct(
        private UserFinder $userFinder,
        private Security $security,
        private bool $testing = false,
    ) {
    }

    /**
     * Returns unique => false when the email address is not used or only used by the current user.
     * As the email address is stored in lower case, the email is compared in lowercase.
     */
    public function __invoke(string $email): array
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser && !$this->testing) {
            sleep(random_int(0, 5));
        }

        $user = $this->userFinder->findOneByEmail(Email::fromString(mb_strtolower($email)));

        if (!$user) {
            return ['unique' => true];
        }

        // another user is already using the same email
        if (!$currentUser) {
            return ['unique' => false];
        }

        return [
            'unique' => $currentUser->userId()->sameValueAs($user->userId()),
        ];
    }
}

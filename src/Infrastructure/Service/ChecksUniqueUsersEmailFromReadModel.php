<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Model\Email;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;

class ChecksUniqueUsersEmailFromReadModel implements ChecksUniqueUsersEmail
{
    /** @var UserFinder */
    private $userFinder;

    public function __construct(UserFinder $userFinder)
    {
        $this->userFinder = $userFinder;
    }

    public function __invoke(Email $email): ?UserId
    {
        if ($user = $this->userFinder->findOneByEmail($email)) {
            return $user->userId();
        }

        return null;
    }
}

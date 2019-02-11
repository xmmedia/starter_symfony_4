<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Model\Email;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserId;
use App\Repository\UserRepository;

class ChecksUniqueUsersEmailFromReadModel implements ChecksUniqueUsersEmail
{
    /** @var UserRepository */
    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(Email $email): ?UserId
    {
        if ($user = $this->userRepo->findOneByEmail($email)) {
            return $user->userId();
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserList;

class AdminAddUserMinimumHandler
{
    /** @var UserList */
    private $userRepo;

    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmail;

    public function __construct(
        UserList $userRepo,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail
    ) {
        $this->userRepo = $userRepo;
        $this->checksUniqueUsersEmail = $checksUniqueUsersEmail;
    }

    public function __invoke(AdminAddUserMinimum $command): void
    {
        $user = User::addByAdminMinimum(
            $command->userId(),
            $command->email(),
            $command->encodedPassword(),
            $command->role(),
            $this->checksUniqueUsersEmail
        );

        $this->userRepo->save($user);
    }
}

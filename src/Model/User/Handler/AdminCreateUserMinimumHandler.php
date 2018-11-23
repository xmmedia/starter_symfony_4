<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminCreateUserMinimum;
use App\Model\User\Exception\DuplicateEmailAddress;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserList;

class AdminCreateUserMinimumHandler
{
    /** @var UserList */
    private $userRepo;

    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmailAddress;

    public function __construct(
        UserList $userRepo,
        ChecksUniqueUsersEmail $checksUniqueUsersEmailAddress
    ) {
        $this->userRepo = $userRepo;
        $this->checksUniqueUsersEmailAddress = $checksUniqueUsersEmailAddress;
    }

    public function __invoke(AdminCreateUserMinimum $command): void
    {
        if ($userId = ($this->checksUniqueUsersEmailAddress)($command->email())) {
            throw DuplicateEmailAddress::withEmail($command->email(), $userId);
        }

        $user = User::createByAdminMinimum(
            $command->userId(),
            $command->email(),
            $command->encodedPassword(),
            $command->role()
        );

        $this->userRepo->save($user);
    }
}

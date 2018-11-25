<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminCreateUser;
use App\Model\User\Exception\DuplicateEmailAddress;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserList;

class AdminCreateUserHandler
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

    public function __invoke(AdminCreateUser $command): void
    {
        if ($userId = ($this->checksUniqueUsersEmailAddress)($command->email())) {
            throw DuplicateEmailAddress::withEmail($command->email(), $userId);
        }

        $user = User::createByAdmin(
            $command->userId(),
            $command->email(),
            $command->encodedPassword(),
            $command->role(),
            $command->active(),
            $command->firstName(),
            $command->lastName(),
            $command->sendInvite()
        );

        $this->userRepo->save($user);
    }
}

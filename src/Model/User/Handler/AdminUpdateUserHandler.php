<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Exception\DuplicateEmailAddress;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserList;

class AdminUpdateUserHandler
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

    public function __invoke(AdminUpdateUser $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        if ($userId = ($this->checksUniqueUsersEmailAddress)($command->email())) {
            if (!$command->userId()->sameValueAs($userId)) {
                throw DuplicateEmailAddress::withEmail($command->email(), $userId);
            }
        }

        $user->updateByAdmin(
            $command->email(),
            $command->role(),
            $command->firstName(),
            $command->lastName()
        );

        $this->userRepo->save($user);
    }
}

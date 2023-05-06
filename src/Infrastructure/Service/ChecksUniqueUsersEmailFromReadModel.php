<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserIdInterface;
use App\Projection\User\UserFinder;
use Xm\SymfonyBundle\Model\Email;

class ChecksUniqueUsersEmailFromReadModel implements ChecksUniqueUsersEmail
{
    public function __construct(private readonly UserFinder $userFinder)
    {
    }

    public function __invoke(Email $email): ?UserIdInterface
    {
        if ($user = $this->userFinder->findOneByEmail($email)) {
            return $user->userId();
        }

        return null;
    }
}

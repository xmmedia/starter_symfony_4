<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserIdInterface;
use App\Projection\User\UserFinder;
use Xm\SymfonyBundle\Model\Email;

final readonly class ChecksUniqueUsersEmailFromReadModel implements ChecksUniqueUsersEmail
{
    public function __construct(private UserFinder $userFinder)
    {
    }

    public function __invoke(Email $email): ?UserIdInterface
    {
        return $this->userFinder->findOneByEmail($email)?->userId();
    }
}

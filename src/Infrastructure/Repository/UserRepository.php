<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRepository;

final class UserRepository extends AggregateRepository implements UserList
{
    public function save(User $user): void
    {
        $this->saveAggregateRoot($user);
    }

    public function get(UserId $userId): ?User
    {
        return $this->getAggregateRoot($userId->toString());
    }
}

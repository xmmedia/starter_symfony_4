<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthId;
use App\Model\Auth\AuthList;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRepository;

final class AuthRepository extends AggregateRepository implements AuthList
{
    public function save(Auth $auth): void
    {
        $this->saveAggregateRoot($auth);
    }

    public function get(AuthId $authId): ?Auth
    {
        return $this->getAggregateRoot($authId->toString());
    }
}

<?php

declare(strict_types=1);

namespace App\Projection\Auth;

use App\Projection\Table;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

final class AuthReadModel extends AbstractReadModel
{
    public function init(): void
    {
        // no table, update the user table
    }

    protected function loggedIn(string $userId, \DateTimeImmutable $lastLogin): void
    {
        $tableName = Table::USER;

        $sql = <<<Query
UPDATE `{$tableName}` SET login_count = login_count + 1, last_login = :last_login WHERE user_id = :user_id;
Query;
        $statement = $this->connection->prepare($sql);

        $statement->bindValue('last_login', $lastLogin, 'datetime_immutable');
        $statement->bindValue(':user_id', $userId);

        $statement->executeQuery();
    }

    #[\Override]
    public function reset(): void
    {
        $this->delete();
    }

    #[\Override]
    public function delete(): void
    {
        $tableName = Table::USER;

        $sql = <<<Query
UPDATE `{$tableName}` SET login_count = 0, last_login = null;
Query;

        $this->connection->executeQuery($sql);
    }
}

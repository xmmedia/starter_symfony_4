<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Projection\Table;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

final class UserTokenReadModel extends AbstractReadModel
{
    protected const TABLE = Table::USER_TOKEN;

    public function init(): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
CREATE TABLE `$tableName` (
  `token` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `generated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOT;

        $this->connection->executeQuery($sql);

        $sql = <<<EOT
ALTER TABLE `$tableName`
  ADD PRIMARY KEY (`token`),
  ADD KEY `user_id` (`user_id`);
EOT;

        $this->connection->executeQuery($sql);
    }

    protected function add(array $data, array $types = []): void
    {
        $this->connection->insert(self::TABLE, $data, $types);
    }

    protected function removeAllForUser(string $userId): void
    {
        $this->connection->delete(self::TABLE, ['user_id' => $userId]);
    }
}

<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Projection\Table;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

final class UserReadModel extends AbstractReadModel
{
    protected const TABLE = Table::USER;

    public function init(): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
CREATE TABLE `$tableName` (
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `email` varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `roles` json NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_count` int(11) DEFAULT 0 NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOT;

        $statement = $this->connection->prepare($sql);
        $statement->executeQuery();

        $sql = <<<EOT
ALTER TABLE `$tableName`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`) USING BTREE;
EOT;

        $statement = $this->connection->prepare($sql);
        $statement->executeQuery();
    }

    protected function insert(array $data, array $types = []): void
    {
        $this->connection->insert(self::TABLE, $data, $types);
    }

    protected function update(string $userId, array $data, array $types = []): void
    {
        $this->connection->update(
            self::TABLE,
            $data,
            ['user_id' => $userId],
            $types
        );
    }

    protected function loggedIn(string $userId, \DateTimeImmutable $lastLogin): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
UPDATE `$tableName` SET login_count = login_count + 1, last_login = :last_login WHERE user_id = :user_id;
EOT;

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue('last_login', $lastLogin, 'datetime');
        $stmt->bindValue('user_id', $userId);

        $stmt->executeQuery();
    }
}

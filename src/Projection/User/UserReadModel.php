<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Projection\Table;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

final class UserReadModel extends AbstractReadModel
{
    protected const string TABLE = Table::USER;
    private const array TYPES = [
        'verified'  => 'boolean',
        'active'    => 'boolean',
        'roles'     => 'json',
        'user_data' => 'json',
    ];
    #[\Override]
    protected ?array $tables = [
        self::TABLE,
    ];

    public function init(): void
    {
        $this->initUserTable();
    }

    protected function insert(array $data): void
    {
        $this->connection->insert(self::TABLE, $data, self::TYPES);
    }

    protected function update(string $userId, array $data): void
    {
        $this->connection->update(
            self::TABLE,
            $data,
            ['user_id' => $userId],
            self::TYPES,
        );
    }

    protected function remove(string $userId): void
    {
        $this->connection->delete(self::TABLE, ['user_id' => $userId]);
    }

    private function initUserTable(): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
CREATE TABLE `{$tableName}` (
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `roles` json NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_count` int(11) DEFAULT 0 NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `user_data` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOT;

        $this->connection->executeQuery($sql);

        $sql = <<<EOT
ALTER TABLE `{$tableName}`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`) USING BTREE;
EOT;
        $this->connection->executeQuery($sql);
    }
}

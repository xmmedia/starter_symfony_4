<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Projection\Table;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

final class UserReadModel extends AbstractReadModel
{
    protected const string TABLE = Table::USER;
    protected ?array $tables = [
        self::TABLE,
    ];

    public function init(): void
    {
        $this->initUserTable();
        $this->initUserTotpTable();
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
            $types,
        );
    }

    protected function remove(string $userId): void
    {
        $this->connection->delete(self::TABLE, ['user_id' => $userId]);
    }

    protected function totpSetupRequested(string $userId, string $pendingSecret): void
    {
        $this->connection->executeStatement(
            \sprintf(
                'INSERT INTO `%s` (`user_id`, `totp_pending_secret`) VALUES (:userId, :pendingSecret)
                 ON DUPLICATE KEY UPDATE `totp_pending_secret` = :pendingSecret',
                Table::USER_TOTP,
            ),
            ['userId' => $userId, 'pendingSecret' => $pendingSecret],
        );
    }

    protected function totpEnable(string $userId): void
    {
        $this->connection->executeStatement(
            \sprintf(
                'UPDATE `%s` SET `totp_secret` = `totp_pending_secret`, `totp_pending_secret` = NULL WHERE `user_id` = :userId',
                Table::USER_TOTP,
            ),
            ['userId' => $userId],
        );
    }

    protected function totpDelete(string $userId): void
    {
        $this->connection->delete(Table::USER_TOTP, ['user_id' => $userId]);
    }

    private function initUserTable(): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
CREATE TABLE `{$tableName}` (
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `email` varchar(150) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `roles` json NOT NULL,
  `last_login` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
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

    private function initUserTotpTable(): void
    {
        $tableName = Table::USER_TOTP;

        $sql = <<<EOT
CREATE TABLE IF NOT EXISTS `{$tableName}` (
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `totp_secret` varchar(191) COLLATE utf8mb4_bin DEFAULT NULL,
  `totp_pending_secret` varchar(191) COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOT;

        $this->connection->executeQuery($sql);
    }
}

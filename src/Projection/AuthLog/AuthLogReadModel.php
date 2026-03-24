<?php

declare(strict_types=1);

namespace App\Projection\AuthLog;

use App\Projection\Table;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

final class AuthLogReadModel extends AbstractReadModel
{
    protected const string TABLE = Table::AUTH_LOG;

    public function init(): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
CREATE TABLE `{$tableName}` (
  `auth_log_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `event_type` varchar(50) COLLATE utf8mb4_bin NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `impersonated_user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `email` varchar(150) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_bin DEFAULT NULL,
  `route` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `error_message` varchar(500) COLLATE utf8mb4_bin DEFAULT NULL,
  `occurred_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOT;

        $this->connection->executeQuery($sql);

        $sql = <<<EOT
ALTER TABLE `{$tableName}`
  ADD PRIMARY KEY (`auth_log_id`),
  ADD INDEX `user_id` (`user_id`),
  ADD INDEX `impersonated_user_id` (`impersonated_user_id`),
  ADD INDEX `occurred_at` (`occurred_at`);
EOT;

        $this->connection->executeQuery($sql);
    }

    protected function insert(array $data, array $types = []): void
    {
        $this->connection->insert(self::TABLE, $data, $types);
    }
}

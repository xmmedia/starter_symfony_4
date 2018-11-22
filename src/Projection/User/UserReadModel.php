<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Projection\IsDeletableReadModel;
use App\Projection\IsInitializableReadModel;
use App\Projection\IsResetableReadModel;
use App\Projection\Table;
use Doctrine\DBAL\Connection;
use Prooph\EventStore\Projection\AbstractReadModel;

final class UserReadModel extends AbstractReadModel
{
    use IsInitializableReadModel;
    use IsResetableReadModel;
    use IsDeletableReadModel;

    protected const TABLE = Table::USER;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function init(): void
    {
        $tableName = Table::USER;

        $sql = <<<EOT
CREATE TABLE `$tableName` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `locked` tinyint(1) NOT NULL,
  `roles` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `confirmation_token` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_count` int(11) NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOT;

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        $sql = <<<EOT
ALTER TABLE `$tableName`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING BTREE;
COMMIT;
EOT;

        $statement = $this->connection->prepare($sql);
        $statement->execute();
    }

    protected function insert(array $data, array $types = []): void
    {
        $this->connection->insert(Table::USER, $data, $types);
    }
}

<?php

declare(strict_types=1);

namespace App\Projection\Enquiry;

use App\Projection\IsDeletableReadModel;
use App\Projection\IsInitializableReadModel;
use App\Projection\IsResetableReadModel;
use App\Projection\Table;
use Doctrine\DBAL\Connection;
use Prooph\EventStore\Projection\AbstractReadModel;

final class EnquiryReadModel extends AbstractReadModel
{
    use IsInitializableReadModel;
    use IsResetableReadModel;
    use IsDeletableReadModel;

    protected const TABLE = Table::ENQUIRY;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function init(): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
CREATE TABLE `$tableName` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitted` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOT;

        $statement = $this->connection->prepare($sql);
        $statement->execute();
    }

    protected function insert(array $data, array $types = []): void
    {
        $this->connection->insert(self::TABLE, $data, $types);
    }
}

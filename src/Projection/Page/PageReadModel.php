<?php

declare(strict_types=1);

namespace App\Projection\Page;

use App\Projection\Table;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

final class PageReadModel extends AbstractReadModel
{
    protected const TABLE = Table::PAGE;

    public function init(): void
    {
        $tableName = self::TABLE;

        $sql = <<<EOT
CREATE TABLE `$tableName` (
  `page_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:uuid)',
  `path` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` json NOT NULL,
  `last_modified` datetime(6) NOT NULL,
  `last_modified_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '(DC2Type:uuid)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOT;

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        $sql = <<<EOT
ALTER TABLE `$tableName`
  ADD PRIMARY KEY (`page_id`),
  ADD UNIQUE KEY `path` (`path`);
EOT;

        $statement = $this->connection->prepare($sql);
        $statement->execute();
    }

    protected function insert(array $data, array $types = []): void
    {
        $this->connection->insert(self::TABLE, $data, $types);
    }

    protected function update(string $pageId, array $data, array $types = []): void
    {
        $this->connection->update(
            self::TABLE,
            $data,
            ['page_id' => $pageId],
            $types
        );
    }

    // @todo delete
}

<?php

declare(strict_types=1);

namespace App\Projection\MessengerQueue;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class MessengerQueueMessageFinder
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function findByFilters(MessengerQueueMessageFilters $filters): array
    {
        $queryParts = new MessengerQueueMessageFilterQueryBuilder()->queryParts($filters);

        $offset = $filters->applied(MessengerQueueMessageFilters::OFFSET)
            ? (int) $filters->get(MessengerQueueMessageFilters::OFFSET)
            : 0;

        $sql = <<<Query
SELECT m.id, m.body, m.queue_name, m.created_at, m.available_at, m.delivered_at
FROM `messenger_messages` m
WHERE {$queryParts['where']}
ORDER BY {$queryParts['order']}
LIMIT {$offset}, 30
Query;

        $rows = $this->connection
            ->executeQuery($sql, $queryParts['parameters'], $queryParts['parameterTypes'])
            ->fetchAllAssociative();

        return array_map($this->hydrateRow(...), $rows);
    }

    public function countByFilters(MessengerQueueMessageFilters $filters): int
    {
        $queryParts = new MessengerQueueMessageFilterQueryBuilder()->queryParts($filters);

        $sql = <<<Query
SELECT COUNT(m.id)
FROM `messenger_messages` m
WHERE {$queryParts['where']}
Query;

        return (int) $this->connection
            ->executeQuery($sql, $queryParts['parameters'], $queryParts['parameterTypes'])
            ->fetchNumeric()[0];
    }

    public function find(int $id): ?array
    {
        $sql = <<<Query
SELECT m.id, m.body, m.queue_name, m.created_at, m.available_at, m.delivered_at
FROM `messenger_messages` m
WHERE m.id = :id
Query;

        $row = $this->connection
            ->executeQuery($sql, ['id' => $id])
            ->fetchAssociative();

        if (false === $row) {
            return null;
        }

        return $this->hydrateRow($row);
    }

    private function hydrateRow(array $row): array
    {
        try {
            $envelope = $this->serializer->decode(['body' => $row['body']]);
            $messageClass = $envelope->getMessage()::class;
        } catch (\Throwable) {
            $messageClass = null;
        }

        return [
            'id'           => (int) $row['id'],
            'queueName'    => $row['queue_name'],
            'messageClass' => $messageClass,
            'createdAt'    => $row['created_at'],
            'availableAt'  => $row['available_at'],
            'deliveredAt'  => $row['delivered_at'],
        ];
    }
}

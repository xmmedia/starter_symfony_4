<?php

declare(strict_types=1);

namespace App\Projection\MessengerQueue;

use Xm\SymfonyBundle\Doctrine\FilterQueryBuilder;
use Xm\SymfonyBundle\Util\FiltersInterface;

class MessengerQueueMessageFilterQueryBuilder extends FilterQueryBuilder
{
    protected string $order = 'm.created_at DESC';

    public function queryParts(MessengerQueueMessageFilters|FiltersInterface $filters): array
    {
        if ($filters->applied(MessengerQueueMessageFilters::QUEUE_NAME)) {
            $this->whereClauses[] = 'm.queue_name = :queueName';
            $this->parameters['queueName'] = $filters->get(MessengerQueueMessageFilters::QUEUE_NAME);
        }

        return [
            'join'           => implode(' ', $this->joins),
            'where'          => implode(' AND ', $this->whereClauses),
            'order'          => $this->order,
            'parameters'     => $this->parameters,
            'parameterTypes' => $this->parameterTypes,
        ];
    }
}

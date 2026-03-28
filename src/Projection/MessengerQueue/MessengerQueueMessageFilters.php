<?php

declare(strict_types=1);

namespace App\Projection\MessengerQueue;

use Xm\SymfonyBundle\Util\Filters;

class MessengerQueueMessageFilters extends Filters
{
    public const string QUEUE_NAME = 'queueName';
    public const string OFFSET = 'offset';

    #[\Override]
    protected function parseFilters(array $filters): array
    {
        return $filters;
    }
}

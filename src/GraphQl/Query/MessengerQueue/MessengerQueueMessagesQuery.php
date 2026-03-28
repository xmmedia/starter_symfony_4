<?php

declare(strict_types=1);

namespace App\GraphQl\Query\MessengerQueue;

use App\Projection\MessengerQueue\MessengerQueueMessageFilters;
use App\Projection\MessengerQueue\MessengerQueueMessageFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class MessengerQueueMessagesQuery implements QueryInterface
{
    public function __construct(private MessengerQueueMessageFinder $finder)
    {
    }

    public function __invoke(?array $filters): array
    {
        return $this->finder->findByFilters(MessengerQueueMessageFilters::fromArray($filters));
    }
}

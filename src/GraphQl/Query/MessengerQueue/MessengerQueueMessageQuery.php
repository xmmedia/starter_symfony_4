<?php

declare(strict_types=1);

namespace App\GraphQl\Query\MessengerQueue;

use App\Projection\MessengerQueue\MessengerQueueMessageFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class MessengerQueueMessageQuery implements QueryInterface
{
    public function __construct(private MessengerQueueMessageFinder $finder)
    {
    }

    public function __invoke(int $id): ?array
    {
        return $this->finder->find($id);
    }
}

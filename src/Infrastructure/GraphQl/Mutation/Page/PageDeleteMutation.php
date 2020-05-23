<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\Page;

use App\Model\Page\Command\DeletePage;
use App\Model\Page\PageId;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PageDeleteMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(string $pageId): array
    {
        $pageId = PageId::fromString($pageId);

        $this->commandBus->dispatch(
            DeletePage::now($pageId)
        );

        return [
            'pageId' => $pageId->toString(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\Page;

use App\Model\Page\Command\UnpublishPage;
use App\Model\Page\PageId;
use App\Projection\Page\PageFinder;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PageUnpublishMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var PageFinder */
    private $pageFinder;

    public function __construct(
        MessageBusInterface $commandBus,
        PageFinder $pageFinder
    ) {
        $this->commandBus = $commandBus;
        $this->pageFinder = $pageFinder;
    }

    public function __invoke(string $pageId): array
    {
        $pageId = PageId::fromString($pageId);

        $this->commandBus->dispatch(
            UnpublishPage::now($pageId)
        );

        return [
            'page' => $this->pageFinder->find($pageId),
        ];
    }
}

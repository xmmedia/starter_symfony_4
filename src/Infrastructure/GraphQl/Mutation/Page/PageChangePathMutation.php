<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\Page;

use App\Model\Page\Command\ChangePagePath;
use App\Model\Page\PageId;
use App\Model\Page\Path;
use App\Projection\Page\PageFinder;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PageChangePathMutation implements MutationInterface
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

    public function __invoke(string $pageId, string $newPath): array
    {
        $pageId = PageId::fromString($pageId);
        $newPath = Path::fromUserString($newPath);

        $this->commandBus->dispatch(
            ChangePagePath::to($pageId, $newPath)
        );

        return [
            'page' => $this->pageFinder->find($pageId),
        ];
    }
}

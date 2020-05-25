<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\Page;

use App\Model\Page\Command\UpdatePage;
use App\Model\Page\Content;
use App\Model\Page\PageId;
use App\Model\Page\Title;
use App\Projection\Page\PageFinder;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Util\Json;

class PageUpdateMutation implements MutationInterface
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

    public function __invoke(Argument $args): array
    {
        $pageId = PageId::fromString($args['pageId']);
        $title = Title::fromString($args['title']);
        $content = Content::fromArray(Json::decode($args['content']));

        $this->commandBus->dispatch(
            UpdatePage::to($pageId, $title, $content)
        );

        return [
            'page' => $this->pageFinder->find($pageId),
        ];
    }
}

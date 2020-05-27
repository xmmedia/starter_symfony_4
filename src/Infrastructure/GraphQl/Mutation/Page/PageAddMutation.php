<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\Page;

use App\Model\Page\Command\AddPage;
use App\Model\Page\Content;
use App\Model\Page\PageId;
use App\Model\Page\Path;
use App\Model\Page\Template;
use App\Model\Page\Title;
use App\Projection\Page\PageFinder;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PageAddMutation implements MutationInterface
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

    public function __invoke(array $page): array
    {
        $pageId = PageId::fromString($page['pageId']);
        $path = Path::fromUserString($page['path']);
        $template = Template::fromString($page['template']);
        $title = Title::fromString($page['title']);
        $content = Content::fromJson($page['content']);

        $this->commandBus->dispatch(
            AddPage::to($pageId, $path, $template, $title, $content)
        );

        return [
            'page' => $this->pageFinder->find($pageId),
        ];
    }
}

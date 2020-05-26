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
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Util\Json;

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

    public function __invoke(Argument $args): array
    {
        $pageId = PageId::fromString($args['pageId']);
        $path = Path::fromUserString($args['path']);
        $template = Template::fromString($args['template']);
        $title = Title::fromString($args['title']);
        $content = Content::fromArray(Json::decode($args['content']));

        $this->commandBus->dispatch(
            AddPage::to($pageId, $path, $template, $title, $content)
        );

        return [
            'page' => $this->pageFinder->find($pageId),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Page\Handler;

use App\Model\Page\Command\PublishPage;
use App\Model\Page\Exception\PageNotFound;
use App\Model\Page\PageList;

class PublishPageHandler
{
    /** @var PageList */
    private $pageRepo;

    public function __construct(PageList $pageRepo)
    {
        $this->pageRepo = $pageRepo;
    }

    public function __invoke(PublishPage $command): void
    {
        $page = $this->pageRepo->get($command->pageId());

        if (!$page) {
            throw PageNotFound::withPageId($command->pageId());
        }

        $page->publish();

        $this->pageRepo->save($page);
    }
}

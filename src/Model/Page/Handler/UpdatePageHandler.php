<?php

declare(strict_types=1);

namespace App\Model\Page\Handler;

use App\Model\Page\Command\UpdatePage;
use App\Model\Page\Exception\PageNotFound;
use App\Model\Page\PageList;

class UpdatePageHandler
{
    /** @var PageList */
    private $pageRepo;

    public function __construct(PageList $pageRepo)
    {
        $this->pageRepo = $pageRepo;
    }

    public function __invoke(UpdatePage $command): void
    {
        $page = $this->pageRepo->get($command->pageId());

        if (!$page) {
            throw PageNotFound::withPageId($command->pageId());
        }

        $page->update($command->title(), $command->content());

        $this->pageRepo->save($page);
    }
}

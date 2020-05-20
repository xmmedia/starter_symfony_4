<?php

declare(strict_types=1);

namespace App\Model\Page\Handler;

use App\Model\Page\Command\UnpublishPage;
use App\Model\Page\Exception\PageNotFound;
use App\Model\Page\PageList;
use App\Model\Page\Service\ChecksUniquePath;

class UnpublishPageHandler
{
    /** @var PageList */
    private $pageRepo;

    public function __construct(PageList $pageRepo)
    {
        $this->pageRepo = $pageRepo;
    }

    public function __invoke(UnpublishPage $command): void
    {
        $page = $this->pageRepo->get($command->pageId());

        if (!$page) {
            throw PageNotFound::withPageId($command->pageId());
        }

        $page->unpublish();

        $this->pageRepo->save($page);
    }
}

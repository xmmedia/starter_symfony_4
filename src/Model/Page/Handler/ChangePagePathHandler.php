<?php

declare(strict_types=1);

namespace App\Model\Page\Handler;

use App\Model\Page\Command\ChangePagePath;
use App\Model\Page\Exception\PageNotFound;
use App\Model\Page\PageList;
use App\Model\Page\Service\ChecksUniquePath;

class ChangePagePathHandler
{
    /** @var PageList */
    private $pageRepo;

    /** @var ChecksUniquePath */
    private $checksUniquePath;

    public function __construct(
        PageList $pageRepo,
        ChecksUniquePath $checksUniquePath
    ) {
        $this->pageRepo = $pageRepo;
        $this->checksUniquePath = $checksUniquePath;
    }

    public function __invoke(ChangePagePath $command): void
    {
        $page = $this->pageRepo->get($command->pageId());

        if (!$page) {
            throw PageNotFound::withPageId($command->pageId());
        }

        $page->changePath($command->path(), $this->checksUniquePath);

        $this->pageRepo->save($page);
    }
}

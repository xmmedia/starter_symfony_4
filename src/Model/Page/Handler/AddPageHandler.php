<?php

declare(strict_types=1);

namespace App\Model\Page\Handler;

use App\Model\Page\Command\AddPage;
use App\Model\Page\Page;
use App\Model\Page\PageList;
use App\Model\Page\Service\ChecksUniquePath;

class AddPageHandler
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

    public function __invoke(AddPage $command): void
    {
        $page = Page::add(
            $command->pageId(),
            $command->path(),
            $command->title(),
            $command->content(),
            $this->checksUniquePath
        );

        $this->pageRepo->save($page);
    }
}

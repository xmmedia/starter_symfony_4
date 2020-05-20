<?php

declare(strict_types=1);

namespace App\Model\Page\Handler;

use App\Model\Page\Command\AddPage;
use App\Model\Page\PageList;

class AddPageHandler
{
    /** @var PageList */
    private $pageRepo;

    public function __construct(PageList $pageRepo)
    {
        $this->pageRepo = $pageRepo;
    }

    public function __invoke(AddPage $command): void
    {
        $page = a::addTo(
            $command->PageId(),
            $command->parentId(),
        );

        $this->pageRepo->save($page);
    }
}

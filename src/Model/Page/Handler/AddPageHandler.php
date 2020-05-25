<?php

declare(strict_types=1);

namespace App\Model\Page\Handler;

use App\Model\Page\Command\AddPage;
use App\Model\Page\Page;
use App\Model\Page\PageList;
use App\Model\Page\Service\ChecksUniquePath;
use App\Model\Page\Service\PageContentValidator;

class AddPageHandler
{
    /** @var PageList */
    private $pageRepo;

    /** @var ChecksUniquePath */
    private $checksUniquePath;

    /** @var PageContentValidator */
    private $pageContentValidator;

    public function __construct(
        PageList $pageRepo,
        ChecksUniquePath $checksUniquePath,
        PageContentValidator $pageContentValidator
    ) {
        $this->pageRepo = $pageRepo;
        $this->checksUniquePath = $checksUniquePath;
        $this->pageContentValidator = $pageContentValidator;
    }

    public function __invoke(AddPage $command): void
    {
        $page = Page::add(
            $command->pageId(),
            $command->path(),
            $command->title(),
            $command->content(),
            $this->checksUniquePath,
            $this->pageContentValidator
        );

        $this->pageRepo->save($page);
    }
}

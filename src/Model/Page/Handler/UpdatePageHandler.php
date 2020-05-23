<?php

declare(strict_types=1);

namespace App\Model\Page\Handler;

use App\Model\Page\Command\UpdatePage;
use App\Model\Page\Exception\PageNotFound;
use App\Model\Page\PageList;
use App\Model\Page\Service\PageContentValidator;

class UpdatePageHandler
{
    /** @var PageList */
    private $pageRepo;

    /** @var PageContentValidator */
    private $pageContentValidator;

    public function __construct(
        PageList $pageRepo,
        PageContentValidator $pageContentValidator
    ) {
        $this->pageRepo = $pageRepo;
        $this->pageContentValidator = $pageContentValidator;
    }

    public function __invoke(UpdatePage $command): void
    {
        $page = $this->pageRepo->get($command->pageId());

        if (!$page) {
            throw PageNotFound::withPageId($command->pageId());
        }

        $page->update(
            $command->title(),
            $command->content(),
            $this->pageContentValidator
        );

        $this->pageRepo->save($page);
    }
}

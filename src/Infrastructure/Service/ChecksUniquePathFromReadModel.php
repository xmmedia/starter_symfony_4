<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Model\Page\PageId;
use App\Model\Page\Path;
use App\Model\Page\Service\ChecksUniquePath;
use App\Projection\Page\PageFinder;

class ChecksUniquePathFromReadModel implements ChecksUniquePath
{
    /** @var PageFinder */
    private $pageFinder;

    public function __construct(PageFinder $pageFinder)
    {
        $this->pageFinder = $pageFinder;
    }

    public function __invoke(Path $path): ?PageId
    {
        if ($page = $this->pageFinder->findOneByPath($path)) {
            return $page->pageId();
        }

        return null;
    }
}

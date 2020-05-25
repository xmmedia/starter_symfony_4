<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\Page;

use App\Entity\Page;
use App\Model\Page\PageId;
use App\Projection\Page\PageFinder;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class PageResolver implements ResolverInterface
{
    /** @var PageFinder */
    private $pageFinder;

    public function __construct(PageFinder $pageFinder)
    {
        $this->pageFinder = $pageFinder;
    }

    public function __invoke(string $pageId): ?Page
    {
        return $this->pageFinder->find(PageId::fromString($pageId));
    }
}

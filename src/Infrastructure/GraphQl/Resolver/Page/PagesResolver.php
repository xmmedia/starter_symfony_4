<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\Page;

use App\Projection\Page\PageFinder;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class PagesResolver implements ResolverInterface
{
    /** @var PageFinder */
    private $pageFinder;

    public function __construct(PageFinder $pageFinder)
    {
        $this->pageFinder = $pageFinder;
    }

    public function __invoke(): array
    {
        return $this->pageFinder->findAll();
    }
}

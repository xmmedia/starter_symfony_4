<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Model\Page\Path;
use App\Projection\Page\PageFinder;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class PageRouter implements RouteProviderInterface
{
    /** @var PageFinder */
    private $pageFinder;

    public function __construct(PageFinder $pageFinder)
    {
        $this->pageFinder = $pageFinder;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $collection = new RouteCollection();

        try {
            $path = Path::fromString($request->getPathInfo());
        } catch (\InvalidArgumentException $e) {
            return $collection;
        }

        $page = $this->pageFinder->findOneByPath($path);
        if ($page) {
            $collection->add(
                str_replace('/', '-', $page->path()),
                new Route($page->path(), ['page' => $page])
            );
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteByName($name)
    {
        // we don't implement this
        throw new RouteNotFoundException();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesByNames($names)
    {
        // we don't implement this
        return [];
    }
}

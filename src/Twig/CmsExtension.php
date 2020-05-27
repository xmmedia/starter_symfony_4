<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Page;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CmsExtension extends AbstractExtension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('content_item', [$this, 'contentItem']),
        ];
    }

    /**
     * @return mixed
     */
    public function contentItem(string $item, Page $page = null)
    {
        if (null === $page) {
            $page = $this->requestStack->getCurrentRequest()
                ->attributes
                ->get('page');

            if (!$page) {
                throw new \InvalidArgumentException('The page cannot be found in the request parameters/attributes. Pass it in instead.');
            }
        }

        if (!array_key_exists($item, $page->content())) {
            return null;
        }

        return $page->content()[$item]['value'];
    }
}

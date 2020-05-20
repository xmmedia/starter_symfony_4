<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Page;
use App\Infrastructure\Service\PageTitleGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContentExtension extends AbstractExtension
{
    /** @var PageTitleGenerator */
    private $pageTitleGenerator;

    public function __construct(PageTitleGenerator $pageTitleGenerator)
    {
        $this->pageTitleGenerator = $pageTitleGenerator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('page_title', [$this, 'pageTitle']),
        ];
    }

    public function pageTitle(Page $page): string
    {
        return ($this->pageTitleGenerator)($page);
    }
}

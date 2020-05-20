<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Entity\Page;
use App\Model\Page\Path;
use App\Projection\Page\PageFinder;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageTitleGenerator implements \App\Model\Page\Service\PageTitle
{
    /** @var PageFinder */
    private $pageFinder;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        PageFinder $pageFinder,
        TranslatorInterface $translator
    ) {
        $this->pageFinder = $pageFinder;
        $this->translator = $translator;
    }

    public function __invoke(Page $page): string
    {
        $titleParts = [
            $page->title(),
        ];

        $pathParts = explode('/', substr($page->path(), 1));
        if (\count($pathParts) > 1) {
            do {
                array_pop($pathParts);

                $parentPage = $this->pageFinder->findOneByPath(
                    Path::fromString('/'.implode('/', $pathParts))
                );
                if ($parentPage) {
                    $titleParts[] = $parentPage->title();
                }
            } while ($parentPage && \count($pathParts) > 1);
        }

        $titleParts[] = $this->translator->trans('app.parameter.name');

        return implode(' | ', $titleParts);
    }
}

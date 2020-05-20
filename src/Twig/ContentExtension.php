<?php

declare(strict_types=1);

namespace App\Twig;

use App\Document\Page;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContentExtension extends AbstractExtension
{
    /** @var string */
    private $contentBasePath;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        string $contentBasePath,
        TranslatorInterface $translator
    ) {
        $this->contentBasePath = $contentBasePath;
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('page_title', [$this, 'pageTitle']),
        ];
    }

    public function pageTitle(Page $page): string
    {
        $titleParts = [
            $page->title(),
        ];

        $parent = $page->parentDocument();
        if ($parent instanceof Page) {
            $titleParts[] = $parent->title();
        }

        if ($parent instanceof Page) {
            $parent2 = $parent->parentDocument();
            if ($parent2 instanceof Page) {
                $titleParts[] = $parent2->title();
            }
        }

        $titleParts[] = $this->translator->trans('app.parameter.name');

        return implode(' | ', $titleParts);
    }
}

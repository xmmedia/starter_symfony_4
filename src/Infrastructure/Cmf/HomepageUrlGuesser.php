<?php

declare(strict_types=1);

namespace App\Infrastructure\Cmf;

use App\Document\Page;
use Symfony\Cmf\Bundle\SeoBundle\Model\UrlInformation;
use Symfony\Cmf\Bundle\SeoBundle\Sitemap\GuesserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomepageUrlGuesser implements GuesserInterface
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->urlGenerator = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function guessValues(UrlInformation $urlInformation, $object, $sitemap)
    {
        if (!$object instanceof Page) {
            return;
        }

        if (!$object->isHomepage()) {
            return;
        }

        if ($urlInformation->getLocation()) {
            return;
        }

        $urlInformation->setLocation(
            $this->urlGenerator->generate(
                'index',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }
}

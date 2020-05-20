<?php

declare(strict_types=1);

namespace App\Infrastructure\Cmf;

use App\Document\Page;
use Symfony\Cmf\Bundle\SeoBundle\Model\UrlInformation;
use Symfony\Cmf\Bundle\SeoBundle\Sitemap\GuesserInterface;

class LastModifiedGuesser implements GuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function guessValues(UrlInformation $urlInformation, $object, $sitemap)
    {
        if (!$object instanceof Page) {
            return;
        }

        if ($object->lastModified()) {
            $urlInformation->setLastModification($object->lastModified());

            return;
        }

        if ($object->created()) {
            $urlInformation->setLastModification($object->created());
        }
    }
}

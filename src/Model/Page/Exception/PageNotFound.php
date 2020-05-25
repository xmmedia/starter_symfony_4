<?php

declare(strict_types=1);

namespace App\Model\Page\Exception;

use App\Model\Page\PageId;

final class PageNotFound extends \InvalidArgumentException
{
    public static function withPageId(PageId $page): self
    {
        return new self(
            sprintf(
                'Page with ID "%s" cannot be found.',
                $page->toString()
            )
        );
    }
}

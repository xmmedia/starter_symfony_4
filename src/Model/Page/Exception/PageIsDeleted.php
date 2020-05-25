<?php

declare(strict_types=1);

namespace App\Model\Page\Exception;

use App\Model\Page\PageId;

final class PageIsDeleted extends \RuntimeException
{
    public static function triedTo(PageId $pageId, string $to): self
    {
        return new self(
            sprintf(
                'Tried to "%s" on page "%s" that\'s deleted.',
                $to,
                $pageId
            )
        );
    }
}

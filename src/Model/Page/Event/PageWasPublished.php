<?php

declare(strict_types=1);

namespace App\Model\Page\Event;

use App\Model\Page\PageId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class PageWasPublished extends AggregateChanged
{
    public static function now(PageId $pageId): self
    {
        $event = self::occur($pageId->toString());

        return $event;
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->aggregateId());
    }
}

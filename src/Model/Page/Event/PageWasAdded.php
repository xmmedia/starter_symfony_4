<?php

declare(strict_types=1);

namespace App\Model\Page\Event;

use App\Model\Page\PageId;
use App\Model\Page\ParentId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class PageWasAdded extends AggregateChanged
{
    /** @var ParentId */
    private $parentId;

    public static function to(
        PageId $page,
        ParentId $parentId
    ): self {
        $event = self::occur($page->toString(), [
            'parentId' => $parentId->toString(),
        ]);

        $event->parentId = $parentId;

        return $event;
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->aggregateId());
    }

    public function parentId(): ParentId
    {
        if (null === $this->parentId) {
            $this->parentId = ParentId::fromString($this->payload()['parentId']);
        }

        return $this->parentId;
    }
}

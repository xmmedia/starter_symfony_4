<?php

declare(strict_types=1);

namespace App\Model\Page;

use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Entity;

class Page extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    /** @var PageId */
    private $pageId;

    /** @var ParentId */
    private $parentId;

    /** @var bool */
    private $deleted = false;

    public static function addTo(
        PageId $page,
        ParentId $parentId
    ): self {
        $self = new self();
        $self->recordThat(
            Event\PageWasAdded::toPage(
                $page,
                $parentId,
            )
        );

        return $self;
    }

    public function parentId(): ParentId
    {
        return $this->parentId;
    }

    protected function aggregateId(): string
    {
        return $this->pageId->toString();
    }

    protected function whenPageWasAdded(Event\PageWasAdded $event): void
    {
        $this->pageId = $event->pageId();
        $this->parentId = $event->parentId();
    }

    /**
     * @param Page|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this->pageId->sameValueAs($other->pageId);
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Page;

use App\Model\Page\Service\ChecksUniquePath;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Entity;

class Page extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    /** @var PageId */
    private $pageId;

    /** @var bool */
    private $deleted = false;

    public static function add(
        PageId $page,
        Path $path,
        Title $title,
        Content $content,
        ChecksUniquePath $checksUniquePath
    ): self {
        if (null !== $checksUniquePath($path)) {
            throw new \InvalidArgumentException(sprintf('The path "%s" is not unique', $path));
        }

        $self = new self();
        $self->recordThat(
            Event\PageWasAdded::now(
                $page,
                $path,
                $title,
                $content
            )
        );

        return $self;
    }

    protected function aggregateId(): string
    {
        return $this->pageId->toString();
    }

    protected function whenPageWasAdded(Event\PageWasAdded $event): void
    {
        $this->pageId = $event->pageId();
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

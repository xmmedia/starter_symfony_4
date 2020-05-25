<?php

declare(strict_types=1);

namespace App\Model\Page;

use App\Model\Page\Service\ChecksUniquePath;
use App\Model\Page\Service\PageContentValidator;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Entity;

class Page extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    /** @var PageId */
    private $pageId;

    /** @var Path */
    private $path;

    /** @var bool */
    private $published = false;

    /** @var bool */
    private $deleted = false;

    public static function add(
        PageId $page,
        Path $path,
        Title $title,
        Content $content,
        ChecksUniquePath $checksUniquePath,
        PageContentValidator $pageContentValidator
    ): self {
        if (null !== $checksUniquePath($path)) {
            throw new \InvalidArgumentException(sprintf('The path "%s" is not unique', $path));
        }

        $pageContentValidator($content);

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

    public function publish(): void
    {
        if ($this->deleted) {
            throw Exception\PageIsDeleted::triedTo($this->pageId, 'publish');
        }

        if ($this->published) {
            return;
        }

        $this->recordThat(Event\PageWasPublished::now($this->pageId));
    }

    public function unpublish(): void
    {
        if ($this->deleted) {
            throw Exception\PageIsDeleted::triedTo($this->pageId, 'unpublish');
        }

        if (!$this->published) {
            return;
        }

        $this->recordThat(Event\PageWasUnpublished::now($this->pageId));
    }

    public function update(
        Title $title,
        Content $content,
        PageContentValidator $pageContentValidator
    ): void {
        if ($this->deleted) {
            throw Exception\PageIsDeleted::triedTo($this->pageId, 'update');
        }

        $pageContentValidator($content);

        $this->recordThat(Event\PageWasUpdated::now($this->pageId, $title, $content));
    }

    public function changePath(
        Path $newPath,
        ChecksUniquePath $checksUniquePath
    ): void {
        if ($this->deleted) {
            throw Exception\PageIsDeleted::triedTo($this->pageId, 'change path');
        }

        if ($newPath->sameValueAs($this->path)) {
            return;
        }

        // we don't check for matching the current page
        // if the path is the same, it won't make it here
        if (null !== $checksUniquePath($newPath)) {
            throw new \InvalidArgumentException(sprintf('The path "%s" is not unique', $newPath));
        }

        $this->recordThat(Event\PagePathWasChanged::now($this->pageId, $newPath, $this->path));
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw Exception\PageIsDeleted::triedTo($this->pageId, 'delete');
        }

        $this->recordThat(Event\PageWasDeleted::now($this->pageId));
    }

    protected function aggregateId(): string
    {
        return $this->pageId->toString();
    }

    protected function whenPageWasAdded(Event\PageWasAdded $event): void
    {
        $this->pageId = $event->pageId();
        $this->path = $event->path();
    }

    protected function whenPageWasPublished(Event\PageWasPublished $event): void
    {
        $this->published = true;
    }

    protected function whenPageWasUnpublished(Event\PageWasUnpublished $event): void
    {
        $this->published = false;
    }

    protected function whenPageWasUpdated(Event\PageWasUpdated $event): void
    {
        // noop
    }

    protected function whenPagePathWasChanged(Event\PagePathWasChanged $event): void
    {
        $this->path = $event->newPath();
    }

    protected function whenPageWasDeleted(Event\PageWasDeleted $event): void
    {
        $this->deleted = true;
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

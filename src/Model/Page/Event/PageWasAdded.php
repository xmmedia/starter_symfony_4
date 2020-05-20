<?php

declare(strict_types=1);

namespace App\Model\Page\Event;

use App\Model\Page\Content;
use App\Model\Page\PageId;
use App\Model\Page\Path;
use App\Model\Page\Title;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class PageWasAdded extends AggregateChanged
{
    /** @var Path */
    private $path;

    /** @var Title */
    private $title;

    /** @var Content */
    private $content;

    public static function now(
        PageId $pageId,
        Path $path,
        Title $title,
        Content $content
    ): self {
        $event = self::occur($pageId->toString(), [
            'path'    => $path->toString(),
            'title'   => $title->toString(),
            'content' => $content->toArray(),
        ]);

        $event->path = $path;
        $event->title = $title;
        $event->content = $content;

        return $event;
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->aggregateId());
    }

    public function path(): Path
    {
        if (null === $this->path) {
            $this->path = Path::fromString($this->payload()['path']);
        }

        return $this->path;
    }

    public function title(): Title
    {
        if (null === $this->title) {
            $this->title = Title::fromString($this->payload()['title']);
        }

        return $this->title;
    }

    public function content(): Content
    {
        if (null === $this->content) {
            $this->content = Content::fromArray($this->payload()['content']);
        }

        return $this->content;
    }
}

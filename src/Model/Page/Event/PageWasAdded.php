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
    /** @var string */
    private $path;

    public static function now(
        PageId $page,
        Path $path,
        Title $title,
        Content $content
    ): self {
        $event = self::occur($page->toString(), [
            'path'    => $path->toString(),
            'title'   => $title->toString(),
            'content' => $content->toArray(),
        ]);

        $event->path = $path;

        return $event;
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->aggregateId());
    }

    public function path(): Path
    {
        return Path::fromString($this->payload()['path']);
    }

    public function title(): Title
    {
        return Title::fromString($this->payload()['title']);
    }

    public function content(): Content
    {
        return Content::fromArray($this->payload()['content']);
    }
}

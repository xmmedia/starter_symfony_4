<?php

declare(strict_types=1);

namespace App\Model\Page\Event;

use App\Model\Page\Content;
use App\Model\Page\PageId;
use App\Model\Page\Title;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class PageWasUpdated extends AggregateChanged
{
    /** @var Title */
    private $title;

    /** @var Content */
    private $content;

    public static function now(
        PageId $pageId,
        Title $title,
        Content $content
    ): self {
        $event = self::occur($pageId->toString(), [
            'title'   => $title->toString(),
            'content' => $content->toArray(),
        ]);

        $event->title = $title;
        $event->content = $content;

        return $event;
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->aggregateId());
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

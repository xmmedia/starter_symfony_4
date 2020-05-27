<?php

declare(strict_types=1);

namespace App\Model\Page\Event;

use App\Model\Page\PageId;
use App\Model\Page\Path;
use App\Model\Page\Template;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class PageTemplateWasChanged extends AggregateChanged
{
    /** @var Path */
    private $newTemplate;

    /** @var Path */
    private $oldTemplate;

    public static function now(
        PageId $pageId,
        Template $newTemplate,
        Template $oldTemplate
    ): self {
        $event = self::occur($pageId->toString(), [
            'newTemplate' => $newTemplate->toString(),
            'oldTemplate' => $oldTemplate->toString(),
        ]);

        $event->newTemplate = $newTemplate;
        $event->oldTemplate = $oldTemplate;

        return $event;
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->aggregateId());
    }

    public function newTemplate(): Template
    {
        if (null === $this->newTemplate) {
            $this->newTemplate = Template::fromString($this->payload()['newTemplate']);
        }

        return $this->newTemplate;
    }

    public function oldTemplate(): Template
    {
        if (null === $this->oldTemplate) {
            $this->oldTemplate = Template::fromString($this->payload()['oldTemplate']);
        }

        return $this->oldTemplate;
    }
}

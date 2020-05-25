<?php

declare(strict_types=1);

namespace App\Model\Page\Event;

use App\Model\Page\PageId;
use App\Model\Page\Path;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class PagePathWasChanged extends AggregateChanged
{
    /** @var Path */
    private $newPath;

    /** @var Path */
    private $oldPath;

    public static function now(
        PageId $pageId,
        Path $newPath,
        Path $oldPath
    ): self {
        $event = self::occur($pageId->toString(), [
            'newPath' => $newPath->toString(),
            'oldPath' => $oldPath->toString(),
        ]);

        $event->newPath = $newPath;
        $event->oldPath = $oldPath;

        return $event;
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->aggregateId());
    }

    public function newPath(): Path
    {
        if (null === $this->newPath) {
            $this->newPath = Path::fromString($this->payload()['newPath']);
        }

        return $this->newPath;
    }

    public function oldPath(): Path
    {
        if (null === $this->oldPath) {
            $this->oldPath = Path::fromString($this->payload()['oldPath']);
        }

        return $this->oldPath;
    }
}

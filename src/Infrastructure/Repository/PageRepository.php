<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Model\Page\Page;
use App\Model\Page\PageId;
use App\Model\Page\PageList;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRepository;

final class PageRepository extends AggregateRepository implements PageList
{
    public function save(Page $page): void
    {
        $this->saveAggregateRoot($page);
    }

    public function get(PageId $pageId): ?Page
    {
        return $this->getAggregateRoot($pageId->toString());
    }
}

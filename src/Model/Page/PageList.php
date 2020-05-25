<?php

declare(strict_types=1);

namespace App\Model\Page;

interface PageList
{
    public function save(Page $page): void;

    public function get(PageId $page): ?Page;
}

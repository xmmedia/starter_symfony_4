<?php

declare(strict_types=1);

namespace App\Model\Page\Service;

use App\Entity\Page;

interface PageTitle
{
    public function __invoke(Page $page): string;
}

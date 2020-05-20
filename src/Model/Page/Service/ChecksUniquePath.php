<?php

declare(strict_types=1);

namespace App\Model\Page\Service;

use App\Model\Page\PageId;
use App\Model\Page\Path;

interface ChecksUniquePath
{
    public function __invoke(Path $path): ?PageId;
}

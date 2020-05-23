<?php

declare(strict_types=1);

namespace App\Model\Page\Service;

use App\Model\Page\Content;

interface PageContentValidator
{
    public function __invoke(Content $content): void;
}

<?php

declare(strict_types=1);

namespace App\Model\Page\Service;

use App\Model\Page\Content;
use App\Model\Page\Template;

interface PageContentValidator
{
    public function __invoke(Template $template, Content $content): void;
}

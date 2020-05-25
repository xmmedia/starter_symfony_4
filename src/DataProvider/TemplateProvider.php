<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Model\Page\Template;

class TemplateProvider
{
    /** @var array */
    private $templates;

    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return Template[]
     */
    public function __invoke(): array
    {
        $templates = [];

        foreach ($this->templates as $template => $config) {
            $templates[$template] = Template::fromArray($template, $config);
        }

        return $templates;
    }
}

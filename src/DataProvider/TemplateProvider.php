<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Model\Page\TemplateConfig;

class TemplateProvider
{
    /** @var array */
    private $templates;

    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return TemplateConfig[]
     */
    public function __invoke(): array
    {
        $templates = [];

        foreach ($this->templates as $template => $config) {
            $templates[$template] = TemplateConfig::fromArray($template, $config);
        }

        return $templates;
    }
}

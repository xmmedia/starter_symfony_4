<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\Template;

use App\DataProvider\TemplateProvider;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class TemplateResolver implements ResolverInterface
{
    /** @var TemplateProvider */
    private $templateProvider;

    public function __construct(TemplateProvider $templateProvider)
    {
        $this->templateProvider = $templateProvider;
    }

    public function __invoke(string $template): array
    {
        return ($this->templateProvider)()[$template]->toArray();
    }
}

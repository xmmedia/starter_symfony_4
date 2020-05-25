<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\Template;

use App\DataProvider\TemplateProvider;
use App\Model\Page\Template;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class TemplatesResolver implements ResolverInterface
{
    /** @var TemplateProvider */
    private $templateProvider;

    public function __construct(TemplateProvider $templateProvider)
    {
        $this->templateProvider = $templateProvider;
    }

    public function __invoke(): array
    {
        return array_map(
            function (Template $template): array {
                return $template->toArray();
            },
            ($this->templateProvider)()
        );
    }
}

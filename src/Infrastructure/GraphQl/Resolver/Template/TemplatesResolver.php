<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\Template;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Xm\SymfonyBundle\Util\Json;

class TemplatesResolver implements ResolverInterface
{
    /** @var array */
    private $templates;

    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    public function __invoke(): array
    {
        dump($this->templates);
        return array_map(
            function (string $template, array $config): array {
                return [
                    'template' => $template,
                    'name'     => $config['name'],
                    'default'  => $config['default'],
                    'items'    => array_map(
                        function (string $item, array $config): array {
                            return [
                                'name'   => $item,
                                'type'   => $config['type'],
                                'required'   => $config['required'],
                                'config' => Json::encode($config),
                            ];
                        },
                        array_keys($config['items']),
                        $config['items']
                    ),
                ];
            },
            array_keys($this->templates),
            $this->templates
        );
    }
}

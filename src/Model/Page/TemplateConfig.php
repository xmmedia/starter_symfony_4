<?php

declare(strict_types=1);

namespace App\Model\Page;

use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\Json;

class TemplateConfig implements ValueObject
{
    /** @var string */
    private $template;

    /** @var array */
    private $config;

    public static function fromArray(string $template, array $config): self
    {
        return new self($template, $config);
    }

    private function __construct(string $template, array $config)
    {
        $this->template = $template;
        $this->config = $config;
    }

    public function default(): bool
    {
        return $this->config['default'];
    }

    public function toArray(): array
    {
        return [
            'template' => $this->template,
            'name'     => $this->config['name'],
            'default'  => $this->config['default'],
            'items'    => array_map(
                function (string $item, array $config): array {
                    return [
                        'name'     => $item,
                        'type'     => $config['type'],
                        'required' => $config['required'],
                        'config'   => Json::encode($config),
                    ];
                },
                array_keys($this->config['items']),
                $this->config['items']
            ),
        ];
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this->template === $other->template;
    }
}

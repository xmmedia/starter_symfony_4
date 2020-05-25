<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class CmsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new CmsConfiguration(), $configs);

        $config['templates'] = $this->cleanupItemParams($config['templates']);

        $container->setParameter('cms.templates', $config['templates']);
    }

    private function cleanupItemParams(array $templates): array
    {
        foreach ($templates as $template => $config) {
            foreach ($config['items'] as $item => $itemConfig) {
                foreach ($itemConfig['params'] as $param) {
                    $templates[$template]['items'][$item][$param[0]] = $param[1];
                }
            }
        }

        return $templates;
    }
}

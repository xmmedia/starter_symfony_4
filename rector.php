<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $config): void {
    $config->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // Define what rule sets will be applied
    $config->import(SetList::DEAD_CODE);
    $config->import(DoctrineSetList::DOCTRINE_CODE_QUALITY);
    $config->import(DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES);
    $config->import(SymfonySetList::SYMFONY_54);
    $config->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $config->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $config->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);

    // get services (needed for register a single rule)
    $config->services();
};

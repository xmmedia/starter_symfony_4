<?php

declare(strict_types=1);

// Great tool for configuration: https://mlocati.github.io/php-cs-fixer-configurator/

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->append(['.php-cs-fixer.dist.php']) // include this file as well
    ->exclude('var')
    ->exclude('cache')
    // exclude next two because of the size of these dirs
    ->exclude('node_modules')
    ->exclude('public/js');

return new PhpCsFixer\Config()
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                    => true,
        '@Symfony:risky'              => true,
        '@PSR2'                       => true,
        '@DoctrineAnnotation'         => true,
        // this will include all the rules for earlier PHP versions
        '@PHP8x4Migration'             => true,
        '@PHPUnit10x0Migration:risky'  => true,
        'array_syntax'                 => [
            'syntax' => 'short',
        ],
        'binary_operator_spaces'      => [
            'operators' => [
                '=>' => 'align',
            ],
        ],
        'declare_strict_types'        => true,
        'fopen_flags'                 => false,
        'heredoc_indentation'         => false,
        'method_argument_space'       => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'ordered_imports'             => true,
        'protected_to_private'        => false,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
        'single_line_throw'           => false,
    ])
    ->setFinder($finder);

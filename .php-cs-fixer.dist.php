<?php

declare(strict_types=1);

// Great tool for configuration: https://mlocati.github.io/php-cs-fixer-configurator/

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('cache')
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
        '@PHP84Migration'             => true,
        '@PHPUnit60Migration:risky'   => true,
        'array_syntax'                => [
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

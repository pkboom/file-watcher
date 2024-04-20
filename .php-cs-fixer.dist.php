<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRules([
        '@PhpCsFixer' => true,
        'not_operator_with_successor_space' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'multiline_whitespace_before_semicolons' => true,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
        'global_namespace_import' => true,
        'phpdoc_add_missing_param_annotation' => false,
        'ordered_class_elements' => [
            'order' => ['use_trait'],
        ],
        'yoda_style' => [
            'identical' => null,
            'equal' => null,
        ],
    ])
    ->setFinder($finder);

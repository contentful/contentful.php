<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return function (string $packageName, bool $usePhp7, array $directories, array $exclude = []): Config {
    $year = date('Y');

    $fileHeaderComment = <<<COMMENT
        This file is part of the contentful/$packageName package.

        @copyright 2015-$year Contentful GmbH
        @license   MIT
        COMMENT;

    $finder = Finder::create();
    foreach ($directories as $directory) {
        $finder = $finder->in($directory);
    }

    foreach ($exclude as $path) {
        $finder = $finder->notPath($path);
    }

    $rules = [
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP80Migration' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $fileHeaderComment,
            'location' => 'after_open',
            'separate' => 'both',
        ],
        'linebreak_after_opening_tag' => true,
        'logical_operators' => true,
        'mb_str_functions' => true,
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'new_line_for_chained_calls',
        ],
        'multiline_comment_opening_closing' => true,
        'native_constant_invocation' => [
            'exclude' => ['null', 'false', 'true'],
        ],
        'native_function_invocation' => true,
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_strict' => false,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'semicolon_after_instruction' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ];

    if ($usePhp7) {
        $rules['declare_strict_types'] = true;
        $rules['ternary_to_null_coalescing'] = true;
    }

    $cache = tempnam(sys_get_temp_dir(), $packageName).'-php_cs.cache';

    return (new Config())
        ->setFinder($finder)
        ->setRiskyAllowed(true)
        ->setCacheFile($cache)
        ->setRules($rules)
    ;
};

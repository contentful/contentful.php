<?php

$year = date('Y');

$fileHeaderComment = <<<COMMENT
This file is part of the contentful/contentful package.

@copyright 2015-$year Contentful GmbH
@license   MIT
COMMENT;

$finder = PhpCsFixer\Finder::create()
    ->in('bin')
    ->in('extra')
    ->in('scripts')
    ->in('src')
    ->in('tests')
;

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__.'/.php_cs.cache')
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'header_comment' => [
            'commentType' => 'PHPDoc',
            'header' => $fileHeaderComment,
            'separate' => 'both',
        ],
        'linebreak_after_opening_tag' => true,
        'mb_str_functions' => true,
        'native_function_invocation' => true,
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'phpdoc_order' => true,
        'semicolon_after_instruction' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ])
;

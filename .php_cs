<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in('src')
    ->in('tests');

return Config::create()
    ->setFinder($finder)
    ->setRules([
      '@PSR2' => true,
      'array_syntax' => ['syntax' => 'short'],
      'blank_line_after_opening_tag' => true,
      'blank_line_before_return' => true,
      'cast_spaces' => true,
      'concat_space' => ['spacing' => 'one'],
      'function_typehint_space' => true,
      'hash_to_slash_comment' => true,
      'include' => true,
      'lowercase_cast' => true,
      'magic_constant_casing' => true,
      'method_separation' => true,
      'native_function_casing' => true,
      'no_blank_lines_after_class_opening' => true,
      'no_empty_comment' => true,
      'no_empty_phpdoc' => true,
      'no_empty_statement' => true,
      'no_extra_consecutive_blank_lines' => true,
      'no_short_bool_cast' => true,
      'no_unneeded_control_parentheses' => true,
      'no_unused_imports' => true,
      'no_useless_else' => true,
      'no_useless_return' => true,
      'no_whitespace_in_blank_line' => true,
      'normalize_index_brace' => true,
      'object_operator_without_whitespace' => true,
      'self_accessor' => true,
      'short_scalar_cast' => true,
      'single_blank_line_before_namespace' => true,
      'standardize_not_equals' => true,
      'ternary_operator_spaces' => true,
      'trim_array_spaces' => true,
      'whitespace_after_comma_in_array' => true
    ])
    ->setUsingCache(true);

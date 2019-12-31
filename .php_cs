<?php

/**
 * @noinspection PhpUndefinedNamespaceInspection
 * @noinspection PhpUndefinedClassInspection
 */
return PhpCsFixer\Config::create()
    ->setRules(
        [
            '@PSR1' => true,
            '@PSR2' => true,
            '@PhpCsFixer' => true,

            'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
            'phpdoc_align' => ['align' => 'left'],
        ]
    );

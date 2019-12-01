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

            'array_syntax' => ['syntax' => 'short'],
            'concat_space' => ['spacing' => 'none'],
            'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
            'no_extra_blank_lines' => ['tokens' => ['extra']],
        ]
    );

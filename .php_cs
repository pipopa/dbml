<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2'                              => true,
        'braces'                             => false,
        'method_argument_space'              => false,
        'single_blank_line_at_eof'           => false,
        'no_break_comment'                   => false,
        'single_class_element_per_statement' => false,
        'array_syntax'                       => [
            'syntax' => 'short',
        ],
    ])
;

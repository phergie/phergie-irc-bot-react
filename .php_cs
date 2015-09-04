<?php
/*      // This must be used with 'header_comment', but then a config file is needed for each repository
$header = <<<EOF
Phergie (http://phergie.org)

@link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
@copyright Copyright (c) 2008-2015 Phergie Development Team (http://phergie.org)
@license http://phergie.org/license Simplified BSD License
@package Phergie\Irc\Bot\React
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);
*/


$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__.'/src')
;

return Symfony\CS\Config\Config::create()
    // All PSR-1 and PSR-2 fixers are included here.
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)

    ->fixers([
        //  Accepted styling
        'short_array_syntax',               // Arrays should use the PHP 5.4 short-syntax.
        'multiline_array_trailing_comma',   // Multi-line arrays should have a trailing comma.
        'single_array_no_trailing_comma',   // Single-line arrays should not have trailing comma.
        'unalign_double_arrow',             // Unalign double arrow symbols.

        //  Suggested styling
        //'single_quote',                   // ? Convert double quotes to single quotes for simple strings.
        'standardize_not_equal',            // Replace all <> with !=.
        'unalign_equals',                   // Unalign equals symbols.
        'unused_use',                       // Unused use statements must be removed.
        'whitespacy_lines',                 // Remove trailing whitespace at the end of blank lines.

        //  Non-essential styling
        //'header_comment',                 // ? Set header comment from this file on php files
        'phpdoc_order',                     // Annotations in phpdocs should be ordered so that param annotations come first, then throws annotations, then return annotations.
    ])

    ->finder($finder);

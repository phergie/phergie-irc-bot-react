<?php

class ShortArraySpacesFixer extends Symfony\CS\AbstractFixer
{
    private static $emptySpace = false;

    /**
     * Adds a space in empty arrays declarations.
     *
     * @param boolean $emptySpace default: false
     */
    public static function setEmptySpace($emptySpace)
    {
        $this->emptySpace = $emptySpace;
    }

    public function fix(\SplFileInfo $file, $content)
    {
        $tokens = Symfony\CS\Tokenizer\Tokens::fromCode($content);
        for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
            if (!$tokens->isShortArray($index)) {
                continue;
            }

            if (!$tokens[$index + 1]->isWhiteSpace()) {
                $tokens->insertAt($index + 1, new Symfony\CS\Tokenizer\Token([ T_WHITESPACE, ' ' ]));
            }

            $closeIndex = $tokens->findBlockEnd(Symfony\CS\Tokenizer\Tokens::BLOCK_TYPE_SQUARE_BRACE, $index);
            if (!$tokens[$closeIndex - 1]->isWhiteSpace()) {
                $tokens->insertAt($closeIndex, new Symfony\CS\Tokenizer\Token([ T_WHITESPACE, ' ' ]));
            }

            if ($tokens->getNextNonWhiteSpace($index) === ($closeIndex = $tokens->findBlockEnd(Symfony\CS\Tokenizer\Tokens::BLOCK_TYPE_SQUARE_BRACE, $index))) {
                $tokens->clearRange($index + 1, $closeIndex - 1);
                if (self::$emptySpace) {
                    $tokens->insertAt($index + 1, new Symfony\CS\Tokenizer\Token([ T_WHITESPACE, ' ' ]));
                }
            }
        }
        return $tokens->generateCode();
    }

    public function getDescription()
    {
        return 'PHP short arrays should have spaces after opening bracket and before closing bracket';
    }

    public function getLevel()
    {
        return Symfony\CS\FixerInterface::CONTRIB_LEVEL;
    }

    public function getPriority()
    {
        return -10;
    }
}

class MultilineOperatorsFixer extends Symfony\CS\AbstractFixer
{
    public function fix(\SplFileInfo $file, $content)
    {
        $tokens = Symfony\CS\Tokenizer\Tokens::fromCode($content);
        for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
            if (!$tokens->isBinaryOperator($index)) {
                continue;
            }

            $nextToken = $tokens[$index + 1];
            if ($nextToken->isWhitespace() && !$nextToken->isWhitespace([ 'whitespaces' => " \t" ])) {
                $indent = explode("\n", $nextToken->getContent());
                $nextToken->setContent(rtrim($nextToken->getContent()).' ');

                $prevToken = $tokens[$index - 1];
                if (!$prevToken->isWhitespace()) {
                    $tokens->insertAt($index, new Symfony\CS\Tokenizer\Token([ T_WHITESPACE, "\n" . end($indent) ]));
                } elseif ($prevToken->isWhitespace([ 'whitespaces' => " \t" ])) {
                    $prevToken->setContent("\n" . end($indent) . ltrim($prevToken->getContent()));
                }
            }
        }
        return $tokens->generateCode();
    }

    public function getDescription()
    {
        return 'Multiline operations: The operator should be prepended to the front of the next line.';
    }

    public function getLevel()
    {
        return Symfony\CS\FixerInterface::CONTRIB_LEVEL;
    }
}

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->append([ __DIR__.'/.php_cs' ])
;

return Symfony\CS\Config\Config::create()
    // All PSR-1 and PSR-2 fixers are included here.
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)

    ->addCustomFixer(new ShortArraySpacesFixer())
    ->addCustomFixer(new MultilineOperatorsFixer())
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
        'phpdoc_order',                     // Annotations in phpdocs should be ordered so that param annotations come first, then throws annotations, then return annotations.

        // Custom styling
        'short_array_spaces',
        'multiline_operators',
    ])

    ->finder($finder);

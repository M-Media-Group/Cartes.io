<?php

/*
 * This file is part of the league/commonmark-extras package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (http://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\CategoryAutolink;

use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;

class CategoryParser extends AbstractInlineParser
{
    public function getCharacters()
    {
        return ['#'];
    }

    public function parse(InlineParserContext $inlineContext)
    {
        $cursor = $inlineContext->getCursor();

        // The @ symbol must not have any other characters immediately prior
        $previousChar = $cursor->peek(-1);
        if ($previousChar !== null && $previousChar !== ' ') {
            // peek() doesn't modify the cursor, so no need to restore state first
            return false;
        }

        // Save the cursor state in case we need to rewind and bail
        $previousState = $cursor->saveState();

        // Advance past the @ symbol to keep parsing simpler
        $cursor->advance();

        // Parse the handle
        $handle = $cursor->match('/^[A-Za-z0-9_-]{1,15}(?!\w)/');
        if (empty($handle)) {
            // Regex failed to match; this isn't a valid Twitter handle
            $cursor->restoreState($previousState);

            return false;
        }

        $profileUrl = '/categories/'.str_slug($handle);

        $inlineContext->getContainer()->appendChild(new Link($profileUrl, '#'.$handle));

        return true;
    }
}

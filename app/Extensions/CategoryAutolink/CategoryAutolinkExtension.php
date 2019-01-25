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

use League\CommonMark\Block\Renderer as CoreBlockRenderer;
use League\CommonMark\Extension\Extension;
use League\CommonMark\Inline\Renderer as CoreInlineRenderer;

class CategoryAutolinkExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getInlineParsers()
    {
        return [
            new CategoryParser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockRenderers()
    {
        return [
            'League\CommonMark\Block\Element\Document' => new CoreBlockRenderer\DocumentRenderer(),
            'League\CommonMark\Block\Element\Paragraph' => new CoreBlockRenderer\ParagraphRenderer(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getInlineRenderers()
    {
        return [
            'League\CommonMark\Inline\Element\Text' => new CoreInlineRenderer\TextRenderer(),
            'League\CommonMark\Inline\Element\Link' => new CoreInlineRenderer\LinkRenderer(),
        ];
    }
}

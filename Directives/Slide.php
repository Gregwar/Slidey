<?php

namespace Gregwar\Slidey\Directives;

use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\Directive;
use Gregwar\RST\Parser;
use Gregwar\RST\Document;

/**
 * Handles the begining and the end of the slides
 */
class Slide extends Directive
{
    protected $slide = false;

    public function getName()
    {
        return 'slide';
    }

    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        $html = '';

        if ($this->slide) {
            $html = '</div>';
        }

        $this->slide = true;

        $html .= '<div class="slide '.$data.'">';

        return new RawNode($html);
    }

    public function finalize(Document &$document)
    {
        if ($this->slide) {
            $document->addNode(new RawNode('</div>'));
        }
    }
}

<?php

namespace Gregwar\Slidey\Directives;

use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\Directive;
use Gregwar\RST\Parser;

/**
 * Handles the begining and the end of the slides
 */
class Math extends Directive
{
    public function getName()
    {
        return 'math';
    }

    public function process(Parser $parser, $node, $variable, $data, array $options)
    {
        $document = $parser->getDocument();
        $environment = $parser->getEnvironment();

        $formula = trim($node->getValue());

        $node = new RawNode('$$'.$formula.'$$');

        if ($variable) {
            $environment->setVariable($variable, $node);
        } else {
            $document->addNode($node);
        }
    }

    public function wantCode()
    {
        return true;
    }
}

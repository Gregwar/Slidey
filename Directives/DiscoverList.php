<?php

namespace Gregwar\Slidey\Directives;

use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\Directive;
use Gregwar\RST\Parser;

use Gregwar\Slidey\Nodes\ListNode;

/**
 * Setting the discover flag on the following list
 */
class DiscoverList extends Directive
{
    public function getName()
    {
        return 'discoverList';
    }

    public function process(Parser $parser, $node, $variable, $data, array $options)
    {
        $document = $parser->getDocument();

        if ($node instanceof ListNode) {
            $node->enableDiscover();
        }

        $document->addNode($node);
    }
}

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

        $tex = new \Gregwar\Tex2png\Tex2png($formula, isset($options['density']) ? $options['density'] : 300);
        $tex->setCacheDirectory($environment->relativeUrl('/cache/tex/'));
        $tex->setActualCacheDirectory($environment->getTargetDirectory().'/cache/tex/');

        $node = new RawNode('<img src="'.$tex->generate().'" />');
        $document->addNode($node);
    }
}

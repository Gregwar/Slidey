<?php

namespace Gregwar\Slidey;

use Gregwar\RST\HTML\Span as Base;
use Gregwar\RST\Parser;

class Span extends Base
{
    public function __construct(Parser $parser, $span)
    {
        $tokens = [];
        
        $span = preg_replace_callback('/\$\$(.+)\$\$/mUsi', function($match) use ($parser, &$tokens) {
            $formula = $match[1];
            $token = $this->generateToken();
            $environment = $parser->getEnvironment();
            
            $tex = new \Gregwar\Tex2png\Tex2png($formula, 200);
            $tex->setCacheDirectory($environment->relativeUrl('/cache/tex/'));
            $tex->setActualCacheDirectory($environment->getTargetDirectory().'/cache/tex/');
            $html = '<img class="formula" src="'.$tex->generate().'" />';
            
            $tokens[$token] = [
                'type' => 'raw',
                'text' => $html
            ];
            
            return $token;
        }, $span);

        parent::__construct($parser, $span, $tokens);
    }
}
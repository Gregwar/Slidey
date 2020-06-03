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

            $html = '\\('.$formula.'\\)';
            
            $tokens[$token] = [
                'type' => 'raw',
                'text' => $html
            ];
            
            return $token;
        }, $span);

        parent::__construct($parser, $span, $tokens);
    }
}
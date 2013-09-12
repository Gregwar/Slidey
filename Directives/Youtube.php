<?php

namespace Gregwar\Slidey\Directives;

use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\Directive;
use Gregwar\RST\Parser;
use Gregwar\RST\Document;

/**
 * Embed a Youtube video
 */
class Youtube extends Directive
{
    public function getName()
    {
        return 'youtube';
    }

    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        $embed = false;
        if (strpos('http', $data) === false) {
            $embed = $data;
        } else {
            preg_match('#v=([^&]+)#msi', $data, $matches);

            if (!$matches) {
                $html = 'Cannot parse Youtube link';
            } else {
                $embed = $matches[1];
            }
        }

        if ($embed) {
            $html = '<iframe class="youtube" src="http://www.youtube.com/embed/'.$embed.'" frameborder="0" allowfullscreen></iframe>';
        }

        return new RawNode($html);
    }
}

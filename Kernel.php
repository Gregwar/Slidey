<?php

namespace Gregwar\Slidey;

use Gregwar\Slidey\Span;
use Gregwar\RST\HTML\Kernel as Base;

use Gregwar\RST\HTML\Directives\Wrap;

class Kernel extends Base
{
    public function getDirectives()
    {
        $directives = parent::getDirectives();
        $directives[] = new Directives\Slide;
        $directives[] = new Directives\Math;
        $directives[] = new Directives\DiscoverList;
        $directives[] = new Directives\Youtube;

        $classes = array('textOnly', 'slideOnly', 'discover',
            'step', 'note', 'tip', 'warning', 'spoiler', 'center', 'important', 'success');

        foreach ($classes as $class) {
            $directives[] = new Wrap($class);
        }

        $directives[] = new Wrap('poll', true);
        return $directives;
    }

    public function getClass($name)
    {
        $nodes = array('CodeNode', 'TocNode', 'ListNode', 'TableNode');

        foreach ($nodes as $node) {
            if ($name == 'Nodes\\'.$node) {
                return '\\Gregwar\\Slidey\\Nodes\\'.$node;
            }
        }

        if ($name == 'Span') {
            return Span::class;
        }

        return parent::getClass($name);
    }
}

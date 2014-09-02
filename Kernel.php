<?php

namespace Gregwar\Slidey;

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
        $nodes = array('CodeNode', 'TocNode', 'ListNode');

        foreach ($nodes as $node) {
            if ($name == 'Nodes\\'.$node) {
                return '\\Gregwar\\Slidey\\Nodes\\'.$node;
            }
        }

        return parent::getClass($name);
    }
}

<?php

namespace Gregwar\Slidey;

use Gregwar\RST\HTML\Factory as Base;

use Gregwar\RST\HTML\Directives\Wrap;

class Factory extends Base
{
    public function getDirectives()
    {
        $directives = parent::getDirectives();
        $directives[] = new Directives\Slide;
        $directives[] = new Directives\Math;
        $directives[] = new Directives\DiscoverList;

        $classes = array('textOnly', 'slideOnly', 'discover',
            'step', 'note', 'tip', 'warning', 'spoiler', 'center');

        foreach ($classes as $class) {
            $directives[] = new Wrap($class);
        }

        return $directives;
    }

    public function getClass($name)
    {
        $nodes = array('CodeNode', 'TocNode', 'ListNode');

        if (in_array($name, $nodes)) {
            return '\\Gregwar\\Slidey\\Nodes\\'.$name;
        }

        return parent::getClass($name);
    }
}

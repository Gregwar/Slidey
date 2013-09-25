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

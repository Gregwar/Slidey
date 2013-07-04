<?php

namespace Gregwar\Slidey;

use Gregwar\RST\HTML\Factory as Base;

class Factory extends Base
{
    public function getDirectives()
    {
        $directives = parent::getDirectives();
        $directives[] = new Directives\Slide;
        $directives[] = new Directives\Math;

        return $directives;
    }

    public function getClass($name)
    {
        $nodes = array('CodeNode');

        if (in_array($name, $nodes)) {
            return '\\Gregwar\\Slidey\\Nodes\\'.$name;
        }

        return parent::getClass($name);
    }
}
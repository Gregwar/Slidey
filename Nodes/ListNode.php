<?php

namespace Gregwar\Slidey\Nodes;

use Gregwar\RST\HTML\ListNode as Base;

class ListNode extends Base
{
    protected $discover = false;

    public function enableDiscover()
    {
        $this->discover = true;
    }

    public function createElement($text, $prefix)
    {
        $classes = array();

        if ($prefix == '-') {
            $classes[] = 'dash';
        }

        if ($this->discover) {
            $classes[] = 'discover';
        }
        
        return '<li class="'.implode(' ', $classes).'">'.$text.'</li>';
    }
}

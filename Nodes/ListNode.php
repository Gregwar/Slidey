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

    public function createElement($text)
    {
        return '<li class="'.($this->discover ? 'discover' : '').'">'.$text.'</li>';
    }
}

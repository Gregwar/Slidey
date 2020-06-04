<?php

namespace Gregwar\Slidey\Nodes;

use Gregwar\RST\HTML\Nodes\TocNode as Base;

class TocNode extends Base
{
    public function renderLevel($url, $titles, $level = 1, $path = array())
    {
        $render = parent::renderLevel($url, $titles, $level, $path);

        // if ($level == 1) {
        //     $render = '<div class="slide middleSlide tocSlide">'.$render.'</div>';
        // }

        return $render;
    }
}

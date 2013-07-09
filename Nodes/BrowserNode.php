<?php

namespace Gregwar\Slidey\Nodes;

use Gregwar\RST\Environment;
use Gregwar\RST\Nodes\Node as Base;

class BrowserNode extends Base
{
    protected $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    protected function reference($file)
    {
        if (!is_array($file)) {
            $meta = $this->environment->resolve('/'.$file);
        } else {
            $meta = $file;
        }

        return '<a href="'.$meta['url'].'">'.$meta['title'].'</a>';
    }

    public function render()
    {
        list($before, $after) = $this->environment->getMyToc();

        $prev = $before ? $this->reference($before[count($before)-1]) : null;
        $next = $after ? $this->reference($after[0]) : null;
        $parent = $this->environment->getParent();

        $html = '';

        if ($parent) {
            $ref = $this->reference($parent);
            $html .= '<div class="parent">'.$ref.'</div>';
        }

        if ($prev) {
            $html .= '<div class="prev">&laquo; '.$prev.'</div>';
        }

        if ($next) {
            $html .= '<div class="next">'.$next.' &raquo;</div>';
        }
 
        return $html;
    }
}

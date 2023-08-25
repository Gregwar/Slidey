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
            $meta = $this->environment->resolve('doc', '/'.$file);
        } else {
            $meta = $file;
            $meta['url'] = $this->environment->relativeUrl('/' . $meta['url']);
        }

        return array($meta['url'], $meta['title']);
    }

    public function render()
    {
        list($before, $after) = $this->environment->getMyToc();

        $prev = $before ? $this->reference($before[count($before)-1]) : null;
        $next = $after ? $this->reference($after[0]) : null;
        $parent = $this->environment->getParent();

        $html = '';

        $html .= '<nav>';
        $html .= '<ul class="pagination justify-content-center">';

        if ($prev) {
            $html .= '<li class="page-item"><a class="page-link" href="'.$prev[0].'">&larr; '.$prev[1].'</a></li>';
        }


        if ($parent) {
            $ref = $this->reference($parent);
            $html .= '<li class="page-item"><a class="page-link" href="'.$ref[0].'"><i class="bi bi-folder2"></i> '.$ref[1].'</a></li>';
        }

        if ($next) {
            $html .= '<li class="page-item"><a class="page-link" href="'.$next[0].'">'.$next[1].' &rarr;</a></li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
 
        return $html;
    }
}

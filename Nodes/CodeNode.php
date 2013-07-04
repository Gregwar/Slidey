<?php

namespace Gregwar\Slidey\Nodes;

use Gregwar\RST\Nodes\CodeNode as Base;

class CodeNode extends Base
{
    public function render()
    {
        $language = $this->language ?: 'php';
        $code = trim($this->value);

        $geshi = new \GeSHi($code, $language);
        $geshi->enable_classes();
        $geshi->enable_keyword_links(false);

        return '<div class="highlight">' . $geshi->parse_code() . '</div>';
    }
}

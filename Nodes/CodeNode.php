<?php

namespace Gregwar\Slidey\Nodes;

use Gregwar\RST\Nodes\CodeNode as Base;

class CodeNode extends Base
{
    public function render()
    {
        if ($this->raw) {
            return $this->value;
        } else {
            $language = $this->language ?: 'php';
            $code = htmlspecialchars(trim($this->value));

            if ($language == 'text') {
                $language = 'no-highlight';
            }

            return '<pre><code class="' . $language . ' hljs">' . $code . '</code></pre>';
        }
    }
}

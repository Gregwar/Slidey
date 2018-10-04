<?php

namespace Gregwar\Slidey\Nodes;

use Gregwar\RST\Environment;
use Gregwar\RST\HTML\Nodes\TableNode as Base;

class TableNode extends Base
{
    public function render()
    {
        $html = '<table class="table table-striped"><tbody>';
        foreach ($this->data as $k=>&$row) {
            if (!$row) {
                continue;
            }

            $html .= '<tr>';
            foreach ($row as &$col) {
                $html .= isset($this->headers[$k]) ? '<th>' : '<td>';
                $html .= $col->render();
                $html .= isset($this->headers[$k]) ? '</th>' : '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;

    }
}

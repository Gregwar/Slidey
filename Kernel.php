<?php

namespace Gregwar\Slidey;

use Gregwar\Slidey\Span;
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

        $classes = array(
            'textOnly', 'slideOnly', 'discover',
            'step', 'tip', 'spoiler', 'center'
        );

        foreach ($classes as $class) {
            $directives[] = new Wrap($class);
        }

        $directives[] = new Directives\TriggerWrap('important', 'alert alert-light p-2 fs-5 text-center');
        $directives[] = new Directives\TriggerWrap('note', 'alert alert-info');
        $directives[] = new Directives\TriggerWrap('warning', 'alert alert-warning');
        $directives[] = new Directives\TriggerWrap('success', 'alert alert-success');
        $directives[] = new Directives\TriggerWrap('danger', 'alert alert-danger');

        $directives[] = new Wrap('poll', true);
        return $directives;
    }

    public function getClass($name)
    {
        $nodes = array('CodeNode', 'TocNode', 'ListNode', 'TableNode');

        foreach ($nodes as $node) {
            if ($name == 'Nodes\\' . $node) {
                return '\\Gregwar\\Slidey\\Nodes\\' . $node;
            }
        }

        if ($name == 'Span') {
            return Span::class;
        }

        return parent::getClass($name);
    }
}

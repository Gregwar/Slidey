<?php

namespace Gregwar\Slidey\Directives;

use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\HTML\Directives\Wrap;
use Gregwar\RST\Parser;

/**
 * Same as Wrap, but the trigger class is different from the wrap class
 */
class TriggerWrap extends Wrap
{
    public function getName()
    {
        return $this->trigger;
    }

    protected $trigger;

    public function __construct($trigger, $class)
    {
        $this->trigger = $trigger;
        parent::__construct($class);
    }
}

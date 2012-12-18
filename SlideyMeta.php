<?php

namespace Gregwar\Slidey;

class SlideyMeta
{
    protected $file;
    protected $data;

    public function __construct($file, $data = array())
    {
        $this->file = $file;
        $this->data = $data;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function get($name, $default = null)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return $default;
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function add($key, $value)
    {
        $arr = $this->get($key, array());
        $arr[] = $value;
        $this->set($key, $arr);
    }

    public function getSlug()
    {
        return $this->get('slug');
    }

    public function getAll()
    {
        return $this->data;
    }

    public function clear()
    {
        $this->data = array();
    }
}

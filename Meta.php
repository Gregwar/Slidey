<?php

namespace Gregwar\Slidey;

/**
 * Meta-data for a file
 */
class Meta
{
    protected $file;
    protected $data;

    /**
     * Creates an instance of metadata for a file
     */
    public function __construct($file, $data = array())
    {
        $this->file = $file;
        $this->data = $data;
    }

    /**
     * Gets the file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Gets an entry from the metadata
     */
    public function get($name, $default = null)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return $default;
    }

    /**
     * Sets an entry
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Add a value to an array entry
     */
    public function add($key, $value)
    {
        $arr = $this->get($key, array());
        $arr[] = $value;
        $this->set($key, $arr);
    }

    /**
     * Get all the entries
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * Clear the entries
     */
    public function clear()
    {
        $this->data = array();
    }
}

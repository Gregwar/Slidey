<?php

namespace Gregwar\Slidey;

/**
 * Template rendering fo Slidey
 */
class Template
{
    /**
     * File contents
     */
    public $contentsFile;

    /**
     * Variables
     */
    public $globals = array();

    /**
     * Twig
     */
    protected $twig;

    public function __construct()
    {
        $this->globals = array(
            'mainTitle' => '',
            'footer' => '',
            'css' => array()
        );

        $this->twig = new \Twig_Environment;
    }

    /**
     * Adding an extension
     */
    public function addExtension(\Twig_Extension $extension)
    {
        $this->twig->addExtension($extension);
    }

    /**
     * Setting a global value
     */
    public function set($name, $value)
    {
        $this->globals[$name] = $value;
    }

    /**
     * Setting the directories
     */
    public function setDirectories()
    {
        $loader = new \Twig_Loader_Filesystem(array_merge(func_get_args(), array(__DIR__.'/templates/')));
        $this->twig->setLoader($loader);
    }

    /**
     * Renders a page
     */
    public function render($page, $variables = array())
    {
        return $this->twig->render($page, array_merge($this->globals, $variables));
    }

    /**
     * Adds a stylesheet
     */
    public function addCss($file)
    {
        $this->globals['css'][] .= $file;
    }
}

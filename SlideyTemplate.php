<?php

namespace Gregwar\Slidey;

class SlideyTemplate
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

    public function addExtension(\Twig_Extension $extension)
    {
        $this->twig->addExtension($extension);
    }
    
    public function set($name, $value)
    {
        $this->globals[$name] = $value;
    }

    public function setDirectories()
    {
        $loader = new \Twig_Loader_Filesystem(array_merge(func_get_args(), array(__DIR__.'/templates/')));
        $this->twig->setLoader($loader);
    }

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

<?php

namespace Gregwar;

require_once('SlideyTemplate.php');
require_once('SlideyCacheBuilder.php');

class Slidey
{
    /**
     * Cache directory
     */
    public static $cacheDirectory = 'cache';

    /**
     * Directory containing all pages
     */
    public static $pagesDirectory = 'pages';

    /**
     * Template engine
     */
    public $template;

    /**
     * Current page
     */
    public $page;

    /**
     * Pages meta
     */
    protected $meta = null;

    public function __construct()
    {
	$this->template = new SlideyTemplate;
    }

    /**
     * Gets the cache file from its path
     */
    protected function cacheFilePath($file)
    {
	return self::$cacheDirectory . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Loads the meta
     */
    protected function loadMeta()
    {
	if ($this->meta === null)
	{
	    $cacheFile = $this->cacheFilePath('meta.php');

	    if (file_exists($cacheFile))
	    {
		$this->meta = include($cacheFile);
	    }
	}

	if (!$this->meta) 
	{
	    die('Slidey is not able to find its meta, please build it');
	}
    }

    /**
     * Process the page $page and render it
     */
    public function process($page)
    {
	if (!$page)
	{
	    $page = 'index';
	}

	$this->page = $page;
	$this->loadMeta();

	$template = $this->template;
	$template->slug = $page;

	if (isset($this->meta['manifest'][$page]))
	{
	    $template->title = $this->meta['manifest'][$page];
	    $template->contentsFile = $this->cacheFilePath($page . '.html');
	    $template->render();
	}
	else 
	{
	    $template->title = '404';
	    $template->contents = 'Erreur 404';
	    $template->render();
	}
    }
}

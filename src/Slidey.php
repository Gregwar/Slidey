<?php

namespace Gregwar;

require_once('SlideyTemplate.php');
require_once('SlideyBuilder.php');

class Slidey
{
    /**
     * Target directory
     */
    public static $targetDirectory = 'web';

    /**
     * Directory containing all pages
     */
    public static $pagesDirectory = 'pages';

    /**
     * Gets the target file from its path
     */
    public static function targetFilePath($file)
    {
	return self::$targetDirectory . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Gets the page file path
     */
    public static function pagesFilePath($file)
    {
	return self::$pagesDirectory . DIRECTORY_SEPARATOR . $file;
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

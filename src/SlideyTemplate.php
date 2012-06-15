<?php

namespace Gregwar\Slidey;

class SlideyTemplate
{
    /**
     * Main title
     */
    public $mainTitle = null;

    /**
     * Page title
     */
    public $title;

    /**
     * Page slug
     */
    public $slug;

    /**
     * Additionnal headers
     */
    public $header;

    /**
     * Page contents
     */
    public $contents = null;

    /**
     * Page footer
     */
    public $footer = null;

    /**
     * Page browser
     */
    public $browser = null;

    /**
     * File contents
     */
    public $contentsFile;

    /**
     * Template variables
     */
    public $variables = array();

    public function render($variables = array())
    {
	$this->variables = $variables;
	$slidey = $this;
	include(__DIR__.'/templates/layout.php');
    }

    /**
     * Gets the title
     */
    public function title()
    {
	$title = $this->title;

	if ($this->mainTitle)
	{
	    $title = $this->mainTitle . ' - ' . $title;
	}

	return $title;
    }

    /**
     * Gets the page title
     */
    public function pageTitle()
    {
	return '<h1>' . $this->title . '</h1>';
    }

    /**
     * Returns the contents
     */
    public function contents()
    {
	if ($this->contents) 
	{
	    return $this->contents;
	}
	else
	{
	    foreach ($this->variables as $variable => $value) {
		$$variable = $value;
	    }

	    include($this->contentsFile);
	}
    }

    /**
     * Page footer
     */
    public function footer()
    {
	return $this->footer;
    }

    /**
     * Page header
     */
    public function header()
    {
	return $this->header;
    }

    /**
     * Page browser
     */
    public function browser()
    {
	return $this->browser;
    }

    /**
     * Adds a stylesheet
     */
    public function addCss($file)
    {
	$this->header .= '<link type="text/css" media="screen" rel="stylesheet" href="' . $file .'" />';
    }
}

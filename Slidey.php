<?php

namespace Gregwar\Slidey;

/**
 * Slidey standard package
 */
class Slidey extends SlideyBuilder
{
    /**
     * Target cache directory
     */
    public $cacheDirectory = 'cache';

    /**
     * Default behaviour is to copy the cache directory to the target
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Runs the build, add the cache directory
     */
    public function build($targetDirectory = 'web')
    {
        @mkdir($this->cacheDirectory);
        $this->copy($this->cacheDirectory);
        $this->copy(__DIR__ . '/static/*', '');

        parent::build($targetDirectory);
    }

    /**
     * Highlighting a file using GeSHi
     */
    public function highlight($file, $lang = 'php')
    {
        return $this->highlightString(rtrim(file_get_contents($this->pagesFilePath($file))), $lang);
    }

    /**
     * Highlighting a string using GeShi
     */
    public function highlightString($str, $lang = 'php')
    {
        $geshi = new \GeSHi($str, $lang);
	$geshi->enable_classes();
	$geshi->enable_keyword_links(false);

	return '<div class="highlight">' . $geshi->parse_code() . '</div>';
    }

    /**
     * Managing an image
     */
    public function image($file)
    {
        $image = new \Gregwar\Image\Image($file);
        $image->setCacheDir($this->cacheDirectory . '/images/');

        return $image;
    }

    /**
     * Managing 
     */
    public function tex($formula, $density = 350)
    {
        $tex = new \Gregwar\Tex2png\Tex2png($formula, $density);
        $tex->setCacheDirectory($this->cacheDirectory . '/tex/');

        return $tex->generate();
    }
}

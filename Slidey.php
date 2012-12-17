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

    public function __construct()
    {
        $this->twigFunctions = array_merge($this->twigFunctions, array(
            'image', 'highlight', 'highlightString', 'tex'
        ));

        parent::__construct();
    }

    /**
     * Runs the build, add the cache directory
     */
    public function run()
    {
        @mkdir($this->targetFilePath($this->cacheDirectory), 0755, true);
        $this->copy(__DIR__ . '/static/*', '');

        parent::run();
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
        $image->setActualCacheDir($this->targetFilePath($this->cacheDirectory . '/images/'));

        return $image;
    }

    /**
     * Managing 
     */
    public function tex($formula, $density = 350)
    {
        $tex = new \Gregwar\Tex2png\Tex2png($formula, $density);
        $tex->setCacheDirectory($this->cacheDirectory . '/tex/');
        $tex->setActualCacheDirectory($this->targetFilePath($this->cacheDirectory . '/tex/'));

        return $tex->generate();
    }
}

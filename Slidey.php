<?php

namespace Gregwar\Slidey;

/**
 * Slidey standard package
 */
class Slidey extends Builder
{
    /**
     * Target cache directory
     */
    public $cacheDirectory = 'cache';

    /**
     * Interactive mode
     */
    protected $interactive = null;

    public function __construct()
    {
        $this->twigFunctions = array_merge($this->twigFunctions, array(
            'image', 'highlight', 'highlightString', 'tex'
        ));

        parent::__construct();
    }

    /**
     * Enable the interactive mode
     */
    public function enableInteractive($password, $directory = 'data')
    {
        $this->copy(__DIR__ . '/static/interactive.php');
        $this->interactive =array(
            'password' => sha1($password),
            'directory' => $directory
        );

        $this->template->set('interactive', true);
    }

    /**
     * Runs the build, add the cache directory
     */
    public function run()
    {
        @mkdir($this->targetFilePath($this->cacheDirectory), 0755, true);
        $this->copy(__DIR__ . '/static/slidey/', 'slidey/');

        if ($this->interactive !== null) {
            $config = '<?php return '.var_export($this->interactive, true).';';
            file_put_contents($this->targetFilePath('config.php'), $config);
        }

        parent::run();
    }

    /**
     * Highlighting a file using GeSHi
     */
    public function highlight($file, $lang = 'php')
    {
        $this->meta->add('depends', $this->pagesFilePath($file));

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

	return '<div class="highlight">{% raw %}' . $geshi->parse_code() . '{% endraw %}</div>';
    }

    /**
     * Managing an image
     */
    public function image($file)
    {
        $image = new \Gregwar\Image\Image($file);
        $image->setCacheDir($this->cacheDirectory . '/images/');
        $image->setActualCacheDir($this->targetFilePath($this->cacheDirectory . '/images/'));

        $this->meta->add('depends', $this->pagesFilePath($file));

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

<?php

namespace Gregwar;

/**
 * Builds the cache used by Slidey
 */
class SlideyCacheBuilder
{
    /**
     * The cache that is being geneated
     */
    protected $summary = array();
    protected $manifest = array();
    protected $order = array();

    /**
     * Curent chapter
     */
    protected $currentChapter = 0;

    /**
     * Current part
     */
    protected $currentPart = 0;

    /**
     * Current slug
     */
    protected $slug;

    /**
     * The current file
     */
    protected $file;

    /**
     * Cache directory
     */
    protected $cacheDirectory;

    /**
     * The directory to crawl
     */
    protected $pagesDirectory;

    /**
     * Runs the cache builder
     */
    public function run($directory)
    {
	$this->cacheDirectory = realpath($directory . DIRECTORY_SEPARATOR . Slidey::$cacheDirectory);
	$this->pagesDirectory = realpath($directory . DIRECTORY_SEPARATOR . Slidey::$pagesDirectory);

	echo "Clearing cache\n";
	system('rm -rf ' . $this->cacheDirectory . DIRECTORY_SEPARATOR . '*'."\n");

	echo "Crawling " . $this->pagesDirectory . "\n";
	
	$this->manifest['index'] = 'Table des matières';

	$this->summary['index'] = array(
	    'number' => 0,
	    'chapter' => 'Table des matières',
	    'slug' => 'index',
	    'parts' => array(),
	);

	$this->order = array('index');

	$files = opendir($this->pagesDirectory);

	if (!$files)
	{
	    echo "Unable to open directory\n";
	}
	else
	{
	    while ($file = readdir($files))
	    {
		if (substr($file,-4) == '.php')
		{
		    $this->process($file);
		}
	    }
	}

	closedir($files);
	$this->generateSummary();
	$this->generateBrowsers();
	$this->save();
    }

    /**
     * Processing a file
     */
    public function process($file)
    {
	$this->file = $file;
	echo "Processing ".$this->file."\n";

	ob_start();
	$slidey = $this;
	include($this->pagesDirectory . DIRECTORY_SEPARATOR . $this->file);
	$contents = ob_get_clean();

	file_put_contents($this->cacheDirectory . DIRECTORY_SEPARATOR . $this->slug . '.html', $contents);
    }

    /**
     * Adding a chapter
     */
    public function chapter($chapter, $slug)
    {
	$this->currentChapter++;
	$this->currentPart = 0;

	$this->manifest[$slug] = $this->currentChapter . ' - ' . $chapter;
	$this->slug = $slug;

	$this->summary[$this->slug] = array(
	    'number' => $this->currentChapter,
	    'chapter' => $chapter,
	    'slug' => $slug,
	    'parts' => array(),
	);

	$this->order[] = $slug;
    }

    /**
     * Adding a part
     */
    public function part($title)
    {
	$this->currentPart++;

	$this->summary[$this->slug]['parts'][] = array(
	    'title' => $title,
	    'number' => $this->currentPart,
	);

	return '<h2 id="part' . $this->currentPart . '">' . $this->currentPart . ') ' . $title . '</h2>';
    }

    /**
     * Highlighting a file
     */
    public function highlight($file, $lang='php')
    {
	$geshi = new \GeSHi(rtrim(file_get_contents($this->pagesDirectory . DIRECTORY_SEPARATOR . $file)), $lang);
	$geshi->enable_classes();
	$geshi->enable_keyword_links(false);

	return '<div class="highlight">' . $geshi->parse_code() . '</div>';
    }

    /**
     * Saving the cache
     */
    public function save()
    {
	$cacheFile = $this->cacheDirectory . DIRECTORY_SEPARATOR . 'meta.php';
	echo 'Saving manifest to '.$cacheFile."\n";

	$meta = array(
	    'manifest' => $this->manifest,
	    'summary' => $this->summary,
	    'order' => $this->order
	);

	file_put_contents($cacheFile, '<?php return '.var_export($meta, true).';');
    }

    /**
     * Generates the summary
     */
    public function generateSummary()
    {
	echo "Generating summary\n";

	$summary = $this->summary;

	ob_start();
	include(__DIR__.'/templates/summary.php');
	$contents = ob_get_clean();

	file_put_contents($this->cacheDirectory . DIRECTORY_SEPARATOR . 'index.html', $contents);
    }


    /**
     * Generates a browser for a page
     */
    public function generateBrowser($before, $current, $after)
    {
	ob_start();
	include(__DIR__.'/templates/browser.php');
	return ob_get_clean();
    }

    /**
     * Generates a browser for each page
     */
    public function generateBrowsers()
    {
	$before = $after = $current = null;

	foreach ($this->order as $k => $slug)
	{
	    $before = ($k > 0 ? $this->summary[$this->order[$k-1]] : null);
	    $current = $this->summary[$slug];
	    $after = ($k < count($this->order)-1 ? $this->summary[$this->order[$k+1]] : null);

	    $file = $this->cacheDirectory . DIRECTORY_SEPARATOR . $slug . '.html';

	    file_put_contents($file, $this->generateBrowser($before, $current, $after), FILE_APPEND); 
	}
    }
}

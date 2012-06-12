<?php

namespace Gregwar;

/**
 * Builds the slidey project
 */
class SlideyBuilder
{
    /**
     * The cache that is being geneated
     */
    protected $summary = array();
    protected $manifest = array();
    protected $order = array();
    protected $slugs = array();

    /**
     * The current meta
     */
    protected $meta = array();

    /**
     * Directories that need to be copied
     */
    protected $copyDirectories = array();

    /**
     * Files processed
     */
    protected $processed = array();

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
     * Template
     */
    public $template;

    public function __construct()
    {
	$this->template = new SlideyTemplate;
    }

    /**
     * Run the builder 
     */
    public function build($targetDirectory = 'web', $pagesDirectory = 'pages')
    {
	Slidey::$targetDirectory = $targetDirectory;
	Slidey::$pagesDirectory = $pagesDirectory;

	if (isset($_SERVER['argv'])) {
	    $argv = $_SERVER['argv'];
	}

	if (isset($argv[1])) {
	    if ($argv[1] == 'clean') {
		$this->cleanCache();
	    }
	}

	$this->run();
    }

    /**
     * Cleans the cache
     */
    public function cleanCache()
    {
	echo "* Clearing cache\n";
	system('rm -rf ' . Slidey::targetFilePath('*') ." \n");
    }

    /**
     * Gets the meta filename
     */
    protected function metaFilename()
    {
	return Slidey::targetFilePath('meta.php');
    }

    /**
     * Try to load meta
     */
    protected function loadMeta()
    {
	$metaFile = $this->metaFilename();

	if (file_exists($metaFile)) {
	    $this->meta = @include($metaFile);

	    $this->summary = $this->meta['summary'];
	    $this->order = $this->meta['order'];
	    $this->manifest = $this->meta['manifest'];
	    $this->slugs = $this->meta['slugs'];
	}
    }

    /**
     * Try to get the slug from metadata
     */
    protected function metaSlug($file)
    {
	if (isset($this->slugs[$file])) {
	    return $this->slugs[$file];
	}

	return null;
    }

    /**
     * Runs the cache builder
     */
    public function run()
    {
	$this->loadMeta();

	echo "* Copying static files\n";
	system('cp -R ' . __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . '* ' . Slidey::targetFilePath(''));

	$this->copyAllDirectories();

	echo "* Crawling " . $this->pagesDirectory . "\n";

	// Adding index
	$this->manifest['index'] = 'Table des matières';

	$this->summary['index'] = array(
	    'number' => 0,
	    'chapter' => 'Table des matières',
	    'slug' => 'index',
	    'parts' => array(),
	);

	$this->order = array('index');

	$this->processed['index'] = true;

	$files = opendir(Slidey::$pagesDirectory);

	if (!$files)
	{
	    echo "! Unable to open " . Slidey::$pagesDirectory . "\n";
	    return;
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
	$this->saveMeta();
    }

    /**
     * Processing a file
     */
    public function process($file)
    {
	$this->file = $file;

	$input = Slidey::pagesFilePath($this->file);

	if ($slug = $this->metaSlug($file)) {
	    $output = Slidey::targetFilePath($slug . '.html');

	    if (file_exists($output) && filectime($output) >= filectime($input))
	    {
		echo "! Passing ".$file."\n";
		return;
	    }
	}
	
	echo "* Processing ".$file."\n";

	ob_start();
	$slidey = $this;
	include($input);
	$contents = ob_get_clean();

	$output = Slidey::targetFilePath($this->slug . '.html');

	file_put_contents($output, $contents);
	$this->processed[$this->slug] = true;
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

	$this->slugs[$this->file] = $slug;

	if (!in_array($slug, $this->order)) {
	    $this->order[] = $slug;
	}
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
	$geshi = new \GeSHi(rtrim(file_get_contents(Slidey::pagesFilePath($file))), $lang);
	$geshi->enable_classes();
	$geshi->enable_keyword_links(false);

	return '<div class="highlight">' . $geshi->parse_code() . '</div>';
    }

    /**
     * Saving the meta file
     */
    public function saveMeta()
    {
	$cacheFile = $this->metaFilename();
	echo '* Saving manifest to '.$cacheFile."\n";

	$meta = array(
	    'manifest' => $this->manifest,
	    'summary' => $this->summary,
	    'order' => $this->order,
	    'slugs' => $this->slugs,
	);

	file_put_contents($cacheFile, '<?php return '.var_export($meta, true).';');
    }

    /**
     * Generates the summary
     */
    public function generateSummary()
    {
	echo "* Generating summary\n";

	$summary = $this->summary;

	ob_start();
	include(__DIR__.'/templates/summary.php');
	$contents = ob_get_clean();

	file_put_contents(Slidey::targetFilePath('index.html'), $contents);
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

	    $file = Slidey::targetFilePath($slug . '.html');

	    if (isset($this->processed[$slug])) {
		$this->template->title = $this->manifest[$slug];
		$this->template->browser = $this->generateBrowser($before, $current, $after);
		$this->template->contentsFile = $file;

		ob_start();
		$this->template->render();
		$contents = ob_get_clean();

		file_put_contents($file, $contents);
	    }
	}
    }

    /**
     * Copy a directory to the build
     */
    public function copyDirectory($source, $target = null)
    {
	if ($target === null)
	{
	    $target = '';
	}
	
	$this->copyDirectories[] = array($source, $target);
    }

    /**
     * Copy directories
     */
    public function copyAllDirectories()
    {
	foreach ($this->copyDirectories as $directories)
	{
	    list($source, $target) = $directories;
	    $target = Slidey::targetFilePath($target);
	    system('cp -R ' . $source . ' ' . $target);
	}
    }
}

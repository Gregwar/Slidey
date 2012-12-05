<?php

namespace Gregwar\Slidey;

require_once(__DIR__ . '/SlideyTemplate.php');

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
    protected $annexes = array();
    protected $annexFiles = array();

    /**
     * Target directory for the build
     */
    protected $targetDirectory;

    /**
     * Directory containing pages
     */
    protected $pagesDirectory;

    /**
     * The current meta
     */
    protected $meta = array();

    /**
     * Files that need to be copied
     */
    protected $copies = array();

    /**
     * Directories to create
     */
    protected $mkDirectories = array();

    /**
     * Current annex
     */
    protected $annex;

    /**
     * Pretty links to annexes
     */
    protected $annexLinks = array();

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
     * Current mode (pages or annex)
     */
    protected $mode;

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
     * Gets the path of a target page
     */
    public function targetFilePath($file)
    {
	return $this->targetDirectory . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Gets the path of a target page
     */
    public function pagesFilePath($file)
    {
	return $this->pagesDirectory . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Run the builder 
     */
    public function build($targetDirectory = 'web', $pagesDirectory = 'pages')
    {
	$this->targetDirectory = $targetDirectory;
	$this->pagesDirectory = $pagesDirectory;

	if (isset($_SERVER['argv'])) {
	    $argv = $_SERVER['argv'];
	}

	if (isset($argv[1])) {
	    if ($argv[1] == 'redo' || $argv[1] == 'clean') {
		$this->cleanCache();
            }
	}

        if ($argv[1] != 'clean') {
            $this->run();
        }
    }

    /**
     * Cleans the cache
     */
    public function cleanCache()
    {
	echo "* Clearing cache\n";
	system('rm -rf ' . $this->targetFilePath('*') ." \n");
    }

    /**
     * Gets the meta filename
     */
    protected function metaFilename()
    {
	return $this->targetFilePath('meta.php');
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
	    $this->annexes = $this->meta['annexes'];
	    $this->annexFiles = $this->meta['annexFiles'];
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

        echo '* Crawling ' . $this->pagesDirectory . "\n";

        if (!is_dir($this->targetDirectory)) {
            echo '* Creating ' . $this->targetDirectory . "\n";
            mkdir($this->targetDirectory);
        }

	// Adding index
	$this->manifest['index'] = 'Table des matières';

	$this->summary['index'] = array(
	    'number' => 0,
	    'chapter' => 'Table des matières',
	    'slug' => 'index',
	    'parts' => array(),
	);

	if (!in_array('index', $this->order)) {
	    $this->order[] = 'index';
	}

	// Processing files
	$this->generatePages();

	// Generating annexes
	$this->generateAnnexes();

	// Generating summary
	$this->generateSummary();

	// Generating layouts
	$this->generateLayouts();

	// Saving meta
	$this->saveMeta();

        $this->doMkDir();
        $this->doCopy();
    }

    /**
     * Generating pages
     */
    public function generatePages()
    {
	$this->currentChapter = 0;
	$this->currentPart = 0;
	$this->mode = 'pages';

	$files = opendir($this->pagesDirectory);

	if (!$files)
	{
	    die("! Unable to open " . $this->pagesDirectory . "\n");
	}
	else
	{
	    while ($file = readdir($files))
	    {
		if (substr($file, -4) == '.php')
		{
		    $this->process($file);
		}
	    }
	}

	closedir($files);
    }

    /**
     * Processing annexes
     */
    public function generateAnnexes()
    {
	$this->currentChapter = 0;
	$this->currentPart = 0;
	$this->mode = 'annexes';

	foreach ($this->annexFiles as $file => $depend) {
	    $this->annex = $depend;
	    $this->process($file);

	    ob_start();
	    $annex = $this->annexes[$this->slug];
	    include(__DIR__.'/templates/annex.php');
	    $link = ob_get_clean();

	    $this->annexLinks[$file] = $link;

	    $this->summary[$depend]['annexes'][$this->slug] = array(
		'slug' => $this->slug,
		'title' => $this->annexes[$this->slug]['chapter'],
	    );
	}
    }

    /**
     * Processing a file
     */
    public function process($file)
    {
	$this->file = $file;

	$input = $this->pagesFilePath($this->file);

	if ($this->slug = $this->metaSlug($file)) {
	    $output = $this->targetFilePath($this->slug . '.html');

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

	if ($this->mode == 'pages') {
	    $contents .= '<?php echo $browser; ?' . '>';
	}

	$output = $this->targetFilePath($this->slug . '.tmp.php');

	file_put_contents($output, $contents);
    }

    /**
     * Adding a chapter
     */
    public function chapter($chapter, $slug, $order = null)
    {
        if ($order != null) {
            $this->currentChapter = $order;
        } else {
            $this->currentChapter++;
        }

	$this->currentPart = 0;

	if ($this->mode == 'pages') {
	   $this->manifest[$slug] = $this->currentChapter . ' - ' . $chapter;
	} else {
	   $this->manifest[$slug] = $chapter;
	}

	$this->slug = $slug;

	$data = array(
	    'number' => $this->currentChapter,
	    'chapter' => $chapter,
	    'slug' => $slug,
	    'parts' => array(),
	    'annexes' => array(),
	);

	if ($this->mode == 'pages') {
	    $this->summary[$this->slug] = $data;
	}

	$this->slugs[$this->file] = $slug;

	if ($this->mode == 'annexes') {
	    $data['depend'] = $this->annex;
	    $this->annexes[$this->slug] = $data;
	}

	if ($this->mode == 'pages') {
	    if (!in_array($slug, $this->order)) {
		$this->order[$this->currentChapter] = $slug;
	    }
	}
    }

    /**
     * Adding a part
     */
    public function part($title)
    {
	$this->currentPart++;
	
	$data = array(
	    'title' => $title,
	    'number' => $this->currentPart,
	);

	if ($this->mode == 'pages') {
	    $this->summary[$this->slug]['parts'][] = $data;
	} 

	if ($this->mode == 'annex') {
	    $this->annexes[$this->slug]['parts'][] = $data;
	}

	return '<h2 id="part' . $this->currentPart . '">' . $this->currentPart . ') ' . $title . '</h2>';
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
	    'annexes' => $this->annexes,
	    'annexFiles' => $this->annexFiles,
	);

	file_put_contents($cacheFile, '<?php return '.var_export($meta, true).';');
    }

    /**
     * Generates the summary
     */
    public function generateSummary()
    {
        echo "* Generating summary\n";

        ksort($this->order);

        $order = $this->order;
	$summary = $this->summary;

	ob_start();
	include(__DIR__.'/templates/summary.php');
	$contents = ob_get_clean();

	$contents .= '<?php echo $browser; ?'.'>';

	file_put_contents($this->targetFilePath('index.tmp.php'), $contents);
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
     * Generates a layout for each page
     */
    public function generateLayouts()
    {
	// Rendering pages
	$before = $after = $current = null;

	foreach ($this->order as $k => $slug)
	{
	    $before = ($k > 0 ? $this->summary[$this->order[$k-1]] : null);
	    $current = $this->summary[$slug];
	    $after = ($k < count($this->order)-1 ? $this->summary[$this->order[$k+1]] : null);

	    $tmpFile = $this->targetFilePath($slug . '.tmp.php');

	    if (file_exists($tmpFile)) {	
		$variables = array(
		    'browser' => $this->generateBrowser($before, $current, $after),
		    'annexLinks' => $this->annexLinks,
		);
		
		$this->renderLayout($slug, $tmpFile, $variables);
	    }
	}

	// Rendering annexes
	foreach ($this->annexes as $slug => $annex)
	{
	    $tmpFile = $this->targetFilePath($slug . '.tmp.php');

	    if (file_exists($tmpFile)) {
		$this->renderLayout($slug, $tmpFile);
	    }
	}
    }

    protected function renderLayout($slug, $tmpFile, $variables = array())
    {
	$file = $this->targetFilePath($slug . '.html');

	$this->template->title = $this->manifest[$slug];
	$this->template->contentsFile = $tmpFile;

	ob_start();
	$this->template->render($variables);
	$contents = ob_get_clean();

	file_put_contents($file, $contents);
	unlink($tmpFile);
    }

    /**
     * Copy a directory to the build
     */
    public function copy($source, $target = null)
    {
	if ($target === null)
	{
	    $target = '';
	}
	
	$this->copies[] = array($source, $target);
    }

    /**
     * Creates a directory
     */
    public function mkdir($directory)
    {
        $this->mkDirectories[] = $directory;
    }

    /**
     * Copy directories
     */
    public function doCopy()
    {
	foreach ($this->copies as $copy)
	{
	    list($source, $target) = $copy;
	    $target = $this->targetFilePath($target);
	    system('cp -R ' . $source . ' ' . $target);
	}
    }

    /**
     * Create directories
     */
    public function doMkDir()
    {
        foreach ($this->mkDirectories as $directory)
        {
            $directory = $this->targetFilePath($directory);

            if (!is_dir($directory)) 
            {
                mkdir($directory);
            }
        }
    }

    /**
     * Annex
     */
    public function annex($file)
    {
	$this->annexFiles[$file] = $this->slug;

	return '<?php echo $annexLinks["' . $file . '"]; ?' . '>';
    }
}

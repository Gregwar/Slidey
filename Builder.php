<?php

namespace Gregwar\Slidey;

/**
 * Builds the slidey project
 */
class Builder extends \Twig_Extension
{
    /**
     * Metas informations
     */
    protected $metas;

    /**
     * Functions available for Twig
     */
    protected $twigFunctions = array(
        'chapter', 'part', 'annex', 'annexLink', 'toc', 'summary', 'ref', 'anchor'
    );

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
    protected $meta;

    /**
     * Files that need to be copied
     */
    protected $copies = array();

    /**
     * Directories to create
     */
    protected $mkDirectories = array();

    /**
     * Pretty links to annexes
     */
    protected $annexLinks = array();

    /**
     * Template
     */
    public $template;

    /**
     * Explore queue
     */
    public $exploreQueue;

    /**
     * Already explored
     */
    public $explored;

    public function __construct()
    {
        $this->template = new Template($this);
        $this->template->addExtension($this);
    }

    public function getFunctions()
    {
        $functions = array();

        foreach ($this->twigFunctions as $name) {
            $functions[$name] = new \Twig_Function_Method($this, $name, array('is_safe' => array('html')));
        }

        return $functions;
    }

    public function getName()
    {
        return 'slidey';
    }

    /**
     * Gets the path of a target page
     */
    public function targetFilePath($file)
    {
	return $this->targetDirectory . '/' . $file;
    }

    /**
     * Gets the path of a target page
     */
    public function pagesFilePath($file)
    {
	return $this->pagesDirectory . '/' . $file;
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

        if (!isset($argv[1]) || $argv[1] != 'clean') {
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
        $this->metas = new Metas($this->metaFilename());
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
        
        $this->template->setDirectories(getcwd() . '/' . $this->pagesDirectory, getcwd() . '/' . $this->targetDirectory);

	// Processing files
	$this->explore();

	// Generating layouts
	$this->generateLayouts();

	// Saving meta
	$this->saveMeta();

        $this->doMkDir();
        $this->doCopy();
    }

    public function addToExploreQueue($page)
    {
        if (!isset($this->explored[$page])) {
            $this->explored[$page] = true;
            $this->exploreQueue[] = $page;
        }
    }

    /**
     * Generating pages
     */
    public function explore()
    {
        $this->explored = array();
        $this->exploreQueue = array();

        foreach ($this->metas->getAll() as $file => $meta) {
            $this->addToExploreQueue($file);
        }

        $this->addToExploreQueue('index.html.twig');

        while ($this->exploreQueue) {
            $page = array_shift($this->exploreQueue);
            $this->process($page);
        }
    }

    /**
     * Processing a file
     */
    public function process($file)
    {
        $this->meta = $this->metas->metaForFile($file);

	$input = $this->pagesFilePath($this->meta->getFile());

        if ($this->meta->get('slug')) {
            $time = null;
            $output = $this->targetFilePath($this->meta->get('slug') . '.html');
            $pass = true;

            if (file_exists($output)) {
                $time = filectime($output);
            }

	    if ($time === null || $time < filectime($input))
            {
                $pass = false;
            }

            if ($pass) {
                foreach ($this->meta->get('depends', array()) as $depend) {
                    if (file_exists($depend) && $time < filectime($depend)) {
                        $pass = false;
                    }
                }
            }

            if ($pass) {
		echo '! Passing ' . $file . "\n";
                return;
            }
	}
	
        echo '* Processing ' . $file . "\n";

        $this->meta->clear();

        $contents = $this->template->render($file);
        $contents = $this->appendTwigLayout($contents);

        if ($this->meta->get('slug')) {
            $output = $this->targetFilePath($this->meta->get('slug') . '.tmp.twig');
            file_put_contents($output, $contents);
        }
    }

    /**
     * Adding a chapter
     */
    public function chapter($chapter, $slug)
    {
        $this->meta->set('slug', $slug);
        $this->meta->set('parts', array());
        $this->meta->set('annexes', array());

	if ($this->meta->get('type') == 'page') {
	   $title = $order . ' - ' . $chapter;
	} else {
	   $title = $chapter;
        }

        $this->meta->set('chapter', $title);
    }

    /**
     * Adding a part
     */
    public function part($title)
    {
        $parts =$this->meta->get('parts');
        $number = count($parts)+1;

        $parts[$number] = $title;

        $this->meta->set('parts', $parts);

	return '<h2 id="part' . $number . '">' . $number . ') ' . $title . '</h2>';
    }

    /**
     * Saving the meta file
     */
    public function saveMeta()
    {
	echo '* Saving manifest to '.$this->metas->cacheFile."\n";

        $this->metas->save();
    }

    /**
     * Generates the summary
     */
    public function summary($file, array $pages)
    {
        $summary = $this->metas->generateSummary($file, $pages);

        return $this->template->render('summary.html.twig', array(
            'summary' => $summary,
        ));
    }

    /**
     * Handles table of contents in a page
     */
    public function toc()
    {
        $pages = func_get_args();
        $toc = $this->meta->get('toc', array());

        foreach ($pages as &$page) {
            $this->addToExploreQueue($page);
            $toc[] = $page;
            $page = "'$page'";
        }
        
        $this->meta->set('toc', $toc);

        return '{{ summary("' . $this->meta->getFile() . '", [' . implode(', ', $pages) . ']) }}';
    }

    /**
     * Adds the Twig layout to contnts
     */
    public function appendTwigLayout($contents)
    {
        return
            "{% extends 'layout.html.twig' %}\n".
            "{% block contents %}\n".
            $contents . "\n".
            "{% endblock %}\n"
            ;
    }

    /**
     * Generates a browser for a page
     */
    public function generateBrowser($before, $after)
    {
        return $this->template->render('browser.html.twig', array(
            'before' => $before,
            'after' => $after
        ));
    }

    /**
     * Generates a layout for each page
     */
    public function generateLayouts()
    {
        $tocs = array();
        $beforeAfter = array();
        
        foreach ($this->metas->getAll() as $file => $meta) {
            if ($meta->has('toc')) {
                $before = $after = null;
                $toc = $meta->get('toc');

                foreach ($toc as $k => $page) {
                    $meta = $this->metas->metaForFile($page)->getAll();

                    $after = isset($toc[$k+1]) ? $this->metas->metaForFile($toc[$k+1])->getAll() : null;
                    $beforeAfter[$page] = array($before, $after);
                    $before = $meta;
                }
            }
        }

        foreach ($this->metas->getAll() as $file => $meta) {
            $before = $after = null;

            if (isset($beforeAfter[$file])) {
                list($before, $after) = $beforeAfter[$file];
            }

            $tmpFile = $this->targetFilePath($meta->get('slug') . '.tmp.twig');

            if (file_exists($tmpFile)) {

		$variables = array(
		    'browser' => $this->generateBrowser($before, $after),
		    'annexLinks' => $this->annexLinks,
		);
		
		$this->renderLayout($meta, $tmpFile, $variables);
            }
        }
    }

    protected function renderLayout($meta, $tmpFile, $variables = array())
    {
	$file = $this->targetFilePath($meta->get('slug') . '.html');

        $this->template->set('title', $meta->get('chapter'));

        $contents = $this->template->render($meta->get('slug') . '.tmp.twig', $variables);

	file_put_contents($this->targetFilePath($meta->get('slug') . '.html'), $contents);
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
        $this->meta->add('annexes', $file);

        $this->addToExploreQueue($file);

        return '{{ annexLink("'.$file.'") }}';
    }

    /**
     * Reference
     */
    public function ref($targetSlug, $anchor = '')
    {
        return $targetSlug . '.html#'.$anchor;
    }

    /**
     * Place that can be referenced
     */
    public function anchor($name)
    {
        return '<a name="'.$name.'"></a>';
    }

    /**
     * Returns a link to an annex
     */
    public function annexLink($file)
    {
        return $this->template->render('annex.html.twig', array(
            'annex' => $this->metas->metaForFile($file)->getAll()
        ));
    }
}

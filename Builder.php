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
        'chapter', 'part', 'annex'
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

        // Adding index
        $this->metas->addIndex();

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
	$files = opendir($this->pagesDirectory);

	if (!$files)
	{
	    die("! Unable to open " . $this->pagesDirectory . "\n");
	}
	else
	{
	    while ($file = readdir($files))
	    {
		if (substr($file, -5) == '.twig')
		{
		    $this->process($file, 'page');
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
        $annexes = array();

        foreach ($this->metas->getAll() as $file => $meta) {
            foreach ($meta->get('annexes', array()) as $annex) {
                $annexes[$annex] = $file;
            }
        }

        foreach ($annexes as $file => $depend) {
            $this->process($file, 'annex');

            if (!$this->meta->get('slug')) {
                continue;
            }

            $this->annexLinks[$file] = $this->template->render('annex.html.twig', array(
                'annex' => $this->meta->getAll()
            ));
	}
    }

    /**
     * Processing a file
     */
    public function process($file, $type)
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
        $this->meta->set('type', $type);

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
    public function chapter($chapter, $slug, $order = 0)
    {
        $this->meta->set('number', $order);
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
    public function generateSummary()
    {
        echo "* Generating summary\n";

        $summary = $this->metas->generateSummary();

        $contents = $this->appendTwigLayout($this->template->render('summary.html.twig', array(
            'summary' => $summary,
        )));

	file_put_contents($this->targetFilePath('index.tmp.twig'), $contents);
    }

    public function appendTwigLayout($contents)
    {
        return
                "{% extends 'layout.html.twig' %}\n".
                "{% block contents %}".$contents."\n".
                "{% endblock %}\n"
                ;
    }

    /**
     * Generates a browser for a page
     */
    public function generateBrowser($before, $current, $after)
    {
        return $this->template->render('browser.html.twig', array(
            'before' => $before,
            'current' => $current,
            'after' => $after
        ));
    }

    /**
     * Generates a layout for each page
     */
    public function generateLayouts()
    {
        $order = array();
        foreach ($this->metas->getAll() as $file => $meta) {
            if ($meta->get('type') != 'annex') {
                $order[$meta->get('order')] = $meta->get('number');
            }
        }

        foreach ($this->metas->getAll() as $file => $meta) {
            $current = $meta->get('order');
            $before = isset($order[$current-1]) ? $order[$current-1] : null;
            $after = isset($order[$current+1]) ? $order[$current+1] : null;

            $tmpFile = $this->targetFilePath($meta->get('slug') . '.tmp.twig');

            if (file_exists($tmpFile)) {
		$variables = array(
		    'browser' => $this->generateBrowser($before, $current, $after),
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

        return '{{ annexLinks["'.$file.'"]|raw }}';
    }
}

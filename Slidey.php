<?php

namespace Gregwar\Slidey;

use Gregwar\RST\Builder;

use Gregwar\RST\Nodes\RawNode;

/**
 * Slidey standard package
 */
class Slidey
{
    protected $title;
    protected $builder;
    protected $interactive = null;
    protected $mkdirs = array();

    public function __construct()
    {
        $this->builder = new Builder(new Factory);
        $this->builder->copy(__DIR__.'/static/slidey', 'slidey');
    }

    /**
     * Sets the document title prefix
     */
    public function setTitle($prefix)
    {
        $this->builder->addHook(function($document) use ($prefix) {
            $title = $document->getTitle();

            if ($title) {
                $title = $prefix . ' - ' . $title;
            } else {
                $title = $prefix;
            }

            $document->addHeaderNode(new RawNode('<title>'.htmlspecialchars($title).'</title>'));
        });
    }

    /**
     * Enable the interactive mode
     */
    public function enableInteractive($password, $directory = 'data')
    {
        $this->builder->copy(__DIR__.'/static/interactive.php');

        $this->builder->addHook(function($document) {
            $jss = array('slidey.interactive.js', 'slidey.poll.js');

            foreach ($jss as $js) {
                $document->addJs('/slidey/js/'.$js);
            }
        });

        $this->interactive = array(
            'password' => sha1($password),
            'directory' => $directory,
            'key' => uniqid('slidey_'),
        );
    }

    /**
     * Adds a stylesheet to the final document
     */
    public function addCss($css)
    {
        $this->builder->addHook(function($document) use ($css) {
            $document->addCss('/'.$css);
        });
    }

    /**
     * Copy some files from the original tree to the final document
     */
    public function copy($source, $destination = null)
    {
        $this->builder->copy($source, $destination);
    }

    public function mkdir($directory)
    {
        $this->mkdirs[] = $directory;
    }

    /**
     * Runs the slidey builder on the $source directory and put all the output
     * in the $destination directory
     */
    public function build($destination = 'web', $source = 'pages')
    {
        foreach ($this->mkdirs as $mkdir) {
            $dir = $destination . '/' . $mkdir;

            if (!is_dir($dir)) {
                mkdir($destination . '/' . $mkdir, 0755, true);
            }
        }

        $this->builder->addHook(function($document) {
            $document->addCss('/slidey/css/style.css');

            $jss = array('jquery.js', 'slidey.images.js',
                'slidey.menu.js', 'slidey.mobile.js', 'slidey.spoilers.js', 'slidey.steps.js',
                'slidey.js');

            foreach ($jss as $js) {
                $document->addJs('/slidey/js/'.$js);
            }

            $document->prependNode(new RawNode(file_get_contents(__DIR__.'/static/top.html')));
            $document->addNode(new RawNode(file_get_contents(__DIR__.'/static/bottom.html')));
            $document->addFavicon();
        });

        $this->builder->build($source, $destination);

        if ($this->interactive) {
            $config = '<?php return '.var_export($this->interactive, true).';';
            file_put_contents($this->builder->getTargetFile('config.php'), $config);
        }
    }
}

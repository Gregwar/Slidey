<?php

namespace Gregwar\Slidey;

use Gregwar\RST\Builder;
use Gregwar\RST\Reference;
use Gregwar\RST\Environment;
use Gregwar\RST\Parser;
use Gregwar\RST\Nodes\RawNode;

/**
 * Slidey standard package
 */
class Slidey extends Builder
{
    protected $title;
    protected $interactive = null;
    protected $mkdirs = array();

    /**
     * Create a Slidey instance, using the slidey kernel
     */
    public function __construct()
    {
        parent::__construct(new Kernel);
        $this->copy(__DIR__.'/static/slidey', 'slidey');
    }

    /**
     * Sets the document title prefix
     *
     * @param $title the title prefix
     */
    public function setTitle($prefix)
    {
        $this->addHook(function($document) use ($prefix) {
            $title = $document->getTitle();

            if ($title) {
                $title = $prefix . ' - ' . $title;
            } else {
                $title = $prefix;
            }

            $document->addHeaderNode(new RawNode('<title>'.htmlspecialchars($title).'</title>'));
        });

        return $this;
    }

    /**
     * Enable the interactive mode
     *
     * @param $password the password for the admin
     * @param $directory the target directory for data
     */
    public function enableInteractive($password, $directory = 'data')
    {
        $this->copy(__DIR__.'/static/interactive.php');

        $this->addHook(function($document) {
            $jss = array('slidey.interactive.js');

            foreach ($jss as $js) {
                $document->addJs('/slidey/js/'.$js);
            }
        });

        $this->interactive = array(
            'password' => sha1($password),
            'directory' => $directory,
            'key' => uniqid('slidey_'),
        );

        $this->mkdir($directory);

        return $this;
    }

    /**
     * Adds a stylesheet to the final document
     *
     * @param $css the stylesheet to add
     */
    public function addCss($css)
    {
        $this->addHook(function($document) use ($css) {
            $document->addCss('/'.$css);
        });

        return $this;
    }

    /**
     * Runs the slidey builder on the $source directory and put all the output
     * in the $destination directory
     *
     * @param $destination the destination folder
     * @param $source the source folder, containing all pages
     */
    public function build($destination = 'web', $source = 'pages')
    {
        // Handle cleaning
        global $argv;
        if (count($argv) > 1) {
            if ($argv[1] == 'clean') {
                echo "Cleaning $destination...\n";
                `rm -rf $destination`;
                exit(0);
            }   
        }

        // Add the main hooks
        $this->addHook(function($document) {
            // Adding CSS
            $document->addCss('/slidey/bootstrap/dist/css/bootstrap.css');

            // Adding JS
            $jss = array('jquery.js', 'slidey.images.js', 'slidey.permalink.js',
                'slidey.menu.js', 'slidey.mobile.js', 'slidey.spoilers.js', 'slidey.steps.js',
                'slidey.js', 'main.js');

            foreach ($jss as $js) {
                $document->addJs('/slidey/js/'.$js);
            }
            $document->addJs('/slidey/bootstrap/dist/js/bootstrap.min.js');
            $document->addJs('/slidey/highlight/highlight.pack.js');

            $environment = $document->getEnvironment();
            $home = $environment->resolve('doc', '/index');
            $home = $home['url'];
            // Adding header
            $document->prependNode(new RawNode(str_replace('%home%', $home, file_get_contents(__DIR__.'/static/top.html'))));
            // Browser
            $document->addNode(new Nodes\BrowserNode($document->getEnvironment()));
            // And bottom
            $document->addNode(new RawNode(file_get_contents(__DIR__.'/static/bottom.html')));
            $document->addFavicon();
        });

        // Run the build
        parent::build($source, $destination);

        // Write the interactive file
        if ($this->interactive) {
            $config = '<?php return '.var_export($this->interactive, true).';';
            file_put_contents($this->getTargetFile('config.php'), $config);
        }
    }

    /**
     * Creates an instance of slidey
     */
    public static function create()
    {
        return new self;
    }

    public function addReference(Reference $ref)
    {
        $this->addBeforeHook(function (Parser $parser) use ($ref) {
            $environment = $parser->getEnvironment();
            $environment->registerReference($ref);
        });

        return $this;
    }
}

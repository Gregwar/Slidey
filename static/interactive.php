<?php
session_start();

/**
 * The show state
 */
class State
{
    /**
     * Current state data
     */
    public $current = array();

    /**
     * Directory to use for reading & writing state
     */
    protected $directory;

    /**
     * Build and load the state
     */
    public function __construct($directory)
    {
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        $this->current = @include($directory . '/current.php');
        if (!$this->current) {
            $this->current = array(0, 0);
        }

        $this->directory = $directory;
    }

    /**
     * Persist the data
     */
    public function persist()
    {
        file_put_contents($this->directory . '/current.php', '<?php return '.var_export($this->current, true).';');
    }
}

/**
 * Manage interactions
 */
class Interactive
{
    /**
     * Configuration
     */
    protected $config;

    /**
     * State
     */
    protected $state;

    public function __construct(array $config)
    {
        $this->key = $config['key'];
        $this->status = isset($_SESSION[$this->key]) ? $_SESSION[$this->key] : '';
        $this->config = $config;
        $this->state = new State($config['directory']);
    }

    /**
     * Process the interactive request
     */
    public function run()
    {
        $response = null;
        $action = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

        switch ($action) {
            case '/login':
                $password = isset($_GET['password']) ? $_GET['password'] : null;

                if ($password && sha1($password) == $this->config['password']) {
                    $this->status = 'admin';
                }
                break;
            case '/follow':
                $this->status = 'follower';
                break;
            case '/update':
                $page = isset($_GET['page']) ? $_GET['page'] : 'index.html';
                $slide = isset($_GET['slide']) ? $_GET['slide'] : 0;
                $discover = isset($_GET['discover']) ? $_GET['discover'] : 0;

                $this->state->current = array($page, $slide, $discover);
                $this->state->persist();

                break;
            case '/current':
                $response = $this->state->current;
                break;
            case '/getStatus':
                break;
            case '/logout':
                $this->status = '';
                break;
        }

        if ($response === null) {
            $response = $this->status;
        }

        $_SESSION[$this->key] = $this->status;
        return $response;
    }
}

$config = @include(__DIR__ . '/config.php');
$interactive = new Interactive($config);
$response = $interactive->run();

header('Content-type: application/json');
echo json_encode($response);

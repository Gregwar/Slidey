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
     * Poll infos
     */
    public $poll = array();

    /**
     * Directory to use for reading & writing state
     */
    public $directory;

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
            $this->current = array(
                'page' => '',
                'slide' => 0,
                'discover' => 0,
            );
        }

        $this->poll = @include($directory . '/poll.php');
        if (!$this->poll) {
            $this->poll = array(
                'id' => 0,
                'size' => 0,
                'opened' => 0,
                'answers' => array()
            );
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

    /**
     * Persist poll data
     */
    public function persistPoll()
    {
        $data = '<?php return '.var_export($this->poll, true).';';
        file_put_contents($this->directory . '/poll.php', $data);
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
        $this->status = isset($_SESSION[$this->key]) ? $_SESSION[$this->key] : array();
        $this->config = $config;
        $this->state = new State($config['directory']);
    }

    /**
     * Starts a poll
     */
    public function startPoll($id, $size)
    {
        if ($size > 12) $size = 12;
        $this->state->poll = array(
            'id' => $id,
            'size' => $size,
            'opened' => 1,
            'answers' => array()
        );
        $voteDir = $this->state->directory . '/votes';
        if (is_dir($voteDir)) {
            foreach (scanDir($voteDir) as $file) {
                if ($file != '.' && $file != '..') {
                    unlink($voteDir . '/' . $file);
                }
            }
        } else {
            mkdir($voteDir);
        }
        $this->state->persistPoll();
    }

    /**
     * Vote for the poll
     */
    public function votePoll($vote)
    {
        $id = session_id();
        $voteDir = $this->state->directory . '/votes';
        $poll = &$this->state->poll;
        $vote = (int)$vote;
        if ($poll['opened'] && $vote>=0 && $vote<$poll['size']) {
            file_put_contents($voteDir . '/'.$id, $vote);
        }

    }

    /**
     * Closes the poll
     */
    public function endPoll()
    {
        $this->state->poll['opened'] = 0;
        $this->state->persistPoll();
    }

    /**
     * Info for the poll
     */
    public function infoPoll()
    {
        $poll = $this->state->poll;
        $voteDir = $this->state->directory . '/votes';
        $poll['count'] = count(scanDir($voteDir))-2;

        if ($poll['opened']) {
            unset($poll['answers']);
        } else {
            $answers = array();
            for ($i=0; $i<$poll['size']; $i++) {
                $answers[$i] = 0;
            }
            foreach (scanDir($voteDir) as $file) {
                if ($file != '.' && $file != '..') {
                    $value = (int)file_get_contents($voteDir . '/' . $file);
                    $answers[$value]++;
                }
            }
            $poll['answers'] = $answers;
        }

        return $poll;
    }

    /**
     * Process the interactive request
     */
    public function run()
    {
        $response = null;
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($action) {
            case 'login':
                $password = isset($_GET['password']) ? $_GET['password'] : null;

                if ($password && sha1($password) == $this->config['password']) {
                    $this->status['admin'] = true;
                }
                break;
            case 'follow':
                $this->status['follower'] = true;
                break;
            case 'update':
                $current = array();

                foreach (array('page', 'slide', 'discover') as $key) {
                    $current[$key] = isset($_GET[$key]) ? $_GET[$key] : null;
                    if ($current[$key] == 'null') {
                        $current[$key] = null;
                    }
                }

                $this->state->current = $current;
                $this->state->persist();

                break;
            case 'current':
                $response = $this->state->current;
                $response['status'] = $this->status;
                break;
            case 'logout':
                $this->status = array();
                break;
            case 'startPoll':
                if (isset($_GET['id']) && isset($_GET['size']) && isset($this->status['admin'])) {
                    $this->startPoll($_GET['id'], (int)$_GET['size']);
                }
                break;
            case 'votePoll':
                if (isset($_GET['answer'])) {
                    $this->votePoll((int)$_GET['answer']);
                }
                break;
            case 'endPoll':
                if (isset($this->status['admin'])) {
                    $this->endPoll();
                }
                break;
            case 'infoPoll':
                $response = $this->infoPoll();
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

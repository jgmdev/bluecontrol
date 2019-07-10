#!/usr/bin/env -S php -d extension=webkitgtk.so -d extension=pdo_sqlite.so -d short_open_tag=on -d open_basedir='' -n
<?php

chdir(__DIR__);

/**
 * Handle instances of php built-in webserver.
 */
class PHPServer
{
    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var resource
     */
    protected $process;

    /**
     * @var int
     */
    protected $proccess_id;

    /**
     * @var array
     */
    protected $pipes;

    /**
     * @var string
     */
    protected $router;

    /**
     * @var string
     */
    protected $working_dir;

    /**
     * @var string
     */
    protected $output_log;

    /**
     * @var string
     */
    protected $error_log;

    /**
     * @var array
     */
    protected $descriptors_spec;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->router = "";

        $this->working_dir = "";

        $this->output_log = "";

        $this->error_log = "";

        $this->descriptors_spec = [
            0 => ["pipe", "r"], //STDIN
            1 => ["pipe", "w"], //STDOUT
            2 => ["pipe", "w"], //STDERR
        ];
    }

    /**
     * Start a new insctance of the PHP built-in server. If the given
     * port is already in use it will increment it to the next one until
     * an open port is found.
     *
     * @param string $hostname
     * @param integer $port
     * @param string $executable Path to php binary
     * 
     * @return PHPServer
     */
    public function start(
        string $hostname="localhost", 
        int $port=8080, 
        string $executable=PHP_BINARY
    ): self
    {
        if($this->isRunning())
            $this->stop();

        while(@fsockopen($hostname, $port))
        {
            $port++;
        }

        $this->hostname = $hostname;
        $this->port = $port;

        $cwd = $this->working_dir ? "-t {$this->working_dir}" : "";

        $this->process = proc_open(
            "$executable -S $hostname:$port $cwd {$this->router}", 
            $this->descriptors_spec, 
            $this->pipes
        );

        // Set reading from the streams as non-blocking
        stream_set_blocking($this->pipes[0], 0);
        
        if($this->descriptors_spec[1][0] == "pipe")
        {
            stream_set_blocking($this->pipes[1], 0);
        }

        if($this->descriptors_spec[2][0] == "pipe")
        {
            stream_set_blocking($this->pipes[2], 0);
        }
        
        $this->proccess_id = $this->getStatus()["pid"];

        return $this;
    }

    /**
     * Terminate the running instance of the web server.
     *
     * @return PHPServer
     */
    public function stop(): self
    {
        if(is_resource($this->pipes[0]))
        {
            fclose($this->pipes[0]);
        }

        if(is_resource($this->pipes[1]))
        {
            fclose($this->pipes[1]);
        }

        if(is_resource($this->pipes[2]))
        {
            fclose($this->pipes[2]);
        }

        if(is_resource($this->process))
        {
            proc_terminate($this->process);
        }

        return $this;
    }

    /**
     * Check if the webserver is currently running.
     *
     * @return boolean
     */
    public function isRunning(): bool
    {
        if($data = $this->getStatus())
        {
            return $data["running"];
        }

        return false;
    }

    /**
     * Get the status of the instance as returned by proc_get_status().
     *
     * @return array
     */
    public function getStatus(): array
    {
        if(!is_resource($this->process))
            return [];

        // Flush buffers to prevent blocking
        if(is_resource($this->pipes[0]))
        {
            stream_get_contents($this->pipes[0]);
        }

        if(is_resource($this->pipes[1]))
        {
            stream_get_contents($this->pipes[1]);
        }

        if(is_resource($this->pipes[2]))
        {
            stream_get_contents($this->pipes[2]);
        }

        if($status = proc_get_status($this->process))
        {
            return $status;
        }

        return [];
    }

    /**
     * Hostname used to listen for connections.
     *
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * Port used to listen for connections. Use this after calling start().
     *
     * @return string
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Get the std output of the instance from the pipe or log file.
     *
     * @return string
     */
    public function getOutput(): string
    {
        if($this->output_log)
            return file_get_contents($this->output_log);

        return $this->readPipe(1);
    }

    /**
     * Get the std errors of the instance from the pipes or log file.
     *
     * @return string
     */
    public function getErrors(): string
    {
        if($this->error_log)
            return file_get_contents($this->error_log);

        return $this->readPipe(2);
    }

    /**
     * Get the instance process id.
     *
     * @return integer
     */
    public function getPID(): int
    {
        return $this->proccess_id;
    }

    /**
     * Set the php router file for the server.
     *
     * @param string $file
     * 
     * @return PHPServer
     */
    public function setRouter(string $file): self
    {
        if(!file_exists($file))
        {
            throw new Exception("Router file does not exists.");
        }

        $this->router = $file;

        return $this;
    }

    /**
     * Set the working directory path for the server.
     *
     * @param string $path
     * 
     * @return PHPServer
     */
    public function setWorkingDir(string $path): self
    {
        if(!is_dir($path))
        {
            throw new Exception("The path is not a valid directory.");
        }

        $this->working_dir = $path;

        return $this;
    }

    /**
     * Use a file for std error logging.
     *
     * @param string $file
     * @return PHPServer
     */
    public function setErrorLog(string $file): self
    {
        if(!file_exists($file))
        {
            if(file_put_contents($file, "") === false)
            {
                throw new Exception("Can not create error log file.");
            }

            unlink($file);
        }
        elseif(!is_writable($file))
        {
            throw new Exception("Can not write to error log file.");
        }

        $this->error_log = $file;
        $this->descriptors_spec[2] = ["file", $file, "a"];

        return $this;
    }

    /**
     * Use a file for std output logging.
     *
     * @param string $file
     * @return void
     */
    public function setOutputLog(string $file): void
    {
        if(!file_exists($file))
        {
            if(file_put_contents($file, "") === false)
            {
                throw new Exception("Can not create log file.");
            }

            unlink($file);
        }
        elseif(!is_writable($file))
        {
            throw new Exception("Can not write to log file.");
        }

        $this->output_log = $file;
        $this->descriptors_spec[1] = ["file", $file, "a"];
    }

    /**
     * Delete log files of previously started instance, this should be 
     * called after stop().
     *
     * @return void
     */
    public function deleteLogs(): void
    {
        if(file_exists($this->output_log))
        {
            unlink($this->output_log);
        }

        if(file_exists($this->error_log))
        {
            unlink($this->error_log);
        }
    }

    /**
     * Read any of the pipes from the process, 
     * especially 1 (STDOUT) and 2 (STDERR).
     *
     * @param integer $number Pipe number
     * 
     * @return string
     */
    private function readPipe(int $number): string
    {
        $output = "";

        while($data = fread($this->pipes[$number], 4096))
        {
            $output .= $data;
        }

        return $output;
    }
}

if($argv[1] == "ui")
{
    $server = new PHPServer();
    $server->start();

    $hostname = $server->getHostname();
    $port = $server->getPort();

    if(class_exists("\\WebKitGtk\\WebView"))
    {
        $view = new WebKitGtk\WebView("Blue Control");
        $view->loadURI("http://$hostname:$port");
        $view->resize(1200, 760);
        $view->setIcon("images/icon.svg");
        $view->show();
    }
    else
    {
        passthru("chromium --app=\"http://$hostname:$port\" 2>&1 > /dev/null");
    }

    $server->stop();
}
else
{
    include "service.php";
}
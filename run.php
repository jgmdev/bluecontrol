#!php -d extension=pdo_sqlite.so -d short_open_tag=on -d open_basedir='' -n
<?php
// WORK IN PROGRESS

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
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];
    }

    public function start(
        string $hostname="localhost", 
        int $port=8080, 
        string $executable=PHP_BINARY
    )
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
    }

    public function stop()
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
    }

    public function isRunning(): bool
    {
        if($data = $this->getStatus())
        {
            return $data["running"];
        }

        return false;
    }

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

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getOutput(): string
    {
        if($this->output_log)
            return file_get_contents($this->output_log);

        return $this->readPipe(1);
    }

    public function getErrors(): string
    {
        if($this->error_log)
            return file_get_contents($this->error_log);

        return $this->readPipe(2);
    }

    public function getPID(): int
    {
        return $this->proccess_id;
    }

    public function setRouter($file): void
    {
        if(!file_exists($file))
        {
            throw new Exception("Router file does not exists.");
        }

        $this->router = $file;
    }

    public function setWorkingDir($path): void
    {
        if(!is_dir($path))
        {
            throw new Exception("The path is not a valid directory.");
        }

        $this->working_dir = $path;
    }

    public function setErrorLog(string $file): void
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
    }

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

$server = new PHPServer();
$server->start();

$hostname = $server->getHostname();
$port = $server->getPort();

passthru("chromium --app=\"http://$hostname:$port\" 2>&1 > /dev/null");

$server->stop();
<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link https://github.com/jgmdev/bluecontrol Source code.
 */

namespace BlueControl;

/**
 * Configuration handling using a sqlite database file as backend.
 */
class Settings
{
    /**
     * @var string
     */
    private $home_directory;

    /**
     * @var string
     */
    private $settings_file;

    /**
     * @var string
     */
    private $application_name;

    /**
     * @var \PDO
     */
    private $database;

    /**
     * Constructor.
     * 
     * @param string $application_name
     */
    public function __construct(string $application_name, string $conf_dir='')
    {
        $this->settings = array();
        $this->home_directory = "";
        $this->settings_file = "";
        $this->application_name = "";

        $this->load($application_name, $conf_dir);
    }

    /**
     * Loads and initializes the configuration file.
     *
     * @param string $application_name
     * @param string $conf_dir
     * 
     * @return void
     */
    public function load(string $application_name, string $conf_dir=''): void
    {
        $this->application_name = trim($application_name);

        $this->settings = array();

        if($conf_dir == "")
        {
            if(isset($_SERVER["LOCALAPPDATA"])) //Windows
            {
                $this->home_directory = $_SERVER["HOME"]
                    . "/.config/" . $this->application_name
                ;
            }
            elseif(isset($_SERVER["HOME"])) //Unix
            {
                $this->home_directory = $_SERVER["HOME"]
                    . "/.config/" . $this->application_name
                ;
            }
            else
            {
                $username = posix_getpwuid(posix_geteuid())["name"];

                $this->home_directory = "/home/" . $username
                    . "/.config/" . $this->application_name
                ;
            }
        }
        else
        {
            $this->home_directory = rtrim($conf_dir, "/\\");
        }

        if(!file_exists($this->home_directory))
        {
            mkdir($this->home_directory);
        }

        $this->settings_file = $this->home_directory . "/settings.db";

        if(file_exists($this->settings_file))
        {
            $this->database = new \PDO("sqlite:".$this->settings_file);
        }
        else
        {
            $this->database = new \PDO("sqlite:".$this->settings_file);

            $this->database->query(
                "create table settings ("
                    . "section text, "
                    . "name text, "
                    . "value text "
                    . ")"
            );
        }
    }

    /**
     * Gets a setting from the global section.
     *
     * @param string $name
     * @param string $default
     * 
     * @return string|array
     */
    public function get(string $name, string $default="")
    {
        $statement = $this->database->prepare(
            "select * from settings where section='global' and name = ?"
        );

        $statement->execute([$name]);

        if($data = $statement->fetch(\PDO::FETCH_ASSOC))
        {
            if(substr($data["value"], 0, 2) == "a:")
                $data["value"] = unserialize($data["value"]);

            return $data["value"];
        }

        return $default;
    }

    /**
     * Gets a setting from a specific section.
     *
     * @param string $section
     * @param string $name
     * @param string $default
     * 
     * @return string|array
     */
    public function getFromSection(
        string $section, string $name, string $default=""
    )
    {
        $statement = $this->database->prepare(
            "select * from settings where section = ? and name = ?"
        );

        $statement->execute([$section, $name]);

        if($data = $statement->fetch(\PDO::FETCH_ASSOC))
        {
            if(substr($data["value"], 0, 2) == "a:")
                $data["value"] = unserialize($data["value"]);

            return $data["value"];
        }

        return $default;
    }

    /**
     * Saves a setting in the global section.
     *
     * @param string $name
     * @param string|array $value
     * 
     * @return void
     */
    public function set(string $name, $value)
    {
        if(is_array($value))
        {
            $value = serialize($value);
        }

        if($this->get($name, "|NOTSET|") == "|NOTSET|")
        {
            $statement = $this->database->prepare(
                "insert into settings (section, name, value) values("
                    . "'global', ?, ?"
                    . ")"
            );

            $statement->execute([$name, $value]);
        }
        else
        {
            $statement = $this->database->prepare(
                "update settings set "
                    . "value = ? "
                    . "where "
                    . "section = 'global' and name = ?"
            );

            $statement->execute([$value, $name]);
        }
    }

    /**
     * Saves a setting in a specific section.
     *
     * @param string $section
     * @param string $name
     * @param string|array $value
     * 
     * @return void
     */
    public function setInSection(string $section, string $name, $value)
    {
        if($this->getFromSection($section, $name, "|NOTSET|") == "|NOTSET|")
        {
            $statement = $this->database->prepare(
                "insert into settings (section, name, value) values("
                    . "?, ?, ?"
                    . ")"
            );

            $statement->execute([$section, $name, $value]);
        }
        else
        {
            $statement = $this->database->prepare(
                "update settings set "
                    . "value = ? "
                    . "where "
                    . "section = ? and name = ?"
            );

            $statement->execute([$value, $section, $name]);
        }
    }
}

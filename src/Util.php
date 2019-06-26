<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link https://github.com/jgmdev/bluecontrol Source code.
 */

namespace BlueControl;

/**
 * Easily get a list of timezones.
 */
class Util
{

    /**
     * Get a list of temperatures as used by the application.
     * 
     * @return string[] List of timezones.
     */
    static function getTemps(): array
    {
        return [
            1000,
            1500,
            2500,
            3000,
            4000,
            5500,
            6500
        ];
    }

    /**
     * Get a list of time periods of the day as used by the application.
     * 
     * @return string[] List of timezones.
     */
    static function getTimes(): array
    {
        return [
            "MORNING",
            "AFTERNOON",
            "EVENING",
            "NIGHT"
        ];
    }

}
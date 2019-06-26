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

    /**
     * Get the hours range for each time of the day.
     * 
     * @return string[] List of timezones.
     */
    static function getDayRanges(): array
    {
        return [
            "MORNING" => [5, 11],
            "AFTERNOON" => [12, 17],
            "EVENING" => [18, 21],
            "NIGHT" => [22, 4]
        ];
    }

    /**
     * Gets the current day period of the day.
     *
     * @return string
     */
    static function getDayPeriod(): string
    {
        $current_hour = date("G", time());
        
        foreach(self::getDayRanges() as $time => $range)
        {
            $match = false;

            if($range[0] < $range[1])
            {
                if(
                    $current_hour >= $range[0] 
                    && 
                    $current_hour <= $range[1]
                )
                {
                    $match = true;
                }
            }
            else
            {
                if(
                    $current_hour >= $range[0] 
                    && 
                    $current_hour <= 23
                )
                {
                    $match = true;
                }
                elseif(
                    $current_hour >= 0 
                    && 
                    $current_hour <= $range[1]
                )
                {
                    $match = true;
                }
            }

            if($match)
            {
                return strtolower($time);
            }
        }

        return "";
    }

    /**
     * Default configuration settings.
     *
     * @param Settings $settings
     * @return void
     */
    static function setDefaults(Settings $settings)
    {
        if($settings->get("automatic") == "")
        {
            $settings->set("automatic", "1");
        }

        if($settings->get("timezone") == "")
        {
            $settings->set("automatic", "UTC");
        }

        if($settings->getFromSection("time_day", "morning") == "")
        {
            $settings->setInSection("time_day", "morning", "5500");
        }

        if($settings->getFromSection("time_day", "afternoon") == "")
        {
            $settings->setInSection("time_day", "afternoon", "6500");
        }

        if($settings->getFromSection("time_day", "evening") == "")
        {
            $settings->setInSection("time_day", "evening", "4000");
        }

        if($settings->getFromSection("time_day", "night") == "")
        {
            $settings->setInSection("time_day", "night", "3000");
        }
    }

}
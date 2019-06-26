<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link https://github.com/jgmdev/bluecontrol Source code.
 */

require "src/Autoloader.php";
require "vendor/autoload.php";

use BlueControl\Util;
use BlueControl\XRandR;
use BlueControl\Settings;

XRandR::init();

$settings = new Settings("BlueControl");
Util::setDefaults($settings);

// Get/Set timezone
$timezone = $settings->get("timezone", "UTC");
date_default_timezone_set($timezone);

// Get current temperature
if(($value = $settings->get("current_temperature")) != "")
{
    XRandR::setTemperature(floatval($value));
}

// Check if automatic cycling is enabled
$auto = $settings->get("automatic", "0");
$auto = $auto == "" ? "0" : $auto;

while(true)
{
    if($auto == "1")
    {
        XRandR::init();

        $current_temp = floatval(
            $settings->get("current_temperature", "4000")
        );

        $new_temp = floatval(
            $settings->getFromSection(
                "time_day",
                Util::getDayPeriod()
            )
        );

        if($current_temp != $new_temp)
        {
            $settings->set("current_temperature", $new_temp);

            XRandR::setTemperatureGradually($current_temp, $new_temp);
        }
    }

    sleep(60 * 10); // Run every 10 minutes
}
<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link https://github.com/jgmdev/bluecontrol Source code.
 */

namespace BlueControl;

class XRandR
{
    static $monitor = "";

    // Predifined Color temperatures.
    const C1000K = 1000;
    const C4000K = 4000;
    const C5500K = 5500;
    const C6500K = 6500;
    const C10000K = 10000;

    /**
     * Detects the active monitor.
     *
     * @return void
     */
    static function init()
    {
        ob_start();
        self::$monitor = system(
            "xrandr --listactivemonitors | cut -d' ' -f6 | tail -n 1"
        );
        ob_end_clean();
    }

    static function getDescription(int $temperature): string
    {
        $temperatures = [
            1000 => "Lowest value (super warm/red)",
            1500 => "Almost the lowest value",
            2500 => "The night is really dark",
            3000 => "The night is getting darker",
            4000 => "Default night light on temperature",
            5500 => "Balanced night light temperature",
            6500 => "Default night light off temperature",
            10000 => "Highest value (super cool/blue)"
        ];

        return $temperatures[$temperature];
    }

    /**
     * Converts a color temperature expressed in kelvin to RGB.
     * 
     * Credit for this algorithm on:
     * http://www.tannerhelland.com/4435/convert-temperature-rgb-algorithm-code/
     *
     * @param float $temperature
     * 
     * @return array [r, g, b]
     */
    static function kelvinToRGB(float $temperature): array
    {
        $temperature = $temperature / 100;
    
        // Calculate Red
        $red = 0;

        if($temperature <= 66)
        {
            $red = 255;
        }
        else
        {
            $red = $temperature - 60;
            $red = 329.698727446 * ($red ^ -0.1332047592);
            $red = $red < 0 ? 0 : $red;
            $red = $red > 255 ? 255 : $red;
        }
        
        // Calculate Green
        $green = 0;

        if($temperature <= 66)
        {
            $green = $temperature;
            $green = 99.4708025861 * log($green) - 161.1195681661;
            $green = $green < 0 ? 0 : $green;
            $green = $green > 255 ? 255 : $green;
        }
        else
        {
            $green = $temperature - 60;
            $green = 288.1221695283 * ($green ^ -0.0755148492);
            $green = $green < 0 ? 0 : $green;
            $green = $green > 255 ? 255 : $green;
        }
        
        // Calculate Blue
        $blue = 0;

        if($temperature >= 66)
        {
            $blue = 255;
        }
        {
            if($temperature <= 19)
            {
                $blue = 0;
            }
            else
            {
                $blue = $temperature - 10;
                $blue = 138.5177312231 * log($blue) - 305.0447927307;
                $blue = $blue < 0 ? 0 : $blue;
                $blue = $blue > 255 ? 255 : $blue;
            }
        }

        return ["r" => $red, "g" => $green, "b" => $blue];
    }

    static function getRGBString(float $temperature): string
    {
        $rgb = self::kelvinToRGB($temperature);

        return "{$rgb['r']}, {$rgb['g']}, {$rgb['b']}";
    }

    static function setTemperature(float $temperature): string
    {
        $rgb = self::kelvinToRGB($temperature);

        $r = self::byteToGamma($rgb["r"]);
        $g = self::byteToGamma($rgb["g"]);
        $b = self::byteToGamma($rgb["b"]);

        return system(
            "xrandr "
                . "--output ".self::$monitor
                . " --gamma $r:$g:$b"
        );
    }

    static function setTemperatureGradually(float $from, float $to): void
    {
        if($from > $to)
        {
            for($i=$from; $i>$to; $i-=100)
            {
                self::setTemperature($i);
    
                usleep(1000*20);
            }
        }
        else
        {
            for($i=$from; $i<=$to; $i+=100)
            {
                self::setTemperature($i);

                usleep(1000*20);
            }
        }

        self::setTemperature($to);
    }

    static function byteToGamma($byte): float
    {
        $percent = $byte / 255;

        return $percent * 1.0;
    }
}
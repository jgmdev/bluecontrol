<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link https://github.com/jgmdev/bluecontrol Source code.
 */

require "src/Autoloader.php";
require "vendor/autoload.php";

use Puente\Puente;
use BlueControl\Util;
use BlueControl\XRandR;
use BlueControl\Settings;
use BlueControl\Timezones;

XRandR::init();

$settings = new Settings("BlueControl");
Util::setDefaults($settings);

// Get/Set timezone
$timezone = $settings->get("timezone");
date_default_timezone_set($timezone);

// Get current temperature
$current_temp = 0;
if(($value = $settings->get("current_temperature")) != "")
{
    $current_temp = $value;
    XRandR::setTemperature(floatval($value));
}

// Get automatic cycling configuration
$auto = $settings->get("automatic");

// Make the puente to handle UI events and more...
$puente = new Puente();

$puente->addCode("currentTemp = $current_temp;");

$puente->jq("#menu input[type='radio'][value='$auto']")->prop([
    "checked" => "true"
]);

$puente->jq("div.buttons a")->click(
    function(Puente $puente, $data) use($settings){
        $value = intval(str_replace("K", "", $data["val"]));

        if(is_numeric($data["current"]) && $data["current"] > 0)
        {
            XRandR::setTemperatureGradually(
                intval($data["current"]), 
                $value
            );
        }
        else
        {
            XRandR::setTemperature($value);
        }

        $settings->set("current_temperature", $value);
        
        $puente->jq("div.buttons a")->removeClass("active");
        $puente->jq("#".$data["id"])->addClass("active");

        $puente->addCode("currentTemp = $value;");

        if($data["auto"] == "1")
        {
            $puente->jq("#menu input[type='radio'][value='0']")->prop([
                "checked" => "true"
            ]);

            $settings->set("automatic", "0");

            $puente->addCode("myPopup.show();");
        }
    },
    "{"
        . "val: jq(this).html(), "
        . "id: jq(this).attr('id'), "
        . "current: currentTemp, "
        . "auto: jq('#menu input[name=\\'auto\\']:checked').val()"
    . "}"
);

$puente->jq("div.buttons a")->mouseenter(
    function(Puente $puente, $data){
        $value = intval(str_replace("K", "", $data["val"]));
            
        if($data["visible"] == "false"){
            $puente->jq("#description")
                ->css(array("opacity" => "0.0"))
                ->show()
            ;
        }

        $puente->jq("#description")->show()->fadeTo(
            "150", 0.8, function(Puente $puente, $data) use($value){
                $puente->jq("#description")
                    ->html(XRandR::getDescription($value))
                    ->fadeTo("150", 1.0)
                ;
            }
        );
    }, 
    "{"
        . "val: jq(this).html(), "
        . "id: jq(this).attr('id'), "
        . "visible: jq('#description').is(':visible')"
    . "}"
);

$puente->jq("#menu input[type='radio']")->change(
    function(Puente $puente, $data) use($settings){
        $settings->set("automatic", $data["value"]);
    }, 
    "{"
        . "name: jq(this).attr('name'), "
        . "value: jq(this).val()"
    . "}"
);

$puente->jq("#menu select")->change(
    function(Puente $puente, $data) use($settings){
        if($data["name"] == "timezone")
        {
            $settings->set(
                "timezone", 
                $data["value"]
            );
        }
        else
        {
            $settings->setInSection(
                "time_day",
                strtolower($data["name"]), 
                $data["value"]
            );
        }
    }, 
    "{"
        . "name: jq(this).attr('name'), "
        . "value: jq(this).val()"
    . "}"
);

// Seems to only work on chromium app mode
$puente->jq("js:window")->on(
    "resize",
    function(Puente $puente, $data)
    {
        if($data["width"] <= 400 && $data["height"] < 750)
        {
            $puente->window()->resizeTo(400, 750);
        }
        elseif($data["width"] <= 500 && $data["height"] < 750)
        {
            $puente->window()->resizeTo(500, 750);
        }
        elseif($data["width"] <= 400)
        {
            $puente->window()->resizeTo(400, 750);
        }
    },
    "{width: window.innerWidth, height: window.innerHeight}"
);

// Start listening for the UI events
$puente->listenRequest();
?>
<html>
<head>
<title>Blue Light Control</title>
<link rel="icon" type="image/svg+xml" href="images/icon.svg" />
<link rel="icon" type="image/png" href="images/icon.png" sizes="128x128" />
<link rel="stylesheet" type="text/css" href="css/jquery.simplepopup.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="js/jquery-3.4.1.js"></script>
<script src="js/jquery.sidemenu.js"></script>
<script src="js/jquery.simplepopup.js"></script>
<script>
$(document).ready(function(){
    $('#menu').sideMenu({
        width: '300px',
        position: "right",
        duration: 1000,
        button: 'header .menu',
        hideButton: "#menu .hide",
        zIndex: 5000,
        scroll: true,
        rememberStatus: false,
        onShow: null,
        onHide: null
    });

    myPopup = $('#popup').simplePopup({
        delay: 0,
        overlayColor: 'rgba(50, 50, 50, 0.6)',
        button: null,
        hideButton: null,
        zIndex: 5000,
        displayOnce: false,
        onShow: null,
        onHide: null,
        autoShow: false,
        showClose: false,
        showOk: true,
        okLabel: "Ok",
        effectDelay: 300,
        onMouseLeave: false,
        title: ""
    });
});
</script>
<?php $puente->executeCode(); ?>
</head>

<body>
    <div id="background">
        <header>
            <a 
                class="logo" 
                title="Blue Control" 
                href="https://github.com/jgmdev/bluecontrol"
                target="_blank"
            >
                <img alt="Blue Control" src="images/logo.png" />
            </a>
            <a class="menu"><img src="images/menu.png" /></a>
        </header>

        <div id="menu">
            <div class="container">
                <a class="hide">X</a>

                <h2>Timezone</h2>

                <select name="timezone" id="timezone">
                    <?php 
                        foreach(Timezones::getList() as $tz){
                            $selected = "";
                            if($timezone == $tz)
                            {
                                $selected .= 'selected="selected"';
                            }

                            print '<option '.$selected.' value="'.$tz.'">'
                                . $tz
                                . '</option>'
                            ;
                        }
                    ?>
                </select>

                <h2>Automatic Temperature</h2>
                <input id="auto_on" type="radio" name="auto" value="1" /> 
                <label for="auto_on">ON</label>
                <input id="auto_off" type="radio" name="auto" value="0" /> 
                <label for="auto_off">OFF</label>

                <h2>Cycling Settings</h2>

                <?php foreach(Util::getTimes() as $day){ ?>
                <label for="day<?=$day?>"><?=$day?></label>
                <select name="<?=strtolower($day)?>" id="day<?=$day?>">
                    <?php 
                        $temp_value = $settings->getFromSection(
                            "time_day",
                            strtolower($day), 
                            "4000"
                        );

                        foreach(Util::getTemps() as $temp){
                            $selected = "";
                            if($temp == $temp_value)
                            {
                                $selected = 'selected="selected"';
                            }
                            
                            print '<option '.$selected.' value="'.$temp.'">'
                                . $temp .'K'
                                . '</option>'
                            ;
                        }
                    ?>
                </select>
                <?php } ?>
            </div>
        </div>

        <div class="buttons">
            <?php foreach(Util::getTemps() as $temp){ ?>
            <?php 
                $active=""; 
                if($temp == $current_temp){
                    $active = 'class="active"';
                } 
            ?>
            <a 
                <?=$active?> 
                style="background-color: rgb(<?=XRandR::getRGBString($temp)?>);" 
                id="color<?=$temp?>"
            >
                <?=$temp?>K
            </a>
            <? } ?>
        </div>

        <div id="description">
            Choose a Color Temperature
        </div>

        <div id="popup">
            Automatic temperature cycling disabled.
            <p>
                When manually choosing a color temperature the automatic
                cycling of color temperatures based on the time of day
                is disabled. Enable it again from the menu if needed.
            </p>
        </div>
    </div>
</body>

</html>
#!/usr/bin/env bash
# bluecontrol launcher script.

APP_PATH="$(pwd)"

cd $APP_PATH

PHP_FOUND=$(command -v php)

if [ "$PHP_FOUND" = "" ]; then
    echo "Please install php (http://php.net/)"
    exit
fi

runui()
{
    local port=8079

    if [ "$1" != "" ]; then
        if [ "$1" != "ui" ]; then
            port=$1
        fi
    fi

    local port_open=0
    local server=""

    until [ $port_open -eq 1 ]; do
        port=$(($port+1))

        port_open=$(netstat -an | grep $port | grep LISTEN)

        if [ "$port_open" = "" ]; then
            port_open=1
        fi
    done

    php -d short_open_tag=on -d open_basedir='' -S localhost:$port &

    server=$(pgrep -n -f php)

    local browser=""

    local chromium_installed=$(command -v chromium)
    local firefox_installed=$(command -v firefox)

    if [ -n "$chromium_installed" ]; then
        chromium --app="http://localhost:$port" &
        browser="chromium"
    elif [ -n "$firefox_installed" ]; then
        firefox "http://localhost:$port" &
        browser="firefox"
    fi

    sleep 7 # wait to properly get browser process id

    local id=$(pgrep -n -f $browser)

    if [ "$browser" != "chromium" ]; then
        while [ "$(ps -A | grep $id)" != "" ]; do
            sleep 10
        done
    else
        while [ "$(wmctrl -l | grep 'Blue Light Control')" != "" ]; do
            sleep 10
        done
    fi

    echo -n "Shutting webserver down... "
    kill $server
    echo "(Done!)"
}

case $1 in
    'ui' )
        shift 2
        runui $1
        exit
        ;;
esac

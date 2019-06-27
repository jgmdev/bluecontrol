# Blue Control

Color temperature adjustment tool that automatically enters night mode for
you.

![BlueColor Logo](https://raw.githubusercontent.com/jgmdev/bluecontrol/master/images/icon.png)

**OS:** Linux
**License:** MIT
**Status:** Beta

## About
Blue Control is a PHP application that lets you control your display 
color temperature in order to reduce eye strain at nights. It uses the
[Puente](https://github.com/jgmdev/puente) library and serves as a showcase
of the things that can be done with it. This application uses PHP's builtin
webserver to serve the GUI and chromium **app** flag to show it. Also the
application optionally makes use of systemd to register a service that 
automatically changes the color temperature for you depending on the time
of the day and your color temperature measures.

There are four day time ranges where you can choose the desired color temperature:

* Morning = 5:00AM - 11:00PM
* Afternoon = 12:00PM - 5:00PM
* Evening = 6:00PM - 10:00PM
* Night = 11:00PM - 4:00PM

## Installation

Before installing make sure you have the following dependencies installed:

**Dependencies**

* php - interface and service that automatically changes color temperature
* php-sqlite - store the application settings
* chromium - display the user interface
* xorg-xrandr - change color temperature
* wmctrl - used to check chromium status
* composer - to retrieve application dependancies (puente)

By default the application is configured to be installed on /usr and it can
be installed as follows:

```sh
composer install
make
sudo make install
```

If you want to use another directory you can specify it to the make command
before installing:

```sh
make PREFIX=/my/path
```

Also if you are going to package the application for a linux distribution
you can give DESTDIR to make in order to install into that specific directory
for later compressing, etc...

```sh
make DESTDIR=/packages/bluecontrol/tree install
```

## Screenshots

### Main
![day](https://raw.githubusercontent.com/jgmdev/bluecontrol/master/screenshots/day.png)

## Settings
![settings](https://raw.githubusercontent.com/jgmdev/bluecontrol/master/screenshots/settings.png)

### Compact
![compact](https://raw.githubusercontent.com/jgmdev/bluecontrol/master/screenshots/compact.png)
![compact settings](https://raw.githubusercontent.com/jgmdev/bluecontrol/master/screenshots/compact-settings.png)
DESTDIR = ""
PREFIX = "/usr"

.PHONY: install uninstall

all:
	@sed "s|APP_PATH='/usr/lib/bluecontrol'|APP_PATH='${PREFIX}/lib/bluecontrol'|g" \
	resources/bluecontrol > bluecontrol
	
	@chmod 0755 bluecontrol

	@sed "s|Exec=/usr/bin/bluecontrol ui|Exec=${PREFIX}/bin/bluecontrol ui|g" \
	resources/bluecontrol.desktop > bluecontrol.desktop

	@sed "s|ExecStart=/usr/bin/bluecontrol|ExecStart=${PREFIX}/bin/bluecontrol|g" \
	resources/bluecontrol.service > bluecontrol.service

install:
	@if [ ! -e "bluecontrol" ]; then\
		echo "Run 'make all' before install.";\
		exit 1;\
	fi

	@if [ ! -d "${DESTDIR}${PREFIX}/lib/bluecontrol" ]; then\
		mkdir -p "${DESTDIR}${PREFIX}/lib/bluecontrol";\
	fi

	@if [ ! -d "${DESTDIR}${PREFIX}/bin" ]; then\
		mkdir -p "${DESTDIR}${PREFIX}/bin";\
	fi

	@if [ ! -d "${DESTDIR}${PREFIX}/share/applications" ]; then\
		mkdir -p "${DESTDIR}${PREFIX}/share/applications";\
	fi

	@if [ ! -d "${DESTDIR}${PREFIX}/lib/systemd/user/" ]; then\
        mkdir -p "${DESTDIR}${PREFIX}/lib/systemd/user/";\
    fi

	@if [ ! -d "${DESTDIR}${PREFIX}/share/icons/hicolor/48x48/apps/" ]; then\
        mkdir -p "${DESTDIR}${PREFIX}/share/icons/hicolor/48x48/apps/";\
    fi

	@echo -n 'Copying files.'

	@cp -r src "${DESTDIR}${PREFIX}/lib/bluecontrol/" > /dev/null 2>&1
	@cp -r js "${DESTDIR}${PREFIX}/lib/bluecontrol/" > /dev/null 2>&1
	@cp -r css "${DESTDIR}${PREFIX}/lib/bluecontrol/" > /dev/null 2>&1
	@cp -r images "${DESTDIR}${PREFIX}/lib/bluecontrol/" > /dev/null 2>&1
	@cp -r vendor "${DESTDIR}${PREFIX}/lib/bluecontrol/" > /dev/null 2>&1

	@echo -n '.'

	@cp index.php "${DESTDIR}${PREFIX}/lib/bluecontrol/" > /dev/null 2>&1
	@cp service.php "${DESTDIR}${PREFIX}/lib/bluecontrol/" > /dev/null 2>&1
	@cp LICENSE "${DESTDIR}${PREFIX}/lib/bluecontrol/" > /dev/null 2>&1

	@cp bluecontrol "${DESTDIR}${PREFIX}/bin/" > /dev/null 2>&1
	@cp bluecontrol.desktop "${DESTDIR}${PREFIX}/share/applications/" > /dev/null 2>&1
	@cp bluecontrol.service "${DESTDIR}${PREFIX}/lib/systemd/user/" > /dev/null 2>&1
	@cp images/icon.svg "${DESTDIR}${PREFIX}/share/icons/hicolor/48x48/apps/bluecontrol.svg" > /dev/null 2>&1

	@echo -n '.'

	@rm bluecontrol
	@rm bluecontrol.desktop
	@rm bluecontrol.service

	@echo " (done)"

uninstall:
	@echo -n 'Removing installation files...'

	@rm -rf "${DESTDIR}${PREFIX}/lib/bluecontrol" > /dev/null 2>&1
	@rm "${DESTDIR}${PREFIX}/bin/bluecontrol" > /dev/null 2>&1
	@rm "${DESTDIR}${PREFIX}/share/applications/bluecontrol.desktop" > /dev/null 2>&1
	@rm "${DESTDIR}${PREFIX}/lib/systemd/user/bluecontrol.service" > /dev/null 2>&1
	@rm "${DESTDIR}${PREFIX}/share/icons/hicolor/48x48/apps/bluecontrol.svg" > /dev/null 2>&1

	@echo " (done)"

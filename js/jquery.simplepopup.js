/**
 * Copyright 2016, Jefferson González (https://github.com/jgmdev/simplepopup)
 * This file is licensed with the MIT License, check the LICENSE file
 * for version and details or visit https://opensource.org/licenses/MIT
 */

$.fn.simplePopup = function(options) {
    var defaults = {
        delay: 0,
        overlayColor: 'rgba(50, 50, 50, 0.4)',
        button: null,
        hideButton: null,
        zIndex: 5000,
        displayOnce: false,
        onShow: null,
        onHide: null,
        autoShow: true,
        showClose: true,
        showOk: true,
        okLabel: "Ok",
        effectDelay: 300,
        onMouseLeave: false,
        popupID: null,
        title: ""
    };

    var settings = $.extend({}, defaults, options);
    var parent = this;

    this.popupID = this.selector;

    this.showPopup = function(popup, force){
        if(parent.displayed && !force){
            return;
        }

        if(settings.displayOnce && !force){
            if(localStorage){
                if(localStorage.getItem(parent.popupID) == "1"){
                    return;
                }
            }
        }

        parent.displayed = true;

        $("body").css({overflow: "hidden"});

        popup.css({
            width: $(window).width(),
            height: $(window).height()
        });

        var container = popup.find(".popup-container");
        container.css({display: "none"});

        popup.fadeIn(settings.effectDelay, function(){
            container.css({
                top: ($(window).outerHeight() / 2) - (container.outerHeight() / 2),
                left: ($(window).outerWidth() / 2) - (container.outerWidth() / 2)
            });

            container.fadeIn(200);
        });

        if(settings.onShow){
            settings.onShow();
        }

        if(settings.displayOnce){
            if(localStorage){
                localStorage.setItem(parent.popupID, "1");
            }
        }
    }

    this.hidePopup = function(popup){
        popup.fadeOut(settings.effectDelay, function(){
            $("body").css({overflow: "auto"});
        });

        if(settings.onHide){
            settings.onHide();
        }
    }

    this.each(function() {
        var messageHTML = $(this).detach();
        var overlay = $('<div class="popup-overlay"></div>');
        var popup = $('<div class="popup-container"></div>');
        var close = $('<div class="popup-close"><span>'+settings.title+'</span><a>X</a></div>');
        var ok = $('<div class="popup-ok"><button>'+settings.okLabel+'</button></div>');

        if(settings.popupID){
            parent.popupID = settings.popupID;
        }

        messageHTML.css({display: "block"});

        overlay.css({
            display: "none",
            position: "fixed",
            zIndex: settings.zIndex,
            top: 0,
            left: 0,
            width: $(window).width(),
            height: $(window).height(),
            backgroundColor: settings.overlayColor
        });

        if(settings.showClose){
            popup.append(close);

            popup.find(".popup-close").click(function(event){
                parent.hidePopup(overlay);
                event.preventDefault();
            });
        }

        popup.append(messageHTML);

        if(settings.showOk){
            popup.append(ok);

            popup.find(".popup-ok").click(function(event){
                parent.hidePopup(overlay);
                event.preventDefault();
            });
        }

        overlay.append(popup);

        popup.css({
            position: "fixed",
            display: "inline",
            top: ($(window).height() / 2) - (popup.outerHeight() / 2),
            left: ($(window).width() / 2) - (popup.outerWidth() / 2)
        });

        if(settings.button){
            $(settings.button).click(function(event){
                if($(overlay).css("display") == "none"){
                    parent.showPopup(overlay);
                } else{
                    parent.hidePopup(overlay);
                }
                event.preventDefault();
            });
        }

        if(settings.hideButton){
            $(settings.hideButton).click(function(event){
                parent.hidePopup(overlay);
                event.preventDefault();
            });
        }

        $(window).resize(function(){
            $(overlay).css({
                width: $(window).width(),
                height: $(window).height()
            });

            $(popup).css({
                top: ($(window).height() / 2) - (popup.outerHeight() / 2),
                left: ($(window).width() / 2) - (popup.outerWidth() / 2)
            });
        });

        $("body").append(overlay);

        parent.overlay = overlay;

        if(settings.displayOnce){
            if(localStorage){
                if(localStorage.getItem(parent.popupID) == "1"){
                    return;
                }
            }
        }

        if(settings.autoShow && !settings.onMouseLeave){
            setTimeout(
                function(){
                    parent.showPopup(overlay);
                },
                settings.delay
            );
        }

        if(settings.onMouseLeave){
            $(document).mouseleave(function(){
                parent.showPopup(overlay);
            })
        }

        $(document).keyup(function(e) {
            if (e.keyCode == 27) {
                 parent.hidePopup(overlay);
            }
        });
    });

    this.show = function(){
        parent.showPopup(parent.overlay, true);
    };

    this.hide = function(){
        parent.hidePopup(parent.overlay);
    };

    return this;
};

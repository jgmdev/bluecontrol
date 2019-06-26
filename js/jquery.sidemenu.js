/**
 * Copyright 2016, Jefferson González (https://github.com/jgmdev/sidemenu)
 * This file is licensed with the MIT License, check the LICENSE file
 * for version and details or visit https://opensource.org/licenses/MIT
 */

$.fn.sideMenu = function(options) {
    var defaults = {
        width: null,
        position: "right",
        duration: 1000,
        button: null,
        hideButton: null,
        zIndex: 5000,
        scroll: false,
        rememberStatus: false,
        onShow: null,
        onHide: null
    };

    var settings = $.extend({}, defaults, options);

    this.each(function() {
        var sideMenuParent = this;

        if(!settings.width){
            settings.width = $(this).outerWidth(true);
        }

        $(this).css({
            display: "none",
            position: "fixed",
            zIndex: settings.zIndex,
            top: 0,
            width: parseInt(settings.width) + "px"
        });

        if(settings.position == "right"){
            $(this).css({
                right: -($(this).width())
            });
        } else {
            $(this).css({
                left: "-" + settings.width
            });
        }

        if(settings.button){
            $(settings.button).click(function(event){
                if($(sideMenuParent).css("display") == "none"){
                    showSideMenu(sideMenuParent);
                } else{
                    hideSideMenu(sideMenuParent);
                }
                event.preventDefault();
            });
        }

        if(settings.hideButton){
            $(settings.hideButton).click(function(event){
                hideSideMenu(sideMenuParent);
                event.preventDefault();
            });
        }

        if(settings.scroll){
            $(sideMenuParent).css("overflowY", "auto");
        }

        if(settings.rememberStatus){
            if(localStorage){
                if(
                    localStorage.getItem(settings.button) == "open"
                    &&
                    //Dont do it on small screens
                    ($(window).width() > $(sideMenuParent).width()*2)
                )
                {
                    showSideMenu(sideMenuParent);
                }
            }
        }
    });

    function showSideMenu(menu){
        $(menu).css({
            display: "block",
            height: $(document).height()
        });

        if(settings.position == "right"){
            $(menu).animate(
                {
                    right: 0
                },
                settings.duration,
                function(){
                    $(menu).css({
                        position: "absolute"
                    });
                }
            );
        } else{
            $(menu).animate(
                {
                    left: 0
                },
                settings.duration,
                function(){
                    $(menu).css({
                        position: "absolute"
                    });
                }
            );
        }

        if(settings.rememberStatus){
            if(localStorage){
                localStorage.setItem(settings.button, "open");
            }
        }

        if(settings.onShow){
            settings.onShow();
        }
    }

    function hideSideMenu(menu){
        $(menu).css({
            position: "fixed"
        });

        if(settings.position == "right"){
            $(menu).animate(
                {
                    right: -($(menu).width())
                },
                settings.duration,
                function(){
                    $(menu).css({display: "none"});
                }
            );
        } else{
            $(menu).animate(
                {
                    left: "-" + settings.width
                },
                settings.duration,
                function(){
                    $(menu).css({display: "none"});
                }
            );
        }

        if(settings.rememberStatus){
            if(localStorage){
                localStorage.setItem(settings.button, "");
            }
        }

        if(settings.onHide){
            settings.onHide();
        }
    }

    return this;
};

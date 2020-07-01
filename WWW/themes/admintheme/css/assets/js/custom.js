/*
Template Name: Material Pro Admin
Author: Themedesigner
Email: niravjoshi87@gmail.com
File: js
*/
jQuery(function() {
    "use strict";
    jQuery(function() {
        jQuery(".preloader").fadeOut();
    });

    // ==============================================================
    // This is for the top header part and sidebar part
    // ==============================================================
    var set = function() {
        var width = (window.innerWidth > 0) ? window.innerWidth : this.screen.width;
        var topOffset = 70;
        if (width < 1170) {
            jQuery("body").addClass("mini-sidebar");
            jQuery('.navbar-brand span').hide();
            jQuery(".scroll-sidebar, .slimScrollDiv").css("overflow-x", "visible").parent().css("overflow", "visible");
            jQuery(".sidebartoggler i").addClass("ti-menu");
        } else {
            jQuery("body").removeClass("mini-sidebar");
            jQuery('.navbar-brand span').show();
            //jQuery(".sidebartoggler i").removeClass("ti-menu");
        }

        var height = ((window.innerHeight > 0) ? window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            jQuery(".page-wrapper").css("min-height", (height) + "px");
        }
    };
    jQuery(window).ready(set);
    jQuery(window).on("resize", set);

    // topbar stickey on scroll
    jQuery(".fix-header .topbar").stick_in_parent({});

    // this is for close icon when navigation open in mobile view
    jQuery(".nav-toggler").click(function() {
        jQuery("body").toggleClass("show-sidebar");
        jQuery(".nav-toggler i").toggleClass("ti-menu");
        jQuery(".nav-toggler i").addClass("ti-close");
    });
    jQuery(".sidebartoggler").on('click', function() {
        //jQuery(".sidebartoggler i").toggleClass("ti-menu");
    });
    jQuery(".search-box a, .search-box .app-search .srh-btn").on('click', function() {
        jQuery(".app-search").toggle(200);
    });

    // ==============================================================
    // Auto select left navbar
    // ==============================================================
    jQuery(function() {
        var url = window.location;
        var element = jQuery('ul#sidebarnav a').filter(function() {
            return this.href == url;
        }).addClass('active').parent().addClass('active');
        while (true) {
            if (element.is('li')) {
                element = element.parent().addClass('in').parent().addClass('active');
            } else {
                break;
            }
        }
    });
    // ==============================================================
    //tooltip
    // ==============================================================
    jQuery(function() {
            jQuery('[data-toggle="tooltip"]').tooltip()
        })
        // ==============================================================
        // Sidebarmenu
        // ==============================================================
    jQuery(function() {
        jQuery('#sidebarnav').metisMenu();
    });
    // ==============================================================
    // Slimscrollbars
    // ==============================================================
    jQuery('.scroll-sidebar').slimScroll({
        position: 'left',
        size: "5px",
        height: '100%',
        color: '#dcdcdc'
    });
    // ==============================================================
    // Resize all elements
    // ==============================================================
    jQuery("body").trigger("resize");
});

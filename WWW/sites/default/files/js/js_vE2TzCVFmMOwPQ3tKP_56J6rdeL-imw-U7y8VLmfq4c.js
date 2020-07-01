window.matchMedia||(window.matchMedia=function(){"use strict";var e=window.styleMedia||window.media;if(!e){var t=document.createElement("style"),i=document.getElementsByTagName("script")[0],n=null;t.type="text/css";t.id="matchmediajs-test";i.parentNode.insertBefore(t,i);n="getComputedStyle"in window&&window.getComputedStyle(t,null)||t.currentStyle;e={matchMedium:function(e){var i="@media "+e+"{ #matchmediajs-test { width: 1px; } }";if(t.styleSheet){t.styleSheet.cssText=i}else{t.textContent=i}return n.width==="1px"}}}return function(t){return{matches:e.matchMedium(t||"all"),media:t||"all"}}}());
;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

Drupal.debounce = function (func, wait, immediate) {
  var timeout = void 0;
  var result = void 0;
  return function () {
    for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    var context = this;
    var later = function later() {
      timeout = null;
      if (!immediate) {
        result = func.apply(context, args);
      }
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) {
      result = func.apply(context, args);
    }
    return result;
  };
};;
/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function ($, Drupal) {
  function init(i, tab) {
    var $tab = $(tab);
    var $target = $tab.find('[data-drupal-nav-tabs-target]');
    var isCollapsible = $tab.hasClass('is-collapsible');

    function openMenu(e) {
      $target.toggleClass('is-open');
    }

    function handleResize(e) {
      $tab.addClass('is-horizontal');
      var $tabs = $tab.find('.tabs');
      var isHorizontal = $tabs.outerHeight() <= $tabs.find('.tabs__tab').outerHeight();
      $tab.toggleClass('is-horizontal', isHorizontal);
      if (isCollapsible) {
        $tab.toggleClass('is-collapse-enabled', !isHorizontal);
      }
      if (isHorizontal) {
        $target.removeClass('is-open');
      }
    }

    $tab.addClass('position-container is-horizontal-enabled');

    $tab.on('click.tabs', '[data-drupal-nav-tabs-trigger]', openMenu);
    $(window).on('resize.tabs', Drupal.debounce(handleResize, 150)).trigger('resize.tabs');
  }

  Drupal.behaviors.navTabs = {
    attach: function attach(context, settings) {
      var $tabs = $(context).find('[data-drupal-nav-tabs]');
      if ($tabs.length) {
        var notSmartPhone = window.matchMedia('(min-width: 300px)');
        if (notSmartPhone.matches) {
          $tabs.once('nav-tabs').each(init);
        }
      }
    }
  };
})(jQuery, Drupal);;
/**
 * @file
 * Handles responsive navigation blocks (breadcrumbs and tabs).
 */
(function ($, Drupal) {

  'use strict';

  function init(i, breadcrumb_block) {
    var $bcBlock = $(breadcrumb_block);
    var $tabsBlock = $bcBlock.siblings('.block-local-tasks-block');

    function handleResize() {
      $tabsBlock.addClass('is-combined-with-breadcrumb');

      var breadcrumbWidth = 0;
      $bcBlock.find('ol > li').each(function (index, elem) {
        breadcrumbWidth += $(elem).outerWidth(true);
      });

      var primaryTabsWidth = 0;
      $tabsBlock.find('.tabs.primary > li').each(function (index, elem) {
        primaryTabsWidth += $(elem).outerWidth(true);
      });

      $tabsBlock.toggleClass('is-combined-with-breadcrumb', $bcBlock.innerWidth() > (breadcrumbWidth + primaryTabsWidth));
    }

    $(window).on('resize.tabs', Drupal.debounce(handleResize, 50)).trigger('resize.tabs');

    // Register triggering of resize on menu expand.
    $('[data-toolbar-tray="toolbar-item-administration-tray"]')
      .once('responsive-navigation')
      .on('click', function () {
        $(window).trigger('resize.tabs');
      });
  }

  /**
   * Initialise the navigation JS.
   */
  Drupal.behaviors.navigation = {
    attach: function (context) {
      var $bcBlock = $(context).find('.block-system-breadcrumb-block');
      if ($bcBlock.length) {
        var notSmartPhone = window.matchMedia('(min-width: 300px)');
        if (notSmartPhone.matches) {
          $bcBlock.once('responsive-navigation').each(init);
        }
      }
    }
  };

})(jQuery, Drupal);
;

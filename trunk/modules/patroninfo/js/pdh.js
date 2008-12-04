// $Id: tableheader.js,v 1.16 2008/01/30 10:17:39 goba Exp $

Drupal.DivDoScroll = function() {
  if (typeof(Drupal.DivOnScroll)=='function') {
    Drupal.DivOnScroll();
  }
};

Drupal.behaviors.Div = function (context) {
  // This breaks in anything less than IE 7. Prevent it from running.
  if (jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 7) {
    return;
  }

  // Keep track of all sticky divs 
  var divs = [];

  $('div.sticky-enabled', context).each(function () {
    // Clone div so it inherits original jQuery properties.
    var divClone = $(this).clone(true).insertBefore(this).wrap('<div class="sticky-div"></div>').parent().css({
      position: 'fixed',
      top: '0px'
    });
    divClone = $(divClone)[0];
    divs.push(divClone);

    // Store related div to the clone.  it is the element we just found
    var pdiv = $(this);
    divClone.pdiv = pdiv;
    // Finish initialzing header positioning.
    tracker(divClone);

    $(this).addClass('sticky-enabled');
    $(this).addClass('div-processed');
  });

  // Track positioning and visibility.
  function tracker(e) {
    // Save positioning data.
    var viewHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
    if (e.viewHeight != viewHeight) {
      e.viewHeight = viewHeight;
      e.vPosition = $(e.pdiv).offset().top - 4;
      e.hPosition = $(e.pdiv).offset().left;
      e.vLength = $(e.pdiv).clientHeight - 100;
      $(e).css('width', $(e.pdiv).css('width'));
      // Resize header and its cell widths.
      //var parentCell = $('th', e.table);
      //$('th', e).each(function(index) {
      //  var cellWidth = parentCell.eq(index).css('width');
        // Exception for IE7.
      //  if (cellWidth == 'auto') {
      //    cellWidth = parentCell.get(index).clientWidth +'px';
      //  }
      //  $(this).css('width', cellWidth);
      //});
    }

    // Track horizontal positioning relative to the viewport and set visibility.
    var hScroll = document.documentElement.scrollLeft || document.body.scrollLeft;
    var vOffset = (document.documentElement.scrollTop || document.body.scrollTop) - e.vPosition;
    var visState = (vOffset > 220) ? 'visible' : 'hidden';
    //var visState = (vOffset > 0 && vOffset < e.vLength) ? 'visible' : 'hidden';
    //var visState = (vOffset > 0 && vOffset < e.vLength) ? 'visible' : 'visible';
    $(e).css({left: -hScroll + e.hPosition +'px', visibility: visState});
  }
  // Only attach to scrollbars once, even if Drupal.attachBehaviors is called
  //  multiple times.
  if (!$('body').hasClass('div-processed')) {
    $('body').addClass('div-processed');
    $(window).scroll(Drupal.DivDoScroll);
    $(document.documentElement).scroll(Drupal.DivDoScroll);
  }

  // Track scrolling.
  Drupal.DivOnScroll = function() {
    $(divs).each(function () {
      tracker(this);
    });
  };

  // Track resizing.
  var time = null;
  var resize = function () {
    // Ensure minimum time between adjustments.
    if (time) {
      return;
    }
    time = setTimeout(function () {
      $('div.sticky-div').each(function () {
        // Force cell width calculation. ?? 
        this.viewHeight = 0;
        tracker(this);
      });
      // Reset timer
      time = null;
    }, 250);
  };
  $(window).resize(resize);
};

jQuery(function ($) {

  $(window).on('load', function () {
    $('a.modal').each(function () { 
      $(this).attr('data-mfp-src', $(this).attr('href')).addClass('no-scroll');
    });
    
    $('a.modal').magnificPopup({
      items: {
          src: '#request_demo',
          type: 'inline'
      },
      midClick: true // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
    });
  });
});
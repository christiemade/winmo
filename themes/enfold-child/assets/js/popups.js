jQuery(function ($) {
  jQuery(document).ready(function () {
    $('.modal').each(function () { 
      $(this).attr('data-mfp-src', '#request_demo').addClass('no-scroll');
    });
    
    $('.modal').magnificPopup({
      items: {
          src: '#request_demo',
          type: 'inline'
      },
      midClick: true // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
    });
  });
});
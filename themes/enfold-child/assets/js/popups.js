jQuery(function ($) {
  jQuery(document).ready(function () {
    $(".modal, .avia-button").each(function () {
      $(this).attr("data-mfp-src", $(this).attr("href")).addClass("no-scroll");
      $(this).bind("click", function (e) {
        e.preventDefault();
        $.magnificPopup.open({
          items: {
            src: $(this).attr("href"),
            type: "inline",
          },
          midClick: true, // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
        });
      });
    });
  });
});

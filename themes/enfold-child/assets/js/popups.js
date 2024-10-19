jQuery(function ($) {
  jQuery(document).ready(function () {
    $(".modal").each(function () {
      $(this).attr("data-mfp-src", $(this).attr("href")).addClass("no-scroll");
    });

    $(".modal").magnificPopup({
      items: {
        src: $(this).attr("href"),
        type: "inline",
      },
      midClick: true, // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
    });
  });
});

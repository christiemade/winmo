jQuery(function ($) {
  jQuery(document).ready(function () {
    $(document).on(
      "click",
      "#request_demo .avia-button, .cta .avia-button",
      function (e) {
        //e.preventDefault();
        //var $target = $(e.currentTarget);
        //openPopup($target);
      }
    );

    $(".avia-button, .modal").each(function () {
      $(this).attr("data-mfp-src", $(this).attr("href")).addClass("no-scroll");
    });

    $(".modal").each(function () {
      $(this).bind("click", function (e) {
        e.preventDefault();
        openPopup($(this));
      });
    });
  });

  function openPopup($target) {
    $.magnificPopup.open({
      items: {
        src: $target.attr("href"),
        type: "inline",
      },
      midClick: true, // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
    });
  }
});

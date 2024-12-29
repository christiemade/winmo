jQuery(function ($) {
  // Ability control loops inside the promise based on external events
  var stopme = false;
  var progressBar;

  // We only want to run this if it's one of ours
  $(document).on("ajaxError", function (e, xhr, settings, exception) {
    const date = new Date(e.timeStamp);
    console.log(date.toDateString() + " " + date.toTimeString());
    if (settings.data) {
      var errormsg = "There has been a fatal error.  Please press restart<div></div>";
      stopme = true;
      if (xhr.status == 502) {
        errormsg = "502 Error. Server is feeling sluggish. Press Restart or come back later.";
      }

      progressBar = $(".row.processing");

      $(progressBar)
        .removeClass("processing")
        .addClass("error")
        .addClass("loaded");
      $(progressBar).find(".launch").val("Restart");
      $(progressBar).find(".progress").removeClass("building");
      $(progressBar)
        .find(".progress")
        .html(errormsg + "<div></div>");
      $(".row").removeClass("processing").addClass("loaded");
    } else {
      console.log("Not our ajax");
    }
  });

  $(document).on("click", ".launch", function (e) {
    // Only one at a time
    if (!$(".row.processing").length) {
      $(".row").removeClass("loaded");

      $(this).parents(".row").addClass("processing").removeClass("error");

      let type = "";
      switch ($(e.currentTarget).attr("id")) {
        case "company_launch":
          type = "companies";
          break;
        case "agency_launch":
          type = "agencies";
          break;
        case "contacts_launch":
          type = "contacts";
          break;
        default:
          type = "companies";
          break;
      }

      // Initiate empty progress bar - flashing
      progressBar = $(this).parents(".row").find(".progress");
      console.log(progressBar);
      $(progressBar)
        .removeClass("building")
        .removeClass("complete")
        .addClass("loading")
        .html("<div></div>")
        .children("div")
        .text("loading")
        .width("100%");

      stopme = false;
      fetchData(type, progressBar);
    }
  });

  const fetchData = async (type, progressBar, atts = []) => {
    if (!atts["page"]) atts["page"] = 1;
    let metadata = await fetchMeta(type, atts, progressBar);
    let total = metadata.total_pages;
    let current_page = metadata.page;
    let first_total = "";
    let second_total = "";

    if (type == "contacts" || type == "agency_contacts") {
      first_total = metadata.first_total;
      second_total = metadata.second_total;
    } else {
      first_total = metadata.total_pages;
    }

    // Build progress bar
    $(progressBar).children("div").css("width", "0px");
    $(progressBar).removeClass("loading").addClass("building");
    $(progressBar).children("div").text("");
    var barWidth = $(progressBar).width();

    for (current_page; current_page <= total || stopme; current_page++) {
      if (!stopme) {
        var response = await Promise.all([
          jsdelay(type, current_page, total, first_total),
          timeout(1000),
        ]);

        if (response && response[0].data) {
          try {
            console.log(`Page ${current_page} processed successfully.`);
            $(progressBar)
              .children("div")
              .css("width", Math.ceil((current_page * barWidth) / total));
            $(progressBar).attr(
              "data-before",
              "Downloading: " + current_page + " / " + total
            );

            // Contacts, start round 2
            if (type == "contacts" && current_page == first_total) {
              // We need to sent total and first_total through
              atts = {
                page: Math.ceil(parseInt(current_page) + 1),
                first_total: first_total,
                total: total,
              };
              fetchData("agency_contacts", progressBar, atts);
            }

            // Finish - Double stopme to cover our bases
            if (stopme || current_page == total) {
              stopme = true;
              $(progressBar).removeClass("building");
              $(".row").removeClass("processing");
            }
          } catch (error) {
            console.log(`Error processing page ${current_page}:`, error);
            if ($(progressBar).hasClass('building')) {
              $(progressBar)
                .removeClass("building")
                .addClass("error");
              $(progressBar)
                .children("div")
                .text("`Error processing page ${current_page}");
              $(".row").removeClass("processing").addClass("loaded");
            }
          }
        } else {
          console.log(`No data found for page ${current_page}`);

          if ($(progressBar).hasClass('building')) {
            $(progressBar)
              .removeClass("building")
              .addClass("error");
            $(progressBar)
              .children("div")
              .text("No data found for page ${current_page}");
            $(".row").removeClass("processing").addClass("loaded");
          }
        }
      } else {
        stopme = true;
        $(progressBar)
          .removeClass("building")
          .addClass("error");
        $(progressBar)
          .children("div")
          .text("Server time out on page #" + page + "!");
        $(".row").removeClass("processing").addClass("loaded");
      }
    }
  };
  async function fetchMeta(type, atts, progressBar) {
    const thenable = {
      then(resolve, _reject) {
        $.ajax({
          url: apiAjax.ajaxurl,
          type: "POST",
          data: {
            action: "process_api_data", // Your WP action hook
            grab: "meta",
            page: atts["page"],
            total: atts["total"],
            first_total: atts["first_total"],
            type: type, // TYPE needs to be "agency_contacts"
          },
          success: function (data) {
            resolve(JSON.parse(data));
          },
          statusCode: {
            502: function (e) {
              $(progressBar)
                .removeClass("building")
                .addClass("error");
              $(progressBar)
                .children("div")
                .text("Server time out on page #" + page + "!");
              $(".row").removeClass("processing").addClass("loaded");
            },
            500: function (e) {
              console.log("Can I do something about THIS error?");
            },
          },
          error: function (data, more) {
            // more == "error"
            console.log("Error met");
            console.log(data);
          },
        }).fail(function (jqXHR, textStatus, errorThrown) {
          console.log("Fail field " + textStatus);
          // Request failed. Show error message to user.
          // errorThrown has error message, or "timeout" in case of timeout.
          $(progressBar)
            .removeClass("building")
            .addClass("error");
          $(progressBar)
            .children("div")
            .text("Server time out on page #" + page + "!");
          $(".row").removeClass("processing").addClass("loaded");
        });
      },
    };
    return await thenable; // "resolved!"
  }

  // Promise to wait x ms before continuing
  function timeout(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  // Delay each fetchPage call by 3 seconds whenever ready
  async function jsdelay(type, current_page, total, first_total) {
    if (!stopme) {
      await timeout(6000);
      return fetchPage(type, current_page, total, first_total);
    } else {
      return false;
    }
  }

  // Grab a single page from the API
  async function fetchPage(type, page, total, first_total = 0) {
    const thenable = {
      then(resolve, _reject) {
        
        $.ajax({
          url: apiAjax.ajaxurl,
          type: "POST",
          data: {
            action: "process_api_data", // Your WP action hook
            grab: "page",
            page: page,
            type: type,
            total: total,
            first_total: first_total,
          },
          success: function (data) {
            //console.log("Eventually we got a parse issue here. Unexpected character at line one.");
            console.log(data);
            var decodeData = JSON.parse(data);

            // This is the end of the script, clean up!
            if (decodeData.last == true) {
              stopme = true;

              progressBar
                .removeClass("building")
                .addClass("complete");
              
              progressBar.parents('.row')
                .removeClass('processing')
                .siblings().addClass('loaded');
              
              progressBar.parents('.row').addClass('loaded').find('.launch')
                  .val('Restart');
              
              $(".row").removeClass("processing");
            } else {
              resolve(JSON.parse(data));
            }
          },
        });
      },
    };

    return await thenable; // "resolved!"
  }
});

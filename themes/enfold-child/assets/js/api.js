jQuery(function ($) {
  // Ability control loops inside the promise based on external events
  var stopme = false;

  $(document).on("ajaxError", function (e, xhr, settings, exception) {
    //console.log("Acknowledging an error occured!");
    console.log("stopme is: " + stopme);
    console.log(e);
    console.log(xhr.status);
    console.log(settings);
    console.log(exception);
    const date = new Date(e.timeStamp);
    console.log(date.toDateString() + " " + date.toTimeString());
    stopme = true;
    if (xhr.status == 502) {
      console.log("502 Error");
    }

    var progressBar = $(".row.processing");

    $(progressBar)
      .removeClass("processing")
      .addClass("error")
      .addClass("loaded");
    $(progressBar).find(".launch").val("Restart");
    $(progressBar).find(".progress").removeClass("building");
    $(progressBar)
      .find(".progress")
      .html("There has been a fatal error.  Please press restart<div></div>");
    $(".row").removeClass("processing").addClass("loaded");
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
      var progressBar = $(this).parents(".row").find(".progress");
      $(progressBar)
        .removeClass("building")
        .removeClass("complete")
        .addClass("loading")
        .html("<div></div>")
        .children("div")
        .text("loading")
        .width("100%");

      fetchData(type, progressBar);
    }
  });

  const fetchData = async (type, progressBar, atts = []) => {
    console.log(atts);
    if (!atts["page"]) atts["page"] = 1;
    console.log(type);
    let metadata = await fetchMeta(type, atts, progressBar);
    console.log(metadata);
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

    console.log(current_page);
    console.log(total);
    for (current_page; current_page <= total || stopme; current_page++) {
      var response = await Promise.all([
        jsdelay(type, current_page, total, first_total),
        timeout(1000),
      ]);

      if (response && response[0].data) {
        // Could loop be stopped here? if last = true then dont try?
        console.log(stopme);
        try {
          console.log(first_total);
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
            console.log(atts);
            fetchData("agency_contacts", progressBar, atts);
          }

          // Need to see if the last var is available
          console.log(response[0].data);

          // Need to make sure the PHP scripts are stopped
          console.log("Stop yet? " + stopme);

          // Finish
          if (current_page == total) {
            stopme = true;
            $(progressBar).removeClass("building").addClass("complete");
            $(".row").removeClass("processing");
          }
        } catch (error) {
          console.log(`Error processing page ${current_page}:`, error);
          // Handle error, maybe retry or log
        }
      } else {
        console.log(`No data found for page ${current_page}`);
      }
    }
  };
  async function fetchMeta(type, atts, progressBar) {
    console.log(atts);
    console.log(type);
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
            console.log(data);
            resolve(JSON.parse(data));
          },
          statusCode: {
            502: function (e) {
              console.log("Custom effect here please");
              console.log(e);
              $(progressBar)
                .addClass("removeClass")
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
            .addClass("removeClass")
            .removeClass("building")
            .addClass("error");
          $(progressBar)
            .children("div")
            .text("Server time out on page #" + page + "!");
          $(".row").removeClass("processing").addClass("loaded");
          console.log(errorThrown);
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
    await timeout(6000);
    return fetchPage(type, current_page, total, first_total);
  }

  // Grab a single page from the API
  async function fetchPage(type, page, total, first_total = 0) {
    console.log("Fetch page from " + type + " " + page + " Total: " + total);

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
            console.log(data); // Could loop be stopped here?
            resolve(JSON.parse(data));
          },
        });
      },
    };

    return await thenable; // "resolved!"
  }
});

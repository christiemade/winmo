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
    console.log(type);
    let metadata = await fetchMeta(type, atts, progressBar);

    // Error Check
    let metaarray = JSON.parse(metadata);
    if (metaarray['error']) {
      console.log(metaarray['error']);
      updateBar(progressBar, "fail", {error: metaarray['error']});
      stopme = true;
      return;
    }
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
    console.log("Attempt to get bar going after a restart: ");
    updateBar($(progressBar), 'pass', { current_page: current_page, total: total });

    for (current_page; current_page <= total; current_page++) {
      if (stopme) {
        break;
      }

      try {
        const response = await jsdelay(type, current_page, total, first_total);
        console.log(response);
        if (response && response.data) {
          console.log(`Response recieved - Page ${current_page} processed successfully.`);
          $(progressBar)
            .children("div")
            .css("width", Math.ceil((current_page * barWidth) / total));
          $(progressBar).attr(
            "data-before",
            "Downloading: " + current_page + " / " + total);

          // Switch to Agency
          console.log("Current Page: " + current_page + ", Total: " + total + ", First Total: " + first_total);
          if (type === "contacts" && current_page === first_total) {
            // We need to send total and first_total through
            atts = {
              page: Math.ceil(current_page + 1),
              first_total,
              total,
            };
            await fetchData("agency_contacts", progressBar, atts);
          }
          
          // Finish
          if (current_page === total) {
            console.log('Finished processing all pages.');
            stopme = true;
          }
        } else {
          
          if ($(progressBar).hasClass('building')) {
            $(progressBar)
              .removeClass("building")
              .addClass("error");
            $(progressBar)
              .children("div")
              .text(atts['error']);
            $(".row").removeClass("processing").addClass("loaded");
          }
          console.warn('No data for page:', current_page);
          stopme = true;
          break;
        }


      } catch (error) {
        console.error('Error in jsdelay or fetchPage:', error);
        stopme = true
        if ($(progressBar).hasClass('building')) {
          $(progressBar)
            .removeClass("building")
            .addClass("error");
          $(progressBar)
            .children("div")
            .text(atts['error']);
          $(".row").removeClass("processing").addClass("loaded");
        }
        break;
      }

       /*if(stopme) {
        stopme = true;
        $(progressBar)
          .removeClass("building")
          .addClass("error");
        $(progressBar)
          .children("div")
          .text("Server time out on page #" + current_page + "!");
        $(".row").removeClass("processing").addClass("loaded");
      }*/
    }
  };
  async function fetchMeta(type, atts, progressBar) {
    const thenable = {
      then(resolve, _reject) {
        console.log("Fetching the meta first.");
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
          error: function (data, more, message) {
            // more == "error"
            console.log("Error met");
            console.log(message);
            console.log(data);
            console.log(more);
            $(progressBar)
              .removeClass("building").removeClass("loading")
              .addClass("error");
            $(progressBar)
              .children("div")
              .text("Test");
            $(".row").removeClass("processing").addClass("loaded");
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
    console.log("Going into JS delay with type " + type);
    if (stopme) {
      console.log('Stopped by stopme.');
      return false;
    }

    console.log('Delaying for 4000ms...');
    await timeout(4000);

    console.log('Calling fetchPage...');
    return await fetchPage(type, current_page, total, first_total);

  }

  // Grab a single page from the API
  async function fetchPage(type, page, total, first_total = 0) {
    console.log("Inside fetchPage " + page);
    return new Promise((resolve, reject) => {
     
        console.log("Inside then... so this is what gets send to PHP: " + page);
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
            try {
              var decodeData = JSON.parse(data);

              // This is the end of the script, clean up!
              if (decodeData.last) {
                stopme = true;
                resolve(JSON.parse(data)); // Resolve the last page
                updateBar(progressBar, "end");
              } else {
                resolve(JSON.parse(data)); // Resolve normally
                updateBar(progressBar, "pass");
              }

            } catch (error) {
              console.error('Error parsing data:', error);
              reject(error);
              updateBar(progressBar, "fail");
            }
          },
          error: function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
            console.error('AJAX error:', textStatus, errorThrown);
            reject(new Error('AJAX request failed'));
            updateBar(progressBar, "fail", { current_page: page, total: total, error: textStatus });
          },
        });
      });
  }  

  function updateBar(progressBar, action = "", atts = {}) {
    console.log("Make the bar... " + action);

    switch (action) {
      case "fail":
        progressBar
        .removeClass("building").removeClass('loading')
          .addClass("error");
        progressBar.children('div').text(atts.error);
        $(".row").removeClass("processing").addClass("loaded");

        break;
      case "end":
        progressBar
        .removeClass("building").removeClass("error")
        .addClass("complete");
        
        progressBar.parents('.row')
          .removeClass('processing')
          .siblings().addClass('loaded');
        
        progressBar.parents('.row').addClass('loaded').find('.launch')
            .val('Restart');
        
        $(".row").removeClass("processing");
        break;
      case "pass":
        progressBar.removeClass("error");
        var barWidth = progressBar.width();
        console.log(barWidth);
        console.log("Fix Bar now...");
        progressBar
          .children("div")
          .css("width", Math.ceil((atts.current_page * barWidth) / atts.total));
        progressBar.children('div').css("width", Math.ceil((atts.current_page * barWidth) / atts.total));
        progressBar.attr("data-before", "Downloading: " + atts.current_page + " / " + atts.total);
        break;
    }

  }
});

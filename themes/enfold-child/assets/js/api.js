jQuery(function ($) {
  // Ability control loops inside the promise based on external events
  var stopme = false;
  var progressBar;

  // We only want to run this if it's one of ours
  $(document).on("ajaxError", function (e, xhr, settings, exception) {

    console.log(settings.data);

    // Only work with API data
    if (!settings.data || settings.data.indexOf("action=process_api_data") === -1) {
      console.log("Not our ajax");
      return;
    }

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

  const fetchData = async (type, progressBar, atts = {}) => {
  if (!atts.page) atts.page = 1;

  let metadata;

  try {
    metadata = atts.skipMeta ? atts : await fetchMeta(type, atts, progressBar);
  } catch (error) {
    console.error("fetchMeta failed:", error);
    updateBar(progressBar, "fail", { error: error });
    stopme = true;
    return;
  }
    
    let total = atts.total || metadata.total_pages;
    let current_page = atts.page || metadata.page;
    let per_page = atts.per_page || metadata.per_page || "";

    if (!total) {
      console.error("Missing total_pages", metadata);
      updateBar(progressBar, "fail", { error: "Invalid metadata from API" });
      stopme = true;
      return;
    }

    let first_total = 0;
    let second_total = 0;
    let first_per_page = 0;
    let second_per_page = 0;

    if (type === "contacts" || type === "agency_contacts") {
      first_total = atts.first_total || metadata.first_total || 0;
      second_total = atts.second_total || metadata.second_total || 0;
      first_per_page = atts.first_per_page || metadata.first_per_page || 0;
      second_per_page = atts.second_per_page || metadata.second_per_page || 0;
    }

    // Build progress bar
    $(progressBar).children("div").css("width", "0px");
    $(progressBar).removeClass("loading").addClass("building");
    $(progressBar).children("div").text("");

    var barWidth = $(progressBar).width();
    updateBar($(progressBar), 'pass', { current_page: current_page, total: total });
    let loop = 0;

    for (current_page; current_page <= total; current_page++) {
      loop++;
      if (stopme) {
        break;
      }

      try {
        const response = await jsdelay(type, current_page, total, first_total, per_page, loop, progressBar);
        console.log(response);
        if (response && response.data) {
          console.log(`Response recieved - Page ${current_page} processed successfully.`);
          $(progressBar)
            .children("div")
            .css("width", Math.ceil((current_page * barWidth) / total));
          $(progressBar).attr(
            "data-before",
            "Downloading: " + current_page + " / " + total);
console.log("Loop: " + loop);
          // Switch to Agency
          console.log("Current Page: " + current_page + ", Total: " + total + ", First Total: " + first_total + " Per Page: "+per_page);
          if (type === "contacts" && current_page === first_total) {
            // We need to send total and first_total through
            atts = {
              page: current_page + 1,
              total: total,
              first_total: first_total,
              second_total: second_total,
              first_per_page: first_per_page,
              second_per_page: second_per_page,
              per_page: second_per_page,
              skipMeta: true
            };
            
            await fetchData("agency_contacts", progressBar, atts);

            stopme = true;
            break;
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
              .text(response?.error || "Unknown error");
            $(".row").removeClass("processing").addClass("loaded");
          }
          console.warn('No data for page:', current_page);
          stopme = true;
          break;
        }


      } catch (error) {
        console.error('Error in jsdelay or fetchPage on page ' + current_page +':', error);
        stopme = true
        if ($(progressBar).hasClass('building')) {
          $(progressBar)
            .removeClass("building")
            .addClass("error");
          $(progressBar)
          .children("div")
          .text(String(error || "Unknown error"));
          $(".row").removeClass("processing").addClass("loaded");
        }
        break;
      }
    }
  };


  async function fetchMeta(type, atts, progressBar) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: apiAjax.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "process_api_data",
          grab: "meta",
          page: atts.page || 1,
          total: atts.total,
          first_total: atts.first_total,
          type: type,
          per_page: atts.per_page,
          loop: 0
        },
        success: function (data) {
          console.log(data);

          // WP error wrapper
          if (data && data.success === false) {
            reject(data.data || "Unknown error");
            return;
          }

          // WP success wrapper
          if (data && data.success === true) {
            resolve(data.data);
            return;
          }

          // Raw success payload from wp_send_json()
          resolve(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
          const msg = errorThrown || textStatus || "AJAX error";

          $(progressBar)
            .removeClass("building loading")
            .addClass("error");

          $(progressBar)
            .children("div")
            .text("Server error on page #" + (atts.page || 1) + ": " + msg);

          $(".row").removeClass("processing").addClass("loaded");

          reject(msg);
        }
      });
    });
  }

  // Promise to wait x ms before continuing
  function timeout(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  // Delay each fetchPage call by 3 seconds whenever ready
  async function jsdelay(type, current_page, total, first_total, per_page,loop) {
    console.log("Going into JS delay with type " + type + " loop "+loop);
    if (stopme) {
      console.log('Stopped by stopme.');
      return false;
    }

    console.log('Delaying for 4000ms...');
    await timeout(4000);

    console.log('Calling fetchPage...');
    return await fetchPage(type, current_page, total, first_total, per_page, loop, progressBar);

  }

  // Grab a single page from the API
  async function fetchPage(type, page, total, first_total = 0, per_page, loop = 1, progressBar) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: apiAjax.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "process_api_data",
          grab: "page",
          page: page,
          type: type,
          total: total,
          first_total: first_total,
          per_page: per_page,
          loop: loop
        },
        success: function (data) {
          console.log("fetchPage success raw data:", data);
          console.log("fetchPage type/page:", type, page);

          if (data && data.success === false) {
            console.log("fetchPage rejected because success === false:", data);
            reject(data.data || "Unknown error");
            return;
          }

          const payload = (data && data.success === true) ? data.data : data;

          console.log("fetchPage payload:", payload);

          if (!payload) {
            reject("Empty response payload");
            return;
          }

          console.log("Is last? ", payload.last);

          if (payload.last) {
            stopme = true;
            updateBar(progressBar, "end");
          } else {
            updateBar(progressBar, "pass", {
              current_page: page,
              total: total
            });
          }

          resolve(payload);
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error("AJAX error on page " + page + ":", {
            status: jqXHR.status,
            textStatus,
            errorThrown,
            responseText: jqXHR.responseText
          });

          updateBar(progressBar, "fail", {
            current_page: page,
            total: total,
            error: errorThrown || textStatus || ("HTTP " + jqXHR.status)
          });

          reject(errorThrown || textStatus || ("HTTP " + jqXHR.status));
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

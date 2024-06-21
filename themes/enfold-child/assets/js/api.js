jQuery(function ($) {

  $(document).on("click", ".launch", function (e) { 

    // Only one at a time
    if (!$('.row.processing').length) {
 
      $('.row').removeClass('loaded');

      $(this).parents('.row').addClass('processing');

      let type = "";
      switch ($(e.currentTarget).attr('id')) {
        case 'company_launch':
          type = 'companies';
          break;
        case 'agency_launch':
          type = 'agencies';
          break;
        case 'contacts_launch':
          type = 'contacts';
          break;
        default:
          type = 'companies';
          break;
      }

      console.log(type);

      // Initiate empty progress bar - flashing
      var progressBar = $(this).parents('.row').find(".progress");
      $(progressBar).removeClass('building').removeClass('complete').addClass('loading');
      $(progressBar).children('div').text('loading').width('100%');

      fetchData(type, progressBar);
    }
  });

  const fetchData = async (type, progressBar, page = 1) => {

    let metadata = await fetchMeta(type);
    let total = metadata.total_pages;
    let current_page = metadata.page; 
    let first_total = "";
    if (type == "contacts") {
      first_total = metadata.first_total;
    }
    

    // Build progress bar
    $(progressBar).children('div').css('width', '0px');
    $(progressBar).removeClass('loading').addClass('building');
    $(progressBar).children('div').text('');
    var barWidth = $(progressBar).width();
    for (current_page; current_page <= total; current_page++) {
      const response = await fetchPage(type, current_page, total, first_total);

      if (response && response.data) {
        try {

          $(progressBar).children('div').css('width', Math.ceil((current_page * barWidth) / total));
  
          // Contacts, start round 2
          if ((type == "contacts") && (current_page == first_total)) {
            fetchData("company_contacts", progressBar);
          }

          // Finish
          if (current_page == total) {
            $(progressBar).removeClass('building').addClass('complete');
            $('.row').removeClass('processing');
          }
        } catch (error) {
          console.log(`Error processing page ${current_page}:`, error);
          $(progressBar).removeClass('loading').removeClass('building').addClass('error');
          $(progressBar).children('div').text('Error processing pages.');
        }
      } else {
        console.log(`No data found for page ${current_page}`);
        $(progressBar).removeClass('loading').removeClass('building').addClass('error');
        $(progressBar).children('div').text('No data found for page '+current_page);
      }

      
    }

    async function fetchMeta(type) {
      const thenable = {
        
        then(resolve, _reject) {
          $.ajax({
            url: apiAjax.ajaxurl,
            type: 'POST',
            data: {
              action: 'process_api_data', // Your WP action hook
              grab: 'meta',
              page: page,
              type: type
            }, success: function (data) {
              resolve(JSON.parse(data));
            }
          });
        },
      };
      return await thenable; // "resolved!"
    }


    }

  async function fetchPage(type, page, total, first_total = 0) {
    console.log("Fetch page from " + type + " " + page +  " Total: "+total);
      
    const thenable = {
        
      then(resolve, _reject) {

        $.ajax({
          url: apiAjax.ajaxurl,
          type: 'POST',
          data: {
            action: 'process_api_data', // Your WP action hook
            grab: 'page',
            page: page,
            type: type,
            total: total,
            first_total: first_total
          },
          success: function (data) {
            resolve(JSON.parse(data));
          }
        });
      },
    };
    return await thenable; // "resolved!"
  }
  
});


jQuery(function ($) {
   jQuery(document).ready(function () {
      
      // Autoload some items
      jQuery.ajax({
         type: "post",
         dataType: "json",
         url: winmoAjax.ajaxurl,
         data: { action: 'winmo_contact_list', data: 'per-page=50', pageNumber: '1', nonce: $("#filter-form").attr('data-nonce') },
         success: function (data, response) {
            if (response == "success") {
               $('#all-contacts').html(data);
            }
            else {
               alert("There was a problem.")
            }
         }
      })

      $(document).on('submit', '#filter-form', function (e) {
         e.preventDefault();

         var nonce = $(this).attr("data-nonce")
         var form = $(this);
      
         jQuery.ajax({
            type: "post",
            dataType: "json",
            url: winmoAjax.ajaxurl,
            data: { action: "winmo_contact_list", data: form.serialize(), nonce: nonce },
            success: function (data, response) {
               if (response == "success") {
                  $('#all-contacts').html(data);
               }
               else {
                  alert("There was a problem.")
               }
            }
         })
 
      }) // onSubmit
 
   }); // onReady

   $(document).on('click', '.page-link', function (e) {
      e.preventDefault();
   
      var page_number = $(this).data('pageNumber');
      var current_query = "pageNumber=" + page_number;
   
      if ($(this).data('query')) {
         current_query = $(this).data('query') + '&' + current_query;
      }

      jQuery.ajax({
         type: "post",
         dataType: "json",
         url: winmoAjax.ajaxurl,
         data: current_query,
         success: function (data, response) {
            if (response == "success") {
               $('#all-contacts').html(data);
            }
            else {
               alert("There was a problem.")
            }
         }
      })

   }); // OnClick
});
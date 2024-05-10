jQuery(function ($) {
   jQuery(document).ready(function () {
      
      if ($("#filter-form").length) {
         // Autoload some items
         jQuery.ajax({
            type: "post",
            dataType: "json",
            url: winmoAjax.ajaxurl,
            data: { action: $("#filter-form").attr('data-action'), data: 'alpha=a', nonce: $("#filter-form").attr('data-nonce') },
            success: function (data, response) {
               if (response == "success") {
                  $('.all-content').html(data);
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
               data: { action: form.attr("data-action"), data: form.serialize(), nonce: nonce },
               async: false,
               success: function (data, response) {
                  if (response == "success") {
                     $('.all-content').html(data);
                  }
                  else {
                     alert("There was a problem.")
                  }
               }
            })
 
         }) // onSubmit
      }
 
   }); // onReady
});
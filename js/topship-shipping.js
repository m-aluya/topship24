jQuery(function($) {
    $(document).on('change', '#shipping-state, #shipping-city, #shipping-address_1, #shipping-country, #shipping-postcode', function() {
   
      
        const state = $('#shipping-state').val();
        const city = $('#shipping-city').val();
        const address = $('#shipping-address_1').val();
        const country = $('#shipping-country').val();
        const postcode = $('#shipping-postcode').val();
       

        // Send AJAX request to fetch shipping rate
        $.ajax({
            url: topship_rate_object.ajax_url, 
            type: 'POST',
            data: {
                action: 'andaf', 
                state: state,
                city: city,
                address: address,
                country: country,
                postcode: postcode,
            },
            beforeSend: function() {
                console.log('Fetching shipping rate...');
            },
            success: function(response) {
             console.log(response);
            // $('body').trigger('update_checkout');
             $(document.body).trigger('update_checkout');
           
            },
            error: function(xhr, status, error) {
                console.log(error);
               
            },
        });
    });
});

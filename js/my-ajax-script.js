// A $( document ).ready() block.
/*jQuery( document ).ready(function() {
   jQuery(document).on('click', '.verify', function(e) {
           e.preventDefault();
        var curr = jQuery("#currency option:selected").text();
         var val  = jQuery('#currency option:selected').attr("data");
       
        var data = "currency="+ curr + "&amount=" + val;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: my_ajax_object.ajax_url,
           data : {
                action : 'getTransction',
                currency : curr,
                amount:val
            },
            beforeSend: function(){
                jQuery("#image").css({"display": "block"});
            },
            complete: function(){
                jQuery("#image").css({"display": "none"});
            },
            success: function(response){
                
                console.log(response);
                if(response.status =='success'){
                   jQuery('#message').html('Your transection is successfull. transection ID is' + response.transctionID);
                }
                if(response.status =='error'){
                    jQuery('#message').html( response.message);
                }
            }
        });
    });
});

*/





jQuery(document).on('click', '.verify', function(e) {
           e.preventDefault();
        var curr = jQuery("#currency option:selected").text();
        var hash = jQuery("#hash").val();
        var text =jQuery("#hash").attr("data");
        var act =jQuery("#act").val();
        if(hash ==''){
            alert('please enter the transection ID');
            return;
        }
       jQuery.ajax({
            type: "post",
            dataType: "json",
            url: my_ajax_object.ajax_url,
           data : {
                action : 'getTransction',
                hash : hash,
                currency : curr,
                act:act,
                orderid:text
            },
            beforeSend: function(){
                jQuery("#image").css({"display": "block"});
            },
            complete: function(){
                jQuery("#image").css({"display": "none"});
            },
            success: function(response){
                
                console.log(response);
                if(response.status =='success'){
                   jQuery('#message').html('Your transection hash id is saved for tracking');
                }
                if(response.status =='error'){
                    jQuery('#message').html( response.message);
                }
            }
        });
    });




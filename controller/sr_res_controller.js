/**
Used for sending reservation information to be queried against the database.
*/

var res_input = {room_id:'',
start_hour:'',
start_minute:'',
end_hour:'',
end_minute:'',
day:'',
month:'',
year:'',
phone:'',
email:'',
name:'',
purpose:'',
room_name:''}; //Used for reservation operations

function send_reservation_info(operation){
    res_input.room_id = jQuery('#sr_room option:selected').val();
    res_input.start_hour = parseInt(jQuery('#sr_start_hour option:selected').val(), 10) + parseInt(jQuery('#sr_start_ampm option:selected').val(), 10);
    res_input.start_minute = jQuery('#sr_start_minute option:selected').val();
    res_input.end_hour = parseInt(jQuery('#sr_end_hour option:selected').val(), 10) + parseInt(jQuery('#sr_end_ampm option:selected').val(), 10);
    res_input.end_minute = jQuery('#sr_end_minute option:selected').val();
    res_input.day = jQuery('#sr_day option:selected').val();
    res_input.month = jQuery('#sr_month option:selected').val();
    res_input.year = jQuery('#sr_year option:selected').val();
    res_input.phone = jQuery('#sr_phone').val();
    res_input.email = ( (jQuery('#sr_email').val().search('@') != -1) ? jQuery('#sr_email').val():'');
    res_input.name = jQuery('#sr_name').val();
    res_input.purpose = jQuery('#sr_purpose').val();
    res_input.room_name = jQuery('#sr_room option:selected').text();

    var data={action: operation, res_info: res_input};
    jQuery.post(
        reservation_ajax.ajaxurl,
        data, 
        function(response){jQuery('#' + operation +'_status').html(response);
            if(operation == 'display_reservation'){
                jQuery('#display_reservation').val('Check Reservations');
                jQuery('#add_reservation_form').show();}
            if(operation == 'add_reservation'){
                jQuery('#add_reservation').val('Submit Reservation');
                send_reservation_info('display_reservation');}
        });
}

function cancel_reservation(reservation_id){
    var data={action: 'cancel_reservation', cancelled: reservation_id};
    jQuery.post(
        reservation_ajax.ajaxurl,
        data, 
        function(){
            send_reservation_info('display_reservation');
            jQuery('#add_reservation_status').html('');
    });
}

jQuery(document).ready(
    function(){
    jQuery('#add_reservation').click(
        function(){
            jQuery('#add_reservation').val('Working');
            send_reservation_info('add_reservation');    
    });
    jQuery('#display_reservation').click(
        function(){
            jQuery('#display_reservation').val('Working');
            send_reservation_info('display_reservation');
            jQuery('#add_reservation_status').html('');
    });
    
    jQuery("#display_reservation_status").on("click","#cancel_button", function(){jQuery(this).val('Working');});
    
});


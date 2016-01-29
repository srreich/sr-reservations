/**
Used for sending reservation information to be queried against the database.
*/

var res_input = {room_id:'',
start_hour:'',
start_minute:'',
end_hour:'',
end_minute:'',
date_stamp:'',
phone:'',
email:'',
name:'',
purpose:'',
room_name:''};
var interval;

function send_calendar_info(clear_add_status){
    if(typeof clear_add_status !== 'undefined'){jQuery('#add_reservation_status').html('');}
    res_input.room_id = jQuery('#sr_room option:selected').val();
    res_input.room_name = jQuery('#sr_room option:selected').text();
    var data={action: 'view_calendar', calendar_room: res_input.room_id};

    jQuery.post(
        calendar_ajax.ajaxurl,
        data,
        function(response){jQuery('#display_calendar_status').html(response);}
    );
}

function send_timestamp_info(input){
    if(typeof input !== 'undefined'){
        jQuery('#add_reservation_status').html('');
        res_input.date_stamp = input;
    }
    var data={action: 'calendar_reservation_check', calendar_timestamp: res_input.date_stamp, calendar_room: res_input.room_id};

    jQuery.post(
        calendar_ajax.ajaxurl, 
        data,
        function(response){
            jQuery('#display_reservation_status').html(response);
            jQuery('#add_reservation_form').show();
    });
}

function cancel_reservation(reservation_id){
    var data={action: 'cancel_reservation', cancelled: reservation_id};
    
    jQuery.post(
        calendar_ajax.ajaxurl, 
        data, 
        function(){
            send_timestamp_info();
            send_calendar_info('clear');
    });
}

function send_reservation_info(){
    res_input.start_hour = parseInt(jQuery('#sr_start_hour option:selected').val(), 10) + parseInt(jQuery('#sr_start_ampm option:selected').val(), 10);
    res_input.start_minute = parseInt(jQuery('#sr_start_minute option:selected').val(), 10);
    res_input.end_hour = parseInt(jQuery('#sr_end_hour option:selected').val(), 10) + parseInt(jQuery('#sr_end_ampm option:selected').val(), 10);
    res_input.end_minute = parseInt(jQuery('#sr_end_minute option:selected').val(), 10);
    res_input.phone = jQuery('#sr_phone').val();
    res_input.email = ( (jQuery('#sr_email').val().search('@') != -1) ? jQuery('#sr_email').val():'');
    res_input.name = jQuery('#sr_name').val();
    res_input.purpose = jQuery('#sr_purpose').val();

    var data={action: 'calendar_add_reservation', res_info: res_input};
    
    jQuery.post(
        calendar_ajax.ajaxurl,
        data, 
        function(response){
            jQuery('#add_reservation').val('Submit Reservation');
            jQuery('#add_reservation_status').html(response);
            send_timestamp_info();
            send_calendar_info();
    });
}

jQuery(document).ready(
    function(){
    jQuery('#sr_room').change(function(){
            send_calendar_info('clear');
            if(interval !== 'undefined'){clearInterval(interval);}
            interval = setInterval(function(){send_calendar_info();}, 15000);
            jQuery('#display_reservation_status').html('');
            jQuery('#add_reservation_form').hide();
        });

    jQuery('#add_reservation').click(function(){
        jQuery('#add_reservation').val('Working');
        send_reservation_info();
        });

    jQuery('#display_reservation_status').on("click","#cancel_button", function(){jQuery(this).val('Working');});
    }
);
/**
Used in backend admin operations
*/

var input = {room:'',
branch:'',
name:''};

function send_input(table){
    var room_class = '.' + table + '.rooms option:selected';
    var branch_class = '.' + table + '.branches option:selected';
    var name_class = '.' + table + '.name';
    var operation_class = '.' + table + '.operation option:selected';

    if(table == 'room'){input.room = jQuery(room_class).val();}

    input.branch = jQuery(branch_class).val();
    input.name = jQuery(name_class).val();
    var input_action = jQuery(operation_class).val() + '_' + table;
    var data = {action: input_action, op_info: input};

    jQuery.post(
        backend_ajax.ajaxurl,
        data, 
        function(response){
            jQuery('#sr_status').html(response);
            refresh_rooms();
            refresh_branches();
    });
}

function refresh_branches(){
    var data = {action: 'format_branches'};

    jQuery.post(
        backend_ajax.ajaxurl,
        data,
        function(response){jQuery('.branches').html(response);}
    );
}

function refresh_rooms(){
    var data = {action: 'format_rooms'};

    jQuery.post(
        backend_ajax.ajaxurl,
        data,
        function(response){jQuery('.rooms').html(response);}
    );
}

function initial_branch(){
    input.room = jQuery('.room.rooms option:selected').val();
    var data = {action: 'default_branch', op_info: input};

    jQuery.post(
        backend_ajax.ajaxurl,
        data,
        function(response){jQuery('.room.branches').val(response);}
    );
}

function clear_reservations(){
    var data = {action: 'delete_reservations'};

    jQuery.post(
        backend_ajax.ajaxurl,
        data,
        function(response){jQuery('#sr_status').html(response);}
    );
}

jQuery(document).ready(function(){
    jQuery(".operate").click(function(){
        if(jQuery(this).hasClass("room")){send_input('room');}
        if(jQuery(this).hasClass("branch")){send_input('branch');}
    });

    jQuery("#clear_reservations").click(function(){clear_reservations();});
    jQuery(".rooms").change(function(){initial_branch();});
});
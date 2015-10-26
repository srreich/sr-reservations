<?php

/**
Displays 31 day overview of reservations for a room
*/

if( !defined( 'ABSPATH' ) ) {exit;}

class SR_Res_Calendar{

    public function __construct(){
 
        $rooms = get_rooms();
        ?>
        
        <select id="sr_room">
        <option value="">Select a Conference Room</option>
        
        <?php
        foreach($rooms as $room){?> <option value="<?php echo esc_attr($room["id"]); ?>"><?php echo esc_html($room["room"]); ?> </option> <?php } ?>

        </select><table id="display_calendar_status"></table><table id="display_reservation_status"></table><div id="add_reservation_form" style="display:none">
        <div id="add_reservation_status">
        </div>
        <div>
        <label for="sr_start_hour">Start</label>
            <select id="sr_start_hour">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="0">12</option>
            </select>
            :
            <select id="sr_start_minute">
                <option value="0">00</option>
                <option value="5">05</option>
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="20">20</option>
                <option value="25">25</option>
                <option value="30">30</option>
                <option value="35">35</option>
                <option value="40">40</option>
                <option value="45">45</option>
                <option value="50">50</option>
                <option value="55">55</option>
            </select>
            <select id="sr_start_ampm">
                <option value="0">AM</option>
                <option value="12">PM</option>
            </select>
        </div>
        </br>
        <div>
        <label for="sr_end_hour">End</label>
            <select id="sr_end_hour">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="0">12</option>
            </select>
            :
            <select id="sr_end_minute">
                <option value="0">00</option>
                <option value="5">05</option>
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="20">20</option>
                <option value="25">25</option>
                <option value="30">30</option>
                <option value="35">35</option>
                <option value="40">40</option>
                <option value="45">45</option>
                <option value="50">50</option>
                <option value="55">55</option>
            </select>
            <select id="sr_end_ampm">
                <option value="0">AM</option>
                <option value="12">PM</option>
            </select>
        </div>
        </br>
        <div>
            <label for="sr_purpose">Purpose</label>
            <input type="text" id="sr_purpose"/>*
        </div>
        </br>
        <div>
            <label for="sr_name">Name</label>
            <input type="text" id="sr_name"/>*
        </div>
        </br>
        <div>
            <label for="sr_email">Email</label>
            <input type="text" id="sr_email"/>*
        </div>
        </br>
        <div>
            <label for="sr_phone">Phone</label>
            <input type="text" id="sr_phone"/>*
        </div>
        </br>
        <div>
            <input type="button" id="add_reservation" value="Submit Reservation"></input>
        </div>
    </div>
    <?php
        
        add_action('wp_footer', array($this,'enqueue_calendar_scripts'));
        
    }
    
    public function enqueue_calendar_scripts(){
        wp_register_script('sr_res_calendar', plugins_url('../controller/sr_res_calendar.js', __FILE__),array('jquery'));
        wp_localize_script('sr_res_calendar', 'calendar_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
        wp_enqueue_script('sr_res_calendar');
        
        wp_register_style('sr_res_css',plugins_url('../view/sr_res_css.css',__FILE__));
        wp_enqueue_style('sr_res_css');
    }
}
?>
<?php

/**
Displays forms and reservation information to user.
*/

if( !defined( 'ABSPATH' ) ) {exit;}

class SR_Res_View{

    public function __construct(){
 
        $rooms = get_rooms();
        ?>
        <option value="">Select a Conference Room</option>
        <?php
        foreach($rooms as $room){ ?> <option value="<?php echo esc_attr($room["id"]); ?>"><?php echo esc_html($room["room"]); ?></option> <?php }

        $default_month = idate('m');
        $default_day = idate('d');
        
        $month_array = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        
        $month_values = '';
        $day_values = '';        
        
        for($i=1; $i<=12; $i++){
            $month_values .= '<option value="' . esc_attr($i) . '" ';
            
            if($default_month == $i){
                $month_values .= 'selected ';
            }
        
            $month_values .= '>' . esc_html($month_array[$i-1]) . '</option>';
        }
        
        for($i=1; $i<=31; $i++){
            $day_values .= '<option value="' . esc_attr($i) . '" ';
            
            if($default_day == $i){
                $day_values .= 'selected ';
            }
        
            $day_values .= '>' . esc_html($i) . '</option>';
        }
        ?>
        </select>
        <select id="sr_month">
            <?php echo $month_values; ?>
        </select>
        <select id="sr_day">
            <?php echo $day_values; ?>
        </select>
        <select id="sr_year">
            <option value="<?php echo esc_attr(idate("Y")); ?>"><?php echo esc_html(idate("Y")); ?></option>
            <option value="<?php echo esc_attr((idate("Y")+1)); ?>"><?php echo esc_html((idate("Y")+1)); ?></option>
        </select>
        <input type="button" id="display_reservation" value="Check Reservations"></input>
        
        <table id="display_reservation_status">
        </table>
        <div id="add_reservation_form" style="display:none">
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
        add_action('wp_footer', array($this,'enqueue_reservation_scripts'));
        
    }
    
    public function enqueue_reservation_scripts(){
        wp_register_script('sr_res_controller', plugins_url('../controller/sr_res_controller.js', __FILE__),array('jquery'));
        wp_localize_script('sr_res_controller', 'reservation_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
        wp_enqueue_script('sr_res_controller');
    
        wp_register_style('sr_res_css',plugins_url('../view/sr_res_css.css',__FILE__));
        wp_enqueue_style('sr_res_css');
    }
}
?>
<?php

/**
Database Queries and Actions for the standard form.
*/

if( ! defined( 'ABSPATH' ) ) {exit;} // Exit if accessed directly

if(!class_exists('SR_Res_View')) require_once(dirname(dirname(__FILE__)).'/view/sr_res_view.php');

add_action('wp_ajax_add_reservation','add_reservation');
add_action('wp_ajax_nopriv_add_reservation','add_reservation');

add_action('wp_ajax_display_reservation','display_reservation');
add_action('wp_ajax_nopriv_display_reservation','display_reservation');
    
add_action('wp_ajax_cancel_reservation', 'cancel_reservation');
add_action('wp_ajax_nopriv_cancel_reservation','cancel_reservation');
    
function add_reservation(){
    global $wpdb;

    $reservation = $_POST['res_info'];

    $sanitized = array(
        'end_hour'=>sanitize_text_field($reservation['end_hour']),
        'end_minute'=>sanitize_text_field($reservation['end_minute']),
        'start_hour'=>sanitize_text_field($reservation['start_hour']),
        'start_minute'=>sanitize_text_field($reservation['start_minute']),
        'month'=>sanitize_text_field($reservation['month']),
        'day'=>sanitize_text_field($reservation['day']),
        'year'=>sanitize_text_field($reservation['year']),
        'room_id'=>sanitize_text_field($reservation['room_id']),
        'phone'=>sanitize_text_field($reservation['phone']),
        'email'=>sanitize_email($reservation['email']),
        'name'=>sanitize_text_field($reservation['name']),
        'purpose'=>sanitize_text_field($reservation['purpose']),
        'room_name'=>sanitize_text_field($reservation['room_name'])
    );

    $date_problems = validate_calendar_input($sanitized);
    $time_problems = validate_time_input($sanitized);
    
    foreach($date_problems as $key => $value){
        if($value == true){
            wp_die("<p class='sr_error'>Check if $key</p>");
        }
    }

    foreach($time_problems as $key => $value){
        if($value == true){
            wp_die("<p class='sr_error'>Check if the event time $key</p>");
        }
    }

    foreach($sanitized as $key => $input){
        if( $input == '' ){
            wp_die("<p class='sr_error'>Make sure $key field is filled</p>");
        }
    }
    
    // if there is no time conflict
    if(check_time_db($sanitized) == true){
    
        $month = $sanitized['month'];
        $day = $sanitized['day'];
        $year = $sanitized['year'];
    
        $end_time = mktime($sanitized['end_hour'], $sanitized['end_minute'], 0, $month,    $day, $year);
        $start_time = mktime($sanitized['start_hour'], $sanitized['start_minute'], 0, $month, $day, $year);
    
        $data = array(
            'room_id' => $sanitized['room_id'],
            'res_date' => mktime( 0, 0, 0, $month, $day, $year),
            'starts' => $start_time,
            'ends' => $end_time,
            'phone' => $sanitized['phone'],
            'email' => $sanitized['email'],
            'name' => $sanitized['name'],
            'purpose' => $sanitized['purpose']
        );
        $data_types = array(
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s'
        );
    
        $SQL = $wpdb->prefix . "sr_res_reservations";
    
        if( $wpdb->insert($SQL, $data, $data_types) ){
            /* 
               EMAIL RELATED CODE
            
               If your WordPress installation is set up to be able to send email, feel free to uncomment the following line
               of code to send a .ics file to the user's email client when they reserve a room so that they can send out a 
               calendar event to others who will be attending the event for them to accept. Currently only tested using 
               Microsoft Outlook. Make sure to check for related code in sr_res_cal.php and sr_res_standard.php. Related 
               code will contain an "EMAIL RELATED CODE" label beforehand.
            */
            
            // send_ics_file($sanitized['email'], $start_time, $end_time, $sanitized['purpose'], $sanitized['room_name']); 
            wp_die("<p class='sr_success'>Reservation added successfully</p>");
        }
        else{
            wp_die("<p class='sr_error'>There was an unexpected error</p>");
        }
    }
    else{
        wp_die("<p class='sr_error'>Time conflict</p>");
    }
}
    
    /*
        Used in conjunction with add_reservation()
    */
function check_time_db($input){
    date_default_timezone_set('EST');

    global $wpdb;

    $month = $input['month'];
    $day = $input['day'];
    $year = $input['year'];
    
    $end_time = mktime($input['end_hour'], $input['end_minute'], 0, $month, $day, $year);
    $start_time = mktime($input['start_hour'], $input['start_minute'], 0, $month, $day, $year);

    $input_date = mktime( 0, 0, 0, $month, $day, $year);
    
    $SQL = $wpdb->prepare(
        "SELECT res.starts, res.ends FROM " . $wpdb->prefix . "sr_res_reservations as res
        WHERE (res.room_id = %d) AND (res.res_date = %d) AND (res.trash IS FALSE)",
        $input['room_id'],
        $input_date);
    
    $results = $wpdb->get_results($SQL, ARRAY_A);
    
    if( empty($results) ){ return true; }
    else
    {
        foreach($results as $key => $row)
        {
            if( within_range($start_time, $row['starts'], $row['ends']) ){ return false; }
            if( within_range($row['starts'], $start_time, $end_time) ){ return false; }
        }
    }
    
    return true;
}
    
function display_form(){
    $form = new SR_Res_View();
}
    
function display_reservation(){
    $reservation = $_POST['res_info'];

    $sanitized = array(
                'end_hour'=>sanitize_text_field($reservation['end_hour']),
                'end_minute'=>sanitize_text_field($reservation['end_minute']),
                'start_hour'=>sanitize_text_field($reservation['start_hour']),
                'start_minute'=>sanitize_text_field($reservation['start_minute']),
                'month'=>sanitize_text_field($reservation['month']),
                'day'=>sanitize_text_field($reservation['day']),
                'year'=>sanitize_text_field($reservation['year']),
                'room_id'=>sanitize_text_field($reservation['room_id']),
                'phone'=>sanitize_text_field($reservation['phone']),
                'email'=>sanitize_email($reservation['email']),
                'name'=>sanitize_text_field($reservation['name']),
                'purpose'=>sanitize_text_field($reservation['purpose']) 
            );
    
    $data = get_res_list($sanitized['room_id'], $sanitized['month'], $sanitized['day'], $sanitized['year']);
    
    if(!empty($data)){
        if(is_array($data)){
            $list = "<tr><td>Start Time</td><td>End Time</td><td>Date</td><td>Purpose</td><td>Reserved By</td><td>Phone</td><td>Email</td></tr>";
            foreach($data as $key => $row){
                $list .= "<tr><td>" . date('h:i a', $row['starts']) . "</td><td>" . date('h:i a', $row['ends']) . 
                        "</td><td>" . date('m-d-Y', $row['res_date']) . "</td><td>" . esc_html($row['purpose']) . "</td><td>" . 
                        esc_html($row['name']) . "</td><td>" . esc_html($row['phone']) . "</td><td>" . esc_html($row['email']) . 
                        "</td><td><input type='button' id='cancel_button' onclick='cancel_reservation(" . esc_attr($row['id']) . ")' value='Cancel'></td></tr>";
            }
            
            wp_die($list);
        }
        else{
            wp_die("<tr><td class='sr_error'>Check if room is set</td></tr>");
        }
    }
    else{
        wp_die("<tr><td class='nothing_scheduled'>Nothing Scheduled</td></tr>");
    }
}

/* Gets reservation list for form purposes */
function get_res_list($room, $month, $day, $year ){
    global $wpdb;

    date_default_timezone_set('EST');

    if(!empty($room)){
        $date = mktime( 0, 0, 0, $month, $day, $year);

        $SQL = $wpdb->prepare(
        "SELECT res.id, res.res_date, res.starts, res.ends, res.phone, res.email, res.name, res.purpose
        FROM " . $wpdb->prefix . "sr_res_reservations as res
        WHERE (res.room_id = %d) AND (res.res_date = %d) AND (res.trash IS FALSE)
        ORDER BY res.starts",
        $room,
        $date);
        
        $results = $wpdb->get_results($SQL, ARRAY_A);
    }
    else{
        $results = 'garbage';
    }
    
    return $results;
}

function validate_calendar_input($input){
    date_default_timezone_set('EST');

    $problems = array(
        'no letters in date' => false,
        'year is 4 digits' => false,
        'day is in selected month' => false,
        'date in past' => false,
        );
        
    $year = $input['year'];
    $month = $input['month'];
    $day = $input['day'];
        
    // Checks for letters in date fields    
    $pattern = '/\D/';

    if( preg_match($pattern, $year) OR
        preg_match($pattern, $month) OR
        preg_match($pattern, $day) ){
        $problems['no letters in date'] = true;
        return $problems;
    }
        
    // Makes sure the year is in a four digit format
    if( strlen($year) != 4 ){
        $problems['year is 4 digits'] = true;
        return $problems;
    }
    
    // Checks if the input date itself is in the Gregorian calendar
    if( !checkdate($month, $day, $year) ){
        $problems['day is in selected month'] = true;
        return $problems;
    }
    
    // After checking that the input date itself is valid
    $input_date = mktime(0, 0, 0, $month, $day, $year);
    $current_date = mktime(0, 0, 0, date('n'), date('j'), date('Y'));

    // Checks if input date is earlier than current date
    if( $input_date < $current_date ){$problems['date in past'] = true;}
    
    return $problems;
}
    
function validate_time_input($input){
    date_default_timezone_set('EST');

    $problems = array(
        'contains letters' => false,
        'starts before it ends' => false,
        'starts or ends before now' => false,
    );
    
    // checks for letters in time fields
    $pattern = '/\D/';
    
    // Checks if the time fields contain anything besides a number
    if( preg_match($pattern,$input['end_hour']) OR
        preg_match($pattern,$input['end_minute']) OR
        preg_match($pattern,$input['start_hour']) OR
        preg_match($pattern,$input['start_minute'])
        ){
            $problems['contains letters'] = true;
            return $problems;
        }

    // If there aren't any time fields that contain letters
    $end_time = mktime($input['end_hour'],$input['end_minute'],0,$input['month'],$input['day'],$input['year']);
    $start_time = mktime($input['start_hour'],$input['start_minute'],0,$input['month'],$input['day'],$input['year']);
    $current_time = time();

    // End time less than start time
    if($end_time < $start_time){
        $problems['starts before it ends'] = true;
        return $problems;
    }

    // Starting or end time less than current time
    if($start_time < $current_time){$problems['starts or ends before now'] = true;}

    return $problems;
}
?>
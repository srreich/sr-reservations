<?php

/**
Database Queries and Actions for Calendar operations.
*/

if( ! defined( 'ABSPATH' ) ) {exit;} // Exit if accessed directly

if(!class_exists('SR_Res_Calendar')) require_once(dirname(dirname(__FILE__)).'/view/sr_res_calendar.php');

add_action('wp_ajax_calendar_add_reservation','calendar_add_reservation');
add_action('wp_ajax_nopriv_calendar_add_reservation','calendar_add_reservation');

add_action('wp_ajax_view_calendar', 'view_calendar');
add_action('wp_ajax_nopriv_view_calendar', 'view_calendar');

add_action('wp_ajax_calendar_reservation_check','calendar_reservation_check');
add_action('wp_ajax_nopriv_calendar_reservation_check','calendar_reservation_check');

function calendar_add_reservation(){
    date_default_timezone_set('EST');
    
    global $wpdb;
    
    $reservation = $_POST['res_info'];

    $sanitized = array(
        'end_hour'=>sanitize_text_field($reservation['end_hour']),
        'end_minute'=>sanitize_text_field($reservation['end_minute']),
        'start_hour'=>sanitize_text_field($reservation['start_hour']),
        'start_minute'=>sanitize_text_field($reservation['start_minute']),
        'date_stamp'=>sanitize_text_field($reservation['date_stamp']),
        'room_id'=>sanitize_text_field($reservation['room_id']),
        'phone'=>sanitize_text_field($reservation['phone']),
        'email'=>sanitize_email($reservation['email']),
        'name'=>sanitize_text_field($reservation['name']),
        'purpose'=>sanitize_text_field($reservation['purpose']),
        'room_name'=>sanitize_text_field($reservation['room_name']) 
    );

    $time_problems = calendar_validate_time_input($sanitized);

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
    if(calendar_check_time_db($sanitized) == true){
        
        $date = $sanitized['date_stamp'];
            
        $end_time = $date + ($sanitized['end_hour'] * 3600) + ($sanitized['end_minute'] * 60);
        $start_time = $date + ($sanitized['start_hour'] * 3600) + ($sanitized['start_minute'] * 60);
        $purpose = $sanitized['purpose'];
        $email = $sanitized['email'];
        
        $SQL = $wpdb->prefix . "sr_res_reservations";
        $data = array(
            'room_id' => $sanitized['room_id'],
            'res_date' => $date,
            'starts' => $start_time,
            'ends' => $end_time,
            'phone' => $sanitized['phone'],
            'email' => $email,
            'name' => $sanitized['name'],
            'purpose' => $purpose
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
        
        if( $wpdb->insert($SQL, $data, $data_types) ){
            /* 
               EMAIL RELATED CODE
            
               If your WordPress installation is set up to be able to send email, feel free to uncomment the following line
               of code to send a .ics file to the user's email client when they reserve a room so that they can send out a 
               calendar event to others who will be attending the event for them to accept. Currently only tested using 
               Microsoft Outlook. Make sure to check for related code in sr_res_db.php and sr_res_standard.php. Related 
               code will contain an "EMAIL RELATED CODE" label beforehand.
            */
        
            //send_ics_file($email, $start_time, $end_time, $purpose, $sanitized['room_name']);
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

function get_reservation_calendar($input){
    date_default_timezone_set('EST');
    
    global $wpdb;
    
    $today = mktime( 0, 0, 0, idate('m'), idate('d'), idate('Y') );
    $out_thirty = mktime( 0, 0, 0, idate('m'), (idate('d') + 30), idate('Y') );

    $SQL = $wpdb->prepare(
        "SELECT DISTINCT res.res_date 
        FROM " . $wpdb->prefix . "sr_res_reservations as res 
        WHERE (res.res_date BETWEEN %d AND %d) AND (res.room_id = %d) AND (res.trash IS FALSE)",
        $today,
        $out_thirty,
        $input);
    
    $results = $wpdb->get_col($SQL);
    
    return $results;
}
    
function view_calendar(){
    $room = sanitize_text_field($_POST['calendar_room']);
    
    if($room != 0){
        $data = get_reservation_calendar($room);
            
        $sun = array();
        $mon = array();
        $tues = array();
        $wed = array();
        $thurs = array();
        $fri = array();
        $sat = array();
            
        $week = [$sun, $mon, $tues, $wed, $thurs, $fri, $sat];
        
        for($i = 0; $i < idate('w'); $i++){
            $week[$i][] = "<td class='grayed'></td>";
        }
        for($i = 0; $i < 31; $i++){
            $timestamp = mktime( 0, 0, 0, idate('m'), (idate('d') + $i), idate('Y') );
            
            if( in_array($timestamp, $data) ){
                $week[idate('w', $timestamp)][] = "<td class='reserved'>" . date('M-d',$timestamp) 
                . "</br><input type='button' value='Check' onclick='send_timestamp_info($timestamp)'></td>";
            }
            else{
                $week[idate('w', $timestamp)][] = "<td class='nothing_reserved'>" . date('M-d',$timestamp) 
                . "</br><input type='button' value='Free' onclick='send_timestamp_info($timestamp)'></td>";
            }
        }

        if(!empty($week)){
            $calendar = "<tr><td>Sunday</td><td>Monday</td><td>Tuesday</td><td>Wednesday</td><td>Thursday</td><td>Friday</td><td>Saturday</td></tr>";
            
            for($i = 0; $i < count($week[0]); $i++){
                $calendar .= "<tr>";
                
                for($j = 0; $j < 7; $j++){
                    $calendar .= $week[$j][$i];
                }

                $calendar .= "</tr>";
            }
        }
        
        wp_die($calendar);
    }
    else{
        wp_die("<p class='sr_error'>Check Room</p>");
    }
}

function calendar_reservation_shortcode(){
    $calendar = new SR_Res_Calendar();
}

function calendar_reservation_check(){
    $room = sanitize_text_field($_POST['calendar_room']);
    $timestamp = sanitize_text_field($_POST['calendar_timestamp']);

    $data = get_res_list_timestamp($room, $timestamp);
    
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

/*
    Used in conjunction with add_reservation()
*/
function calendar_check_time_db($input){
    date_default_timezone_set('EST');

    global $wpdb;

    $date = $input['date_stamp'];

    $end_time = $date + ($input['end_hour'] * 3600) + ($input['end_minute'] * 60);
    $start_time = $date + ($input['start_hour'] * 3600) + ($input['start_minute'] * 60);
    
    $SQL = $wpdb->prepare(
        "SELECT res.starts, res.ends 
        FROM " . $wpdb->prefix . "sr_res_reservations as res 
        WHERE (res.room_id = %d) AND (res.res_date = %d) AND (res.trash IS FALSE)",
        $input['room_id'],
        $date);
    
    $results = $wpdb->get_results($SQL, ARRAY_A);
    
    if( empty($results) ){ return true; }
    else{
        foreach($results as $key => $row){
            if( within_range($start_time, $row['starts'], $row['ends']) ){ return false; }
            if( within_range($row['starts'], $start_time, $end_time) ){ return false; }
        }
    }
    
    return true;
}
    
function get_res_list_timestamp($room, $timestamp){
    global $wpdb;
    
    date_default_timezone_set('EST');
    
    if(!empty($room)){
        $SQL = $wpdb->prepare(
            "SELECT res.id, res.res_date, res.starts, res.ends, res.phone, res.email, res.name, res.purpose
            FROM " . $wpdb->prefix . "sr_res_reservations as res
            WHERE (res.room_id = %d) AND (res.res_date = %d) AND (res.trash IS FALSE)
            ORDER BY res.starts",
            $room,
            $timestamp);
        
        $results = $wpdb->get_results($SQL, ARRAY_A);
    }
    else{
        $results = 'garbage';
    }
    
    return $results;        
}
    
function calendar_validate_time_input($input){
    date_default_timezone_set('EST');

    $problems = array(
        'contains letters' => false,
        'starts before it ends' => false,
        'starts or ends before now' => false,
    );

    $pattern = '/\D/';
    $end_hour = $input['end_hour'];
    $end_minute = $input['end_minute'];
    $start_hour = $input['start_hour'];
    $start_minute = $input['start_minute'];
        
    if(preg_match($pattern,$end_hour) OR
       preg_match($pattern, $end_minute) OR
       preg_match($pattern, $start_hour) OR
       preg_match($pattern, $start_minute)
    ){
        $problems['contains letters'] = true;
        return $problems;
    }
    
    $date = $input['date_stamp'];

    $end_time = $date + (3600 * $input['end_hour']) + (60 * $input['end_minute']);
    $start_time = $date + (3600 * $input['start_hour']) + (60 * $input['start_minute']);
    $current_time = time();

    // End time less than start time
    if($end_time < $start_time){
        $problems['starts before it ends'] = true;
        return $problems;
    }

    // Starting or end time less than current time
    if( ($start_time < $current_time) ){$problems['starts or ends before now'] = true;}

    return $problems;
}
    
?>
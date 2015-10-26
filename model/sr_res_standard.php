<?php

/**
Database queries and actions used by all types of views.
*/

if( ! defined( 'ABSPATH' ) ) {exit;} // Exit if accessed directly

add_action('wp_ajax_cancel_reservation', 'cancel_reservation');
add_action('wp_ajax_nopriv_cancel_reservation', 'cancel_reservation');

// Marks a reservation as trash, so that the delete_reservation() function will remove it once the cron job to do this runs
function cancel_reservation(){

    global $wpdb;

    date_default_timezone_set('EST');
    
    $cancelled = sanitize_text_field($_POST['cancelled']);
    
    $table = $wpdb->prefix . "sr_res_reservations";
    
    /* 
        EMAIL RELATED CODE
            
        If your WordPress installation is set up to be able to send email, feel free to uncomment the following section
        of code to send an email to the user when their reservation has been canceled. Make sure to check for related
        code later in this file for sending .ics files, as well as in sr_res_cal.php and sr_res_db.php. Related code will
        contain an "EMAIL RELATED CODE" label beforehand.
    */
    
    /*
    $alert_SQL = $wpdb->prepare("SELECT res.email, res.res_date, res.starts, res.ends, res.purpose
    FROM $table as res
    WHERE res.id = %d",
    $cancelled);
    
    $email = $wpdb->get_row($alert_SQL, ARRAY_A);
    
    $purpose = $email['purpose'];
    $dated = date('m-d-Y',$email['res_date']);
    $starts = date('h:i a',$email['starts']);
    $ends = date('h:i a', $email['ends']);
    
    $client_message = "Your reservation for $purpose scheduled on $dated from $starts to $ends has been cancelled.";
    
    wp_mail($email['email'], "Reservation Cancelled", $client_message, "From: Reservation System <do not reply>");
    */
    
    $data = array('trash' => 1);
    $where = array('id' => $cancelled);
    $data_type = array('%d');
    $where_type = array('%d');
    
    $wpdb->update($table, $data, $where, $data_type, $where_type);
    
    wp_die();
}

function get_branches(){
    global $wpdb;
    
    $SQL = "
        SELECT b.id, b.branch
        FROM " . $wpdb->prefix . "sr_res_branches as b
        WHERE b.trash IS FALSE
        ORDER BY b.id
        ";

    $results = $wpdb->get_results($SQL, ARRAY_A);
    
    return $results;
}
    
function get_rooms(){
    global $wpdb;
    
    $SQL = "
        SELECT r.id, r.room 
        FROM " . $wpdb->prefix ."sr_res_rooms as r
        WHERE r.trash IS FALSE
        ";
    
    $results = $wpdb->get_results($SQL, ARRAY_A);
    
    return $results;
}

/* 
    EMAIL RELATED CODE
    
    If your WordPress installation is set up to be able to send email, feel free to uncomment the following section
    of code to send a .ics file to the user's email client when they reserve a room so that they can send out a 
    calendar event to others who will be attending the event for them to accept. Currently only tested using 
    Microsoft Outlook. Make sure to check for related code earlier in this file for reservation cancellations, as well as 
    in sr_res_cal.php and sr_res_db.php. Related code will contain an "EMAIL RELATED CODE" label beforehand.
*/ 
/*
function send_ics_file($email, $start, $end, $summary, $location){

    date_default_timezone_set('EST');

    $data = "
    BEGIN:VCALENDAR\n
    PRODID:-//Microsoft Corporation//Outlook MIMEDIR//EN\n
    VERSION:2.0\n
    BEGIN:VEVENT\n
    DTSTAMP:" . date('Ymd\THis',$start) . "\n
    UID:" . $start . $email . $end . "\n
    DTSTART:" . date('Ymd\THis', $start) . "\n
    DTEND:" . date('Ymd\THis', $end) . "\n
    ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;CN=$email:mailto:$email\n
    SUMMARY: $summary\n
    DESCRIPTION: To send invites to those attending, double click on this item in your inbox, add the emails of all attendees in the \"TO\" field, edit the body, and then click on the send button.\n
    LOCATION: $location\n
    END:VEVENT\n
    END:VCALENDAR\n
    ";
    
    $headers = array();
    $headers[] = "Content-type: text/calendar";
    $headers[] = "From: Reservation System <do not reply>";
    
    wp_mail($email, $summary, $data, $headers);    

    return;
}
*/

// Checks to see if start <= a < ends
function within_range($a, $starts, $ends){
    return ( ( ($starts <= $a) AND ($a < $ends) ) ? true:false );
}
    
?>
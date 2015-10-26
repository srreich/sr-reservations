<?php

/**
Backend administration operations
*/

if( !defined( 'ABSPATH' ) ) {exit;} // Exit if accessed directly

add_action('wp_ajax_add_branch', 'add_branch');
add_action('wp_ajax_add_room', 'add_room');
add_action('wp_ajax_delete_reservations', 'delete_reservations');
add_action('wp_ajax_edit_room', 'edit_room');
add_action('wp_ajax_edit_branch', 'edit_branch');
add_action('wp_ajax_delete_branch', 'delete_branch');
add_action('wp_ajax_delete_room', 'delete_room');
add_action('wp_ajax_default_branch', 'default_branch');
add_action('wp_ajax_format_rooms', 'format_rooms');
add_action('wp_ajax_format_branches', 'format_branches');

function add_branch(){

    global $wpdb;
    
    $admin = array_map('sanitize_text_field',$_POST['op_info']);
    
    if( empty($admin['name']) ){
        wp_die("<p class='sr_error'>No name given</p>");
    }
    
    $SQL = $wpdb->prefix . "sr_res_branches";
    $data = array('branch' => $admin['name']);
    $data_types = array('%s');
    
    if($wpdb->insert($SQL, $data, $data_types)){
        wp_die("<p class='sr_success'>Branch successfully created</p>");
    }
    else{
        wp_die("<p class='sr_error'>Branch creation error</p>");
    }
}

function add_room(){

    global $wpdb;
    
    $admin = array_map('sanitize_text_field',$_POST['op_info']);
    
    if( empty($admin['branch']) ){
        wp_die("<p class='sr_error'>No branch selected</p>");
    }
    if( empty($admin['name']) ){
        wp_die("<p class='sr_error'>No name given</p>");
    }
    
    $SQL = $wpdb->prefix . "sr_res_rooms";
    $data = array('branch_id' => $admin['branch'],
        'room' => $admin['name']
    );
    $data_types = array('%d','%s');
    
    if($wpdb->insert($SQL, $data, $data_types)){
        wp_die("<p class='sr_success'>Room successfully created</p>");
    }
    else{
        wp_die("<p class='sr_error'>Room creation error</p>");
    }
}

function delete_reservations(){

    global $wpdb;
    
    $expired_SQL = 
        'DELETE FROM ' . $wpdb->prefix . 'sr_res_reservations
        WHERE ends < UNIX_TIMESTAMP() OR trash IS TRUE';
    
    if($wpdb->query($expired_SQL)){
        wp_die("<p class='sr_success'>Cancelled and expired reservations cleared from database</p>");
    }
    else{
        wp_die("<p class='sr_error'>No reservations to delete</p>");
    }
}

function edit_room(){

    global $wpdb; 
    
    $admin = array_map('sanitize_text_field', $_POST['op_info']);
    
    if( empty($admin['room']) ){
        wp_die("<p class='sr_error'>No room selected to be edited</p>");
    }
    if( empty($admin['name']) ){
        wp_die("<p class='sr_error'>No name given</p>");
    }
    if( empty($admin['branch']) ){
        wp_die("<p class='sr_error'>No branch given</p>");
    }
    
    $data = array();
    $data_format = array();
    $where = array('id' => $admin['room']);
    $where_format = array('%d');
    
    // gets branch id for a particular room
    $setup_SQL = $wpdb->prepare(
        "SELECT res.branch_id 
        FROM " . $wpdb->prefix . "sr_res_rooms as res
        WHERE res.id = '%d'",
        $admin['room']
        );
        
    if($wpdb->get_var($setup_SQL) !== $admin['branch']){
        $data['branch_id'] = $admin['branch'];
        $data_format[] = "%d";
    }

    $data['room'] = $admin['name'];
    $data_format[] = "%s";
    
    $SQL = $wpdb->prefix . 'sr_res_rooms';
    
    if($wpdb->update($SQL, $data, $where, $data_format, $where_format)){
        wp_die("<p class='sr_success'>Room successfully edited</p>");
    }
    else{
        wp_die("<p class='sr_error'>Room edit error</p>");
    }
}

function edit_branch(){
    
    global $wpdb;
    
    $admin = array_map('sanitize_text_field', $_POST['op_info']);
    
    if( empty($admin['branch']) ){
        wp_die("<p class='sr_error'>No branch selected to be edited</p>");
    }
    if( empty($admin['name']) ){
        wp_die("<p class='sr_error'>No name given</p>");
    }
    
    $SQL = $wpdb->prefix . "sr_res_branches";
    $data = array('branch' => $admin['name']);
    $data_format = array('%s');
    $where = array('id' => $admin['branch']);
    $where_format = array('%d');
    
    if($wpdb->update($SQL, $data, $where, $data_format, $where_format)){
        wp_die("<p class='sr_success'>Branch successfully edited</p>");
    }
    else{
        wp_die("<p class='sr_error'>Branch edit error</p>");
    }
}

function default_branch(){

    global $wpdb;
    
    $admin = array_map('sanitize_text_field', $_POST['op_info']);
    
    $SQL = $wpdb->prepare(
        "SELECT res.branch_id
        FROM " . $wpdb->prefix . "sr_res_rooms as res
        WHERE res.id = %d",
        $admin['room']);
    
    $branch = $wpdb->get_var($SQL);
    
    if( !empty($branch) ){
        wp_die($branch);
    }
    else{
        wp_die();
    }
}

function delete_branch(){
    
    global $wpdb;
    
    $admin = array_map('sanitize_text_field', $_POST['op_info']);
    
    if( empty($admin['branch']) ){
        wp_die("<p class='sr_error'>No branch selected</p>");
    }
    
    $end_message = '';
    
    $reservation_SQL = $wpdb->prefix . "sr_res_reservations";
    $room_SQL = $wpdb->prefix . "sr_res_rooms";
    $branch_SQL = $wpdb->prefix . "sr_res_branches";
    $room_where = array('branch_id' => $admin['branch']);
    $branch_where = array('id' => $admin['branch']);
    $where_format = array('%d');
        
    if($wpdb->delete($room_SQL, $room_where, $where_format)){
        $end_message .= "<p class='sr_success'>Rooms in selected branch successfully deleted</p>";
    }
    
    if($wpdb->delete($branch_SQL, $branch_where, $where_format)){
        $end_message .= "<p class='sr_success'>Branch successfully deleted</p>";
    }
    else{
        $end_message .= "<p class='sr_error'>Branch deletion error</p>";
    }
    
    wp_die($end_message);
}

function delete_room(){

    global $wpdb;
    
    $admin = array_map('sanitize_text_field', $_POST['op_info']);
    
    if( empty($admin['room']) ){
        wp_die("<p class='sr_error'>No room selected</p>");
    }
    
    $SQL = $wpdb->prefix . "sr_res_rooms";
    $where = array('id' => $admin['room']);
    $where_format = array('%d');
    
    if($wpdb->delete($SQL, $where, $where_format)){
        wp_die("<p class='sr_success'>Room successfully deleted</p>");
    }
    else{
        wp_die("<p class='sr_error'>Room deletion error</p>");
    }
}

function format_rooms(){

    $raw_rooms = get_rooms();
    $rooms = "<option value=''>Select Room</option>";
    
    foreach($raw_rooms as $value){
        $rooms .= "<option value='" . esc_attr($value['id']) . "'>" . esc_html($value['room']) . "</option>";
    }
    
    echo $rooms;
}
    
function format_branches(){

    $raw_branches = get_branches();
    $branches = "<option value=''>Select Branch</option>";
        
    foreach($raw_branches as $value){
        $branches .= "<option value='" . esc_attr($value['id']) . "'>" . esc_html($value['branch']) . "</option>";
    }
    
    echo $branches;
}
?>
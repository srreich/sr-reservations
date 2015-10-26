<?php

/* Admin Menu page */

if ( ! defined( 'ABSPATH' ) ) exit; 

class SR_Res_Admin{

    private $slug = 'sr_res_admin';
    
    function __construct(){
    
        $page = add_menu_page('Reservation Administration', 'Reservation Administration', 'manage_reservations', $this->slug, array($this, 'operations_page'));
        add_submenu_page($this->slug, 'Reservation Documentation', 'Reservation Documentation', 'manage_reservations', $this->slug.'_documentation', array($this, 'documentation_page'));
    
        add_action("admin_print_scripts-$page", array($this,'enqueue_admin_scripts'));
    }
    
    function operations_page(){
    
        $raw_branches = get_branches();
        $raw_rooms = get_rooms();
        $branches = '';
        $rooms = '';
        
        foreach($raw_branches as $value){
            $branches .= '<option value="' . esc_attr($value["id"]) . '">' . esc_html($value["branch"]) . '</option>';
        }
        
        foreach($raw_rooms as $value){
            $rooms .= '<option value="' . esc_attr($value["id"]) . '">' . esc_html($value["room"]) . '</option>';
        }
    ?>
        <div id="sr_status"><p style="visibility:hidden">Content goes here</p></div>
        <h2>Branch Operations</h2>
            <select class="branch operation">
                <option value="add">Add Branch</option>
                <option value="edit">Edit Branch</option>
                <option value="delete">Delete Branch</option>
            </select>
            <select class="branch branches">
                <option value="">Select Branch</option>
                
                  <?php echo $branches;?>
                
            </select>
            <input type="text" class="branch name" size="20"></input>
            <input type="button" class="branch operate" value="Submit"></input>
            
        <h2>Room Operations</h2>
            <select class="room operation">
                <option value="add">Add Room</option>
                <option value="edit">Edit Room</option>
                <option value="delete">Delete Room</option>
            </select>
            <select class="room rooms">
                <option value="">Select Room</option>
                <?php echo $rooms;?>
            </select>
            <select class="room branches">
                <option value="">Select Branch</option>
                <?php echo $branches;?>
            </select>
            <input type="text" class="room name" size="20"></input>
            <input type="button" class="room operate" value="Submit"></input>
            
        <h2>Clear Reservations</h2>
            <span>Remove expired and cancelled reservations <input type="button" id="clear_reservations" value="Submit"></input></span>
    <?php
    }
    
    function documentation_page(){
    ?>
        <h2>General Information</h2>
            <ul>
                <li>Backend administrative reservation operations are only accessible to logged in users with either the "Admin" or "Reservation Manager" role.</li>
                <li>Using the standard reservation form, users are able to reserve or check reservations for a room at any point in time from now to December 31st of next year.</li>
                <li>Using the calendar view form, users can see a 31 day calendar showing which days a room is free and which have at least one reservation. Users can then check reservations
                made on those days, and make a reservation. Once a room has been selected, the calendar view for that room will update every 15 seconds.</li>
                <li>Check <a href="#shortcodes">Shortcode Usage</a> below to see which shortcode to use for displaying a particular form.</li>
            </ul>
        <h2>Branch Operations</h2>
            <h3>Adding a Branch</h3>
                <ol>
                    <li>Select the "Add Branch" option from the first dropdown menu in the "Branch Operations" section.</li>
                    <li>Enter the name for the new branch in the text box that follows.</li>
                    <li>Click on the "Submit" button to complete.</li>
                </ol>
                <ul>
                    <li>Note: The "Select Branch" dropdown menu has no effect on adding branches.</li>
                </ul>
            <h3>Editing a Branch Name</h3>
                <ol>
                    <li>Select the "Edit Branch" option from the first dropdown menu in the "Branch Operations" section.</li>
                    <li>Select the branch name that you want to change from the second dropdown menu.</li>
                    <li>Enter the new name for the branch in the text box that follows.</li>
                    <li>Click on the "Submit" button to complete.</li>
                </ol>
            <h3>Deleting a Branch</h3>
                <ol>
                    <li>Select the "Delete Branch" option from the first dropdown menu in the "Branch Operations" section.</li>
                    <li>Select the branch that you want to delete from the second dropdown menu.</li>
                    <li>Click on the "Submit" button to complete.</li>
                </ol>
                <ul>
                    <li>Note: The text box has no effect on deleting branches.</li>
                    <li>Note: Deleting a branch will also delete any rooms that are tied to that branch.</li>
                </ul>
        <h2>Room Operations</h2>
            <h3>Adding a Room</h3>
                <ol>
                    <li>Select the "Add Room" option from the first dropdown menu in the "Room Operations" section.</li>
                    <li>Select the branch that the room will be tied to in the third dropdown menu.</li>
                    <li>Enter the name for the new room in the text box that follows.</li>
                    <li>Click on the "Submit" button to complete.</li>
                </ol>
                <ul>
                    <li>Note: The "Select Room" dropdown menu has no effect on adding rooms.</li>
                </ul>
            <h3>Editing a Room Name</h3>
                <ol>
                    <li>Select the "Edit Room" option from the first dropdown menu in the "Room Operations" section.</li>
                    <li>Select the room name that you want to change from the second dropdown menu.</li>
                    <li>Enter the new name for the room in the text box that follows.</li>
                    <li>Click on the "Submit" button to complete.</li>
                </ol>
                <ul>
                    <li>Note: When you select a room to edit, the third dropdown menu will automatically change to the branch it is currently tied to. 
                    Unless you also want to change which branch the room is tied to, do not change this value.</li>
                </ul>
            <h3>Editing a Branch - Room Relation</h3>
                <ol>
                    <li>Select the "Edit Room" option from the first dropdown menu in the "Room Operations" section.</li>
                    <li>Select the room that you want to edit from the second dropdown menu.</li>
                    <li>Select the branch that you want the room to be tied to from the third dropdown menu.</li>
                    <li>Click on the "Submit" button to complete.</li>
                </ol>
                <ul>
                    <li>Note: Unless you also want to edit the name of the room, make sure that the text field that follows is blank.
                </ul>
            <h3>Deleting a Room</h3>
                <ol>
                    <li>Select the "Delete Room" option from the first dropdown menu in the "Room Operations" section.</li>
                    <li>Select the room that you want to delete from the second dropdown menu.</li>
                    <li>Click on the "Submit" button to complete.</li>
                </ol>
                <ul>
                    <li>Note: Neither the branch dropdown menu nor the text box have any effect on deleting rooms.</li>
                </ul>
            <h3>Deleting Expired and Cancelled Reservations</h3>
                <p>To remove expired and cancelled reservations from the database, simply click on the "Submit" button in the "Clear Reservations" section.</p>
        <h2 id="shortcodes">Shortcode Usage</h2>
            <p>When entering shortcodes, make sure to include the square brackets.</p>
            <h3>Display standard reservation form</h3>
                <p>To display a standard reservation form, enter the <strong><code>[sr_reservation_form]</code></strong> shortcode into a page.</p>
            <h3>Display 31 day calendar styled view</h3>
                <p>To display a 31 day calendar styled view of reservations, enter the <strong><code>[sr_reservation_calendar]</code></strong> shortcode into a page.</p>
    <?php
    }
        
    function enqueue_admin_scripts(){
        wp_register_script('sr_res_backend', plugins_url('../controller/sr_res_backend.js', __FILE__),array('jquery'));
        wp_localize_script('sr_res_backend', 'backend_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
        wp_enqueue_script('sr_res_backend');
    
        wp_register_style('sr_res_css', plugins_url('../view/sr_res_css.css',__FILE__));
        wp_enqueue_style('sr_res_css');
    }
}
?>
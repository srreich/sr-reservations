<?php
/*
Plugin Name: SR Room Reservations
Description: Plugin for reserving a room for a period of time.
Version: 1.0
Author: Stefan Reich
License: GPL2

one line to give the program's name and an idea of what it does.
Copyright (C) 2015 Stefan Reich

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
    
/*

Portions of this particular file are based on the pta-volunteer-sign-up-sheets.php
file in the "PTA Volunteer Sign Up Sheets" WordPress plugin developed by Stephen Sherrard.

*/    
    
    if ( ! defined( 'ABSPATH' ) ) {exit;} // Exit if accessed directly
    
    // Save version # in database for future upgrades
    if (!defined('SR_ROOM_RESERVATION_VERSION_KEY'))
        define('SR_ROOM_RESERVATION_VERSION_KEY', 'sr_room_reservation_version');
    
    if (!defined('SR_ROOM_RESERVATION_VERSION_NUM'))
        define('SR_ROOM_RESERVATION_VERSION_NUM', '1.0');
    
    add_option(SR_ROOM_RESERVATION_VERSION_KEY, SR_ROOM_RESERVATION_VERSION_NUM);
    
    require_once(dirname(__FILE__).'/model/sr_res_standard.php');
    require_once(dirname(__FILE__).'/model/sr_res_db.php');
    require_once(dirname(__FILE__).'/model/sr_res_cal.php');
    
    global $wpdb;
    $db_version = '1.0';

    
    // Shortcodes
    add_shortcode('sr_reservation_form', 'display_form');
    add_shortcode('sr_reservation_calendar','calendar_reservation_shortcode');
    // End Shortcodes
    
    register_activation_hook(__FILE__, 'sr_res_activate');
    register_deactivation_hook( __FILE__, 'deactivate');
    
    add_action('plugins_loaded', 'init');
    add_action('init', 'public_init' );
    
    add_action('admin_init', 'admin_init'); 
    add_action('admin_menu', 'admin_menu');
    
    /**
    * Admin Menu
    */
    
    function admin_menu() {
        if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_reservations' ) ) {
            if (!class_exists('SR_Res_Admin')) {
                require_once(dirname(__FILE__).'/view/sr_res_admin.php');

                $sr_res_admin = new SR_Res_Admin();
            }
        }
    }
    
    function admin_init(){
        if(current_user_can('manage_options') || current_user_can('manage_reservations') ){
            require_once(dirname(__FILE__).'/model/sr_res_backend.php');
        }
    }
    
    function public_init() {}
    
    // TODO
    function init() {
        // Check our database version and run the activate function
        $current = get_option( "sr_res_db_version" );
        if ($current < $db_version) {
            sr_res_activate();
        }
    }
    
    /**
    * Activate the plugin
    */
    function sr_res_activate() {
        global $wpdb;
    
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Database Tables
        // **********************************************************
        $sql = 'CREATE TABLE ' . $wpdb->prefix . 'sr_res_rooms(
            id INT NOT NULL AUTO_INCREMENT,
            room VARCHAR(50) NOT NULL,
            branch_id INT NOT NULL,
            trash BOOL NOT NULL DEFAULT FALSE,
            PRIMARY KEY  (id)
        ) $charset_collate;';
        $sql .= 'CREATE TABLE ' . $wpdb->prefix . 'sr_res_reservations(
            id INT NOT NULL AUTO_INCREMENT,
            room_id INT NOT NULL,
            res_date INT NOT NULL,
            starts INT NOT NULL,
            ends INT NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(50) NOT NULL,
            name VARCHAR(50) NOT NULL,
            purpose VARCHAR(50) NOT NULL,
            trash BOOL NOT NULL DEFAULT FALSE,
            PRIMARY KEY  (id)
        ) $charset_collate;';
        $sql .= 'CREATE TABLE ' . $wpdb->prefix . 'sr_res_branches(
            id INT NOT NULL AUTO_INCREMENT,
            branch VARCHAR(50) NOT NULL,
            trash BOOL NOT NULL DEFAULT FALSE,
            PRIMARY KEY  (id)
        ) $charset_collate;';
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option("sr_res_db_version", $db_version);
        
        // Add custom role and capability
        $role = get_role( 'author' );
        add_role('reservation_manager', 'Reservation Manager', $role->capabilities);
        $role = get_role('reservation_manager');
        if (is_object($role)) {
            $role->add_cap('manage_reservations');
        }

        $role = get_role('administrator');
        if (is_object($role)) {
            $role->add_cap('manage_reservations');
        }

    }
    
    /**
    * Deactivate the plugin
    */
    function deactivate() {
        // Check permissions and referer
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );

        // Remove custom role and capability
        $role = get_role('reservation_manager');
        if (is_object($role)) {
            $role->remove_cap('manage_reservations');
            $role->remove_cap('read');
            remove_role('reservation_manager');
        }
        $role = get_role('administrator');
        if (is_object($role)) {
            $role->remove_cap('manage_reservations');
        }

    }

?>
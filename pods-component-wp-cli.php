<?php
/*
Plugin Name: Pods - WP-CLI Integration
Plugin URI: http://podsframework.org/
Description: Adds WP-CLI Integration for the Pods API
Version: 1.0 Alpha 1
Author: Pods Framework Team
Author URI: http://podsframework.org/about/

Copyright 2009-2013  Pods Foundation, Inc  (email : contact@podsfoundation.org)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'pods_components_get', 'pods_component_wp_cli_init' );
add_action( 'pods_components_load', 'pods_component_wp_cli_load' );

function pods_component_wp_cli_init () {
    register_activation_hook( __FILE__, 'pods_component_wp_cli_reset' );
    register_deactivation_hook( __FILE__, 'pods_component_wp_cli_reset' );

    pods_component_wp_cli_load();

    add_filter( 'pods_components_register', array( 'PodsComponent_WP_CLI', 'component_register' ) );
}

function pods_component_wp_cli_load () {
    $component_path = plugin_dir_path( __FILE__ );
    $component_file = $component_path . 'PodsComponent_WP_CLI.php';

    require_once( $component_file );

    PodsComponent_WP_CLI::$component_path = $component_path;
    PodsComponent_WP_CLI::$component_file = $component_file;
}

function pods_component_wp_cli_reset () {
    delete_transient( 'pods_components' );
}
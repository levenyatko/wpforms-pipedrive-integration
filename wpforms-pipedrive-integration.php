<?php
    /*
    Plugin Name: WPForms Pipedrive integration
    Description: The plugin allows to send WPForms data to the Pipedrive CRM
    Author: Daria Levchenko
    Version: 0.5.0
    Requires at least: 4.9
    Requires PHP: 7.4
    License: GPLv3
    License URI: https://www.gnu.org/licenses/gpl-3.0.html
    */

    if (!defined('ABSPATH')) {
        exit;
    }

    if ( ! defined( 'WPFPD_PLUGIN_DIR' ) ) {
        define( 'WPFPD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }

    if ( ! defined( 'WPFPD_PLUGIN_DIR_URL' ) ) {
        define( 'WPFPD_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
    }

    require_once WPFPD_PLUGIN_DIR . '/includes/functions.php';
    require_once WPFPD_PLUGIN_DIR . '/includes/class-plugin.php';

    wpfpd_instance();
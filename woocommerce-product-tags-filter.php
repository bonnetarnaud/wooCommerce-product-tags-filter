<?php
/*
Plugin Name: WooCommerce Product Tags Filter
Description: Adds a widget to filter products by tags.
Version: 1.2
Author: Arnaud Bonnet
*/

// Load the widget class file
require_once plugin_dir_path(__FILE__) . 'class/init-class.php';

function add_plugin_scripts()
{
    wp_enqueue_style('tags-filter', plugins_url("/", __FILE__) . 'assets/css/tags-filter.css', array(), '1.1', 'all');
}

add_action('wp_enqueue_scripts', 'add_plugin_scripts');

<?php
/*
Plugin Name: Bitcoin Block Date Converter
Plugin URI: https://SuperAtic.net
Description: Converts all dates in posts and pages to Bitcoin block numbers.
Version: 1.0.3.13
Author: SuperAtic inc.
Author URI: https://SuperAtic.com
Text Domain: bitcoin-block-date-converter
Domain Path: /languages
*/

// Convert date to Bitcoin block number
function convert_date_to_block_number($the_date, $d, $post) {
    $date_display_option = get_option('date_display_option', 'normal');

    if ($date_display_option == 'normal') {
        return $the_date;
    }

    $post_time = strtotime($post->post_date);
    $genesis_block_time = strtotime('2009-01-03 00:00:00');
    $block_generation_time = 10 * 60;

    if ($post_time < $genesis_block_time) {
        return $the_date;
    }

    $block_number = floor(($post_time - $genesis_block_time) / $block_generation_time);
    $block_link = '<a href="https//mempool.space/block/' . $block_number . '" >' . $block_number . '</a>';

    if ($date_display_option == 'block') {
        return wp_kses(__('Bitcoin block: ', 'bitcoin-block-date-converter') . $block_number, array( 'a' => array( 'href' => array() ) ));
    } else {
        return wp_kses($the_date . __(' - Bitcoin block: ', 'bitcoin-block-date-converter') . $block_number , array( 'a' => array( 'href' => array() ) ));
    }
}

add_filter('get_the_date', 'convert_date_to_block_number', 20, 3); // Increased priority to 20 to make sure our function runs last

// Load plugin text domain
function bbd_load_textdomain() {
    load_plugin_textdomain( 'bitcoin-block-date-converter', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action('plugins_loaded', 'bbd_load_textdomain');

// Add setting to General Settings page
function add_date_display_option_setting() {
    register_setting('general', 'date_display_option');

    add_settings_field(
        'date_display_option',
        __('Date display option', 'bitcoin-block-date-converter'),
        'date_display_option_setting_html',
        'general'
    );
}

add_action('admin_init', 'add_date_display_option_setting');

// HTML for the setting
function date_display_option_setting_html() {
    $date_display_option = get_option('date_display_option', 'normal');
    $options = array(
        'normal' => __('Normal date', 'bitcoin-block-date-converter'),
        'block' => __('Bitcoin block', 'bitcoin-block-date-converter'),
        'both' => __('Both', 'bitcoin-block-date-converter'),
    );
    echo '<select id="date_display_option" name="date_display_option">';
    foreach ($options as $value => $label) {
        echo '<option value="' . $value . '"' . selected($value, $date_display_option, false) . '>' . $label . '</option>';
    }
    echo '</select>';
}

// Add settings link to plugin actions
function add_settings_link($links) {
    $settings_link = '<a href="options-general.php">' . __('Settings', 'bitcoin-block-date-converter') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'add_settings_link');

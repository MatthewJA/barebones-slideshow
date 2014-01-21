<?php
/**
 * Plugin Name: Barebones Slideshow
 * Plugin URI: http://github.com/MatthewJA/barebones-slideshow
 * Description: Simple slideshow plugin.
 * Version: 0.0.1
 * Author: MatthewJA
 * Requires at least: 3.0
 * Tested up to: 3.8
 * Author URI: http://github.com/MatthewJA
 * License: GPL v2
 */

/* Register functions with WordPress. */
if (is_admin()) {
    // Register initialisation.
    add_action('admin_init', 'barebones_slideshow_init');

    // Register the admin page.
    add_action('admin_menu', 'barebones_slideshow_menu');
}

// Register shortcode [barebones-slideshow] to insert slideshows.
add_shortcode('barebones-slideshow', 'barebones_slideshow_get');


/* Constants. */
// CSS class of the slideshow.
$barebones_slideshow_main_class = 'barebones-slideshow';

// CSS class of the images in the slideshow.
$barebones_slideshow_image_class = 'barebones-slideshow-image';

// CSS class of the caption in the slideshow.
$barebones_slideshow_caption_class = 'barebones-slideshow-caption';


/* Functions. */

/**
 * Initialise plugin.
 */
function barebones_slideshow_init() {
    // Set up settings.
    register_setting('barebones-slideshow-slides', 'barebones-slideshow-slides');
}

/**
 * Set up the admin panel of the plugin.
 */
function barebones_slideshow_menu() {
    add_options_page('Barebones Slideshow Options', 'Barebones Slideshow', 'manage_options', 'barebones-slideshow', 'barebones_slideshow_options');
}

/**
 * Echo the options page HTML.
 */
function barebones_slideshow_options() {
    if (!current_user_can('manage_options')) {
        wp_die(__( 'You do not have sufficient permissions to access this page.'));
    }


    // Set up page.
    echo '<div class="wrap"><h2>Barebones Slideshow</h2>';

    // Start options form.
    echo '<form method="POST" action="options.php">';
    settings_fields('barebones-slideshow-slides');
    do_settings_sections('barebones-slideshow');

    // Close page.
    submit_button();
    echo '</div>';
}

/**
 * Return the slideshow HTML.
 */
function barebones_slideshow_get() {
    return "<div class=\"{$barebones_slideshow_main_class}\">Hello, world!</div>";
}

/** End of file **/
<?php
/**
 * Plugin Name: Barebones Slideshow
 * Plugin URI: http://github.com/MatthewJA/barebones-slideshow
 * Description: Simple slideshow plugin.
 * Version: 0.0.1
 * Author: MatthewJA
 * Author URI: http://github.com/MatthewJA
 * License: GPL v2
 */

/* Register functions with WordPress. */
add_shortcode('barebones-slideshow', 'barebones_slideshow_get');

$barebones_slideshow_class = 'barebones-slideshow';

function barebones_slideshow_get() {
	return "<div class=\"{$barebones_slideshow_class}\">Hello, world!</div>"
}

/** End of file **/
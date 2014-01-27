<?php
/**
 * Plugin Name: Barebones Slideshow
 * Plugin URI: http://github.com/MatthewJA/barebones-slideshow
 * Description: Simple slideshow plugin.
 * Version: 0.1.2
 * Author: MatthewJA
 * Requires at least: 3.0
 * Tested up to: 3.8
 * Author URI: http://github.com/MatthewJA
 * License: GPL v2
 */


/* Functions. */

/**
 * Register functions.
 */
function barebones_slideshow_register() {
    if (is_admin()) {
        // Register the admin page.
        add_action('admin_menu', 'barebones_slideshow_menu');

        // Register initialisation.
        add_action('admin_init', 'barebones_slideshow_init');
    }

    // Register shortcode [barebones-slideshow] to insert slideshows.
    add_shortcode('barebones-slideshow', 'barebones_slideshow_get');
}

/**
 * Initialise plugin.
 */
function barebones_slideshow_init() {
    // Set up settings.
    register_setting('barebones_slideshow_slides', 'barebones_slideshow_slides');
    add_settings_section('barebones_slideshow_main', 'Settings', 'barebones_slideshow_settings_text', 'barebones_slideshow');
    add_settings_field('barebones_slideshow_images', 'Images in Slideshow', 'barebones_slideshow_setting_images', 'barebones_slideshow', 'barebones_slideshow_main');
    add_settings_field('barebones_slideshow_captions', 'Captions in Slideshow', 'barebones_slideshow_setting_captions', 'barebones_slideshow', 'barebones_slideshow_main');
    add_settings_field('barebones_slideshow_slidetime', 'Slide Time (ms)', 'barebones_slideshow_setting_slidetime', 'barebones_slideshow', 'barebones_slideshow_main');
}

/**
 * Set up the admin panel of the plugin.
 */
function barebones_slideshow_menu() {
    add_options_page('Barebones Slideshow Options', 'Barebones Slideshow', 'manage_options', 'barebones_slideshow', 'barebones_slideshow_options');
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
    settings_fields('barebones_slideshow_slides');
    do_settings_sections('barebones_slideshow');

    // Close page.
    submit_button();
    echo '</div>';
}

/**
 * Echo the settings text.
 */
function barebones_slideshow_settings_text() {
    echo '<p>Modify slides for Barebones Slideshow here.</p>';
}

/**
 * Echo the slideshow images settings.
 */
function barebones_slideshow_setting_images() {
    $options = get_option('barebones_slideshow_slides');
    echo "<input id=\"barebones_slideshow_images\" name=\"barebones_slideshow_slides[images]\" size=\"40\" type=\"text\" value=\"{$options['images']}\" />";
    echo '<p>Input URLs separated by pipes (|).</p>';
}

/**
 * Echo the slideshow slide time settings.
 */
function barebones_slideshow_setting_slidetime() {
    $options = get_option('barebones_slideshow_slides');
    echo "<input id=\"barebones_slideshow_slidetime\" name=\"barebones_slideshow_slides[slidetime]\" size=\"40\" type=\"text\" value=\"{$options['slidetime']}\" />";
    echo '<p>How long the slides should be displayed for.</p>';
}

/**
 * Echo the slideshow captions settings.
 */
function barebones_slideshow_setting_captions() {
    $options = get_option('barebones_slideshow_slides');
    echo "<input id=\"barebones_slideshow_captions\" name=\"barebones_slideshow_slides[captions]\" size=\"40\" type=\"text\" value=\"{$options['captions']}\" />";
    echo '<p>Input captions for the slides, separated by pipes (|). If you do not want a caption for a particular image, just leave that caption blank. ';
    echo 'Leave blank if you want no captions.</p>';
}

/**
 * Return the slideshow HTML.
 */
function barebones_slideshow_get() {
    $options = get_option('barebones_slideshow_slides');

    $slide_time = $options['slidetime'] ? $options['slidetime'] : '1000';

    // Get an array of images.
    $images = explode('|', preg_replace('/\s/', '', $options['images']));

    $image_count = sizeof($images);

    if ($image_count == 0) {
        // No images to display, so don't show the slideshow.
        return '';
    }

    $image_tags = array();
    for ($i=0; $i < $image_count; $i++) { 
        $image_tags[] = '<img src="' . $images[$i] . '" class="' . 'barebones-slideshow-image' . '" style="width:100%; display:none; position:absolute;" />';
    }

    // Sort out captions.
    $captions = explode('|', $options['captions']);

    if (sizeof($captions) == 0) {
        $captions_enabled = 'false';
    } else {
        $captions_enabled = 'true';
    }

    $caption_tags = array();
    for ($i=0; $i < sizeof($captions); $i++) { 
        $caption_tags[] = '<div class="barebones-slideshow-caption" style="position:absolute; bottom:0; left:0; background-color:#FFFFFF; display: none;">' . $captions[$i] . '</div>';
    }

    // Construct the JavaScript code for the slideshow.
    $code = <<<JAVASCRIPT
        <script>
            (function() {
                var slideUpto = 0;
                var maxSlide = {$image_count};

                var slideTime = {$slide_time};
                var slideSpeed = 5;

                var captions = {$captions_enabled};

                var slideshows = null;

                var transitionBetween = function(last, next, speed) {
                    // Transition between slide last and slide next.

                    // Show the next slide and set reference positions.
                    var lastLeft = [];
                    var nextLeft = [];
                    for (var i = slideshows.length - 1; i >= 0; i--) {
                        slideshows[i].children[next].style.display = 'inline';
                        if (slideshows[i].children[next + maxSlide])
                            slideshows[i].children[next + maxSlide].style.display = 'block';
                        lastLeft.push(0);
                        nextLeft.push(slideshows[i].children[last].offsetWidth);
                    };

                    var ticker = 0;

                    // Define the actual transition.
                    var doTransition = function() {
                        if (ticker < 1000) {
                            ticker += 1;
                            for (var i = slideshows.length - 1; i >= 0; i--) {
                                var currentLeft = (1 - Math.cos(ticker/1000 * Math.PI)) / 2 * -slideshows[i].children[last].offsetWidth;
                                currentLeft += slideshows[i].children[last].offsetWidth * 0.0007;
                                lastLeft[i] = currentLeft;
                                nextLeft[i] = currentLeft + slideshows[i].children[last].offsetWidth;

                                slideshows[i].children[last].style.left = lastLeft[i] + 'px';
                                slideshows[i].children[next].style.left = nextLeft[i] + 'px';

                                if (captions) {
                                    if (slideshows[i].children[last + maxSlide])
                                        slideshows[i].children[last + maxSlide].style.left = lastLeft[i] + 'px';
                                    if (slideshows[i].children[next + maxSlide])
                                        slideshows[i].children[next + maxSlide].style.left = nextLeft[i] + 'px';
                                }
                            }

                            setTimeout(doTransition, 1);
                        } else {
                            // Clean up.
                            for (var i = slideshows.length - 1; i >= 0; i--) {
                                slideshows[i].children[last].style.display = 'none';
                                if (slideshows[i].children[last + maxSlide])
                                    slideshows[i].children[last + maxSlide].style.display = 'none';
                                slideshows[i].children[last].style.left = '0';
                                slideshows[i].children[next].style.left = '0';
                            };

                            // Continue the slideshow.
                            setTimeout(transitionSlide, slideTime);
                        }
                    }

                    doTransition();
                };

                var transitionSlide = function() {
                    var last = slideUpto;
                    slideUpto = (slideUpto + 1) % maxSlide;
                    transitionBetween(last, slideUpto, slideSpeed);
                };

                var beginSlideshow = function() {
                    slideshows = document.getElementsByClassName('barebones-slideshow');
                    if (slideshows.length > 0) {
                        for (var i = slideshows.length - 1; i >= 0; i--) {
                            slideshows[i].children[0].style.display = 'inline';
                            if (slideshows[i].children[maxSlide])
                                slideshows[i].children[maxSlide].style.display = 'inline';
                            slideshows[i].style.height = slideshows[i].children[0].offsetHeight + 'px';
                        };
                        transitionSlide();
                    }
                }

                if (window.addEventListener) {
                    window.addEventListener('load', beginSlideshow, false);
                } else if (window.attachEvent) {
                    window.attachEvent('onload', beginSlideshow);
                }
            }).call();
        </script>
JAVASCRIPT;

    return '<div class="' . 'barebones-slideshow' . '" style="overflow:hidden; width:100%; position:relative;">' . implode($image_tags) . implode($caption_tags) . '</div>' . $code;
}


/* Main */
barebones_slideshow_register();

/** End of file **/
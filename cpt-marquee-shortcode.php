<?php
/**
 * Plugin Name: CPT Marquee Shortcode
 * Description: Display custom post type titles in a horizontal marquee or vertical list, with options for limit, speed, direction, separator, prefix, and links.
 * Version: 1.3
 * Author: MD. Asraful Islam
 * Author URI: https://www.asraful.com.bd/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cpt-marquee
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * WP tested up to: 6.8.1
 * GitHub Plugin URI: https://github.com/AsrafulDev/cpt-marquee-shortcode
 */

if (!defined('ABSPATH')) exit;

// Register the shortcode
function cpt_title_marquee_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_type' => 'post',
        'limit'     => 5,
        'speed'     => 5,
        'direction' => 'left',
        'separator' => ' | ',
        'prefix'    => '',
        'list_type' => 'horizontal', // NEW: list_type
    ), $atts, 'cpt_marquee');

    $query = new WP_Query(array(
        'post_type'      => sanitize_text_field($atts['post_type']),
        'posts_per_page' => intval($atts['limit']),
        'post_status'    => 'publish',
    ));

    if (!$query->have_posts()) return '';

    ob_start();

    // Horizontal marquee output
    if ($atts['list_type'] === 'horizontal') {
        echo '<marquee direction="' . esc_attr($atts['direction']) . '" scrollamount="' . intval($atts['speed']) . '" onmouseover="this.stop();" onmouseout="this.start();">';
        $items = [];
        while ($query->have_posts()) {
            $query->the_post();
            $title = esc_html(get_the_title());
            $link = esc_url(get_permalink());
            $prefix = esc_html($atts['prefix']);
            $items[] = "<a href='{$link}' style='text-decoration:none;' target='_blank'>{$prefix}{$title}</a>";
        }
        echo implode(esc_html($atts['separator']), $items);
        echo '</marquee>';
    }

    // Vertical list output
    else {
        echo '<ul class="cpt-marquee-vertical-list">';
        while ($query->have_posts()) {
            $query->the_post();
            $title = esc_html(get_the_title());
            $link = esc_url(get_permalink());
            $prefix = esc_html($atts['prefix']);
            echo "<li><a href='{$link}' target='_blank'>{$prefix}{$title}</a></li>";
        }
        echo '</ul>';
        ?>
        <style>
        .cpt-marquee-vertical-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .cpt-marquee-vertical-list li {
            margin: 5px 0;
        }
        </style>
        <?php
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('cpt_marquee', 'cpt_title_marquee_shortcode');

// Admin menu
function cpt_marquee_add_admin_menu() {
    add_options_page(
        'CPT Marquee Shortcode Generator',
        'CPT Marquee',
        'manage_options',
        'cpt-marquee-generator',
        'cpt_marquee_admin_page'
    );
}
add_action('admin_menu', 'cpt_marquee_add_admin_menu');

// Admin page UI
function cpt_marquee_admin_page() {
    ?>
    <div class="wrap">
        <h1>CPT Marquee Shortcode Generator</h1>
        <form id="cpt-marquee-form">
            <table class="form-table">
                <tr>
                    <th><label for="post_type">Post Type</label></th>
                    <td>
                        <select id="post_type">
                            <?php
                            $post_types = get_post_types(['public' => true], 'objects');
                            foreach ($post_types as $slug => $pt) {
                                echo '<option value="' . esc_attr($slug) . '">' . esc_html($pt->label) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="limit">Limit</label></th>
                    <td><input type="number" id="limit" value="5" min="1" /></td>
                </tr>
                <tr>
                    <th><label for="speed">Speed</label></th>
                    <td><input type="number" id="speed" value="5" min="1" /></td>
                </tr>
                <tr>
                    <th><label for="direction">Direction</label></th>
                    <td>
                        <select id="direction">
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                            <option value="up">Up</option>
                            <option value="down">Down</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="separator">Separator</label></th>
                    <td><input type="text" id="separator" value=" | " /></td>
                </tr>
                <tr>
                    <th><label for="prefix">Item Prefix</label></th>
                    <td><input type="text" id="prefix" value="→ " /></td>
                </tr>
                <tr>
                    <th><label for="list_type">List Type</label></th>
                    <td>
                        <select id="list_type">
                            <option value="horizontal">Horizontal (Marquee)</option>
                            <option value="vertical">Vertical (List)</option>
                        </select>
                    </td>
                </tr>
            </table>
            <p><button type="button" class="button button-primary" onclick="generateShortcode()">Generate Shortcode</button></p>
        </form>

        <h2>Generated Shortcode:</h2>
        <textarea id="generated-shortcode" rows="2" style="width:100%;" readonly></textarea>
    </div>

    <script>
        function generateShortcode() {
            const postType = document.getElementById('post_type').value;
            const limit = document.getElementById('limit').value;
            const speed = document.getElementById('speed').value;
            const direction = document.getElementById('direction').value;
            const separator = document.getElementById('separator').value;
            const prefix = document.getElementById('prefix').value;
            const listType = document.getElementById('list_type').value;

            const shortcode = `[cpt_marquee post_type="${postType}" limit="${limit}" speed="${speed}" direction="${direction}" separator="${separator.replace(/"/g, '&quot;')}" prefix="${prefix.replace(/"/g, '&quot;')}" list_type="${listType}"]`;
            document.getElementById('generated-shortcode').value = shortcode;
        }
    </script>
    <?php
}

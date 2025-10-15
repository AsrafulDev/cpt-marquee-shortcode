<?php
/**
 * Plugin Name: CPT Marquee Shortcode
 * Description: Display custom post type content in a horizontal marquee or vertical list, with options for title toggle, content type (full/excerpt/none), limit, speed, direction, separator, prefix, and more.
 * Version: 1.4
 * Author: MD. Asraful Islam
 * Author URI: https://www.asraful.com.bd/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cpt-marquee
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * WP tested up to: 6.8.1
 */

if (!defined('ABSPATH')) exit;

/**
 * =======================================================
 * SHORTCODE FUNCTION
 * =======================================================
 */
function cpt_title_marquee_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_type'    => 'post',
        'limit'        => 5,
        'speed'        => 5,
        'direction'    => 'left',
        'separator'    => ' | ',
        'prefix'       => '',
        'list_type'    => 'horizontal',
        'show_title'   => 'true',
		'title_bold'  => 'true',
        'title_sep'   => ' : ',
        'content_type' => 'none',
    ), $atts, 'cpt_marquee');

    $query = new WP_Query(array(
        'post_type'      => sanitize_text_field($atts['post_type']),
        'posts_per_page' => intval($atts['limit']),
        'post_status'    => 'publish',
    ));

    if (!$query->have_posts()) return '';

    ob_start();

    // HORIZONTAL MARQUEE
    if ($atts['list_type'] === 'horizontal') {
        echo '<marquee direction="' . esc_attr($atts['direction']) . '" scrollamount="' . intval($atts['speed']) . '" onmouseover="this.stop();" onmouseout="this.start();" style="white-space:nowrap;">';

        $items = [];
        while ($query->have_posts()) {
            $query->the_post();

            $title   = esc_html(get_the_title());
            $link    = esc_url(get_permalink());
            $prefix  = esc_html($atts['prefix']);
			
			$title_html = '';
			if ( $atts['show_title'] === 'true' ) {
				$title_text = get_the_title();
				if ( $atts['title_bold'] === 'true' ) {
					$title_text = '<strong>' . esc_html( $title_text ) . '</strong>';
				} else {
					$title_text = esc_html( $title_text );
				}
				$title_html = $title_text . esc_html( $atts['title_sep'] );
			}
            // Content handling
            $content_html = '';
            if ($atts['content_type'] === 'full') {
                $content_html = ' ' . wp_strip_all_tags(get_the_content());
            } elseif ($atts['content_type'] === 'excerpt') {
                $content_html = ' ' . wp_strip_all_tags(get_the_excerpt());
            }

            // Conditional title
            $display = ($atts['show_title'] === 'true') ? "{$prefix}{$title_html}" : '';

            $items[] = "<a href='{$link}' style='text-decoration:none; margin-right:10px;' target='_blank'>{$display}{$content_html}</a>";
        }

        echo implode(esc_html($atts['separator']), $items);
        echo '</marquee>';
    }

    // VERTICAL LIST
    else {
        echo '<ul class="cpt-marquee-vertical-list">';
        while ($query->have_posts()) {
            $query->the_post();

            $title   = esc_html(get_the_title());
            $link    = esc_url(get_permalink());
            $prefix  = esc_html($atts['prefix']);
			
			$title_html = '';
			if ( $atts['show_title'] === 'true' ) {
				$title_text = get_the_title();
				if ( $atts['title_bold'] === 'true' ) {
					$title_text = '<strong>' . esc_html( $title_text ) . '</strong>';
				} else {
					$title_text = esc_html( $title_text );
				}
				$title_html = $title_text . esc_html( $atts['title_sep'] );
			}
			
            $content_html = '';
            if ($atts['content_type'] === 'full') {
                $content_html = '<div class="cpt-content">' . wp_strip_all_tags(get_the_content()) . '</div>';
            } elseif ($atts['content_type'] === 'excerpt') {
                $content_html = '<div class="cpt-content">' . wp_strip_all_tags(get_the_excerpt()) . '</div>';
            }

            echo "<li><a href='{$link}' target='_blank'>";
            if ($atts['show_title'] === 'true') {
                echo "{$prefix}{$title_html}";
            }
            echo "</a> {$content_html}</li>";
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
            margin: 8px 0;
        }
        .cpt-content {
            font-size: 14px;
            display: block;
            margin-top: 4px;
            color: #555;
        }
        </style>
        <?php
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('cpt_marquee', 'cpt_title_marquee_shortcode');

/**
 * =======================================================
 * ADMIN MENU + SHORTCODE GENERATOR
 * =======================================================
 */
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

/**
 * ADMIN PAGE UI
 */
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

                <!-- NEW OPTIONS -->
                <tr>
                    <th><label for="show_title">Show Title</label></th>
                    <td>
                        <select id="show_title">
                            <option value="true">Show</option>
                            <option value="false">Hide</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="bold_title">Show Title</label></th>
                    <td>
                        <select id="bold_title">
                            <option value="true">Bold</option>
                            <option value="false">Regular</option>
                        </select>
                    </td>
                </tr>
				<tr>
                    <th><label for="serfix">Title Serfix</label></th>
                    <td><input type="text" id="serfix" value=" : " /></td>
                </tr>
                <tr>
                    <th><label for="content_type">Content Type</label></th>
                    <td>
                        <select id="content_type">
                            <option value="none">No Content</option>
                            <option value="excerpt">Excerpt</option>
                            <option value="full">Full Content</option>
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
            const showTitle = document.getElementById('show_title').value;
            const boldTitle = document.getElementById('bold_title').value;
            const serfix = document.getElementById('serfix').value;
            const contentType = document.getElementById('content_type').value;

            const shortcode = `[cpt_marquee post_type="${postType}" limit="${limit}" speed="${speed}" direction="${direction}" separator="${separator.replace(/"/g, '&quot;')}" prefix="${prefix.replace(/"/g, '&quot;')}" list_type="${listType}" show_title="${showTitle}" title_bold="${boldTitle}" title_sep="${serfix.replace(/"/g, '&quot;')}" content_type="${contentType}"]`;
            document.getElementById('generated-shortcode').value = shortcode;
        }
    </script>
    <?php
}

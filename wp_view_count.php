<?php
/**
 * Plugin Name: WP View Count
 * Plugin URI: https://github.com/azmazm/wp-view-count
 * Description: A plugin for WordPress which allows counting and displaying the number of views on a given post with a simple shortcode
 * Version: 1.0.0
 * Author: Szymon Bedeniczuk
 */

 defined('ABSPATH') || exit;

 function count_views(){
    if(!is_single()) return;
    global $post;
    $post_id = $post->ID;
    $cookie_id = 'wp_view_count_' . $post_id . '_visited';
    $visited = isset($_COOKIE[$cookie_id]) ? $_COOKIE[$cookie_id] : false; // check if the cookie is set
    if($visited){
        return;
    }

    $views = get_post_meta($post_id, 'views', true);
    
    if(!is_numeric($views)){
        delete_post_meta($post_id, 'views'); // error handling check, to make sure views is a number
        add_post_meta($post_id, 'views', '1'); // if views is not a number, set it to 1
    }else{
        $views++;
        update_post_meta($post_id, 'views', $views);
    }

    setcookie($cookie_id, 'visited', time() + 3600, '/');
 }

 add_action('wp', 'count_views');

 function get_views(){
    global $post;
    $post_id = $post->ID;
    $views = get_post_meta($post_id, 'views', true);
    return $views;
}

function wp_view_count(){
    return esc_html__('Views: ', 'wp_view_count') . get_views(); //view count shortcode
}
add_shortcode('wp_view_count', 'wp_view_count');


add_action('admin_menu', 'wp_view_count_add_page');

function wp_view_count_add_page() {
    add_management_page(
        __('Posts Views', 'wp_view_count'), // page title 
        __('Posts Views', 'wp_view_count'), // page name in the menu
        'manage_options',                   // security
        'wp_view_count',                    // menu slug
        'wp_view_count_page'                // callback method
    );
}

function wp_view_count_reset_views() { // reset views of the post
    if (
        isset($_POST['wp_view_count_reset_views']) &&
        isset($_POST['post_id']) &&
        current_user_can('manage_options') // security
    ) {
        $post_id = intval($_POST['post_id']);
        update_post_meta($post_id, 'views', 0);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Views have been reset.', 'wp_view_count') . '</p></div>'; // admin message
    }
}

add_action('admin_init', 'wp_view_count_reset_views');

function wp_view_count_page() {
    $args = [
        'post_type'      => 'post',
        'posts_per_page' => 5,
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'views',
        'order'          => 'DESC',
    ]; // arguments for the WP_Query, takes top 5 posts by views

    $posts = new WP_Query($args);

    echo '<div class="wrap">';
    echo '<h2>' . esc_html__('5 Most Popular Posts', 'wp_view_count') . '</h2>';

    if ($posts->have_posts()) {
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>' . esc_html__('Title', 'wp_view_count') . '</th><th>' . esc_html__('Views', 'wp_view_count') . '</th><th>' . esc_html__('Actions', 'wp_view_count') . '</th></tr></thead><tbody>';

        while ($posts->have_posts()) {
            $posts->the_post();
            $post_id = get_the_ID();
            $views = get_post_meta($post_id, 'views', true);
            $views = is_numeric($views) ? $views : 0;

            echo '<tr>';
            echo '<td><a href="' . esc_url(get_permalink($post_id)) . '" target="_blank">' . esc_html(get_the_title()) . '</a></td>'; // link to the post with the title
            echo '<td>' . esc_html($views) . '</td>';
            echo '<td>
                    <a href="' . esc_url(get_edit_post_link($post_id)) . '" class="button">' . esc_html__('Edit Post' , 'wp_view_count') . '</a> 
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="post_id" value="' . esc_attr($post_id) . '">
                        <input type="submit" name="wp_view_count_reset_views" class="button button-secondary" value= ' . esc_attr__('Reset Views', 'wp_view_count') . '>
                    </form>
                  </td>'; //link to edit the post and form for resetting views of a post, form used for security, as using GET for updating information is a bad practice
            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>' . esc-html__('No posts to display', 'wp_view_count') . '.</p>'; // no posts error handling
    }

    echo '</div>';
    wp_reset_postdata(); 
}

?>

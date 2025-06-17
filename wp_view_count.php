<?php
/**
 * Plugin Name: WP View Count
 * Plugin URI: https://github.com/azmazm/wp-view-count
 * Description: A plug in for WordPress which allows for counting and displaying the number of views on posts
 * Version: 1.0.0
 * Author: Szymon Bedeniczuk
 */

 function count_views(){
    if(!is_single()) return;
    global $post;
    $post_id = $post->ID;
    $cookie_id = $post_id . '_visited';
    $visited = isset($_COOKIE[$cookie_id]) ? $_COOKIE[$cookie_id] : false;
    if($visited){
        return;
    }

    $views = get_post_meta($post_id, 'views', true);
    
    if(!is_numeric($views)){
        delete_post_meta($post_id, 'views');
        add_post_meta($post_id, 'views', '1');
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
    return "Views: " . get_views();
}
add_shortcode('wp_view_count', 'wp_view_count');


add_action('admin_menu', 'wp_view_count_add_page');

function wp_view_count_add_page() {
    add_management_page(
        'Posts Views',        
        'Posts Views',        
        'manage_options',   
        'wp_view_count',      
        'wp_view_count_page' 
    );
}

function wp_view_count_reset_views() {
    if (
        isset($_POST['wp_view_count_reset_views']) &&
        isset($_POST['post_id']) &&
        current_user_can('manage_options')
    ) {
        $post_id = intval($_POST['post_id']);
        delete_post_meta($post_id, 'views');
        add_post_meta($post_id, 'views', 0);
        echo '<div class="notice notice-success is-dismissible"><p>Views were successfully reset.</p></div>';
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
    ];

    $posts = new WP_Query($args);

    echo '<div class="wrap">';
    echo '<h2>5 most popular posts</h2>';

    if ($posts->have_posts()) {
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>Title</th><th>Views</th><th>Actions</th></tr></thead><tbody>';

        while ($posts->have_posts()) {
            $posts->the_post();
            $post_id = get_the_ID();
            $views = get_post_meta($post_id, 'views', true);
            $views = is_numeric($views) ? $views : 0;

            echo '<tr>';
            echo '<td><a href="' . get_permalink($post_id) . '" target="_blank">' . get_the_title() . '</a></td>';
            echo '<td>' . $views . '</td>';
            echo '<td>
                    <a href="' . get_edit_post_link($post_id) . '" class="button">Edit Post</a>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="post_id" value="' . esc_attr($post_id) . '">
                        <input type="submit" name="wp_view_count_reset_views" class="button button-secondary" value="Reset Views">
                    </form>
                  </td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No posts to display.</p>';
    }

    echo '</div>';
    wp_reset_postdata();
}

?>
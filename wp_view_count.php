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

 add_action('init', 'count_views');

 function get_views(){
    global $post;
    $post_id = $post->ID;
    $views = get_post_meta($post_id, 'views', true);
    return $views;
}

function wp_view_count(){
    return "Wyświetlenia: " . get_views();
}
add_shortcode('wp_view_count', 'wp_view_count');
?>
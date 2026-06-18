<?php
/**
 * 性能优化集合
 * 移除WordPress冗余功能，精简资源，提升加载速度
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// 移除头部冗余信息
remove_action( 'wp_head', 'wp_generator' );               // 移除WP版本号
remove_action( 'wp_head', 'wlwmanifest_link' );           // 移除离线编辑器接口
remove_action( 'wp_head', 'rsd_link' );                   // 移除RPC接口
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );   // 移除短链接
remove_action( 'wp_head', 'feed_links_extra', 3 );        // 移除额外feed

// 禁用 emoji 加载，减少请求
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/**
 * 图片添加原生懒加载属性
 * WordPress 5.5+ 已内置，此处做兼容增强
 */
function li_cw_add_lazy_loading( $content ) {
    if ( is_admin() ) return $content;
    // 给所有img标签添加loading="lazy"
    $content = preg_replace('/<img(?!.*loading=)/i', '<img loading="lazy"', $content);
    return $content;
}
add_filter( 'the_content', 'li_cw_add_lazy_loading', 10 );
add_filter( 'post_thumbnail_html', 'li_cw_add_lazy_loading', 10 );

/**
 * 移除查询字符串，提升静态资源缓存效率
 */
function li_cw_remove_script_version( $src ) {
    if ( strpos( $src, 'ver=' ) ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}
add_filter( 'style_loader_src', 'li_cw_remove_script_version', 10, 2 );
add_filter( 'script_loader_src', 'li_cw_remove_script_version', 10, 2 );

/**
 * 首页查询优化，预缓存作品和文章
 */
function li_cw_optimize_home_query( $query ) {
    if ( is_front_page() && $query->is_main_query() ) {
        // 禁用分页，减少计算
        $query->set( 'no_found_rows', true );
        // 只查询必要字段
        $query->set( 'update_post_meta_cache', true );
        $query->set( 'update_post_term_cache', false );
    }
}
add_action( 'pre_get_posts', 'li_cw_optimize_home_query' );
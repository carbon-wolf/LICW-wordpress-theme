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
 * 图片添加原生懒加载属性（WP 5.5+ 内置，此处兜底漏网标签）
 * 逐标签处理：img 属性跨行或同行多标签时不会误判
 */
function li_cw_add_lazy_loading( $content ) {
    if ( is_admin() ) return $content;

    $content = preg_replace_callback(
        '/<img\b[^>]*>/i',
        function ( $m ) {
            // 已有 loading 属性则不动
            if ( false !== stripos( $m[0], 'loading=' ) ) {
                return $m[0];
            }
            // 仅在开标签内注入，避免前瞻正则跨标签误判
            return substr_replace( $m[0], ' loading="lazy"', -1 ) . '>';
        },
        $content
    );
    return $content;
}
add_filter( 'the_content', 'li_cw_add_lazy_loading', 10 );
add_filter( 'post_thumbnail_html', 'li_cw_add_lazy_loading', 10 );

/**
 * 移除第三方资源的 ver 查询字符串，提升静态资源缓存效率
 * 主题自身资源保留 LI_CW_VERSION 指纹——否则主题更新后客户端
 * 会长期命中旧缓存，缓存刷新机制失效。
 */
function li_cw_remove_script_version( $src ) {
    // 主题资源豁免（带版本指纹的缓存刷新依赖）
    if ( false !== strpos( $src, LI_CW_THEME_URI ) ) {
        return $src;
    }
    if ( strpos( $src, 'ver=' ) ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}
add_filter( 'style_loader_src', 'li_cw_remove_script_version', 10, 2 );
add_filter( 'script_loader_src', 'li_cw_remove_script_version', 10, 2 );

/**
 * 主查询归档/搜索页优化：关闭不用的缓存与计数
 * （首页由 front-page.php 自建 WP_Query，主查询即页面本体，保持默认行为）
 */
function li_cw_optimize_archive_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->is_search() || $query->is_archive() ) {
        // 归档与搜索页不渲染 meta，跳过 meta 缓存查询
        $query->set( 'update_post_meta_cache', false );
        // 分类/标签/日期/作者归档无标题，跳过 term 缓存查询
        if ( ! $query->is_category() && ! $query->is_tag() && ! $query->is_tax() ) {
            $query->set( 'update_post_term_cache', false );
        }
    }
}
add_action( 'pre_get_posts', 'li_cw_optimize_archive_query' );

<?php
/**
 * Li CW 主题函数入口
 * 加载所有功能模块，统一管理
 * @package Li_CW_Theme
 */

// 直接访问文件则退出
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 主题版本号，用于缓存刷新
define( 'LI_CW_VERSION', '1.1.10' );
define( 'LI_CW_THEME_DIR', get_template_directory() );
define( 'LI_CW_THEME_URI', get_template_directory_uri() );

/**
 * 加载功能模块文件
 * 按功能拆分，便于维护扩展
 */
require_once LI_CW_THEME_DIR . '/inc/theme-support.php';    // 主题基础功能支持
require_once LI_CW_THEME_DIR . '/inc/helper-functions.php'; // 全局工具函数
require_once LI_CW_THEME_DIR . '/inc/customizer.php';       // 后台自定义器
require_once LI_CW_THEME_DIR . '/inc/cpt-project.php';      // 作品自定义文章类型
require_once LI_CW_THEME_DIR . '/inc/performance.php';      // 性能优化
require_once LI_CW_THEME_DIR . '/inc/code-highlight.php';   // 代码高亮
require_once LI_CW_THEME_DIR . '/inc/cpt-shuoshuo.php';     // 说说 CPT
require_once LI_CW_THEME_DIR . '/inc/cpt-photo.php';       // 照片 CPT
require_once LI_CW_THEME_DIR . '/inc/lightbox.php';        // 图片灯箱
require_once LI_CW_THEME_DIR . '/inc/toc.php';             // 文章目录
require_once LI_CW_THEME_DIR . '/inc/og-meta.php';           // Open Graph / Twitter Card
require_once LI_CW_THEME_DIR . '/inc/link-feed.php';        // 友链文章抓取（RSS）
require_once LI_CW_THEME_DIR . '/inc/emoji-apple.php';      // Apple 表情白名单
require_once LI_CW_THEME_DIR . '/inc/comment-emoji.php';    // 评论/正文表情（微信 + Apple）
require_once LI_CW_THEME_DIR . '/inc/comment-render.php';   // 评论渲染 + 说说评论列表
require_once LI_CW_THEME_DIR . '/inc/comment-like.php';     // 评论点赞
require_once LI_CW_THEME_DIR . '/inc/comment-ip.php';       // 评论 IP 属地

/**
 * 加载主题样式与脚本
 */
function li_cw_enqueue_assets() {
    // 主样式表
    wp_enqueue_style( 'li-cw-style', get_stylesheet_uri(), array(), LI_CW_VERSION );

    // Google Fonts — 仅加载实际使用的字重
    wp_enqueue_style(
        'li-cw-fonts',
        'https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,400;0,500;0,700;1,400;1,500&family=LXGW+WenKai&family=Noto+Serif+SC:wght@400;500;700&family=JetBrains+Mono:wght@400&display=swap',
        array(),
        null
    );

    // 主交互脚本 - 页脚加载，不阻塞渲染
    wp_enqueue_script( 'li-cw-main', LI_CW_THEME_URI . '/assets/js/main.js', array(), LI_CW_VERSION, true );

    // 点赞接口：本地化 REST 根地址与 nonce（子目录部署兼容，服务端校验必需）
    wp_localize_script( 'li-cw-main', 'liCwLikeCfg', array(
        'restUrl' => esc_url_raw( rest_url( 'licw/v1/' ) ),
        'nonce'   => wp_create_nonce( 'li_cw_like' ),
    ) );

    // Masonry.js — 瀑布流布局（仅照片墙页面加载，本地化避免 CDN 阻塞）
    if ( is_page_template( 'page-gallery.php' ) ) {
        wp_enqueue_script(
            'masonry',
            LI_CW_THEME_URI . '/assets/js/vendor/masonry.pkgd.min.js',
            array(),
            '4.2.2',
            true
        );
    }

    // 友链文章懒加载（仅友链页面加载）
    if ( is_page_template( 'page-links.php' ) ) {
        wp_enqueue_script(
            'li-cw-link-feed',
            LI_CW_THEME_URI . '/assets/js/link-feed.js',
            array(),
            LI_CW_VERSION,
            true
        );
        wp_localize_script( 'li-cw-link-feed', 'liCwLinkFeed', array(
            'restUrl' => esc_url_raw( rest_url( 'licw/v1/link-feed' ) ),
        ) );
    }

    // 暗色模式脚本 - 头部提前加载，避免页面闪烁
    wp_enqueue_script( 'li-cw-darkmode', LI_CW_THEME_URI . '/assets/js/darkmode.js', array(), LI_CW_VERSION, false );

    // 将自定义器配色输出到内联CSS
    $custom_css = li_cw_get_custom_css();
    wp_add_inline_style( 'li-cw-style', $custom_css );

}
add_action( 'wp_enqueue_scripts', 'li_cw_enqueue_assets' );
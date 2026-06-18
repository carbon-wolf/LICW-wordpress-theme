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
define( 'LI_CW_VERSION', '1.0.5' );
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

/**
 * 加载主题样式与脚本
 */
function li_cw_enqueue_assets() {
    // 主样式表
    wp_enqueue_style( 'li-cw-style', get_stylesheet_uri(), array(), LI_CW_VERSION );

    // 主交互脚本 - 页脚加载，不阻塞渲染
    wp_enqueue_script( 'li-cw-main', LI_CW_THEME_URI . '/assets/js/main.js', array(), LI_CW_VERSION, true );

    // 暗色模式脚本 - 头部提前加载，避免页面闪烁
    wp_enqueue_script( 'li-cw-darkmode', LI_CW_THEME_URI . '/assets/js/darkmode.js', array(), LI_CW_VERSION, false );

    // 将自定义器配色输出到内联CSS
    $custom_css = li_cw_get_custom_css();
    wp_add_inline_style( 'li-cw-style', $custom_css );
    
}
add_action( 'wp_enqueue_scripts', 'li_cw_enqueue_assets' );
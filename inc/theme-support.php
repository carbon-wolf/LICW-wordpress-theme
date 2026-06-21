<?php
/**
 * 主题基础功能注册
 * 菜单、缩略图、标题标签、文章格式等
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function li_cw_theme_setup() {
    // 自动生成页面title标签
    add_theme_support( 'title-tag' );

    // 文章特色图像支持
    add_theme_support( 'post-thumbnails' );

    // 注册导航菜单
    register_nav_menus( array(
        'main-nav' => esc_html__( '主导航', 'li-cw' ),
        'footer-nav' => esc_html__( '页脚导航', 'li-cw' ),
    ));

    // HTML5 支持
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // 自定义背景
    add_theme_support( 'custom-background', array(
        'default-color' => 'f8f6f1',
    ));

    // 自动生成feed链接
    add_theme_support( 'automatic-feed-links' );

    // 文章缩略图尺寸定义
    add_image_size( 'blog-card', 300, 200, true );
    add_image_size( 'project-thumb', 560, 350, true );
    add_image_size( 'single-hero', 1200, 400, true );
    add_image_size( 'photo-thumb', 400, 400, true );
}
add_action( 'after_setup_theme', 'li_cw_theme_setup' );
// 开启WordPress原生链接管理器（友情链接专用）
add_filter( 'pre_option_link_manager_enabled', '__return_true' );
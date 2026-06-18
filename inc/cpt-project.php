<?php
/**
 * 注册「作品 Project」自定义文章类型
 * 独立管理作品集，与文章分离
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function li_cw_register_project_cpt() {

    $labels = array(
        'name'               => esc_html_x( '作品', 'post type general name', 'li-cw' ),
        'singular_name'      => esc_html_x( '作品', 'post type singular name', 'li-cw' ),
        'add_new'            => esc_html_x( '新建作品', 'li-cw' ),
        'add_new_item'       => esc_html__( '添加新作品', 'li-cw' ),
        'edit_item'          => esc_html__( '编辑作品', 'li-cw' ),
        'new_item'           => esc_html__( '新作品', 'li-cw' ),
        'view_item'          => esc_html__( '查看作品', 'li-cw' ),
        'search_items'       => esc_html__( '搜索作品', 'li-cw' ),
        'not_found'          => esc_html__( '未找到作品', 'li-cw' ),
        'menu_name'          => esc_html__( '作品', 'li-cw' ),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'show_in_rest'        => true, // 支持古腾堡编辑器
        'has_archive'         => true,
        'menu_icon'           => 'dashicons-portfolio',
        'menu_position'       => 5,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'rewrite'             => array( 'slug' => 'projects' ),
        'show_in_nav_menus'   => true,
    );

    register_post_type( 'project', $args );
}
add_action( 'init', 'li_cw_register_project_cpt' );

/**
 * 作品自定义字段提示
 * 后台编辑作品时，可在自定义字段中添加：
 * li_cw_project_status - 作品状态（如：开发中 / 设计中 / 已完成）
 * li_cw_project_cat    - 作品分类文案（如：视觉小说 / 设计 / 摄影）
 * li_cw_project_link   - 外部跳转链接（可选）
 */
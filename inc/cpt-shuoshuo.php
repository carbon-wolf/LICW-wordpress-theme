<?php
/**
 * 注册「说说」自定义文章类型
 * 短内容、状态更新，与日志和作品分离
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function li_cw_register_shuoshuo_cpt() {

    $labels = array(
        'name'               => esc_html_x( '说说', 'post type general name', 'li-cw' ),
        'singular_name'      => esc_html_x( '说说', 'post type singular name', 'li-cw' ),
        'add_new'            => esc_html_x( '写说说', 'li-cw' ),
        'add_new_item'       => esc_html__( '新建说说', 'li-cw' ),
        'edit_item'          => esc_html__( '编辑说说', 'li-cw' ),
        'new_item'           => esc_html__( '新说说', 'li-cw' ),
        'view_item'          => esc_html__( '查看说说', 'li-cw' ),
        'search_items'       => esc_html__( '搜索说说', 'li-cw' ),
        'not_found'          => esc_html__( '暂无说说', 'li-cw' ),
        'menu_name'          => esc_html__( '说说', 'li-cw' ),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'show_in_rest'        => true,
        'has_archive'         => true,
        'menu_icon'           => 'dashicons-format-status',
        'menu_position'       => 6,
        'supports'            => array( 'title', 'editor', 'custom-fields' ),
        'rewrite'             => array( 'slug' => 'shuoshuo' ),
        'show_in_nav_menus'   => true,
    );

    register_post_type( 'shuoshuo', $args );
}
add_action( 'init', 'li_cw_register_shuoshuo_cpt' );

/**
 * 说说点赞 REST API
 * POST /wp-json/licw/v1/shuoshuo/{id}/like
 * Body: { action: "like" | "unlike" }
 */
function li_cw_register_like_route() {
    register_rest_route( 'licw/v1', '/shuoshuo/(?P<id>\d+)/like', array(
        'methods'             => 'POST',
        'callback'            => 'li_cw_handle_shuoshuo_like',
        'permission_callback' => '__return_true',
    ));
}
add_action( 'rest_api_init', 'li_cw_register_like_route' );

function li_cw_handle_shuoshuo_like( $request ) {
    $post_id = (int) $request['id'];

    if ( ! get_post( $post_id ) || get_post_type( $post_id ) !== 'shuoshuo' ) {
        return new WP_Error( 'invalid_post', 'Not a shuoshuo', array( 'status' => 404 ) );
    }

    $likes = (int) get_post_meta( $post_id, 'li_cw_shuoshuo_likes', true );
    $action = $request->get_param( 'action' );

    if ( $action === 'unlike' && $likes > 0 ) {
        $likes--;
        update_post_meta( $post_id, 'li_cw_shuoshuo_likes', $likes );
    } elseif ( $action === 'like' ) {
        $likes++;
        update_post_meta( $post_id, 'li_cw_shuoshuo_likes', $likes );
    }

    return array( 'likes' => $likes );
}

/**
 * 说说自定义字段提示
 * 后台编辑说说时，可在自定义字段中添加：
 * li_cw_shuoshuo_mood - 心情标签（如：开心 / 思考中 / 忙碌）
 */

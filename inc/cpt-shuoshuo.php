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
        'supports'            => array( 'title', 'editor', 'comments', 'custom-fields' ),
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
 * 安全层（nonce / 限流 / 凭证 / 原子计数）复用 comment-like.php 公共函数。
 */
function li_cw_register_like_route() {
    register_rest_route( 'licw/v1', '/shuoshuo/(?P<id>\d+)/like', array(
        'methods'             => 'POST',
        'callback'            => 'li_cw_handle_shuoshuo_like',
        'permission_callback' => '__return_true', // 公开接口，安全由 nonce + 限流 + 状态校验保障
        'args'                => array(
            'action' => array(
                'type'              => 'string',
                'required'          => true,
                'enum'              => array( 'like', 'unlike' ),
                'sanitize_callback' => 'sanitize_key',
            ),
        ),
    ));
}
add_action( 'rest_api_init', 'li_cw_register_like_route' );

function li_cw_handle_shuoshuo_like( $request ) {
    $verify = li_cw_verify_like_request( $request );
    if ( is_wp_error( $verify ) ) {
        return $verify;
    }

    $post_id = (int) $request['id'];
    $post    = get_post( $post_id );

    if ( ! $post || 'shuoshuo' !== $post->post_type || 'publish' !== $post->post_status ) {
        return new WP_Error( 'invalid_post', __( '无效的说说。', 'li-cw' ), array( 'status' => 404 ) );
    }

    $action    = $request->get_param( 'action' );
    $proof_key = li_cw_like_proof_key( 'shuoshuo', $post_id );

    if ( 'like' === $action ) {
        if ( li_cw_like_has_proof( $proof_key ) ) {
            // 幂等：已点赞过，返回当前计数
            return array( 'likes' => (int) get_post_meta( $post_id, 'li_cw_shuoshuo_likes', true ), 'idempotent' => true );
        }
        li_cw_meta_atomic_increment( 'post', $post_id, 'li_cw_shuoshuo_likes' );
        li_cw_mark_like_proof( $proof_key );
    } else {
        if ( ! li_cw_like_has_proof( $proof_key ) ) {
            return new WP_Error( 'li_cw_not_liked', __( '尚未点赞过该说说。', 'li-cw' ), array( 'status' => 409 ) );
        }
        li_cw_meta_atomic_decrement( 'post', $post_id, 'li_cw_shuoshuo_likes' );
        li_cw_clear_like_proof( $proof_key );
    }

    return array( 'likes' => (int) get_post_meta( $post_id, 'li_cw_shuoshuo_likes', true ) );
}

/**
 * 说说自定义字段提示
 * 后台编辑说说时，可在自定义字段中添加：
 * li_cw_shuoshuo_mood - 心情标签（如：开心 / 思考中 / 忙碌）
 */

/**
 * 强制说说评论开放，确保每条说说都能评论
 *
 * @param bool $open    当前是否开放
 * @param int  $post_id 文章 ID
 * @return bool
 */
function li_cw_shuoshuo_comments_open( $open, $post_id ) {
    if ( 'shuoshuo' === get_post_type( $post_id ) ) {
        return true;
    }
    return $open;
}
add_filter( 'comments_open', 'li_cw_shuoshuo_comments_open', 10, 2 );

<?php
/**
 * 评论点赞
 * 所有评论都可点赞，后台「评论区设置」可选择性启用。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 是否启用评论点赞
 *
 * @return bool
 */
function li_cw_comment_likes_enabled() {
    return (bool) li_cw_get_option( 'li_cw_comments_like', true );
}

/**
 * 获取评论点赞数
 *
 * @param int $comment_id 评论 ID
 * @return int
 */
function li_cw_get_comment_likes( $comment_id ) {
    return (int) get_comment_meta( $comment_id, 'li_cw_comment_likes', true );
}

/**
 * 渲染评论点赞按钮
 *
 * @param int $comment_id 评论 ID
 * @return string
 */
function li_cw_comment_like_button( $comment_id ) {
    if ( ! li_cw_comment_likes_enabled() ) {
        return '';
    }

    $likes = li_cw_get_comment_likes( $comment_id );

    return '<button type="button" class="comment-like-btn"'
        . ' data-comment-id="' . esc_attr( $comment_id ) . '"'
        . ' data-likes="' . esc_attr( $likes ) . '"'
        . ' aria-label="' . esc_attr__( '点赞', 'li-cw' ) . '">'
        . '<svg class="like-icon" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'
        . '<span class="comment-like-count">' . esc_html( $likes ) . '</span>'
        . '</button>';
}

/**
 * 注册评论点赞 REST 路由
 * POST /wp-json/licw/v1/comment/{id}/like
 */
function li_cw_register_comment_like_route() {
    register_rest_route( 'licw/v1', '/comment/(?P<id>\d+)/like', array(
        'methods'             => 'POST',
        'callback'            => 'li_cw_handle_comment_like',
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'li_cw_register_comment_like_route' );

/**
 * 处理评论点赞
 *
 * @param WP_REST_Request $request 请求
 * @return array|WP_Error
 */
function li_cw_handle_comment_like( $request ) {
    if ( ! li_cw_comment_likes_enabled() ) {
        return new WP_Error( 'li_cw_likes_disabled', __( '评论点赞未启用。', 'li-cw' ), array( 'status' => 403 ) );
    }

    $comment_id = (int) $request['id'];
    if ( ! get_comment( $comment_id ) ) {
        return new WP_Error( 'li_cw_invalid_comment', __( '无效的评论。', 'li-cw' ), array( 'status' => 404 ) );
    }

    $likes  = li_cw_get_comment_likes( $comment_id );
    $action = $request->get_param( 'action' );

    if ( 'unlike' === $action && $likes > 0 ) {
        $likes--;
    } elseif ( 'like' === $action ) {
        $likes++;
    }

    update_comment_meta( $comment_id, 'li_cw_comment_likes', $likes );

    return array( 'likes' => $likes );
}

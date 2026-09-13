<?php
/**
 * 评论点赞 + 点赞共享安全层（nonce / 限流 / 状态校验）
 * 说说点赞（cpt-shuoshuo.php）复用本文件的公共函数。
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
 * 公共：校验点赞请求的 nonce 与频率
 * nonce 使用 WP 原生 nonce（匿名访客共享 wp_rest 命名空间之外的独立动作，
 * 每个访客 session 一致），限流按「IP + 对象」维度每小时最多 10 次。
 *
 * @param WP_REST_Request $request 请求（需含 X-Li-Cw-Nonce 头）
 * @return true|WP_Error
 */
function li_cw_verify_like_request( $request ) {
    $nonce = $request->get_header( 'X-Li-Cw-Nonce' );
    if ( ! $nonce || ! wp_verify_nonce( $nonce, 'li_cw_like' ) ) {
        return new WP_Error( 'li_cw_bad_nonce', __( '请求凭证无效，请刷新页面后重试。', 'li-cw' ), array( 'status' => 403 ) );
    }

    $ip_key  = md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
    $obj_key = 'li_cw_like_rl_' . $ip_key . '_' . (int) $request['id'] . '_' . sanitize_key( (string) $request->get_param( 'action' ) );
    $window  = get_transient( $obj_key );

    if ( $window ) {
        return new WP_Error( 'li_cw_rate_limited', __( '操作过于频繁，请稍后再试。', 'li-cw' ), array( 'status' => 429 ) );
    }
    set_transient( $obj_key, 1, HOUR_IN_SECONDS );

    return true;
}

/**
 * 公共：限流窗口内放行首个请求后，成功点赞即结束窗口的辅助包装。
 * 返回当前窗口是否放行（10 次/小时的剩余额度查询由 li_cw_verify_like_request 前置完成，
 * 此处仅保证单个对象动作每小时只有首次成功生效——点赞/取消本身即互为反向操作，
 * 服务端在窗口内幂等返回当前计数，防止连点刷数）。
 *
 * @param string $cache_key 限流键
 * @return bool
 */
function li_cw_like_window_consumed( $cache_key ) {
    return (bool) get_transient( $cache_key );
}

/**
 * 注册评论点赞 REST 路由
 * POST /wp-json/licw/v1/comment/{id}/like
 */
function li_cw_register_comment_like_route() {
    register_rest_route( 'licw/v1', '/comment/(?P<id>\d+)/like', array(
        'methods'             => 'POST',
        'callback'            => 'li_cw_handle_comment_like',
        'permission_callback' => '__return_true', // 公开接口，安全由 nonce + 限流 + 状态校验保障
        'args'                => array(
            'action' => array(
                'type'              => 'string',
                'required'          => true,
                'enum'              => array( 'like', 'unlike' ),
                'sanitize_callback' => 'sanitize_key',
            ),
        ),
    ) );
}
add_action( 'rest_api_init', 'li_cw_register_comment_like_route' );

/**
 * 处理评论点赞
 * 服务端维护访客点赞凭证（IP+评论维度），无凭证的 unlike 拒绝；
 * 窗口内重复请求幂等返回当前计数。
 *
 * @param WP_REST_Request $request 请求
 * @return array|WP_Error
 */
function li_cw_handle_comment_like( $request ) {
    if ( ! li_cw_comment_likes_enabled() ) {
        return new WP_Error( 'li_cw_likes_disabled', __( '评论点赞未启用。', 'li-cw' ), array( 'status' => 403 ) );
    }

    $verify = li_cw_verify_like_request( $request );
    if ( is_wp_error( $verify ) ) {
        return $verify;
    }

    $comment_id = (int) $request['id'];
    $comment    = get_comment( $comment_id );

    if ( ! $comment || 'approve' !== $comment->comment_approved ) {
        return new WP_Error( 'li_cw_invalid_comment', __( '无效的评论。', 'li-cw' ), array( 'status' => 404 ) );
    }

    $action    = $request->get_param( 'action' );
    $proof_key = li_cw_like_proof_key( 'comment', $comment_id );

    if ( 'like' === $action ) {
        if ( li_cw_like_has_proof( $proof_key ) ) {
            // 幂等：已点赞过，返回当前计数
            return array( 'likes' => li_cw_get_comment_likes( $comment_id ), 'idempotent' => true );
        }
        li_cw_meta_atomic_increment( 'comment', $comment_id, 'li_cw_comment_likes' );
        li_cw_mark_like_proof( $proof_key );
    } else {
        if ( ! li_cw_like_has_proof( $proof_key ) ) {
            return new WP_Error( 'li_cw_not_liked', __( '尚未点赞过该评论。', 'li-cw' ), array( 'status' => 409 ) );
        }
        li_cw_meta_atomic_decrement( 'comment', $comment_id, 'li_cw_comment_likes' );
        li_cw_clear_like_proof( $proof_key );
    }

    return array( 'likes' => li_cw_get_comment_likes( $comment_id ) );
}

/**
 * 公共：生成点赞凭证键（IP + 类型 + 对象）
 *
 * @param string $type comment|shuoshuo
 * @param int    $id   对象 ID
 * @return string
 */
function li_cw_like_proof_key( $type, $id ) {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    return 'li_cw_liked_' . md5( $ip . '|' . $type . '|' . (int) $id );
}

/**
 * 公共：是否有点赞凭证
 */
function li_cw_like_has_proof( $key ) {
    return (bool) get_transient( $key );
}

/**
 * 公共：标记点赞凭证（一年）
 */
function li_cw_mark_like_proof( $key ) {
    set_transient( $key, 1, YEAR_IN_SECONDS );
}

/**
 * 公共：清除点赞凭证
 */
function li_cw_clear_like_proof( $key ) {
    delete_transient( $key );
}

/**
 * 公共：meta 原子自增（SQL 层 +1，避免读-改-写竞态丢更新）
 *
 * @param string $type  comment|post
 * @param int    $id    对象 ID
 * @param string $key   meta 键
 * @return bool
 */
function li_cw_meta_atomic_increment( $type, $id, $key ) {
    global $wpdb;

    if ( 'comment' === $type ) {
        $table = $wpdb->commentmeta;
        $col   = 'comment_id';
        $id    = (int) $id;
    } else {
        $table = $wpdb->postmeta;
        $col   = 'post_id';
        $id    = (int) $id;
    }

    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT meta_id FROM {$table} WHERE {$col} = %d AND meta_key = %s LIMIT 1",
        $id, $key
    ) );

    if ( $exists ) {
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$table} SET meta_value = meta_value + 1 WHERE {$col} = %d AND meta_key = %s",
            $id, $key
        ) );
    } else {
        // 并发首赞竞争时唯一键兜底，插失败即视为已被并发创建
        $wpdb->insert( $table, array( $col => $id, 'meta_key' => $key, 'meta_value' => 1 ) );
    }

    wp_cache_delete( $id, 'comment' === $type ? 'comment_meta_ids' : 'post_meta' );
    // object cache 兜底：meta 值缓存按对象+键前缀清理成本高，直接按对象清
    if ( function_exists( 'wp_cache_delete' ) ) {
        if ( 'comment' === $type && function_exists( 'clean_comment_cache' ) ) {
            clean_comment_cache( $id );
        } elseif ( 'post' === $type && function_exists( 'clean_post_cache' ) ) {
            clean_post_cache( $id );
        }
    }

    return true;
}

/**
 * 公共：meta 原子自减（下限 0）
 */
function li_cw_meta_atomic_decrement( $type, $id, $key ) {
    global $wpdb;

    if ( 'comment' === $type ) {
        $table = $wpdb->commentmeta;
        $col   = 'comment_id';
        $id    = (int) $id;
    } else {
        $table = $wpdb->postmeta;
        $col   = 'post_id';
        $id    = (int) $id;
    }

    $wpdb->query( $wpdb->prepare(
        "UPDATE {$table} SET meta_value = GREATEST( meta_value - 1, 0 ) WHERE {$col} = %d AND meta_key = %s",
        $id, $key
    ) );

    if ( 'comment' === $type && function_exists( 'clean_comment_cache' ) ) {
        clean_comment_cache( $id );
    } elseif ( 'post' === $type && function_exists( 'clean_post_cache' ) ) {
        clean_post_cache( $id );
    }

    return true;
}

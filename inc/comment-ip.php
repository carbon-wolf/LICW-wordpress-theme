<?php
/**
 * 评论 IP 属地
 * 后台可选择性启用；发评论时查询一次并缓存，历史评论由定时任务补全。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 是否启用 IP 属地
 *
 * @return bool
 */
function li_cw_comment_ip_enabled() {
    return (bool) li_cw_get_option( 'li_cw_comments_ip', false );
}

/**
 * 查询单个 IP 的属地（带 transient 缓存）
 *
 * @param string $ip IP 地址
 * @return string
 */
function li_cw_fetch_ip_location( $ip ) {
    if ( ! $ip ) {
        return '';
    }

    // 本地 / 内网地址
    if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
        return __( '本地', 'li-cw' );
    }

    $cache_key = 'li_cw_ip_loc_' . md5( $ip );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    $api = apply_filters( 'li_cw_ip_location_api', 'https://api.vore.top/api/IPdata?ip=' . rawurlencode( $ip ) );
    $loc = '';

    $response = wp_remote_get( $api, array(
        'timeout'    => 4,
        'user-agent' => 'Li-CW-Theme/' . LI_CW_VERSION . '; ' . home_url(),
    ) );

    if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( is_array( $data ) && ! empty( $data['ipdata'] ) ) {
            $adcode  = isset( $data['adcode'] ) && is_array( $data['adcode'] ) ? $data['adcode'] : array();
            $info    = $data['ipdata'];

            // 国内显示省份，国外显示国家
            $province = ! empty( $adcode['p'] ) ? $adcode['p'] : '';
            $info1    = isset( $info['info1'] ) ? $info['info1'] : '';

            $loc = $province ? $province : $info1;
        }
    }

    // 失败也缓存较短时间，避免频繁重试
    set_transient( $cache_key, $loc, $loc ? WEEK_IN_SECONDS : HOUR_IN_SECONDS );

    return $loc;
}

/**
 * 为某条评论查询并保存 IP 属地
 *
 * @param int $comment_id 评论 ID
 */
function li_cw_store_comment_ip_location( $comment_id ) {
    $comment = get_comment( $comment_id );
    if ( ! $comment || empty( $comment->comment_author_IP ) ) {
        return;
    }
    if ( get_comment_meta( $comment_id, 'li_cw_ip_location', true ) ) {
        return;
    }

    $loc = li_cw_fetch_ip_location( $comment->comment_author_IP );
    if ( $loc ) {
        update_comment_meta( $comment_id, 'li_cw_ip_location', $loc );
    }
}

/**
 * 新评论发布后立即查询
 *
 * @param int $comment_id 评论 ID
 */
function li_cw_on_comment_post_ip( $comment_id ) {
    if ( ! li_cw_comment_ip_enabled() ) {
        return;
    }
    li_cw_store_comment_ip_location( $comment_id );
}
add_action( 'comment_post', 'li_cw_on_comment_post_ip', 20 );

/**
 * 定时补全历史评论的 IP 属地（每次少量，避免打满接口）
 *
 * @param int $limit 单次处理条数
 */
function li_cw_backfill_comment_ip( $limit = 10 ) {
    if ( ! li_cw_comment_ip_enabled() ) {
        return;
    }

    $comments = get_comments( array(
        'status'     => 'approve',
        'number'     => max( 1, (int) $limit ),
        'orderby'    => 'comment_date_gmt',
        'order'      => 'DESC',
        'meta_query' => array(
            array(
                'key'     => 'li_cw_ip_location',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ) );

    foreach ( $comments as $comment ) {
        li_cw_store_comment_ip_location( $comment->comment_ID );
    }
}

/**
 * 注册补全任务
 */
function li_cw_schedule_comment_ip_backfill() {
    if ( ! wp_next_scheduled( 'li_cw_backfill_comment_ip' ) ) {
        wp_schedule_event( time() + 120, 'hourly', 'li_cw_backfill_comment_ip' );
    }
}
add_action( 'init', 'li_cw_schedule_comment_ip_backfill' );
add_action( 'li_cw_backfill_comment_ip', 'li_cw_backfill_comment_ip' );

/**
 * 后台访问时顺带补全（WP-Cron 不可靠时的兜底，5 分钟内最多一次）
 */
function li_cw_maybe_backfill_comment_ip_on_admin() {
    if ( ! li_cw_comment_ip_enabled() || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( get_transient( 'li_cw_ip_backfill_lock' ) ) {
        return;
    }
    set_transient( 'li_cw_ip_backfill_lock', 1, 5 * MINUTE_IN_SECONDS );
    li_cw_backfill_comment_ip( 5 );
}
add_action( 'admin_init', 'li_cw_maybe_backfill_comment_ip_on_admin' );

/**
 * 自定义器保存后，若刚开启 IP 属地，立即补一批历史评论
 */
function li_cw_backfill_after_customize() {
    if ( li_cw_comment_ip_enabled() ) {
        li_cw_backfill_comment_ip( 5 );
    }
}
add_action( 'customize_save_after', 'li_cw_backfill_after_customize' );

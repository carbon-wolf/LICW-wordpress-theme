<?php
/**
 * 评论 IP 属地
 * 后台可选择性启用（默认关闭）；发评论时异步查询一次并缓存，
 * 历史评论由定时任务补全。失败写哨兵标记，避免队头饥饿。
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
 * 默认第三方 API 仅在显式开启后调用；可用 li_cw_ip_location_api 过滤器
 * 替换为自托管或其他服务（IP 仅发往所配置的接口）。
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
 * 解析失败写哨兵值 'unknown'，防止失败评论永久占据回填队头（饥饿）。
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
    // 成功：属地；失败：哨兵 'unknown'（渲染层不显示哨兵）
    update_comment_meta( $comment_id, 'li_cw_ip_location', $loc ? $loc : 'unknown' );
}

/**
 * 渲染层辅助：读取属地（哨兵值视为无数据）
 *
 * @param int $comment_id 评论 ID
 * @return string 空串表示无/未知
 */
function li_cw_get_comment_ip_location( $comment_id ) {
    $loc = get_comment_meta( $comment_id, 'li_cw_ip_location', true );
    return ( 'unknown' === $loc ) ? '' : (string) $loc;
}

/**
 * 新评论发布后异步查询（不阻塞发评论请求）
 *
 * @param int $comment_id 评论 ID
 */
function li_cw_on_comment_post_ip( $comment_id ) {
    if ( ! li_cw_comment_ip_enabled() ) {
        return;
    }
    wp_schedule_single_event( time() + 10, 'li_cw_lookup_comment_ip', array( (int) $comment_id ) );
}
add_action( 'comment_post', 'li_cw_on_comment_post_ip', 20 );
add_action( 'li_cw_lookup_comment_ip', 'li_cw_store_comment_ip_location' );

/**
 * 定时补全历史评论的 IP 属地（每次少量，避免打满接口）
 * 哨兵值让 NOT EXISTS 查询自然跳过失败条目，先进先出。
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
        'order'      => 'ASC', // 先进先出，新评论由 comment_post 钩子实时处理
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
    if ( ! li_cw_comment_ip_enabled() ) {
        return;
    }
    if ( ! wp_next_scheduled( 'li_cw_backfill_comment_ip' ) ) {
        wp_schedule_event( time() + 120, 'hourly', 'li_cw_backfill_comment_ip' );
    }
}
add_action( 'init', 'li_cw_schedule_comment_ip_backfill' );
add_action( 'li_cw_backfill_comment_ip', 'li_cw_backfill_comment_ip' );

/**
 * 开关变化时同步定时任务（含关闭时清理）
 */
function li_cw_sync_comment_ip_schedule() {
    if ( li_cw_comment_ip_enabled() ) {
        li_cw_schedule_comment_ip_backfill();
    } else {
        wp_clear_scheduled_hook( 'li_cw_backfill_comment_ip' );
    }
}
add_action( 'customize_save_after', 'li_cw_sync_comment_ip_schedule', 20 );

/**
 * 停用主题时清除定时任务（与 link-feed 同一套清理机制）
 */
function li_cw_clear_comment_ip_cron() {
    wp_clear_scheduled_hook( 'li_cw_backfill_comment_ip' );
    wp_clear_scheduled_hook( 'li_cw_lookup_comment_ip' );
}
add_action( 'switch_theme', 'li_cw_clear_comment_ip_cron' );

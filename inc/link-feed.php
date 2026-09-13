<?php
/**
 * 友链文章抓取（RSS）
 * 为开启了抓取的友链分类抓取站点最近文章，带 transient 缓存与后台定时预热。
 * 抓取结果供友链页「对话框」气泡展示，帮助友链互相引流。
 *
 * 渲染策略：页面只读缓存，不在前台阻塞网络请求；
 * 缓存缺失时安排一次后台刷新，由 WP-Cron 完成抓取。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 判断某个友链分类是否开启文章抓取
 *
 * @param int $term_id link_category 分类 ID
 * @return bool
 */
function li_cw_link_category_has_feed( $term_id ) {
    return (bool) li_cw_get_option( 'li_cw_links_feed_cat_' . absint( $term_id ), false );
}

/**
 * 判断某条友链所属的任一分类是否开启文章抓取
 *
 * @param object $bookmark 友链对象
 * @return bool
 */
function li_cw_link_has_feed( $bookmark ) {
    if ( empty( $bookmark->link_id ) ) {
        return false;
    }

    static $cache = array();
    if ( isset( $cache[ $bookmark->link_id ] ) ) {
        return $cache[ $bookmark->link_id ];
    }

    $cats = ! empty( $bookmark->link_category )
        ? (array) $bookmark->link_category
        : wp_get_object_terms( $bookmark->link_id, 'link_category', array( 'fields' => 'ids' ) );

    $has = false;
    if ( ! is_wp_error( $cats ) ) {
        foreach ( $cats as $term_id ) {
            if ( li_cw_link_category_has_feed( $term_id ) ) {
                $has = true;
                break;
            }
        }
    }

    $cache[ $bookmark->link_id ] = $has;
    return $has;
}

/**
 * 将相对 URL 补全为绝对 URL
 *
 * @param string $url  待补全的 URL
 * @param string $base 基准地址
 * @return string
 */
function li_cw_resolve_url( $url, $base ) {
    $url = trim( $url );
    if ( '' === $url ) {
        return '';
    }
    if ( preg_match( '#^https?://#i', $url ) ) {
        return $url;
    }
    // 协议相对 //host/path
    if ( 0 === strpos( $url, '//' ) ) {
        $scheme = wp_parse_url( $base, PHP_URL_SCHEME );
        return ( $scheme ? $scheme : 'https' ) . ':' . $url;
    }
    $parts  = wp_parse_url( $base );
    $scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : 'https';
    $host   = isset( $parts['host'] ) ? $parts['host'] : '';
    // 根相对 /path
    if ( 0 === strpos( $url, '/' ) ) {
        return $scheme . '://' . $host . $url;
    }
    // 文档相对 path
    return trailingslashit( $scheme . '://' . $host ) . $url;
}

/**
 * 自动发现站点可能存在的 RSS 地址（返回候选列表，按优先级排序）
 * 优先抓取首页 <link rel="alternate">，再回退到常见路径。
 *
 * @param string $site_url 站点首页地址
 * @return array
 */
function li_cw_discover_feed_urls( $site_url ) {
    $site_url = esc_url_raw( $site_url );
    if ( ! $site_url ) {
        return array();
    }

    // 本身已是 feed 地址
    if ( preg_match( '#(/(feed|rss|atom|rss2|index\.xml)/?$|feed=rss2|\.[a-z]+\.xml$)#i', $site_url ) ) {
        return array( $site_url );
    }

    $cache_key = 'li_cw_feed_urls_' . md5( $site_url );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached && is_array( $cached ) ) {
        return $cached;
    }

    $candidates = array();

    // 上次抓取成功的地址优先
    $good = get_transient( 'li_cw_feed_good_' . md5( $site_url ) );
    if ( $good ) {
        $candidates[] = $good;
    }

    // 首页声明的 feed
    $response = wp_remote_get( $site_url, array(
        'timeout'     => 8,
        'redirection' => 3,
        'user-agent'  => 'Li-CW-Theme/' . LI_CW_VERSION . '; ' . home_url(),
    ) );

    if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
        $body = wp_remote_retrieve_body( $response );

        if ( preg_match_all( '#<link[^>]+type=["\']application/(?:rss|atom)\+xml["\'][^>]*>#i', $body, $tags ) ) {
            foreach ( $tags[0] as $tag ) {
                if ( preg_match( '#href=["\']([^"\']+)["\']#i', $tag, $href ) ) {
                    $candidates[] = li_cw_resolve_url( $href[1], $site_url );
                }
            }
        }
    }

    // 常见 feed 路径兜底
    foreach ( array( 'feed/', 'feed', 'rss', 'rss.xml', 'atom.xml', 'index.xml' ) as $path ) {
        $candidates[] = trailingslashit( $site_url ) . $path;
    }

    $candidates = array_values( array_unique( array_filter( $candidates ) ) );
    $candidates = array_slice( $candidates, 0, 6 );

    set_transient( $cache_key, $candidates, 12 * HOUR_IN_SECONDS );
    return $candidates;
}

/**
 * 获取单条友链的 feed 候选地址
 *
 * @param object $bookmark 友链对象（get_bookmarks 返回）
 * @return array
 */
function li_cw_get_bookmark_feed_urls( $bookmark ) {
    $urls = array();

    // 后台手填的 RSS 优先，但失败时仍回退到自动发现
    if ( ! empty( $bookmark->link_rss ) ) {
        $urls[] = esc_url_raw( $bookmark->link_rss );
    }
    if ( ! empty( $bookmark->link_url ) ) {
        foreach ( li_cw_discover_feed_urls( $bookmark->link_url ) as $url ) {
            $urls[] = $url;
        }
    }

    return array_values( array_unique( array_filter( $urls ) ) );
}

/**
 * 友链文章缓存键（由友链 ID、地址与条数决定，无需联网即可计算）
 *
 * @param object $bookmark 友链对象
 * @param int    $count    抓取条数
 * @return string
 */
function li_cw_link_feed_cache_key( $bookmark, $count ) {
    $signature = $bookmark->link_id . '|' . $bookmark->link_url . '|' . $bookmark->link_rss . '|' . absint( $count );
    return 'li_cw_linkfeed_' . md5( $signature );
}

/**
 * 抓取并解析 feed 文章
 * 自行用 SimpleXML 解析，不调用 SimplePie（部分服务器禁用了 assert()，
 * 触发 wp-includes 内 SimplePie 的致命错误，且不允许改动核心文件）。
 *
 * @param string $rss_url feed 地址
 * @param int    $count   抓取条数
 * @return array
 */
function li_cw_fetch_feed_items( $rss_url, $count ) {
    $response = wp_remote_get( $rss_url, array(
        'timeout'     => 10,
        'redirection' => 5,
        'user-agent'  => 'Li-CW-Theme/' . LI_CW_VERSION . '; ' . home_url(),
    ) );

    if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
        return array();
    }

    $body = wp_remote_retrieve_body( $response );
    if ( ! $body ) {
        return array();
    }

    return li_cw_parse_feed( $body, $count );
}

/**
 * 用 SimpleXML 解析 RSS / Atom
 *
 * @param string $body  feed 原文
 * @param int    $count 抓取条数
 * @return array
 */
function li_cw_parse_feed( $body, $count ) {
    if ( ! function_exists( 'simplexml_load_string' ) ) {
        return array();
    }

    $use_errors = libxml_use_internal_errors( true );
    $xml        = simplexml_load_string( $body, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_NONET );
    libxml_clear_errors();
    libxml_use_internal_errors( $use_errors );

    if ( ! $xml ) {
        return array();
    }

    $out = array();

    // Atom
    if ( isset( $xml->entry ) ) {
        foreach ( $xml->entry as $entry ) {
            if ( count( $out ) >= $count ) {
                break;
            }
            $item = li_cw_parse_atom_entry( $entry );
            if ( $item ) {
                $out[] = $item;
            }
        }
        return $out;
    }

    // RSS 2.0 / RDF
    $items = isset( $xml->channel->item ) ? $xml->channel->item : ( isset( $xml->item ) ? $xml->item : array() );
    foreach ( $items as $entry ) {
        if ( count( $out ) >= $count ) {
            break;
        }
        $item = li_cw_parse_rss_item( $entry );
        if ( $item ) {
            $out[] = $item;
        }
    }
    return $out;
}

/**
 * 解析单条 RSS item
 *
 * @param SimpleXMLElement $entry item 节点
 * @return array|null
 */
function li_cw_parse_rss_item( $entry ) {
    $title = isset( $entry->title ) ? trim( (string) $entry->title ) : '';
    $link  = isset( $entry->link ) ? trim( (string) $entry->link ) : '';
    if ( '' === $link && isset( $entry->guid ) ) {
        $link = trim( (string) $entry->guid );
    }
    $date = li_cw_feed_entry_date( $entry, array( 'pubDate', 'date', 'published', 'updated' ) );

    if ( '' === $title || '' === $link ) {
        return null;
    }
    return array(
        'title' => wp_strip_all_tags( $title ),
        'link'  => $link,
        'date'  => $date,
    );
}

/**
 * 解析单条 Atom entry
 *
 * @param SimpleXMLElement $entry entry 节点
 * @return array|null
 */
function li_cw_parse_atom_entry( $entry ) {
    $title = isset( $entry->title ) ? trim( (string) $entry->title ) : '';

    $link = '';
    if ( isset( $entry->link ) ) {
        foreach ( $entry->link as $l ) {
            $rel  = isset( $l['rel'] ) ? (string) $l['rel'] : 'alternate';
            $href = isset( $l['href'] ) ? trim( (string) $l['href'] ) : '';
            if ( '' !== $href && ( 'alternate' === $rel || '' === $rel ) ) {
                $link = $href;
                break;
            }
        }
    }
    if ( '' === $link && isset( $entry->id ) ) {
        $id = trim( (string) $entry->id );
        if ( preg_match( '#^https?://#i', $id ) ) {
            $link = $id;
        }
    }
    $date = li_cw_feed_entry_date( $entry, array( 'updated', 'published', 'pubDate' ) );

    if ( '' === $title || '' === $link ) {
        return null;
    }
    return array(
        'title' => wp_strip_all_tags( $title ),
        'link'  => $link,
        'date'  => $date,
    );
}

/**
 * 从 entry 中提取时间戳
 *
 * @param SimpleXMLElement $entry  entry 节点
 * @param array            $fields 候选字段
 * @return int 0 表示无法解析
 */
function li_cw_feed_entry_date( $entry, $fields ) {
    foreach ( $fields as $field ) {
        if ( isset( $entry->$field ) ) {
            $value = trim( (string) $entry->$field );
            if ( '' !== $value ) {
                $ts = strtotime( $value );
                if ( $ts ) {
                    return $ts;
                }
            }
        }
    }
    return 0;
}

/**
 * 抓取友链文章并写入缓存（供后台任务调用）
 *
 * @param object $bookmark 友链对象
 * @param int    $count    抓取条数
 * @return array
 */
function li_cw_get_link_feed_items( $bookmark, $count = 3 ) {
    $count = max( 1, min( 10, absint( $count ) ) );
    $key   = li_cw_link_feed_cache_key( $bookmark, $count );

    $cached = get_transient( $key );
    if ( false !== $cached ) {
        return $cached;
    }

    $items = array();
    foreach ( li_cw_get_bookmark_feed_urls( $bookmark ) as $url ) {
        $items = li_cw_fetch_feed_items( $url, $count );
        if ( $items ) {
            // 记住有效地址，下次优先尝试
            if ( ! empty( $bookmark->link_url ) ) {
                set_transient( 'li_cw_feed_good_' . md5( $bookmark->link_url ), $url, WEEK_IN_SECONDS );
            }
            break;
        }
    }

    $minutes = max( 5, absint( li_cw_get_option( 'li_cw_links_feed_cache', 60 ) ) );
    set_transient( $key, $items, $minutes * MINUTE_IN_SECONDS );

    return $items;
}

/**
 * 仅从缓存读取友链文章（供前台渲染，不阻塞网络）
 *
 * @param object $bookmark 友链对象
 * @param int    $count    抓取条数
 * @return array|null 数组为已缓存（可能为空），null 为尚未缓存
 */
function li_cw_get_link_feed_items_cached( $bookmark, $count = 3 ) {
    $count = max( 1, min( 10, absint( $count ) ) );
    $key   = li_cw_link_feed_cache_key( $bookmark, $count );

    $cached = get_transient( $key );
    if ( false !== $cached ) {
        return $cached;
    }

    return null;
}

/**
 * 将抓取结果整理为前端可用的数组
 *
 * @param array $items 抓取到的文章
 * @return array
 */
function li_cw_format_feed_items( $items ) {
    $show_date = li_cw_get_option( 'li_cw_links_feed_date', true );

    $out = array();
    foreach ( $items as $item ) {
        // 仅放行 http(s) 链接——恶意 feed 可返回 javascript: 等协议
        $safe_link = esc_url_raw( $item['link'] );
        if ( ! preg_match( '#^https?://#i', $safe_link ) ) {
            continue;
        }
        $row = array(
            'title' => wp_strip_all_tags( $item['title'] ),
            'link'  => $safe_link,
        );
        if ( $show_date && ! empty( $item['date'] ) ) {
            $row['date_human'] = sprintf( __( '%s前', 'li-cw' ), human_time_diff( $item['date'] ) );
            $row['date_iso']   = gmdate( 'Y-m-d', $item['date'] );
        }
        $out[] = $row;
    }
    return $out;
}

/**
 * REST：按需抓取单条友链的文章
 * GET /wp-json/licw/v1/link-feed?link_id={id}
 * 前台懒加载使用，避免阻塞页面，也不依赖 WP-Cron。
 * count 不接受请求参数——固定为自定义器配置值，
 * 防止匿名请求枚举 count 制造缓存击穿、放大外站抓取。
 */
function li_cw_register_link_feed_route() {
    register_rest_route( 'licw/v1', '/link-feed', array(
        'methods'             => 'GET',
        'callback'            => 'li_cw_rest_link_feed',
        'permission_callback' => '__return_true', // 只读公开数据
        'args'                => array(
            'link_id' => array(
                'required'          => true,
                'sanitize_callback' => 'absint',
            ),
        ),
    ) );
}
add_action( 'rest_api_init', 'li_cw_register_link_feed_route' );

/**
 * REST 回调
 *
 * @param WP_REST_Request $request 请求
 * @return WP_REST_Response|WP_Error
 */
function li_cw_rest_link_feed( $request ) {
    $link_id = absint( $request->get_param( 'link_id' ) );
    $count   = max( 1, min( 10, absint( li_cw_get_option( 'li_cw_links_feed_count', 3 ) ) ) );

    $bookmark = $link_id ? get_bookmark( $link_id ) : null;
    if ( ! $bookmark || ! li_cw_link_has_feed( $bookmark ) ) {
        return new WP_Error( 'li_cw_invalid_link', __( '无效的友链。', 'li-cw' ), array( 'status' => 404 ) );
    }

    $items = li_cw_get_link_feed_items( $bookmark, $count );
    return rest_ensure_response( array( 'items' => li_cw_format_feed_items( $items ) ) );
}

/**
 * 收集所有开启了抓取的分类下的友链（按 link_id 去重，防多分类重复抓取）
 *
 * @return array
 */
function li_cw_get_feed_enabled_bookmarks() {
    $terms = get_terms( array(
        'taxonomy'   => 'link_category',
        'hide_empty' => false,
    ) );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return array();
    }

    $bookmarks = array();
    foreach ( $terms as $term ) {
        if ( ! li_cw_link_category_has_feed( $term->term_id ) ) {
            continue;
        }
        $found = get_bookmarks( array( 'category' => $term->term_id ) );
        if ( $found ) {
            foreach ( $found as $bookmark ) {
                $bookmarks[ $bookmark->link_id ] = $bookmark;
            }
        }
    }
    return array_values( $bookmarks );
}

/**
 * 抓取任务：逐个刷新开启了抓取的友链文章缓存
 * 带时间预算（默认 50s），超时自动终止，避免超出 PHP max_execution_time
 */
function li_cw_refresh_link_feeds() {
    $count   = max( 1, min( 10, absint( li_cw_get_option( 'li_cw_links_feed_count', 3 ) ) ) );
    $start   = microtime( true );
    $budget  = 50; // 秒；留出余量给常见的 60s 限制

    foreach ( li_cw_get_feed_enabled_bookmarks() as $bookmark ) {
        if ( microtime( true ) - $start > $budget ) {
            break;
        }
        $key = li_cw_link_feed_cache_key( $bookmark, $count );
        delete_transient( $key );
        li_cw_get_link_feed_items( $bookmark, $count );
    }
}
add_action( 'li_cw_refresh_link_feeds', 'li_cw_refresh_link_feeds' );
add_action( 'li_cw_warm_link_feeds', 'li_cw_refresh_link_feeds' );

/**
 * 注册定时刷新事件
 */
function li_cw_schedule_link_feeds() {
    if ( ! wp_next_scheduled( 'li_cw_refresh_link_feeds' ) ) {
        wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, 'hourly', 'li_cw_refresh_link_feeds' );
    }
}
add_action( 'init', 'li_cw_schedule_link_feeds' );

/**
 * 自定义器保存后，立刻安排一次后台抓取，让首次访问即有内容
 */
function li_cw_warm_feeds_after_customize() {
    wp_schedule_single_event( time() + 5, 'li_cw_warm_link_feeds' );
}
add_action( 'customize_save_after', 'li_cw_warm_feeds_after_customize' );

/**
 * 切换主题时清理并重建定时任务
 */
function li_cw_reschedule_link_feeds() {
    wp_clear_scheduled_hook( 'li_cw_refresh_link_feeds' );
    li_cw_schedule_link_feeds();
}
add_action( 'after_switch_theme', 'li_cw_reschedule_link_feeds' );

/**
 * 停用主题时清除定时任务
 */
function li_cw_clear_link_feeds_cron() {
    wp_clear_scheduled_hook( 'li_cw_refresh_link_feeds' );
    wp_clear_scheduled_hook( 'li_cw_warm_link_feeds' );
}
add_action( 'switch_theme', 'li_cw_clear_link_feeds_cron' );

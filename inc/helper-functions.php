<?php
/**
 * 全局工具函数
 * 复用的公共方法集中管理
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 获取自定义器配置值，带默认值兜底
 * @param string $key 配置键名
 * @param mixed $default 默认值
 * @return mixed
 */
function li_cw_get_option( $key, $default = '' ) {
    $value = get_theme_mod( $key, $default );
    return $value;
}

/**
 * 修正资源 URL 的协议与域名，避免 https 页面出现混合内容
 * - http:// 升级为 https://
 * - 指向本站旧域名/IP 的 wp-content 资源，强制替换为当前站点域名
 * @param string $url 原始 URL
 * @return string 修正后的 URL
 */
function li_cw_fix_asset_url( $url ) {
    if ( ! $url || ! is_string( $url ) ) {
        return $url;
    }

    $home = wp_parse_url( home_url() );
    $scheme = isset( $home['scheme'] ) ? $home['scheme'] : 'https';
    $host   = isset( $home['host'] ) ? $home['host'] : '';

    $parts = wp_parse_url( $url );
    $url_host = isset( $parts['host'] ) ? $parts['host'] : '';
    $url_path = isset( $parts['path'] ) ? $parts['path'] : '';

    // /wp-content/ 与 /wp-includes/ 必为本站资源，无论 host 是否匹配都规范化
    $is_local_path = ( strpos( $url_path, '/wp-content/' ) === 0 || strpos( $url_path, '/wp-includes/' ) === 0 );

    if ( $is_local_path && $url_host !== $host ) {
        $query = isset( $parts['query'] ) ? '?' . $parts['query'] : '';
        return $scheme . '://' . $host . $url_path . $query;
    }

    // host 一致：仅升级协议
    if ( $url_host === $host && $scheme === 'https' && strpos( $url, 'http://' ) === 0 ) {
        return 'https://' . substr( $url, 7 );
    }

    return $url;
}

/**
 * 生成自定义配色内联CSS
 * 从自定义器读取设置，覆盖CSS变量
 * @return string
 */
function li_cw_get_custom_css() {
    $css = '';

    // 读取配色 — OKLCH 默认值
    $bg_page = li_cw_get_option( 'li_cw_bg_page', 'oklch(97.5% 0.005 95)' );
    $bg_card = li_cw_get_option( 'li_cw_bg_card', 'oklch(99% 0.003 95)' );
    $text_primary = li_cw_get_option( 'li_cw_text_primary', 'oklch(15% 0.005 170)' );
    $text_secondary = li_cw_get_option( 'li_cw_text_secondary', 'oklch(48% 0.005 170)' );
    $accent = li_cw_get_option( 'li_cw_accent', 'oklch(30% 0.055 170)' );
    $accent_gold = li_cw_get_option( 'li_cw_accent_gold', 'oklch(68% 0.09 82)' );
    $border = li_cw_get_option( 'li_cw_border', 'oklch(91% 0.008 95)' );

    // 读取字体
    $font_display = li_cw_get_option( 'li_cw_font_display' );
    $font_heading = li_cw_get_option( 'li_cw_font_heading' );
    $font_body = li_cw_get_option( 'li_cw_font_body' );
    $font_ui = li_cw_get_option( 'li_cw_font_ui' );
    $font_accent = li_cw_get_option( 'li_cw_font_accent' );

    // 拼接变量
    $css .= ":root {";
    $css .= "--bg-page: {$bg_page};";
    $css .= "--bg-card: {$bg_card};";
    $css .= "--text-primary: {$text_primary};";
    $css .= "--text-secondary: {$text_secondary};";
    $css .= "--accent: {$accent};";
    $css .= "--accent-gold: {$accent_gold};";
    $css .= "--border-color: {$border};";

    // 字体变量 — display 已合并入 heading
    if ( $font_display ) $css .= "--font-heading: {$font_display};";
    if ( $font_heading ) $css .= "--font-heading: {$font_heading};";
    if ( $font_body ) $css .= "--font-body: {$font_body};";
    if ( $font_ui ) $css .= "--font-ui: {$font_ui};";
    if ( $font_accent ) $css .= "--font-accent: {$font_accent};";

    $css .= "}";

    return $css;
}

/**
 * 获取文章自定义标签
 * @param int $post_id
 * @return string
 */
function li_cw_get_blog_tag( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $tag = get_post_meta( $post_id, 'li_cw_blog_tag', true );
    return esc_html( $tag );
}

/**
 * 获取作品状态标签
 * @param int $post_id
 * @return string
 */
function li_cw_get_project_status( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $status = get_post_meta( $post_id, 'li_cw_project_status', true );
    return $status ? esc_html( $status ) : esc_html__( '已完成', 'li-cw' );
}

/**
 * 计算文章阅读时间（分钟）
 * 中文按 300 字/分钟、英文按 200 词/分钟综合估算
 * @param int|null $post_id
 * @return int 至少为 1
 */
function li_cw_get_reading_time( $post_id = null ) {
    $post = get_post( $post_id );
    if ( ! $post ) {
        return 1;
    }

    $content = $post->post_content;
    $content = strip_shortcodes( $content );
    $content = wp_strip_all_tags( $content );

    // 中文字符（含 CJK 扩展 A 区）
    $chinese_count = preg_match_all( '/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]/u', $content );
    // 英文单词数
    $word_count = str_word_count( $content );

    $minutes = (int) ceil( ( $chinese_count / 300 ) + ( $word_count / 200 ) );
    return max( 1, $minutes );
}

/**
 * 友链页评论开关
 * 当后台开启且当前为友链页模板时，强制评论开放
 * 覆盖 wp-comments-post.php 提交时的判断
 */
function li_cw_force_links_comments_open() {
    if ( ! li_cw_get_option( 'li_cw_links_comments', false ) ) {
        return;
    }

    // 渲染页面：通过页面模板文件判断
    $is_links_page = is_page_template( 'page-links.php' );

    // 提交评论：通过 POST 的 comment_post_ID 判断是否友链页
    if ( ! $is_links_page && isset( $_POST['comment_post_ID'] ) ) {
        $post_id = absint( $_POST['comment_post_ID'] );
        if ( $post_id ) {
            $template = get_page_template_slug( $post_id );
            $is_links_page = ( 'page-links.php' === $template );
        }
    }

    if ( ! $is_links_page ) {
        return;
    }

    add_filter( 'comments_open', '__return_true' );
    add_filter( 'pings_open', '__return_true' );

    // 直接修改 post 对象的 comment_status，确保 comment_form 和 wp_handle_comment_submission 都放行
    $post_id = 0;
    if ( isset( $_POST['comment_post_ID'] ) ) {
        $post_id = absint( $_POST['comment_post_ID'] );
    } elseif ( is_singular() ) {
        $post_id = get_queried_object_id();
    }
    if ( $post_id ) {
        $post = get_post( $post_id );
        if ( $post ) {
            $post->comment_status = 'open';
            $post->ping_status   = 'open';
        }
    }
}
add_action( 'template_redirect', 'li_cw_force_links_comments_open' );

/**
 * 评论置顶 — 将标记为置顶的顶层评论排到列表最前
 * 通过 comment meta "li_cw_pinned" = '1' 标记
 */
function li_cw_pin_comments( $comments, $post_id ) {
	if ( empty( $comments ) ) {
		return $comments;
	}
	$pinned = array();
	$normal = array();
	foreach ( $comments as $c ) {
		if ( empty( $c->comment_parent ) && get_comment_meta( $c->comment_ID, 'li_cw_pinned', true ) ) {
			$pinned[] = $c;
		} else {
			$normal[] = $c;
		}
	}
	return array_merge( $pinned, $normal );
}
add_filter( 'comments_array', 'li_cw_pin_comments', 10, 2 );

// wp-comments-post.php 不触发 template_redirect，需在 init 阶段也处理提交场景
function li_cw_force_links_comments_on_submit() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
    if ( ! isset( $_POST['comment_post_ID'] ) ) return;
    if ( ! li_cw_get_option( 'li_cw_links_comments', false ) ) return;

    $post_id = absint( $_POST['comment_post_ID'] );
    if ( ! $post_id ) return;

    $template = get_page_template_slug( $post_id );
    if ( 'page-links.php' !== $template ) return;

    add_filter( 'comments_open', '__return_true' );
    $post = get_post( $post_id );
    if ( $post ) {
        $post->comment_status = 'open';
    }
}
add_action( 'init', 'li_cw_force_links_comments_on_submit', 1 );

/**
 * 统计照片墙中实际展示的图片总数（含特色图、区块图片、正文内联图片）
 * 结果通过 transient 缓存，发布/删除 photo 时自动清除。
 *
 * @return int
 */
function li_cw_count_gallery_images() {
    $cache_key = 'li_cw_gallery_image_count_v2';
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) {
        return absint( $cached );
    }

    $total  = 0;
    $photos = get_posts( array(
        'post_type'      => 'photo',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));

    foreach ( $photos as $post_id ) {
        // 去重：同一张图在多处出现（特色图、区块、内联）只算一次
        $seen = array();

        if ( has_post_thumbnail( $post_id ) ) {
            $tid = get_post_thumbnail_id( $post_id );
            $seen[] = $tid;
            $total++;
        }

        $content = get_post_field( 'post_content', $post_id );
        if ( ! $content ) {
            continue;
        }

        // 区块图片 — 去重
        if ( function_exists( 'parse_blocks' ) ) {
            $blocks = parse_blocks( $content );
            foreach ( $blocks as $block ) {
                if ( 'core/image' === $block['blockName'] && ! empty( $block['attrs']['id'] ) ) {
                    if ( ! in_array( $block['attrs']['id'], $seen, true ) ) {
                        $seen[] = $block['attrs']['id'];
                        $total++;
                    }
                }
                if ( 'core/gallery' === $block['blockName'] && ! empty( $block['attrs']['ids'] ) ) {
                    foreach ( $block['attrs']['ids'] as $gid ) {
                        if ( ! in_array( $gid, $seen, true ) ) {
                            $seen[] = $gid;
                            $total++;
                        }
                    }
                }
            }
        }

        // 传统内联图片 class — 去重
        if ( preg_match_all( '/wp-image-(\d+)/', $content, $matches ) ) {
            foreach ( array_unique( $matches[1] ) as $mid ) {
                $mid = absint( $mid );
                if ( ! in_array( $mid, $seen, true ) ) {
                    $seen[] = $mid;
                    $total++;
                }
            }
        }
    }

    set_transient( $cache_key, $total, DAY_IN_SECONDS );
    return $total;
}

/**
 * 照片内容变更时清除图片计数缓存
 */
function li_cw_clear_gallery_count_cache( $post_id ) {
    if ( 'photo' === get_post_type( $post_id ) ) {
        delete_transient( 'li_cw_gallery_image_count_v2' );
    }
}
add_action( 'save_post', 'li_cw_clear_gallery_count_cache' );
add_action( 'delete_post', 'li_cw_clear_gallery_count_cache' );
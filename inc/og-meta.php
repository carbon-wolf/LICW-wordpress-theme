<?php
/**
 * Open Graph & Twitter Card 元标签
 * 为社交分享输出 OG / Twitter Card meta 标签
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 获取 OG 兜底图 URL
 * 链路：OG 默认分享图 → 首页主视觉图
 * @return string 空字符串表示无可用图
 */
function li_cw_get_og_fallback_image() {
    $og_default = li_cw_get_option( 'li_cw_og_default_image' );
    if ( $og_default ) {
        return li_cw_fix_asset_url( $og_default );
    }
    $hero = li_cw_get_option( 'li_cw_home_hero_image' );
    if ( $hero ) {
        return li_cw_fix_asset_url( $hero );
    }
    return '';
}

/**
 * 获取当前页规范 URL
 * @return string
 */
function li_cw_get_canonical_url() {
    if ( is_singular() ) {
        $canonical = wp_get_canonical_url();
        if ( $canonical ) {
            return $canonical;
        }
    }
    global $wp;
    return home_url( $wp->request );
}

/**
 * 获取当前页 OG 数据
 * @return array
 */
function li_cw_get_og_data() {
    $data = array(
        'title'       => get_bloginfo( 'name' ),
        'description' => get_bloginfo( 'description' ),
        'url'         => li_cw_get_canonical_url(),
        'type'        => 'website',
        'image'       => li_cw_get_og_fallback_image(),
        'locale'      => get_locale(),
        'site_name'   => get_bloginfo( 'name' ),
    );

    if ( is_front_page() ) {
        $home_desc = li_cw_get_option( 'li_cw_home_desc' );
        if ( $home_desc ) {
            $data['description'] = $home_desc;
        }
    } elseif ( is_home() ) {
        $blog_page_id = get_option( 'page_for_posts' );
        if ( $blog_page_id ) {
            $data['title'] = get_the_title( $blog_page_id );
        }
    } elseif ( is_singular() ) {
        $post = get_post();
        $data['title'] = get_the_title();
        $data['type']  = 'article';

        // description：手写摘要 → 正文截断 → 站点描述
        $excerpt = trim( $post->post_excerpt );
        if ( $excerpt ) {
            $data['description'] = $excerpt;
        } else {
            $content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
            $content = trim( preg_replace( '/\s+/', ' ', $content ) );
            $data['description'] = mb_substr( $content, 0, 120 );
        }
        if ( ! $data['description'] ) {
            $data['description'] = get_bloginfo( 'description' );
        }

        // image：特色图 → 兜底
        if ( has_post_thumbnail() ) {
            $thumb_url = get_the_post_thumbnail_url( $post, 'full' );
            if ( $thumb_url ) {
                $data['image'] = $thumb_url;
            }
        }
    } else {
        // 归档 / 搜索
        $data['title']       = wp_get_document_title();
        $archive_desc        = get_the_archive_description();
        $data['description'] = $archive_desc ? wp_strip_all_tags( $archive_desc ) : get_bloginfo( 'description' );
    }

    return $data;
}

/**
 * 输出 OG 与 Twitter Card meta 标签
 */
function li_cw_output_og_meta() {
    if ( is_admin() ) {
        return;
    }

    $data = li_cw_get_og_data();

    // Open Graph
    echo '<meta property="og:title" content="' . esc_attr( $data['title'] ) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $data['description'] ) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $data['url'] ) . '" />' . "\n";
    echo '<meta property="og:type" content="' . esc_attr( $data['type'] ) . '" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( $data['site_name'] ) . '" />' . "\n";
    echo '<meta property="og:locale" content="' . esc_attr( $data['locale'] ) . '" />' . "\n";
    if ( $data['image'] ) {
        echo '<meta property="og:image" content="' . esc_url( $data['image'] ) . '" />' . "\n";
        echo '<meta property="og:image:alt" content="' . esc_attr( $data['title'] ) . '" />' . "\n";
    }

    // 文章专属标签
    if ( is_singular() ) {
        $post = get_post();
        if ( $post->post_date && $post->post_date !== '0000-00-00 00:00:00' ) {
            echo '<meta property="article:published_time" content="' . esc_attr( mysql2date( 'c', $post->post_date ) ) . '" />' . "\n";
        }
        if ( $post->post_modified && $post->post_modified !== '0000-00-00 00:00:00' ) {
            echo '<meta property="article:modified_time" content="' . esc_attr( mysql2date( 'c', $post->post_modified ) ) . '" />' . "\n";
        }
        $author_url = get_author_posts_url( $post->post_author );
        if ( $author_url ) {
            echo '<meta property="article:author" content="' . esc_url( $author_url ) . '" />' . "\n";
        }
    }

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $data['title'] ) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $data['description'] ) . '" />' . "\n";
    if ( $data['image'] ) {
        echo '<meta name="twitter:image" content="' . esc_url( $data['image'] ) . '" />' . "\n";
    }
}
add_action( 'wp_head', 'li_cw_output_og_meta', 5 );

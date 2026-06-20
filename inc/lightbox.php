<?php
/**
 * 图片灯箱
 * 点击正文图片弹出全屏查看，ESC / 点击遮罩关闭
 *
 * 仅处理 <a><img></a> 包裹的图片（WordPress 默认行为）。
 * 跳过已有 data-lightbox / data-wp-lightbox 的图片。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function li_cw_add_lightbox_attr( $content ) {
    if ( ! is_singular() || is_admin() ) return $content;

    // 第一轮：处理 <a><img class="wp-image-..."></a> 包裹的图片
    $content = preg_replace_callback(
        '/<a\b([^>]*?)href="([^"]*)"([^>]*?)><img\b([^>]*?)class="([^"]*wp-image-[^"]*)"([^>]*?)><\/a>/i',
        function ( $m ) {
            // 已有灯箱属性的跳过
            if ( strpos( $m[1] . $m[3], 'data-lightbox' ) !== false
                || strpos( $m[1] . $m[3], 'data-wp-lightbox' ) !== false ) {
                return $m[0];
            }

            // 外部链接（非本站、非媒体文件）跳过
            $href = $m[2];
            if ( strpos( $href, home_url() ) === false
                && strpos( $href, '/wp-content/uploads/' ) === false
                && strpos( $href, 'http' ) === 0 ) {
                return $m[0];
            }

            // 去掉 WordPress 生成的尺寸后缀 → 原图
            $full = preg_replace( '/-\d+x\d+(\.[a-z]+)$/i', '$1', $href );

            return '<a href="' . esc_url( $full ) . '" data-lightbox="gallery"' . $m[1] . $m[3] . '>'
                . '<img' . $m[4] . 'class="' . esc_attr( $m[5] ) . '"' . $m[6] . '></a>';
        },
        $content
    );

    // 第二轮：处理独立 <img class="wp-image-...">（无 <a> 包裹）
    // 使用 PREG_OFFSET_CAPTURE 定位，跳过已包裹在 <a> 内的图片
    if ( preg_match_all(
        '/<img\b([^>]*?)class="([^"]*wp-image-[^"]*)"([^>]*?)>/i',
        $content,
        $matches,
        PREG_OFFSET_CAPTURE | PREG_SET_ORDER
    ) ) {
        $offset = 0;
        $result = '';

        foreach ( $matches as $m ) {
            $match_pos  = $m[0][1];
            $match_text = $m[0][0];

            // 复制匹配前的内容
            $result .= substr( $content, $offset, $match_pos - $offset );
            $offset = $match_pos + strlen( $match_text );

            // 取匹配位前 256 字符，检查是否在 <a> 标签内
            $before = substr( $content, max( 0, $match_pos - 512 ), $match_pos - max( 0, $match_pos - 512 ) );
            $last_a   = strrpos( $before, '<a ' );
            $last_end = strrpos( $before, '</a>' );

            if ( $last_a !== false && ( $last_end === false || $last_a > $last_end ) ) {
                // 图片已在 <a> 标签内，保持原样
                $result .= $match_text;
                continue;
            }

            // 独立图片：提取 src 并包裹
            $attrs_before = $m[1][0];
            $class_val    = $m[2][0];
            $attrs_after  = $m[3][0];

            if ( preg_match( '/src="([^"]+)"/i', $attrs_before . $attrs_after, $src_m ) ) {
                $src  = $src_m[1];
                $full = preg_replace( '/-\d+x\d+(\.[a-z]+)$/i', '$1', $src );

                $result .= '<a href="' . esc_url( $full ) . '" data-lightbox="gallery">'
                    . '<img' . $attrs_before . 'class="' . esc_attr( $class_val ) . '"' . $attrs_after . '></a>';
            } else {
                $result .= $match_text;
            }
        }

        $result .= substr( $content, $offset );
        $content = $result;
    }

    return $content;
}
add_filter( 'the_content', 'li_cw_add_lightbox_attr', 20 );

function li_cw_enqueue_lightbox() {
    if ( is_singular() ) {
        wp_enqueue_script(
            'li-cw-lightbox',
            LI_CW_THEME_URI . '/assets/js/lightbox.js',
            array(),
            LI_CW_VERSION,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'li_cw_enqueue_lightbox' );

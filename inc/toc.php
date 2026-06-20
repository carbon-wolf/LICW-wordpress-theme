<?php
/**
 * 文章目录 (Table of Contents)
 * 自动从 h2/h3 提取标题，生成带锚点的目录树
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 为正文中的标题添加 id 锚点
 */
function li_cw_add_heading_ids( $content ) {
    if ( ! is_singular() || is_admin() ) return $content;

    $used = array();

    $content = preg_replace_callback(
        '/<h([23])(\s[^>]*)?>(.*?)<\/h[23]>/i',
        function ( $m ) use ( &$used ) {
            $level = $m[1];
            $attrs = $m[2] ? $m[2] : '';
            $text  = wp_strip_all_tags( $m[3] );

            // 已有 id 则跳过
            if ( strpos( $attrs, 'id=' ) !== false ) {
                return $m[0];
            }

            $id = sanitize_title( $text );
            if ( ! $id || is_numeric( $id ) ) {
                $id = 'h' . $level . '-' . uniqid();
            }

            // 去重
            $base = $id;
            $i = 1;
            while ( in_array( $id, $used, true ) ) {
                $id = $base . '-' . ( ++$i );
            }
            $used[] = $id;

            return '<h' . $level . ' id="' . esc_attr( $id ) . '"' . $attrs . '>' . $m[3] . '</h' . $level . '>';
        },
        $content
    );

    return $content;
}
add_filter( 'the_content', 'li_cw_add_heading_ids', 5 );

/**
 * 从文章原始内容提取目录
 * @return string 目录 HTML，无标题时返回空
 */
function li_cw_get_toc() {
    $post = get_post();
    if ( ! $post || ! is_singular() ) return '';

    $content = $post->post_content;

    // 匹配 h2 / h3（可能已有 id 或未添加）
    if ( ! preg_match_all( '/<h([23])(\s[^>]*)?>(.*?)<\/h[23]>/i', $content, $matches, PREG_SET_ORDER ) ) {
        return '';
    }

    // 过滤到至少 2 个标题才显示目录
    $items = array();
    $used  = array();

    foreach ( $matches as $m ) {
        $level = (int) $m[1];
        $attrs = $m[2] ? $m[2] : '';
        $text  = wp_strip_all_tags( $m[3] );

        // 提取或生成 id（与 li_cw_add_heading_ids 逻辑一致）
        if ( preg_match( '/id="([^"]+)"/i', $attrs, $id_match ) ) {
            $id = $id_match[1];
        } else {
            $id = sanitize_title( $text );
            if ( ! $id || is_numeric( $id ) ) {
                $id = 'h' . $level . '-' . uniqid();
            }
            $base = $id;
            $i = 1;
            while ( in_array( $id, $used, true ) ) {
                $id = $base . '-' . ( ++$i );
            }
        }
        $used[] = $id;

        $items[] = array(
            'level' => $level,
            'id'    => $id,
            'text'  => $text,
        );
    }

    if ( count( $items ) < 2 ) return '';

    // 构建嵌套列表
    $html  = '<nav class="toc" aria-label="' . esc_attr__( '文章目录', 'li-cw' ) . '">';
    $html .= '<details class="toc-details" open>';
    $html .= '<summary class="toc-title">' . esc_html__( '目录', 'li-cw' ) . '</summary>';
    $html .= '<ol class="toc-list">';

    $stack   = array(); // 层级栈
    $first   = true;
    $min_lvl = 2;

    foreach ( $items as $item ) {
        $lvl = $item['level'];

        // 关闭前一项
        if ( ! $first ) {
            $html .= '</li>';
        }
        $first = false;

        // 进入子层级
        while ( ! empty( $stack ) && end( $stack ) >= $lvl ) {
            $html .= '</ol></li>';
            array_pop( $stack );
        }

        // 开启新层级
        if ( ! empty( $stack ) && end( $stack ) < $lvl ) {
            $html .= '<ol>';
            $stack[] = $lvl;
        }

        // 首个/顶层项不嵌套子 <ol>
        if ( empty( $stack ) ) {
            $stack[] = $lvl;
        }

        $html .= '<li class="toc-item toc-level-' . $lvl . '">';
        $html .= '<a href="#' . esc_attr( $item['id'] ) . '">' . esc_html( $item['text'] ) . '</a>';
    }

    // 关闭剩余层级
    while ( ! empty( $stack ) ) {
        $html .= '</li></ol>';
        array_pop( $stack );
    }

    $html .= '</details>';
    $html .= '</nav>';

    return $html;
}

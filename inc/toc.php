<?php
/**
 * 文章目录 (Table of Contents)
 * 自动从 h2/h3 提取标题，生成带锚点的目录树
 * id 生成与正文锚点共用同一确定性算法，确保目录链接与正文 id 一致
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 匹配 h2/h3 的正则（反向引用保证开闭标签同层级）
 */
function li_cw_toc_heading_regex() {
    return '/<h([23])(\s[^>]*)?>(.*?)<\/h\1>/is';
}

/**
 * 从标题属性与文本计算锚点 id（确定性）
 * 数字/空文本时基于内容哈希回退，避免 uniqid 导致
 * the_content 与 TOC 两次调用生成不同 id。
 *
 * @param int    $level 标题层级
 * @param string $attrs 原始属性串
 * @param string $text  标题纯文本
 * @return string|null 已有 id 时原样返回（不含则返回 null 表示无需生成）
 */
function li_cw_toc_existing_id( $attrs, $text ) {
    if ( preg_match( '/id=["\']([^"\']+)["\']/i', $attrs, $id_match ) ) {
        return $id_match[1];
    }
    return null;
}

/**
 * 生成确定性锚点 id
 *
 * @param int    $level 层级
 * @param string $text  标题纯文本
 * @param int    $index 标题在文中的序号（用于数字标题回退与去重）
 * @return string
 */
function li_cw_toc_make_id( $level, $text, $index ) {
    $id = sanitize_title( $text );
    if ( ! $id || is_numeric( $id ) ) {
        $id = 'h' . $level . '-' . substr( md5( $text . '|' . $index ), 0, 8 );
    }
    return $id;
}

/**
 * 为正文中的标题添加 id 锚点
 */
function li_cw_add_heading_ids( $content ) {
    if ( ! is_singular() || is_admin() ) return $content;

    $used  = array();
    $index = 0;

    $content = preg_replace_callback(
        li_cw_toc_heading_regex(),
        function ( $m ) use ( &$used, &$index ) {
            $level = $m[1];
            $attrs = $m[2] ? $m[2] : '';
            $text  = wp_strip_all_tags( $m[3] );

            // 已有 id 则跳过
            if ( li_cw_toc_existing_id( $attrs, $text ) !== null ) {
                return $m[0];
            }

            $index++;
            $id = li_cw_toc_make_id( $level, $text, $index );

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
    if ( ! preg_match_all( li_cw_toc_heading_regex(), $content, $matches, PREG_SET_ORDER ) ) {
        return '';
    }

    // 过滤到至少 2 个标题才显示目录
    $items = array();
    $used  = array();
    $index = 0;

    foreach ( $matches as $m ) {
        $level = (int) $m[1];
        $attrs = $m[2] ? $m[2] : '';
        $text  = wp_strip_all_tags( $m[3] );

        $existing = li_cw_toc_existing_id( $attrs, $text );
        if ( null !== $existing ) {
            $id = $existing;
        } else {
            $index++;
            $id = li_cw_toc_make_id( $level, $text, $index );
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

    // 回退基准取首个标题层级（首标题为 h3 时也能正确闭合）
    $prev_level = 0;
    $base_level = $items[0]['level'];

    foreach ( $items as $item ) {
        $lvl = $item['level'];

        if ( $prev_level === 0 ) {
            // 首项：直接开启 <li>，不闭合前项
        } elseif ( $lvl > $prev_level ) {
            // 进入子层级：在前项 <li> 内开启嵌套 <ol>（不闭合前项 <li>）
            $html .= '<ol>';
        } elseif ( $lvl < $prev_level ) {
            // 回到上层：先闭合前项 <li>，再按层级差逐层 </ol></li>
            $html .= '</li>';
            $diff = $prev_level - $lvl;
            for ( $i = 0; $i < $diff; $i++ ) {
                $html .= '</ol></li>';
            }
        } else {
            // 同级：仅闭合前项 <li>
            $html .= '</li>';
        }

        $html .= '<li class="toc-item toc-level-' . $lvl . '">';
        $html .= '<a href="#' . esc_attr( $item['id'] ) . '">' . esc_html( $item['text'] ) . '</a>';
        $prev_level = $lvl;
    }

    // 关闭最后一项 + 回退到顶层
    $html .= '</li>';
    $diff = $prev_level - $base_level;
    for ( $i = 0; $i < $diff; $i++ ) {
        $html .= '</ol></li>';
    }
    $html .= '</ol>';

    $html .= '</details>';
    $html .= '</nav>';

    return $html;
}

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
 * 生成自定义配色内联CSS
 * 从自定义器读取设置，覆盖CSS变量
 * @return string
 */
function li_cw_get_custom_css() {
    $css = '';

    // 读取配色
    $bg_page = li_cw_get_option( 'li_cw_bg_page', '#f8f6f1' );
    $bg_card = li_cw_get_option( 'li_cw_bg_card', '#ffffff' );
    $text_primary = li_cw_get_option( 'li_cw_text_primary', '#1f1f1f' );
    $text_secondary = li_cw_get_option( 'li_cw_text_secondary', '#666666' );
    $accent = li_cw_get_option( 'li_cw_accent', '#1a3a32' );
    $accent_gold = li_cw_get_option( 'li_cw_accent_gold', '#b89a5c' );
    $border = li_cw_get_option( 'li_cw_border', '#e8e4db' );

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

    // 字体变量
    if ( $font_display ) $css .= "--font-display: {$font_display};";
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
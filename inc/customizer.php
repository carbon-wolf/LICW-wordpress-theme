<?php
/**
 * WordPress 原生自定义器配置
 * 所有可视化设置集中在此，无需插件即可后台修改全站内容
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function li_cw_register_customizer( $wp_customize ) {

    // ========== 1. 首页文案设置 ==========
    $wp_customize->add_section( 'li_cw_section_home', array(
        'title'       => esc_html__( '首页设置', 'li-cw' ),
        'priority'    => 20,
    ));

    // 欢迎小字
    $wp_customize->add_setting( 'li_cw_home_welcome', array(
        'default'           => 'WELCOME',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_home_welcome', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '欢迎前缀文字', 'li-cw' ),
        'type'     => 'text',
    ));

    // 大标题
    $wp_customize->add_setting( 'li_cw_home_title', array(
        'default'           => 'Li CW',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_home_title', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '首页大标题', 'li-cw' ),
        'type'     => 'text',
    ));

    // 副标题
    $wp_customize->add_setting( 'li_cw_home_subtitle', array(
        'default'           => '高中生开发者 / 写作者',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_home_subtitle', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '首页副标题', 'li-cw' ),
        'type'     => 'text',
    ));

    // 描述文本
    $wp_customize->add_setting( 'li_cw_home_desc', array(
        'default'           => '正在制作《凭君之光》，记录创作、设计与思考。',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control( 'li_cw_home_desc', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '首页描述文案', 'li-cw' ),
        'type'     => 'textarea',
    ));

    // 主按钮文字
    $wp_customize->add_setting( 'li_cw_home_btn1_text', array(
        'default'           => '查看作品',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_home_btn1_text', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '主按钮文字', 'li-cw' ),
        'type'     => 'text',
    ));

    // 主按钮链接
    $wp_customize->add_setting( 'li_cw_home_btn1_link', array(
        'default'           => '#projects',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control( 'li_cw_home_btn1_link', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '主按钮跳转链接', 'li-cw' ),
        'type'     => 'url',
    ));

    // 次按钮文字
    $wp_customize->add_setting( 'li_cw_home_btn2_text', array(
        'default'           => '关于我',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_setting( 'li_cw_home_btn2_link', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    // 次按钮链接
    $wp_customize->add_setting( 'li_cw_home_btn2_link', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control( 'li_cw_home_btn2_link', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '次按钮跳转链接', 'li-cw' ),
        'type'     => 'url',
    ));

    // 首页右侧主图
    $wp_customize->add_setting( 'li_cw_home_hero_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw', // 改为URL转义，匹配Image控件返回值
    ));
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'li_cw_home_hero_image', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '首页右侧主视觉图', 'li-cw' ),
    )));

    // 首页日志显示数量
    $wp_customize->add_setting( 'li_cw_home_post_count', array(
        'default'           => 3,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control( 'li_cw_home_post_count', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '首页显示日志数量', 'li-cw' ),
        'type'     => 'number',
        'input_attrs' => array( 'min' => 1, 'max' => 10 ),
    ));

    // 首页作品显示数量
    $wp_customize->add_setting( 'li_cw_home_project_count', array(
        'default'           => 3,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control( 'li_cw_home_project_count', array(
        'section'  => 'li_cw_section_home',
        'label'    => esc_html__( '首页显示作品数量', 'li-cw' ),
        'type'     => 'number',
        'input_attrs' => array( 'min' => 1, 'max' => 9 ),
    ));


    // ========== 2. 配色设置 ==========
    $wp_customize->add_section( 'li_cw_section_colors', array(
        'title'       => esc_html__( '配色设置', 'li-cw' ),
        'priority'    => 30,
    ));

    $colors = array(
        'li_cw_bg_page'      => array( 'label' => '页面背景色', 'default' => 'oklch(97.5% 0.005 95)' ),
        'li_cw_bg_card'      => array( 'label' => '卡片背景色', 'default' => 'oklch(99% 0.003 95)' ),
        'li_cw_text_primary' => array( 'label' => '主文字色', 'default' => 'oklch(15% 0.005 170)' ),
        'li_cw_text_secondary' => array( 'label' => '辅助文字色', 'default' => 'oklch(48% 0.005 170)' ),
        'li_cw_accent'       => array( 'label' => '主强调色', 'default' => 'oklch(30% 0.055 170)' ),
        'li_cw_accent_gold'  => array( 'label' => '金色辅助色', 'default' => 'oklch(68% 0.09 82)' ),
        'li_cw_border'       => array( 'label' => '边框分割线色', 'default' => 'oklch(91% 0.008 95)' ),
    );

    foreach ( $colors as $key => $setting ) {
        $wp_customize->add_setting( $key, array(
            'default'           => $setting['default'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, array(
            'section' => 'li_cw_section_colors',
            'label'   => $setting['label'],
        )));
    }


    // ========== 3. 关于页设置 ==========
    $wp_customize->add_section( 'li_cw_section_about', array(
        'title'       => esc_html__( '关于页设置', 'li-cw' ),
        'priority'    => 40,
    ));

    // 头像
    $wp_customize->add_setting( 'li_cw_about_avatar', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw', // 改为URL转义，匹配Image控件返回值
    ));
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'li_cw_about_avatar', array(
        'section' => 'li_cw_section_about',
        'label'   => esc_html__( '个人头像', 'li-cw' ),
    )));

    // 姓名
    $wp_customize->add_setting( 'li_cw_about_name', array(
        'default'           => 'Li CW',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_about_name', array(
        'section' => 'li_cw_section_about',
        'label'   => esc_html__( '姓名/昵称', 'li-cw' ),
        'type'    => 'text',
    ));

    // 身份
    $wp_customize->add_setting( 'li_cw_about_title', array(
        'default'           => '高中生开发者 / 写作者',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_about_title', array(
        'section' => 'li_cw_section_about',
        'label'   => esc_html__( '身份介绍', 'li-cw' ),
        'type'    => 'text',
    ));

    // 个人简介
    $wp_customize->add_setting( 'li_cw_about_desc', array(
        'default'           => '热爱创造，思考与表达。喜欢在混乱中寻找秩序，在代码与文字之间构建世界。',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control( 'li_cw_about_desc', array(
        'section' => 'li_cw_section_about',
        'label'   => esc_html__( '个人简介', 'li-cw' ),
        'type'    => 'textarea',
    ));

   // 社交链接 - 5个可配置项，每个含名称（tooltip用）+ 链接
        // 社交链接 - 5个可配置项，下拉选图标类型，无需手写SVG
    $social_items = array(
        '1' => '社交链接 1',
        '2' => '社交链接 2',
        '3' => '社交链接 3',
        '4' => '社交链接 4',
        '5' => '社交链接 5',
    );

        foreach ( $social_items as $num => $label ) {
        // 图标类型下拉（预设）
        $wp_customize->add_setting( "li_cw_social_{$num}_type", array(
            'default'           => 'link',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control( "li_cw_social_{$num}_type", array(
            'section' => 'li_cw_section_about',
            'label'   => $label . ' - 预设图标',
            'type'    => 'select',
            'choices' => array(
                'link'    => '普通外链',
                'github'  => 'GitHub',
                'twitter' => 'Twitter / X',
                'email'   => '邮箱',
                'wechat'  => '微信',
                'bilibili'=> 'B站',
                'steam'   => 'Steam',
                'qq'      => 'QQ',
            ),
        ));

        // 名称（悬浮提示）
        $wp_customize->add_setting( "li_cw_social_{$num}_name", array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control( "li_cw_social_{$num}_name", array(
            'section' => 'li_cw_section_about',
            'label'   => $label . ' - 名称（悬浮提示）',
            'type'    => 'text',
        ));

        // 链接地址
        $wp_customize->add_setting( "li_cw_social_{$num}_url", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control( "li_cw_social_{$num}_url", array(
            'section' => 'li_cw_section_about',
            'label'   => $label . ' - 链接地址',
            'type'    => 'url',
        ));

        // ====== 新增：自定义SVG代码（可选，填了覆盖预设）======
        $wp_customize->add_setting( "li_cw_social_{$num}_custom_icon", array(
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
        ));
        $wp_customize->add_control( "li_cw_social_{$num}_custom_icon", array(
            'section' => 'li_cw_section_about',
            'label'   => $label . ' - 自定义SVG代码（可选）',
            'type'    => 'textarea',
            'description' => '粘贴内联SVG代码，填写后会覆盖预设图标',
        ));
    }


    // ========== 4. 页脚设置 ==========
    // ICP备案号
    $wp_customize->add_setting( 'li_cw_beian', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_beian', array(
        'section' => 'li_cw_section_footer',
        'label'   => esc_html__( 'ICP备案号', 'li-cw' ),
        'type'    => 'text',
        'description' => '填写后自动在页脚显示并链接到工信部备案平台',
    ));
    $wp_customize->add_section( 'li_cw_section_footer', array(
        'title'       => esc_html__( '页脚设置', 'li-cw' ),
        'priority'    => 50,
    ));

    $wp_customize->add_setting( 'li_cw_footer_copyright', array(
        'default'           => '© 2026 Li CW. All rights reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_footer_copyright', array(
        'section' => 'li_cw_section_footer',
        'label'   => esc_html__( '版权文字', 'li-cw' ),
        'type'    => 'text',
    ));

    $wp_customize->add_setting( 'li_cw_footer_privacy_link', array(
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control( 'li_cw_footer_privacy_link', array(
        'section' => 'li_cw_section_footer',
        'label'   => esc_html__( '隐私政策链接', 'li-cw' ),
        'type'    => 'url',
    ));

    $wp_customize->add_setting( 'li_cw_footer_terms_link', array(
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control( 'li_cw_footer_terms_link', array(
        'section' => 'li_cw_section_footer',
        'label'   => esc_html__( '使用条款链接', 'li-cw' ),
        'type'    => 'url',
    ));
    // ========== 6. 友链页面设置 ==========
    $wp_customize->add_section( 'li_cw_section_links', array(
        'title'       => esc_html__( '友链设置', 'li-cw' ),
        'priority'    => 55,
    ));

    // 友链数量
    $link_count = 8;
    for ( $i = 1; $i <= $link_count; $i++ ) {
        // 站点名称
        $wp_customize->add_setting( "li_cw_link_{$i}_name", array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control( "li_cw_link_{$i}_name", array(
            'section' => 'li_cw_section_links',
            'label'   => "友链 {$i} - 站点名称",
            'type'    => 'text',
        ));

        // 站点链接
        $wp_customize->add_setting( "li_cw_link_{$i}_url", array(
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control( "li_cw_link_{$i}_url", array(
            'section' => 'li_cw_section_links',
            'label'   => "友链 {$i} - 站点地址",
            'type'    => 'url',
        ));

        // 站点描述
        $wp_customize->add_setting( "li_cw_link_{$i}_desc", array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control( "li_cw_link_{$i}_desc", array(
            'section' => 'li_cw_section_links',
            'label'   => "友链 {$i} - 站点描述",
            'type'    => 'text',
        ));

        // 站点头像/Logo
        $wp_customize->add_setting( "li_cw_link_{$i}_avatar", array(
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "li_cw_link_{$i}_avatar", array(
            'section' => 'li_cw_section_links',
            'label'   => "友链 {$i} - 站点头像/Logo",
        )));
    }
    // ========== 5. 字体设置 ==========
    $wp_customize->add_section( 'li_cw_section_fonts', array(
        'title'       => esc_html__( '字体设置', 'li-cw' ),
        'priority'    => 35,
    ));

    // 大标题字体（已合并为标题字体，保留字段兼容旧设置）
    $wp_customize->add_setting( 'li_cw_font_display', array(
        'default'           => '"Noto Serif SC", "Source Han Serif SC", "思源宋体 SC", Georgia, serif',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_font_display', array(
        'section' => 'li_cw_section_fonts',
        'label'   => esc_html__( '标题/展示字体栈', 'li-cw' ),
        'type'    => 'text',
        'description' => '用于首页大标题、页面主标题、章节标题',
    ));

    // 标题字体（中文宋体）
    $wp_customize->add_setting( 'li_cw_font_heading', array(
        'default'           => '"Noto Serif SC", "Source Han Serif SC", "思源宋体 SC", Georgia, serif',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_font_heading', array(
        'section' => 'li_cw_section_fonts',
        'label'   => esc_html__( '次级标题字体栈', 'li-cw' ),
        'type'    => 'text',
        'description' => '用于章节标题、文章标题',
    ));

    // 正文字体 — Alegreya + 思源宋体（拉丁 + CJK 双衬线）
    $wp_customize->add_setting( 'li_cw_font_body', array(
        'default'           => '"Alegreya", "Noto Serif SC", "Source Han Serif SC", "思源宋体 SC", Georgia, serif',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_font_body', array(
        'section' => 'li_cw_section_fonts',
        'label'   => esc_html__( '正文字体栈', 'li-cw' ),
        'type'    => 'text',
        'description' => '用于正文段落。Alegreya 为拉丁文提供温暖的人文主义衬线质感',
    ));

    // 界面字体 — 全站衬线统一
    $wp_customize->add_setting( 'li_cw_font_ui', array(
        'default'           => '"Alegreya", Georgia, "Noto Serif SC", serif',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_font_ui', array(
        'section' => 'li_cw_section_fonts',
        'label'   => esc_html__( '界面字体栈', 'li-cw' ),
        'type'    => 'text',
        'description' => '用于导航、按钮、标签、日期等UI元素。衬线UI是主题的核心差异化',
    ));

    // 点缀字体（霞鹜文楷）
    $wp_customize->add_setting( 'li_cw_font_accent', array(
        'default'           => '"LXGW WenKai", "霞鹜文楷", "Noto Serif SC", "Source Han Serif SC", serif',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control( 'li_cw_font_accent', array(
        'section' => 'li_cw_section_fonts',
        'label'   => esc_html__( '点缀字体栈', 'li-cw' ),
        'type'    => 'text',
        'description' => '用于欢迎语、引用块等少量点缀',
    ));

}
add_action( 'customize_register', 'li_cw_register_customizer' );

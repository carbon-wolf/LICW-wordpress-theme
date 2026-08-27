<?php
/**
 * 注册「作品 Project」自定义文章类型
 * 独立管理作品集，与文章分离
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function li_cw_register_project_cpt() {

    $labels = array(
        'name'               => esc_html_x( '作品', 'post type general name', 'li-cw' ),
        'singular_name'      => esc_html_x( '作品', 'post type singular name', 'li-cw' ),
        'add_new'            => esc_html_x( '新建作品', 'li-cw' ),
        'add_new_item'       => esc_html__( '添加新作品', 'li-cw' ),
        'edit_item'          => esc_html__( '编辑作品', 'li-cw' ),
        'new_item'           => esc_html__( '新作品', 'li-cw' ),
        'view_item'          => esc_html__( '查看作品', 'li-cw' ),
        'search_items'       => esc_html__( '搜索作品', 'li-cw' ),
        'not_found'          => esc_html__( '未找到作品', 'li-cw' ),
        'menu_name'          => esc_html__( '作品', 'li-cw' ),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'show_in_rest'        => true, // 支持古腾堡编辑器
        'has_archive'         => true,
        'menu_icon'           => 'dashicons-portfolio',
        'menu_position'       => 5,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'rewrite'             => array( 'slug' => 'projects' ),
        'show_in_nav_menus'   => true,
    );

    register_post_type( 'project', $args );
}
add_action( 'init', 'li_cw_register_project_cpt' );

/**
 * 徽章配色选项（完全手选，无自动识别）
 * @return array key => 显示文案
 */
function li_cw_get_project_status_colors() {
    return array(
        'gold'  => esc_html__( '金色', 'li-cw' ),
        'green' => esc_html__( '绿色', 'li-cw' ),
        'red'   => esc_html__( '红色', 'li-cw' ),
        'gray'  => esc_html__( '灰色', 'li-cw' ),
    );
}

/**
 * 注册「作品状态」metabox（右侧栏文本框 + 徽章配色下拉）
 */
function li_cw_add_project_status_metabox() {
    add_meta_box(
        'li_cw_project_status_box',
        esc_html__( '作品状态', 'li-cw' ),
        'li_cw_render_project_status_metabox',
        'project',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'li_cw_add_project_status_metabox' );

/**
 * 渲染作品状态 metabox
 * @param WP_Post $post
 */
function li_cw_render_project_status_metabox( $post ) {
    $current = li_cw_get_project_status( $post->ID );
    $color   = get_post_meta( $post->ID, 'li_cw_project_status_color', true );
    $colors  = li_cw_get_project_status_colors();
    if ( ! isset( $colors[ $color ] ) ) {
        $color = 'gold';
    }

    wp_nonce_field( 'li_cw_project_status_box', 'li_cw_project_status_nonce' );
    echo '<label for="li_cw_project_status_input" style="display:block;margin-bottom:4px;">' . esc_html__( '状态文案', 'li-cw' ) . '</label>';
    echo '<input type="text" id="li_cw_project_status_input" name="li_cw_project_status" value="' . esc_attr( $current ) . '" style="width:100%;" placeholder="' . esc_attr__( '如：进行中、已完成', 'li-cw' ) . '">';
    echo '<p class="description">' . esc_html__( '完全自定义，留空显示默认「已完成」。', 'li-cw' ) . '</p>';

    echo '<label for="li_cw_project_status_color" style="display:block;margin:12px 0 4px;">' . esc_html__( '徽章配色', 'li-cw' ) . '</label>';
    echo '<select id="li_cw_project_status_color" name="li_cw_project_status_color" style="width:100%;">';
    foreach ( $colors as $key => $label ) {
        echo '<option value="' . esc_attr( $key ) . '"' . selected( $color, $key, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( '不设置则显示金色。', 'li-cw' ) . '</p>';
}

/**
 * 保存作品状态（自由文本，sanitize 后入库；留空删除）
 * @param int $post_id
 */
function li_cw_save_project_status( $post_id ) {
    if ( ! isset( $_POST['li_cw_project_status_nonce'] )
        || ! wp_verify_nonce( $_POST['li_cw_project_status_nonce'], 'li_cw_project_status_box' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( ! isset( $_POST['li_cw_project_status'] ) ) {
        return;
    }

    $status = sanitize_text_field( wp_unslash( $_POST['li_cw_project_status'] ) );
    if ( '' === $status ) {
        delete_post_meta( $post_id, 'li_cw_project_status' );
    } else {
        update_post_meta( $post_id, 'li_cw_project_status', $status );
    }

    if ( isset( $_POST['li_cw_project_status_color'] ) ) {
        $color = sanitize_key( wp_unslash( $_POST['li_cw_project_status_color'] ) );
        if ( isset( li_cw_get_project_status_colors()[ $color ] ) ) {
            update_post_meta( $post_id, 'li_cw_project_status_color', $color );
        } else {
            delete_post_meta( $post_id, 'li_cw_project_status_color' );
        }
    }
}
add_action( 'save_post_project', 'li_cw_save_project_status' );

/**
 * 项目自定义字段说明（自由字段可继续用，如外部链接）
 */
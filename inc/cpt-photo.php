<?php
/**
 * 注册「照片 Photo」自定义文章类型
 * 独立管理照片，每张照片可写简介、开启评论
 * 附带友好的拍摄信息 Meta Box
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function li_cw_register_photo_cpt() {

    $labels = array(
        'name'               => esc_html_x( '照片', 'post type general name', 'li-cw' ),
        'singular_name'      => esc_html_x( '照片', 'post type singular name', 'li-cw' ),
        'add_new'            => esc_html_x( '新建照片', 'li-cw' ),
        'add_new_item'       => esc_html__( '添加新照片', 'li-cw' ),
        'edit_item'          => esc_html__( '编辑照片', 'li-cw' ),
        'new_item'           => esc_html__( '新照片', 'li-cw' ),
        'view_item'          => esc_html__( '查看照片', 'li-cw' ),
        'search_items'       => esc_html__( '搜索照片', 'li-cw' ),
        'not_found'          => esc_html__( '暂无照片', 'li-cw' ),
        'menu_name'          => esc_html__( '照片', 'li-cw' ),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'show_in_rest'        => true, // 支持古腾堡编辑器
        'has_archive'         => true,
        'menu_icon'           => 'dashicons-format-image',
        'menu_position'       => 7,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'comments' ),
        'rewrite'             => array( 'slug' => 'photos' ),
        'show_in_nav_menus'   => true,
    );

    register_post_type( 'photo', $args );
}
add_action( 'init', 'li_cw_register_photo_cpt' );

/**
 * 注册照片 Meta Box — 替换手写自定义字段
 */
function li_cw_add_photo_meta_box() {
    add_meta_box(
        'li_cw_photo_info',
        esc_html__( '拍摄信息', 'li-cw' ),
        'li_cw_render_photo_meta_box',
        'photo',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'li_cw_add_photo_meta_box' );

/**
 * 渲染 Meta Box 表单
 */
function li_cw_render_photo_meta_box( $post ) {
    wp_nonce_field( 'li_cw_photo_meta', 'li_cw_photo_nonce' );

    $photo_date    = get_post_meta( $post->ID, 'li_cw_photo_date', true );
    $photo_location = get_post_meta( $post->ID, 'li_cw_photo_location', true );
    $photo_camera  = get_post_meta( $post->ID, 'li_cw_photo_camera', true );
    ?>
    <p>
        <label for="li_cw_photo_date"><?php esc_html_e( '拍摄日期', 'li-cw' ); ?></label>
        <input type="date" id="li_cw_photo_date" name="li_cw_photo_date"
               value="<?php echo esc_attr( $photo_date ); ?>"
               style="width:100%;margin-top:4px;">
    </p>
    <p>
        <label for="li_cw_photo_location"><?php esc_html_e( '拍摄地点', 'li-cw' ); ?></label>
        <input type="text" id="li_cw_photo_location" name="li_cw_photo_location"
               value="<?php echo esc_attr( $photo_location ); ?>"
               placeholder="<?php esc_attr_e( '如：杭州西湖', 'li-cw' ); ?>"
               style="width:100%;margin-top:4px;">
    </p>
    <p>
        <label for="li_cw_photo_camera"><?php esc_html_e( '相机 / 器材', 'li-cw' ); ?></label>
        <input type="text" id="li_cw_photo_camera" name="li_cw_photo_camera"
               value="<?php echo esc_attr( $photo_camera ); ?>"
               placeholder="<?php esc_attr_e( '如：Sony A7M4 + 24-70mm f/2.8', 'li-cw' ); ?>"
               style="width:100%;margin-top:4px;">
    </p>
    <?php
}

/**
 * 保存 Meta Box 数据
 */
function li_cw_save_photo_meta( $post_id ) {
    // 跳过自动保存和修订版本
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;

    // 权限检查
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Nonce 验证
    if ( ! isset( $_POST['li_cw_photo_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['li_cw_photo_nonce'], 'li_cw_photo_meta' ) ) return;

    // 保存拍摄日期
    if ( isset( $_POST['li_cw_photo_date'] ) ) {
        update_post_meta( $post_id, 'li_cw_photo_date', sanitize_text_field( $_POST['li_cw_photo_date'] ) );
    }

    // 保存拍摄地点
    if ( isset( $_POST['li_cw_photo_location'] ) ) {
        update_post_meta( $post_id, 'li_cw_photo_location', sanitize_text_field( $_POST['li_cw_photo_location'] ) );
    }

    // 保存相机器材
    if ( isset( $_POST['li_cw_photo_camera'] ) ) {
        update_post_meta( $post_id, 'li_cw_photo_camera', sanitize_text_field( $_POST['li_cw_photo_camera'] ) );
    }
}
add_action( 'save_post', 'li_cw_save_photo_meta' );
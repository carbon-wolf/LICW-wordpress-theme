<?php
/**
 * Template Name: 照片墙
 * 展示全部照片（瀑布流布局）
 * 每篇文章中的每张图片都作为独立卡片展示
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<main class="site-main">
    <div class="container">
        <header class="page-header">
            <h1 class="section-title">
                <span><?php esc_html_e( '照片墙', 'li-cw' ); ?></span>
            </h1>
            <p class="page-subtitle">
                <?php esc_html_e( '用镜头记录下的瞬间。', 'li-cw' ); ?>
            </p>
            <?php
            // 照片总数 badge — 统计实际展示的图片张数（含缓存）
            $total_photos = function_exists( 'li_cw_count_gallery_images' )
                ? li_cw_count_gallery_images()
                : wp_count_posts( 'photo' )->publish;
            if ( $total_photos > 0 ) :
            ?>
            <div class="gallery-count-badge">
                <span class="gallery-count-num"><?php echo absint( $total_photos ); ?></span>
                <span class="gallery-count-label"><?php esc_html_e( '张照片', 'li-cw' ); ?></span>
            </div>
            <?php endif; ?>
        </header>

        <div class="photos-grid" id="masonry-grid">
            <div class="grid-sizer"></div>
            <div class="gutter-sizer"></div>

            <?php
            $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
            $photos_per_page = li_cw_get_option( 'li_cw_photos_per_page', 24 );
            $photos = new WP_Query( array(
                'post_type'      => 'photo',
                'posts_per_page' => $photos_per_page,
                'paged'          => $paged,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
            ));

            if ( $photos->have_posts() ) :
                $item_index = 0;
                while ( $photos->have_posts() ) :
                    $photos->the_post();

                    $post_id    = get_the_ID();
                    $post_url   = get_the_permalink();
                    $post_title = get_the_title();

                    // 照片卡片：收集所有图片
                    $all_images = array();

                    if ( has_post_thumbnail() ) {
                        $all_images[] = get_post_thumbnail_id( $post_id );
                    }

                    $content = get_the_content();
                    if ( $content ) {
                        if ( function_exists( 'parse_blocks' ) ) {
                            $blocks = parse_blocks( $content );
                            foreach ( $blocks as $block ) {
                                if ( $block['blockName'] === 'core/image' && ! empty( $block['attrs']['id'] ) ) {
                                    if ( ! in_array( $block['attrs']['id'], $all_images, true ) ) {
                                        $all_images[] = $block['attrs']['id'];
                                    }
                                }
                                if ( $block['blockName'] === 'core/gallery' && ! empty( $block['attrs']['ids'] ) ) {
                                    foreach ( $block['attrs']['ids'] as $gid ) {
                                        if ( ! in_array( $gid, $all_images, true ) ) {
                                            $all_images[] = $gid;
                                        }
                                    }
                                }
                            }
                        }

                        if ( preg_match_all( '/wp-image-(\d+)/', $content, $matches ) ) {
                            foreach ( $matches[1] as $mid ) {
                                $mid = absint( $mid );
                                if ( ! in_array( $mid, $all_images, true ) ) {
                                    $all_images[] = $mid;
                                }
                            }
                        }
                    }

                    // 元信息
                    $photo_date     = get_post_meta( $post_id, 'li_cw_photo_date', true );
                    $photo_location = get_post_meta( $post_id, 'li_cw_photo_location', true );
                    $photo_camera   = get_post_meta( $post_id, 'li_cw_photo_camera', true );

                    // 正文摘要（截短到 80 字）
                    $raw_excerpt = wp_strip_all_tags( $content );
                    $excerpt     = mb_substr( $raw_excerpt, 0, 80 );
                    if ( mb_strlen( $raw_excerpt ) > 80 ) {
                        $excerpt .= '…';
                    }

                    // 每张图片生成一个 masonry 卡片
                    foreach ( $all_images as $idx => $image_id ) {
                        $full_url = wp_get_attachment_image_url( $image_id, 'full' );

                        // 按图片宽高比分配 8 单位网格 — 每张图对号入座，卡片跟着图片大小走
                        $item_classes = 'masonry-item';
                        $img_meta = wp_get_attachment_metadata( $image_id );
                        if ( $img_meta && ! empty( $img_meta['width'] ) && ! empty( $img_meta['height'] ) ) {
                            $ratio = $img_meta['width'] / $img_meta['height'];
                            if ( $ratio >= 2.0 ) {
                                $units = 8;        // 全景 → 整行
                            } elseif ( $ratio >= 1.6 ) {
                                $units = 6;        // 宽横图 → 3/4
                            } elseif ( $ratio >= 1.35 ) {
                                $units = 5;        // 横图 → 5/8
                            } elseif ( $ratio >= 1.05 ) {
                                $units = 4;        // 微宽 → 半宽
                            } else {
                                $units = 2;        // 竖图/方图 → 1/4
                            }
                            $item_classes .= ' masonry-item--u' . $units;
                        }

            ?>
                        <div class="<?php echo esc_attr( $item_classes ); ?>" data-index="<?php echo absint( $item_index ); ?>" style="--item-index: <?php echo absint( $item_index ); ?>">
                            <article class="photo-card reveal">
                                <a href="<?php echo esc_url( $full_url ); ?>" data-lightbox="photo-wall" class="photo-link">
                                    <div class="photo-thumb">
                                        <div class="photo-skeleton"></div>
                                        <?php echo wp_get_attachment_image( $image_id, 'photo-wall', false, array( 'alt' => esc_attr( $post_title ) ) ); ?>
                                    </div>
                                    <div class="photo-overlay">
                                        <h3 class="photo-overlay-title"><?php echo esc_html( $post_title ); ?></h3>
                                        <div class="photo-overlay-meta">
                                            <?php if ( $photo_date ) : ?>
                                                <span class="photo-date"><?php echo esc_html( $photo_date ); ?></span>
                                            <?php endif; ?>
                                            <?php if ( $photo_location ) : ?>
                                                <span class="photo-location"><?php echo esc_html( $photo_location ); ?></span>
                                            <?php endif; ?>
                                            <?php if ( $photo_camera ) : ?>
                                                <span class="photo-camera"><?php echo esc_html( $photo_camera ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ( $excerpt ) : ?>
                                            <p class="photo-overlay-excerpt"><?php echo esc_html( $excerpt ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </article>
                        </div>
            <?php
                    } // end foreach image

                    $item_index++;

                    // 无图占位
                    if ( empty( $all_images ) ) {
            ?>
                        <div class="masonry-item" data-index="<?php echo absint( $item_index ); ?>" style="--item-index: <?php echo absint( $item_index ); ?>">
                            <article class="photo-card reveal">
                                <a href="<?php echo esc_url( $post_url ); ?>" class="photo-link">
                                    <div class="photo-thumb">
                                        <div class="photo-thumb-placeholder"></div>
                                    </div>
                                    <div class="photo-overlay">
                                        <h3 class="photo-overlay-title"><?php echo esc_html( $post_title ); ?></h3>
                                    </div>
                                </a>
                            </article>
                        </div>
            <?php
                    }

                endwhile;
                wp_reset_postdata();
            else :
                echo '<p class="photos-empty">' . esc_html__( '暂无照片', 'li-cw' ) . '</p>';
            endif;
            ?>
        </div>

        <div class="pagination">
            <?php
            echo paginate_links( array(
                'total'     => $photos->max_num_pages,
                'current'   => $paged,
                'prev_text' => '←',
                'next_text' => '→',
            ));
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>

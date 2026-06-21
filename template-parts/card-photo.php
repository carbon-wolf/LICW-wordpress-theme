<?php
/**
 * 照片卡片 - 照片墙页面复用
 * 必须在循环内调用
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$post_id        = get_the_ID();
$photo_date     = get_post_meta( $post_id, 'li_cw_photo_date', true );
$photo_location = get_post_meta( $post_id, 'li_cw_photo_location', true );
$photo_camera   = get_post_meta( $post_id, 'li_cw_photo_camera', true );

// 正文摘要，hover 时展示
$raw_text = wp_strip_all_tags( get_the_excerpt() ?: get_the_content() );
$excerpt  = mb_substr( $raw_text, 0, 230 );
if ( mb_strlen( $raw_text ) > 230 ) {
    $excerpt .= '...';
}
?>
<article class="photo-card reveal">
    <a href="<?php echo esc_url( get_the_permalink() ); ?>">
        <div class="photo-thumb">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'photo-thumb', array( 'alt' => the_title_attribute( 'echo=0' ) ) ); ?>
            <?php else : ?>
                <div class="photo-thumb-placeholder"></div>
            <?php endif; ?>
            <?php if ( $excerpt ) : ?>
                <div class="photo-excerpt">
                    <p><?php echo esc_html( $excerpt ); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <div class="photo-info">
            <h3 class="photo-title"><?php the_title(); ?></h3>
            <div class="photo-meta">
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
        </div>
    </a>
</article>
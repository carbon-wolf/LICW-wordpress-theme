<?php
/**
 * 说说卡片
 * 必须在循环内调用
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$mood = get_post_meta( get_the_ID(), 'li_cw_shuoshuo_mood', true );
?>
<article class="shuoshuo-card reveal">
    <header class="shuoshuo-card-meta">
        <time class="shuoshuo-card-date" datetime="<?php echo get_the_date( 'c' ); ?>">
            <?php echo get_the_date( 'Y/m/d' ); ?>
        </time>
        <?php if ( $mood ) : ?>
            <span class="shuoshuo-card-mood"><?php echo esc_html( $mood ); ?></span>
        <?php endif; ?>
    </header>

    <div class="shuoshuo-card-content">
        <?php the_content(); ?>
    </div>

    <div class="shuoshuo-card-actions">
        <button class="like-btn"
                data-post-id="<?php the_ID(); ?>"
                data-likes="<?php echo (int) get_post_meta( get_the_ID(), 'li_cw_shuoshuo_likes', true ); ?>"
                aria-label="<?php esc_attr_e( '点赞', 'li-cw' ); ?>">
            <svg class="like-icon" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            <span class="like-count"><?php echo (int) get_post_meta( get_the_ID(), 'li_cw_shuoshuo_likes', true ); ?></span>
        </button>
    </div>
</article>

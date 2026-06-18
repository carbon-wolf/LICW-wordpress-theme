<?php
/**
 * 日志卡片 - 首页与列表页复用
 * 必须在循环内调用
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = get_the_ID();
$tag = li_cw_get_blog_tag( $post_id );
?>
<article class="blog-card reveal">
    <div class="blog-card-date">
        <div><?php echo get_the_date( 'm/d' ); ?></div>
        <div class="year"><?php echo get_the_date( 'Y' ); ?></div>
    </div>

    <div class="blog-card-content">
        <?php if ( $tag ) : ?>
            <span class="blog-card-tag"><?php echo esc_html( $tag ); ?></span>
        <?php endif; ?>
        <h3>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>
        <p class="blog-card-excerpt">
            <?php echo wp_trim_words( get_the_excerpt(), 18 ); ?>
        </p>
    </div>

    <?php if ( has_post_thumbnail() ) : ?>
        <div class="blog-card-thumb">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'blog-card', array( 'alt' => the_title_attribute( 'echo=0' ) ) ); ?>
            </a>
        </div>
    <?php endif; ?>
</article>
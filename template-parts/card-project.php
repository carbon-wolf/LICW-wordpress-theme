<?php
/**
 * 作品卡片 - 首页与作品页复用
 * 必须在循环内调用
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = get_the_ID();
$status = li_cw_get_project_status( $post_id );
$cat = get_post_meta( $post_id, 'li_cw_project_cat', true );
$external_link = get_post_meta( $post_id, 'li_cw_project_link', true );
$link = $external_link ? $external_link : get_the_permalink();
?>
<article class="project-card">
    <a href="<?php echo esc_url( $link ); ?>" <?php if ( $external_link ) echo 'target="_blank" rel="noopener"'; ?>>
        <div class="project-thumb">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'project-thumb', array( 'alt' => the_title_attribute( 'echo=0' ) ) ); ?>
            <?php else : ?>
                <div style="width:100%;height:100%;background:var(--border-color);"></div>
            <?php endif; ?>
        </div>
        <div class="project-info">
            <span class="project-status"><?php echo esc_html( $status ); ?></span>
            <h3 class="project-title"><?php the_title(); ?></h3>
            <?php if ( $cat ) : ?>
                <p class="project-cat"><?php echo esc_html( $cat ); ?></p>
            <?php endif; ?>
        </div>
    </a>
</article>
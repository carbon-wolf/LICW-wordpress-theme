<?php
/**
 * 友链卡片（未开启文章抓取的分类使用）
 *
 * @var array $args {
 *     @type object $link 友链对象（get_bookmarks 返回）
 * }
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$link = isset( $args['link'] ) ? $args['link'] : null;
if ( ! $link ) {
    return;
}
?>
<a href="<?php echo esc_url( $link->link_url ); ?>"
   class="link-card reveal"
   target="_blank"
   rel="noopener noreferrer">
    <?php if ( $link->link_image ) : ?>
        <img src="<?php echo esc_url( $link->link_image ); ?>"
             alt="<?php echo esc_attr( $link->link_name ); ?>"
             class="link-avatar"
             loading="lazy">
    <?php else : ?>
        <div class="link-avatar link-avatar-text" aria-hidden="true">
            <?php echo esc_html( mb_substr( $link->link_name, 0, 1 ) ); ?>
        </div>
    <?php endif; ?>
    <div class="link-info">
        <div class="link-name"><?php echo esc_html( $link->link_name ); ?></div>
        <?php if ( $link->link_description ) : ?>
            <div class="link-desc"><?php echo esc_html( $link->link_description ); ?></div>
        <?php endif; ?>
    </div>
</a>

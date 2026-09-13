<?php
/**
 * 友链对话框
 * 一位朋友一个对话框：头像与站点名在气泡外，气泡内只放 TA 的最近文章。
 *
 * @var array $args {
 *     @type object $link       友链对象（get_bookmarks 返回）
 *     @type int    $feed_count 抓取文章数
 * }
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$link = isset( $args['link'] ) ? $args['link'] : null;
if ( ! $link ) {
    return;
}

$feed_count   = isset( $args['feed_count'] ) ? absint( $args['feed_count'] ) : 3;
$feed_enabled = li_cw_link_has_feed( $link );
// 复用调用方（page-links.php）已查过的缓存，避免同一 transient 读两次
$feed_items   = $feed_enabled && isset( $args['feed_items'] ) ? $args['feed_items'] : ( $feed_enabled ? li_cw_get_link_feed_items_cached( $link, $feed_count ) : null );
$show_date    = li_cw_get_option( 'li_cw_links_feed_date', true );
$is_pending   = $feed_enabled && ! is_array( $feed_items );
?>
<article class="link-bubble reveal">
    <a class="link-bubble-avatar"
       href="<?php echo esc_url( $link->link_url ); ?>"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="<?php echo esc_attr( $link->link_name ); ?>">
        <?php if ( $link->link_image ) : ?>
            <img src="<?php echo esc_url( $link->link_image ); ?>"
                 alt="<?php echo esc_attr( $link->link_name ); ?>"
                 loading="lazy">
        <?php else : ?>
            <span class="link-bubble-initial" aria-hidden="true"><?php echo esc_html( mb_substr( $link->link_name, 0, 1 ) ); ?></span>
        <?php endif; ?>
    </a>

    <div class="link-bubble-body">
        <div class="link-bubble-head">
            <a class="link-bubble-name"
               href="<?php echo esc_url( $link->link_url ); ?>"
               target="_blank"
               rel="noopener noreferrer"><?php echo esc_html( $link->link_name ); ?></a>
            <?php if ( $link->link_description ) : ?>
                <span class="link-bubble-desc"><?php echo esc_html( $link->link_description ); ?></span>
            <?php endif; ?>
        </div>

        <div class="link-bubble-msg<?php echo $is_pending ? ' is-pending' : ''; ?>"<?php
            if ( $is_pending ) :
                ?> data-li-cw-feed data-link-id="<?php echo esc_attr( $link->link_id ); ?>" data-count="<?php echo esc_attr( $feed_count ); ?>"<?php
            endif;
        ?>>
            <?php if ( $feed_enabled ) : ?>
                <?php if ( is_array( $feed_items ) && $feed_items ) : ?>
                    <ul class="link-bubble-feed">
                        <?php foreach ( $feed_items as $item ) : ?>
                            <li class="link-feed-item">
                                <a href="<?php echo esc_url( $item['link'] ); ?>" target="_blank" rel="noopener noreferrer">
                                    <span class="link-feed-dot" aria-hidden="true"></span>
                                    <span class="link-feed-title"><?php echo esc_html( $item['title'] ); ?></span>
                                    <?php if ( $show_date && ! empty( $item['date'] ) ) : ?>
                                        <time class="link-feed-date"
                                              datetime="<?php echo esc_attr( gmdate( 'Y-m-d', $item['date'] ) ); ?>"><?php echo esc_html( human_time_diff( $item['date'] ) ); ?><?php esc_html_e( '前', 'li-cw' ); ?></time>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php elseif ( $is_pending ) : ?>
                    <p class="link-bubble-empty is-pending"><?php esc_html_e( '正在收信…', 'li-cw' ); ?></p>
                <?php else : ?>
                    <p class="link-bubble-empty"><?php esc_html_e( '暂时没有收到 TA 的新文章。', 'li-cw' ); ?></p>
                <?php endif; ?>
            <?php else : ?>
                <p class="link-bubble-empty"><?php esc_html_e( '暂时没有收到 TA 的新文章。', 'li-cw' ); ?></p>
            <?php endif; ?>
        </div>
    </div>
</article>

<?php
/**
 * 内容内分页导航（<!--nextpage--> 触发）
 * 文章页与关于页共用同一套「继续阅读」样式。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $numpages, $page;

if ( $numpages > 1 ) :
?>
<nav class="post-pages" aria-label="<?php esc_attr_e( '内容分页', 'li-cw' ); ?>">
    <span class="post-pages-label"><?php esc_html_e( '继续阅读', 'li-cw' ); ?></span>
    <?php
    for ( $i = 1; $i <= $numpages; $i++ ) :
        if ( $i === (int) $page ) :
            // 当前页：实心主色高亮，不可点击
    ?>
    <span class="post-page-num is-current" aria-current="page"><?php echo esc_html( $i ); ?></span>
    <?php
        else :
            // _wp_link_page() 内部已转义，兼容各种固定链接结构
            echo _wp_link_page( $i );
    ?><?php echo esc_html( $i ); ?></a>
    <?php
        endif;
    endfor;
    ?>
</nav>
<?php
endif;

<?php
/**
 * Template Name: 说说页面
 * 按时间倒序展示所有说说
 */

if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main class="site-main">
    <div class="container">
        <header class="page-header">
            <h1 class="section-title">
                <span><?php esc_html_e( '说说', 'li-cw' ); ?></span>
            </h1>
            <p class="page-subtitle">
                <?php esc_html_e( '碎片、随想与日常。', 'li-cw' ); ?>
            </p>
        </header>

        <div class="shuoshuo-list">
            <?php
            $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
            $per_page = absint( li_cw_get_option( 'li_cw_shuoshuo_count', 15 ) );
            $first_shuoshuo_id = 0;

            $shuoshuo = new WP_Query( array(
                'post_type'      => 'shuoshuo',
                'posts_per_page' => $per_page,
                'paged'          => $paged,
                'post_status'    => 'publish',
            ));

            if ( $shuoshuo->have_posts() ) :
                while ( $shuoshuo->have_posts() ) :
                    $shuoshuo->the_post();
                    if ( ! $first_shuoshuo_id ) {
                        $first_shuoshuo_id = get_the_ID();
                    }
                    get_template_part( 'template-parts/card-shuoshuo' );
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p class="shuoshuo-empty">' . esc_html__( '还没有说说，先去看看别的吧。', 'li-cw' ) . '</p>';
            endif;
            ?>
        </div>

        <?php if ( $shuoshuo->max_num_pages > 1 ) : ?>
            <div class="pagination">
                <?php
                echo paginate_links( array(
                    'total'     => $shuoshuo->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '←',
                    'next_text' => '→',
                ));
                ?>
            </div>
        <?php endif; ?>

        <?php
        // 共享评论弹窗：临时以一条说说为上下文，让 comment_form 正常输出；
        // 实际评论对象由 JS 按点击的说说切换 comment_post_ID。
        if ( $first_shuoshuo_id ) :
            $GLOBALS['post'] = get_post( $first_shuoshuo_id );
            setup_postdata( $GLOBALS['post'] );
            get_template_part( 'template-parts/comment-modal' );
            wp_reset_postdata();
        endif;
        ?>
    </div>
</main>

<?php get_footer(); ?>

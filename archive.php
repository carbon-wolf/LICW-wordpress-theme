<?php
/**
 * 日志归档列表页
 */
get_header();
?>

<main class="site-main">
    <div class="container" style="padding-top: 40px;">
        <header class="archive-header" style="margin-bottom: 32px;">
            <h1 class="section-title">
                <span><?php esc_html_e( '日志', 'li-cw' ); ?></span>
            </h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: -16px; margin-bottom: 24px;">
                <?php esc_html_e( '记录开发、设计、写作与生活中的思考。', 'li-cw' ); ?>
            </p>
        </header>

        <div class="blog-list">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/card-blog' );
                endwhile;
            else :
                echo '<p>' . esc_html__( '暂无内容', 'li-cw' ) . '</p>';
            endif;
            ?>
        </div>

        <!-- 分页 -->
        <div class="pagination">
            <?php
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => '←',
                'next_text' => '→',
            ));
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
<?php
/**
 * 主索引模板（兜底文件）
 * WordPress 主题必备，作为所有页面的最终回退模板
 * @package Li_CW_Theme
 */
get_header();
?>

<main class="site-main">
    <div class="container" style="padding-top: 40px;">
        <?php
        if ( have_posts() ) :

            // 标题
            the_archive_title( '<h1 class="section-title"><span>', '</span></h1>' );
            ?>

            <div class="blog-list">
                <?php
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/card-blog' );
                endwhile;
                ?>
            </div>

            <div class="pagination">
                <?php
                the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '←',
                    'next_text' => '→',
                ));
                ?>
            </div>

        <?php else : ?>

            <div style="text-align: center; padding: 80px 0;">
                <h2 style="font-family: var(--font-serif); margin-bottom: 12px;">
                    <?php esc_html_e( '未找到内容', 'li-cw' ); ?>
                </h2>
                <p style="color: var(--text-secondary);">
                    <?php esc_html_e( '抱歉，没有找到您要的内容。', 'li-cw' ); ?>
                </p>
                <p style="margin-top: 24px;">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary">
                        <?php esc_html_e( '返回首页', 'li-cw' ); ?>
                    </a>
                </p>
            </div>

        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
<?php
/**
 * 搜索结果模板
 * @package Li_CW_Theme
 */
get_header();
?>

<main class="site-main">
    <div class="container" style="padding-top: 40px;">
        <header class="archive-header" style="margin-bottom: 32px;">
            <h1 class="section-title">
                <span>
                    <?php
                    /* translators: %s: 搜索关键词 */
                    printf( esc_html__( '搜索结果：%s', 'li-cw' ), '<span style="color:var(--text-secondary);font-weight:normal;">' . get_search_query() . '</span>' );
                    ?>
                </span>
            </h1>
        </header>

        <?php if ( have_posts() ) : ?>
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
            <p style="text-align: center; padding: 60px 0; color: var(--text-secondary);">
                <?php esc_html_e( '没有找到匹配的结果。', 'li-cw' ); ?>
            </p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
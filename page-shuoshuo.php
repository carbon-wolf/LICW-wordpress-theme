<?php
/**
 * Template Name: 说说页面
 * 按时间倒序展示所有说说
 */
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

            $shuoshuo = new WP_Query( array(
                'post_type'      => 'shuoshuo',
                'posts_per_page' => $per_page,
                'paged'          => $paged,
                'post_status'    => 'publish',
            ));

            if ( $shuoshuo->have_posts() ) :
                while ( $shuoshuo->have_posts() ) :
                    $shuoshuo->the_post();
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
    </div>
</main>

<?php get_footer(); ?>

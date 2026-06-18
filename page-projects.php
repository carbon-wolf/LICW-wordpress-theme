<?php
/**
 * Template Name: 作品列表页
 * 展示全部作品
 */
get_header();
?>

<main class="site-main">
    <div class="container" style="padding-top: 40px;">
        <header class="page-header" style="margin-bottom: 32px;">
            <h1 class="section-title">
                <span><?php esc_html_e( '作品', 'li-cw' ); ?></span>
            </h1>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: -16px; margin-bottom: 24px;">
                <?php esc_html_e( '一些正在进行或完成的项目。', 'li-cw' ); ?>
            </p>
        </header>

        <div class="projects-grid">
            <?php
            $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
            $projects = new WP_Query( array(
                'post_type'      => 'project',
                'posts_per_page' => 9,
                'paged'          => $paged,
                'post_status'    => 'publish',
            ));

            if ( $projects->have_posts() ) :
                while ( $projects->have_posts() ) :
                    $projects->the_post();
                    get_template_part( 'template-parts/card-project' );
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p>' . esc_html__( '暂无作品', 'li-cw' ) . '</p>';
            endif;
            ?>
        </div>

        <!-- 分页 -->
        <div class="pagination">
            <?php
            echo paginate_links( array(
                'total'   => $projects->max_num_pages,
                'current' => $paged,
                'prev_text' => '←',
                'next_text' => '→',
            ));
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
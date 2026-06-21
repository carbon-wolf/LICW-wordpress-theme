<?php
/**
 * Template Name: 照片墙
 * 展示全部照片
 */
get_header();
?>

<main class="site-main">
    <div class="container">
        <header class="page-header">
            <h1 class="section-title">
                <span><?php esc_html_e( '照片墙', 'li-cw' ); ?></span>
            </h1>
            <p class="page-subtitle">
                <?php esc_html_e( '用镜头记录下的瞬间。', 'li-cw' ); ?>
            </p>
        </header>

        <div class="photos-grid">
            <?php
            $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
            $photos = new WP_Query( array(
                'post_type'      => 'photo',
                'posts_per_page' => li_cw_get_option( 'li_cw_photos_per_page', 12 ),
                'paged'          => $paged,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
            ));

            if ( $photos->have_posts() ) :
                while ( $photos->have_posts() ) :
                    $photos->the_post();
                    get_template_part( 'template-parts/card-photo' );
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p class="photos-empty">' . esc_html__( '暂无照片', 'li-cw' ) . '</p>';
            endif;
            ?>
        </div>

        <!-- 分页 -->
        <div class="pagination">
            <?php
            echo paginate_links( array(
                'total'     => $photos->max_num_pages,
                'current'   => $paged,
                'prev_text' => '←',
                'next_text' => '→',
            ));
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
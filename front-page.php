<?php
/**
 * 首页模板
 * 包含 Hero、最新日志、精选作品
 */
get_header();
?>

<main class="site-main">
    <div class="container">
        <?php get_template_part( 'template-parts/hero-home' ); ?>
                <!-- 最新日志区块 - 有内容才显示 -->
        <?php
        $post_count = li_cw_get_option( 'li_cw_home_post_count', 3 );
        $latest_posts = new WP_Query( array(
            'post_type'      => 'post',
            'posts_per_page' => absint( $post_count ),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        ));

        if ( $latest_posts->have_posts() ) :
        ?>
        <section class="home-latest-posts" id="latest-posts">
            <h2 class="section-title">
                <span><?php esc_html_e( '最新日志', 'li-cw' ); ?></span>
                <?php
                // 读取后台文章页配置，无配置则返回首页
                $blog_page = get_option('page_for_posts');
                $blog_url = $blog_page ? get_permalink($blog_page) : home_url('/');
                ?>
                <a href="<?php echo esc_url( $blog_url ); ?>" class="more-link">
                    <?php esc_html_e( '查看所有日志 →', 'li-cw' ); ?>
                </a>
            </h2>

            <div class="blog-list">
                <?php
                while ( $latest_posts->have_posts() ) :
                    $latest_posts->the_post();
                    get_template_part( 'template-parts/card-blog' );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- 精选作品区块 - 有内容才显示 -->
        <?php
        $project_count = li_cw_get_option( 'li_cw_home_project_count', 3 );
        $projects = new WP_Query( array(
            'post_type'      => 'project',
            'posts_per_page' => absint( $project_count ),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        ));

        if ( $projects->have_posts() ) :
        ?>
        <section class="home-featured-projects" id="projects" style="margin-top: 60px;">
            <h2 class="section-title">
                <span><?php esc_html_e( '精选作品', 'li-cw' ); ?></span>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>" class="more-link">
                    <?php esc_html_e( '查看所有作品 →', 'li-cw' ); ?>
                </a>
            </h2>

            <div class="projects-grid">
                <?php
                while ( $projects->have_posts() ) :
                    $projects->the_post();
                    get_template_part( 'template-parts/card-project' );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
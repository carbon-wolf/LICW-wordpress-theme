<?php
/**
 * 单篇文章模板
 */
get_header();
?>

<main class="site-main">
    <div class="container">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
        ?>

        <article class="single-post">
            <!-- 文章头部 -->
            <header class="single-header reveal">
                <div class="single-meta">
                    <?php echo get_the_date(); ?>
                    <?php
                    $tag = li_cw_get_blog_tag( get_the_ID() );
                    if ( $tag ) echo ' · ' . esc_html( $tag );
                    $tags = get_the_tags();
                    if ( $tags ) {
                        foreach ( $tags as $tag_obj ) {
                            echo ' · <a href="' . esc_url( get_tag_link( $tag_obj->term_id ) ) . '" class="single-cat-link">' . esc_html( $tag_obj->name ) . '</a>';
                        }
                    }
                    ?>
                </div>

                <h1 class="single-title"><?php the_title(); ?></h1>
            </header>

            <!-- 特色图 -->
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'single-hero', array( 'class' => 'single-hero-img', 'alt' => the_title_attribute( 'echo=0' ) ) ); ?>
            <?php endif; ?>

            <div class="single-body">
                <!-- 文章目录 -->
                <?php echo li_cw_get_toc(); ?>

                <!-- 正文内容 -->
                <div class="single-content">
                <?php the_content(); ?>

                <?php
                wp_link_pages( array(
                    'before' => '<div class="page-links">' . esc_html__( '分页：', 'li-cw' ),
                    'after'  => '</div>',
                ));
                ?>
            </div>
            </div><!-- .single-body -->
        </article>
                <!-- 上一篇 / 下一篇导航 -->
        <div class="post-navigation">
            <div class="nav-prev">
                <?php
                $prev_post = get_previous_post();
                if ( $prev_post ) :
                ?>
                    <span class="nav-label"><?php esc_html_e( '上一篇', 'li-cw' ); ?></span>
                    <a href="<?php echo get_permalink( $prev_post->ID ); ?>" 
                       class="nav-title" 
                       title="<?php echo esc_attr( $prev_post->post_title ); ?>">
                        <?php echo esc_html( $prev_post->post_title ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="nav-next">
                <?php
                $next_post = get_next_post();
                if ( $next_post ) :
                ?>
                    <span class="nav-label"><?php esc_html_e( '下一篇', 'li-cw' ); ?></span>
                    <a href="<?php echo get_permalink( $next_post->ID ); ?>" 
                       class="nav-title" 
                       title="<?php echo esc_attr( $next_post->post_title ); ?>">
                        <?php echo esc_html( $next_post->post_title ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif;
        ?>
        <?php
            endwhile;
        endif;
        ?>
    </div>
</main>

<?php get_footer(); ?>
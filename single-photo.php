<?php
/**
 * 单张照片模板
 * 显示完整照片、拍摄信息、简介与评论区
 */
get_header();
?>

<main class="site-main">
    <div class="container">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();

                $photo_date     = get_post_meta( get_the_ID(), 'li_cw_photo_date', true );
                $photo_location = get_post_meta( get_the_ID(), 'li_cw_photo_location', true );
                $photo_camera   = get_post_meta( get_the_ID(), 'li_cw_photo_camera', true );
        ?>

        <article class="single-photo">
            <!-- 照片头部 -->
            <header class="single-header reveal">

                <h1 class="single-title"><?php the_title(); ?></h1>

                <?php if ( $photo_date || $photo_location || $photo_camera ) : ?>
                <div class="photo-tags">
                    <?php if ( $photo_date ) : ?>
                        <span class="photo-tag"><?php echo esc_html( $photo_date ); ?></span>
                    <?php endif; ?>
                    <?php if ( $photo_location ) : ?>
                        <span class="photo-tag"><?php echo esc_html( $photo_location ); ?></span>
                    <?php endif; ?>
                    <?php if ( $photo_camera ) : ?>
                        <span class="photo-tag"><?php echo esc_html( $photo_camera ); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </header>

            <div class="single-body">
                <!-- 照片简介 -->
                <div class="single-content">
                <?php the_content(); ?>
                </div>
            </div>
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
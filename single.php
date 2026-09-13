<?php
/**
 * 单篇文章模板
 */

if ( ! defined( 'ABSPATH' ) ) exit;
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
                    $reading_time = li_cw_get_reading_time();
                    if ( $reading_time ) {
                        echo ' · ' . esc_html( $reading_time ) . esc_html__( ' 分钟', 'li-cw' );
                    }
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

                <h1 class="single-title"><?php echo esc_html( get_the_title() ); ?></h1>
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

                <?php get_template_part( 'template-parts/post-pages' ); ?>
            </div>
            </div><!-- .single-body -->
        </article>
        <!-- 上一篇 / 下一篇导航 -->
        <div class="post-navigation">
            <?php
            // 上下篇结构一致，统一渲染（WordPress 原生 adjacent post）
            $adjacent = array(
                array( 'dir' => 'prev', 'post' => get_previous_post(), 'label' => __( '上一篇', 'li-cw' ) ),
                array( 'dir' => 'next', 'post' => get_next_post(), 'label' => __( '下一篇', 'li-cw' ) ),
            );
            foreach ( $adjacent as $item ) :
                if ( ! $item['post'] ) {
                    continue;
                }
            ?>
            <div class="nav-<?php echo esc_attr( $item['dir'] ); ?>">
                <span class="nav-label"><?php echo esc_html( $item['label'] ); ?></span>
                <a href="<?php echo esc_url( get_permalink( $item['post']->ID ) ); ?>"
                   class="nav-title"
                   title="<?php echo esc_attr( $item['post']->post_title ); ?>">
                    <?php echo esc_html( $item['post']->post_title ); ?>
                </a>
            </div>
            <?php endforeach; ?>
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
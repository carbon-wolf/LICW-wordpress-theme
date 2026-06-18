<?php
/**
 * 默认页面模板
 * 普通页面通用
 */
get_header();
?>

<main class="site-main">
    <div class="container" style="padding-top: 40px;">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
        ?>
        <article class="single-content">
            <header class="single-header" style="padding: 20px 0 30px;">
                <h1 class="single-title"><?php the_title(); ?></h1>
            </header>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
        <?php
            endwhile;
        endif;
        ?>
    </div>
</main>

<?php get_footer(); ?>
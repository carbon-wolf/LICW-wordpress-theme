<?php
/**
 * Template Name: 友链页面
 */
get_header();
?>

<main class="site-main">
    <div class="container">
        <header class="page-header">
            <h1 class="section-title">
                <span><?php the_title(); ?></span>
            </h1>
            <p class="page-subtitle">
                <?php echo esc_html( li_cw_get_option( 'li_cw_links_subtitle', __( '友人、工具、常去的地方。', 'li-cw' ) ) ); ?>
            </p>
        </header>

        <?php if ( get_the_content() ) : ?>
            <div class="links-intro">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>

        <?php
        $feed_count = max( 1, min( 10, absint( li_cw_get_option( 'li_cw_links_feed_count', 3 ) ) ) );

        // 获取所有链接分类（WordPress 原生 link_category）
        $link_cats = get_terms( array(
            'taxonomy'   => 'link_category',
            'orderby'    => 'slug',
            'order'      => 'ASC',
            'hide_empty' => true,
        ) );

        if ( ! empty( $link_cats ) && ! is_wp_error( $link_cats ) ) :
            foreach ( $link_cats as $cat ) :
                $bookmarks = get_bookmarks( array(
                    'category'       => $cat->term_id,
                    'orderby'        => 'name',
                    'order'          => 'ASC',
                    'show_description' => 1,
                ) );
                if ( ! $bookmarks ) continue;
                $feed_enabled = li_cw_link_category_has_feed( $cat->term_id );
        ?>
            <h2 class="links-category-title"><?php echo esc_html( $cat->name ); ?></h2>
                <?php if ( ! empty( $cat->description ) ) : ?>
                    <p class="links-category-desc"><?php echo esc_html( $cat->description ); ?></p>
                <?php endif; ?>
        <?php
                if ( $feed_enabled ) :
                    // 开启抓取：有内容或待抓取 → 气泡；已确认抓取失败 → 卡片，排在气泡之后
                    $bubbles        = array();
                    $fallback_cards = array();
                    foreach ( $bookmarks as $link ) {
                        $items = li_cw_get_link_feed_items_cached( $link, $feed_count );
                        if ( is_array( $items ) && ! $items ) {
                            $fallback_cards[] = $link;
                        } else {
                            $bubbles[] = $link;
                        }
                    }

                    if ( $bubbles ) :
            ?>
            <div class="links-dialogue">
            <?php
                    foreach ( $bubbles as $link ) {
                        get_template_part( 'template-parts/link-bubble', null, array(
                            'link'       => $link,
                            'feed_count' => $feed_count,
                        ) );
                    }
            ?>
            </div>
            <?php
                    endif;

                    if ( $fallback_cards ) :
            ?>
            <div class="links-grid links-fallback">
            <?php
                    foreach ( $fallback_cards as $link ) {
                        get_template_part( 'template-parts/link-card', null, array(
                            'link' => $link,
                        ) );
                    }
            ?>
            </div>
            <?php
                    endif;
                else :
            ?>
            <div class="links-grid">
            <?php
                    foreach ( $bookmarks as $link ) {
                        get_template_part( 'template-parts/link-card', null, array(
                            'link' => $link,
                        ) );
                    }
            ?>
            </div>
        <?php
                endif;
            endforeach;
        else :
            // 回退：无分类时显示全部链接（默认卡片）
            $bookmarks = get_bookmarks( array(
                'orderby'        => 'name',
                'order'          => 'ASC',
                'show_description' => 1,
            ) );
        ?>
            <div class="links-grid">
            <?php if ( $bookmarks ) :
                foreach ( $bookmarks as $link ) :
                    get_template_part( 'template-parts/link-card', null, array(
                        'link' => $link,
                    ) );
                endforeach;
            else :
                echo '<p class="links-empty">' . esc_html__( '暂无友链', 'li-cw' ) . '</p>';
            endif;
            ?>
            </div>
        <?php endif; ?>

        <?php
        if ( li_cw_get_option( 'li_cw_links_comments', false ) ) :
            global $post;
            $orig_status = $post->comment_status;
            $post->comment_status = 'open';
            add_filter( 'comments_open', '__return_true' );
            comments_template();
            remove_filter( 'comments_open', '__return_true' );
            $post->comment_status = $orig_status;
        endif;
        ?>
    </div>
</main>

<?php get_footer(); ?>

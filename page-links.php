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
        ?>
            <h2 class="links-category-title"><?php echo esc_html( $cat->name ); ?></h2>
                <?php if ( ! empty( $cat->description ) ) : ?>
                    <p class="links-category-desc"><?php echo esc_html( $cat->description ); ?></p>
                <?php endif; ?>
            <div class="links-grid">
            <?php foreach ( $bookmarks as $link ) : ?>
                <a href="<?php echo esc_url( $link->link_url ); ?>"
                   class="link-card"
                   target="_blank"
                   rel="noopener noreferrer">
                    <?php if ( $link->link_image ) : ?>
                        <img src="<?php echo esc_url( $link->link_image ); ?>" alt="<?php echo esc_attr( $link->link_name ); ?>" class="link-avatar">
                    <?php else : ?>
                        <div class="link-avatar" style="background: var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); font-family: var(--font-heading); font-weight: 600;">
                            <?php echo mb_substr( $link->link_name, 0, 1 ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="link-info">
                        <div class="link-name"><?php echo esc_html( $link->link_name ); ?></div>
                        <?php if ( $link->link_description ) : ?>
                            <div class="link-desc"><?php echo esc_html( $link->link_description ); ?></div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
            </div>
        <?php
            endforeach;
        else :
            // 回退：无分类时显示全部链接
            $bookmarks = get_bookmarks( array(
                'orderby'        => 'name',
                'order'          => 'ASC',
                'show_description' => 1,
            ) );
        ?>
            <div class="links-grid">
            <?php if ( $bookmarks ) :
                foreach ( $bookmarks as $link ) :
            ?>
                <a href="<?php echo esc_url( $link->link_url ); ?>"
                   class="link-card"
                   target="_blank"
                   rel="noopener noreferrer">
                    <?php if ( $link->link_image ) : ?>
                        <img src="<?php echo esc_url( $link->link_image ); ?>" alt="<?php echo esc_attr( $link->link_name ); ?>" class="link-avatar">
                    <?php else : ?>
                        <div class="link-avatar" style="background: var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); font-family: var(--font-heading); font-weight: 600;">
                            <?php echo mb_substr( $link->link_name, 0, 1 ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="link-info">
                        <div class="link-name"><?php echo esc_html( $link->link_name ); ?></div>
                        <?php if ( $link->link_description ) : ?>
                            <div class="link-desc"><?php echo esc_html( $link->link_description ); ?></div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php
                endforeach;
            else :
                echo '<p style="text-align:center; color:var(--text-secondary); grid-column:1/-1; padding:40px 0;">暂无友链</p>';
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

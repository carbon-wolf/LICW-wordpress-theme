<?php
/**
 * Template Name: 友链页面
 * 调用WordPress原生链接管理器，无限添加，后台直接管理
 */
get_header();
?>

<main class="site-main">
    <div class="container links-wrap">
        <header class="page-header">
            <h1 class="section-title">
                <span><?php the_title(); ?></span>
            </h1>
            <?php if ( get_the_content() ) : ?>
                <div style="color: var(--text-secondary); margin-top: -12px; margin-bottom: 24px;">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="links-grid">
            <?php
            // 获取所有已添加的友情链接
            $bookmarks = get_bookmarks(array(
                'orderby'        => 'name',
                'order'          => 'ASC',
                'category_name'  => '', // 可指定分类，留空显示全部
                'show_updated'   => 0,
                'show_description' => 1,
            ));

            if ( $bookmarks ) :
                foreach ( $bookmarks as $link ) :
            ?>
                <a href="<?php echo esc_url( $link->link_url ); ?>" 
                   class="link-card" 
                   target="_blank" 
                   rel="noopener noreferrer">
                    <?php if ( $link->link_image ) : ?>
                        <img src="<?php echo esc_url( $link->link_image ); ?>" alt="<?php echo esc_attr( $link->link_name ); ?>" class="link-avatar">
                    <?php else : ?>
                        <div class="link-avatar" style="background: var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); font-family: var(--font-display); font-weight: 600;">
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
    </div>
</main>

<?php get_footer(); ?>
<?php
/**
 * 404 错误页面模板
 * @package Li_CW_Theme
 */
get_header();
?>

<main class="site-main">
    <div class="container">
        <div style="text-align: center; padding: 120px 0;">
            <p style="color: var(--accent-gold); letter-spacing: 0.2em; margin-bottom: 16px;">404 ERROR</p>
            <h1 style="font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 16px;">
                <?php esc_html_e( '页面走丢了', 'li-cw' ); ?>
            </h1>
            <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto 32px;">
                <?php esc_html_e( '您访问的页面不存在或已被移除。', 'li-cw' ); ?>
            </p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary">
                <?php esc_html_e( '回到首页', 'li-cw' ); ?>
            </a>
        </div>
    </div>
</main>

<?php get_footer(); ?>
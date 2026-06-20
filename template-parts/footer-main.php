<?php
/**
 * 页脚
 * 左右分组：站点归属 | 访客链接
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$beian = li_cw_get_option( 'li_cw_beian' );
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <!-- 左组：站点归属信息 -->
            <div class="footer-group">
                <span class="footer-copyright"><?php echo esc_html( li_cw_get_option( 'li_cw_footer_copyright', '© 2026 Li CW. All rights reserved.' ) ); ?></span>
                <?php if ( $beian ) : ?>
                    <span class="footer-beian">
                        <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html( $beian ); ?>
                        </a>
                    </span>
                <?php endif; ?>
                <?php if ( li_cw_get_option( 'li_cw_footer_credit', true ) ) : ?>
                    <span class="footer-credit">
                        <a href="https://github.com/carbon-wolf/LICW-wordpress-theme" target="_blank" rel="noopener noreferrer">由 Li CW 主题 驱动</a>
                    </span>
                <?php endif; ?>
            </div>

            <!-- 右组：访客链接 -->
            <div class="footer-group">
                <?php
                if ( has_nav_menu( 'footer-nav' ) ) {
                    ?>
                    <nav class="footer-nav">
                        <?php
                        wp_nav_menu( array(
                            'theme_location' => 'footer-nav',
                            'container'      => false,
                            'menu_class'     => '',
                            'fallback_cb'    => false,
                            'depth'          => 1,
                        ));
                        ?>
                    </nav>
                    <?php
                } else {
                    $privacy = li_cw_get_option( 'li_cw_footer_privacy_link' );
                    $terms   = li_cw_get_option( 'li_cw_footer_terms_link' );
                    if ( $privacy || $terms ) {
                        echo '<nav class="footer-nav"><ul>';
                        if ( $privacy ) {
                            printf( '<li><a href="%s">%s</a></li>',
                                esc_url( $privacy ),
                                esc_html__( '隐私政策', 'li-cw' )
                            );
                        }
                        if ( $terms ) {
                            printf( '<li><a href="%s">%s</a></li>',
                                esc_url( $terms ),
                                esc_html__( '使用条款', 'li-cw' )
                            );
                        }
                        echo '</ul></nav>';
                    }
                }
                ?>
                <a href="#" class="back-to-top"><?php esc_html_e( '回到顶部 ↑', 'li-cw' ); ?></a>
            </div>
        </div>

        <?php
        $custom_html = li_cw_get_option( 'li_cw_footer_custom_html' );
        if ( $custom_html ) :
        ?>
            <div class="footer-custom-html">
                <?php echo wp_kses_post( $custom_html ); ?>
            </div>
        <?php endif; ?>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

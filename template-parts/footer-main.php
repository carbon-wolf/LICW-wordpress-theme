<?php
/**
 * 通用页脚
 * 新增：备案号输出位置
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$beian = li_cw_get_option( 'li_cw_beian' );
?>
<footer class="site-footer">
    <div class="container footer-inner">
        <div class="copyright">
            <?php echo esc_html( li_cw_get_option( 'li_cw_footer_copyright', '© 2026 Li CW. All rights reserved.' ) ); ?>
            <?php if ( $beian ) : ?>
                <span class="footer-beian" style="margin-left: 16px;">
                    <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer">
                        <?php echo esc_html( $beian ); ?>
                    </a>
                </span>
            <?php endif; ?>
        </div>
        <div class="footer-links">
            <?php
            $privacy = li_cw_get_option( 'li_cw_footer_privacy_link' );
            $terms = li_cw_get_option( 'li_cw_footer_terms_link' );
            if ( $privacy ) : ?>
                <a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( '隐私政策', 'li-cw' ); ?></a>
            <?php endif; ?>
            <?php if ( $terms ) : ?>
                <a href="<?php echo esc_url( $terms ); ?>"><?php esc_html_e( '使用条款', 'li-cw' ); ?></a>
            <?php endif; ?>
            <a href="#" class="back-to-top"><?php esc_html_e( '回到顶部 ↑', 'li-cw' ); ?></a>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
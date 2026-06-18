<?php
/**
 * 通用顶部导航栏
 * 新增：搜索弹窗、修复暗色模式按钮
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<header class="site-header">
    <div class="container header-inner">
        <!-- 站点Logo -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" style="font-family: var(--font-display);">
            <?php bloginfo( 'name' ); ?>
        </a>

        <!-- 主导航 -->
        <nav class="main-nav">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'main-nav',
                'container'      => false,
                'menu_class'     => '',
                'fallback_cb'    => false,
                'depth'          => 1,
            ));
            ?>
        </nav>

        <!-- 右侧操作区 -->
        <div class="header-actions">
            <button class="search-toggle" aria-label="<?php esc_attr_e( '搜索', 'li-cw' ); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>
            <button class="dark-toggle" aria-label="<?php esc_attr_e( '切换暗色模式', 'li-cw' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
            </button>
        </div>
    </div>
</header>

<!-- 搜索弹窗 -->
<div class="search-modal" id="searchModal">
    <div class="container">
        <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <input type="search" name="s" placeholder="<?php esc_attr_e( '输入关键词搜索...', 'li-cw' ); ?>" required>
        </form>
        <button class="search-close" aria-label="<?php esc_attr_e( '关闭搜索', 'li-cw' ); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
</div>
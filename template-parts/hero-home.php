<?php
/**
 * 首页欢迎 Hero 区块
 * 修复：主图显示逻辑，适配自定义器URL存储
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$welcome  = li_cw_get_option( 'li_cw_home_welcome', 'WELCOME' );
$title    = li_cw_get_option( 'li_cw_home_title', 'Li CW' );
$subtitle = li_cw_get_option( 'li_cw_home_subtitle', '高中生开发者 / 写作者' );
$desc     = li_cw_get_option( 'li_cw_home_desc', '正在制作《凭君之光》，记录创作、设计与思考。' );
$btn1_text = li_cw_get_option( 'li_cw_home_btn1_text', '查看作品' );
$btn1_link = li_cw_get_option( 'li_cw_home_btn1_link', get_post_type_archive_link( 'project' ) );
$btn2_text = li_cw_get_option( 'li_cw_home_btn2_text', '关于我' );
// 默认关于页链接：自动识别slug为about的页面
$about_page = get_page_by_path( 'about' );
$default_about_link = $about_page ? get_permalink( $about_page ) : '#';
$btn2_link = li_cw_get_option( 'li_cw_home_btn2_link', $default_about_link );
$hero_img = li_cw_get_option( 'li_cw_home_hero_image' );
?>

<section class="home-hero">
    <div class="hero-text">
        <?php if ( $welcome ) : ?>
            <p class="hero-welcome">◆ <?php echo esc_html( $welcome ); ?></p>
        <?php endif; ?>

        <h1 class="hero-title"><?php echo esc_html( $title ); ?></h1>
        <p class="hero-subtitle"><?php echo esc_html( $subtitle ); ?></p>
        <p class="hero-desc"><?php echo esc_html( $desc ); ?></p>

        <div class="hero-buttons">
            <?php if ( $btn1_text ) : ?>
                <a href="<?php echo esc_url( $btn1_link ); ?>" class="btn-primary">
                    <?php echo esc_html( $btn1_text ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $btn2_text ) : ?>
                <a href="<?php echo esc_url( $btn2_link ? $btn2_link : $default_about_link ); ?>" class="btn-text">
                    <?php echo esc_html( $btn2_text ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( $hero_img ) : ?>
        <div class="hero-image">
            <img src="<?php echo esc_url( $hero_img ); ?>" alt="<?php echo esc_attr( $title ); ?>" 
                 style="width: 100%; height: auto; max-height: 500px; object-fit: cover; border-radius: var(--radius);">
        </div>
    <?php endif; ?>
</section>
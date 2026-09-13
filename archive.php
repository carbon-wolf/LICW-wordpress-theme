<?php
/**
 * 归档列表页（分类/标签/日期/作者/CPT 归档）
 * 原生 the_archive_title() 标题，按文章类型分派卡片模板
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<main class="site-main">
    <div class="container" style="padding-top: 40px;">
        <header class="archive-header" style="margin-bottom: 32px;">
            <h1 class="section-title">
                <span><?php the_archive_title(); ?></span>
            </h1>
            <?php
            $archive_desc = '';
            if ( is_category() || is_tag() || is_tax() ) {
                // 分类/标签描述（已过核心过滤器，archive_description 自带 wp_kses）
                $archive_desc = get_the_archive_description();
            }
            if ( $archive_desc ) :
                ?>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-top: -16px; margin-bottom: 24px;">
                    <?php echo $archive_desc; // phpcs:ignore WordPress.Security.EscapeOutput -- get_the_archive_description() 已过 wp_kses ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="blog-list">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) :
                    the_post();

                    // 按文章类型分派卡片模板
                    $card = 'card-blog';
                    if ( 'project' === get_post_type() ) {
                        $card = 'card-project';
                    } elseif ( 'photo' === get_post_type() ) {
                        $card = 'card-photo';
                    } elseif ( 'shuoshuo' === get_post_type() ) {
                        $card = 'card-shuoshuo';
                    }

                    get_template_part( 'template-parts/' . $card );
                endwhile;
            else :
                echo '<p>' . esc_html__( '暂无内容', 'li-cw' ) . '</p>';
            endif;
            ?>
        </div>

        <!-- 分页 -->
        <div class="pagination">
            <?php
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => '←',
                'next_text' => '→',
            ));
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>

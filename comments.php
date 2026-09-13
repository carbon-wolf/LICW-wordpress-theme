<?php
/**
 * 精简版评论模板
 * 评论列表极简展示，表单点击弹出
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( post_password_required() ) return;
?>

<div class="comments-area" id="comments">

        <!-- 发表评论按钮 -->
        <?php if ( comments_open() ) : ?>
            <div style="text-align: center; margin-top: 24px;">
                <button class="btn-primary" id="openCommentForm">
                    <?php esc_html_e( '发表评论', 'li-cw' ); ?>
                </button>
            </div>
        <?php else : ?>
            <p style="text-align:center; color:var(--text-secondary); margin-top:24px; font-size:0.9rem;">
                <?php esc_html_e( '评论已关闭', 'li-cw' ); ?>
            </p>
        <?php endif; ?>

    <!-- 评论列表 -->
    <?php if ( have_comments() ) : ?>
        <h3 class="comments-title">
            <?php
            $comment_count = get_comments_number();
            printf( esc_html( _n( '1 条评论', '%s 条评论', $comment_count, 'li-cw' ) ), (int) $comment_count );
            ?>
        </h3>
        <ol class="comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 32,
                'type'        => 'comment',
                'callback'    => 'li_cw_simple_comment', // 自定义精简评论项
            ));
            ?>
        </ol>

        <!-- 评论分页 -->
        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
            <div class="comment-pagination" style="text-align:center; margin-top:16px; font-family:var(--font-ui); font-size:0.85rem;">
                <?php paginate_comments_links( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <?php if ( comments_open() ) : ?>
            <p style="text-align:center; color:var(--text-secondary); margin-top:8px; font-size:0.9rem;">
                <?php esc_html_e( '还没有评论，来说两句吧', 'li-cw' ); ?>
            </p>
        <?php endif; ?>
    <?php endif; ?>

    <?php get_template_part( 'template-parts/comment-modal' ); ?>

</div>

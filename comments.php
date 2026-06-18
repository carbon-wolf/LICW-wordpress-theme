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
            printf( _n( '1 条评论', '%s 条评论', $comment_count, 'li-cw' ), $comment_count );
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
    <?php endif; ?>

    <!-- 弹出式评论表单模态框 -->
    <div class="comment-modal" id="commentModal">
        <div class="comment-modal-mask"></div>
        <div class="comment-modal-box">
            <div class="comment-modal-header">
                <h4 style="font-family:var(--font-heading); font-weight:500; margin:0;"><?php esc_html_e( '发表评论', 'li-cw' ); ?></h4>
                <button class="comment-modal-close" id="closeCommentForm">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="comment-modal-body">
                <?php
                comment_form( array(
                    'title_reply'   => '',
                    'fields'        => array(
                        'author' => '<p class="comment-form-author"><label>昵称*</label><input type="text" name="author" autocomplete="name" required></p>',
                        'email'  => '<p class="comment-form-email"><label>邮箱（不会公开）*</label><input type="email" name="email" autocomplete="email" required></p>',
                        'url'    => '<p class="comment-form-url"><label>个人网站</label><input type="url" name="url" autocomplete="url"></p>',
                    ),
                    'comment_field' => '<p class="comment-form-comment"><label>评论内容*</label><textarea name="comment" id="comment" rows="4" autocomplete="off" required></textarea></p>',
                    'label_submit'  => '提交评论',
                    'submit_button' => '<button type="submit" class="btn-primary">%4$s</button>',
                    'must_log_in'   => '<p class="must-log-in" style="font-size:0.9rem; color:var(--text-secondary);">请先登录后发表评论</p>',
                ));
                ?>
            </div>
        </div>
    </div>

</div>

<?php
/**
 * 自定义精简评论项输出
 */
function li_cw_simple_comment( $comment, $args, $depth ) {
    ?>
    <li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
        <div class="comment-body">
            <div class="comment-author">
                <?php echo get_avatar( $comment, 32 ); ?>
                <span class="fn"><?php echo get_comment_author(); ?></span>
            </div>
            <div class="comment-meta">
                <?php echo get_comment_date(); ?>
            </div>
            <div class="comment-content">
                <?php comment_text(); ?>
            </div>
            <div class="comment-reply">
                <?php
                comment_reply_link( array_merge( $args, array(
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth'],
                )));
                ?>
            </div>
        </div>
    <?php
}
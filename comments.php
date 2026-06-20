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
                <h4 class="comment-modal-title"><?php esc_html_e( '发表评论', 'li-cw' ); ?></h4>
                <div class="comment-modal-header-actions">
                    <span class="comment-cancel-reply" style="display:none;">
                        <a href="#" id="cancelReplyInModal"><?php esc_html_e( '取消回复', 'li-cw' ); ?></a>
                    </span>
                    <button class="comment-modal-close" id="closeCommentForm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="comment-modal-body">
                <?php
                $commenter = wp_get_current_commenter();
                $req       = get_option( 'require_name_email' );
                $consent   = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';

                comment_form( array(
                    'title_reply'          => '',
                    'comment_notes_before' => '',
                    'comment_notes_after'  => '',
                    'logged_in_as'         => '',
                    'fields'               => array(
                        'author'  => '<p class="comment-form-author"><label>' . esc_html__( '昵称', 'li-cw' ) . ( $req ? ' *' : '' ) . '</label><input type="text" name="author" value="' . esc_attr( $commenter['comment_author'] ) . '" autocomplete="name" required></p>',
                        'email'   => '<p class="comment-form-email"><label>' . esc_html__( '邮箱（不会公开）', 'li-cw' ) . ( $req ? ' *' : '' ) . '</label><input type="email" name="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" autocomplete="email" required></p>',
                        'url'     => '<p class="comment-form-url"><label>' . esc_html__( '个人网站', 'li-cw' ) . '</label><input type="url" name="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" autocomplete="url"></p>',
                        'cookies' => '<p class="comment-form-cookies"><label><input type="checkbox" name="wp-comment-cookies-consent" value="yes"' . $consent . '> ' . esc_html__( '本地保存我的信息，以便下次评论时使用。', 'li-cw' ) . '</label></p>',
                    ),
                    'comment_field' => '<p class="comment-form-comment"><label>' . esc_html__( '评论内容', 'li-cw' ) . ' *</label><textarea name="comment" id="comment" rows="4" autocomplete="off" required></textarea></p>',
                    'label_submit'  => esc_html__( '', 'li-cw' ),
                    'submit_button' => '<button type="submit" class="btn-primary">%4$s</button>',
                    'must_log_in'   => '<p class="must-log-in" style="font-size:0.9rem; color:var(--text-secondary);">' . esc_html__( '请先登录后发表评论', 'li-cw' ) . '</p>',

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
    $is_reply = ( $depth > 1 );
    $parent_author = '';
    if ( $is_reply ) {
        $parent_comment = get_comment( $comment->comment_parent );
        if ( $parent_comment ) {
            $parent_author = $parent_comment->comment_author;
        }
    }
    ?>
    <li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
        <div class="comment-body">
            <div class="comment-author">
                <?php echo get_avatar( $comment, 32 ); ?>
                <span class="fn"><?php echo get_comment_author(); ?></span>
            </div>
            <div class="comment-meta">
                <?php echo get_comment_date(); ?>
                <?php if ( $is_reply && $parent_author ) : ?>
                    <span class="comment-reply-to">
                        <?php printf( esc_html__( '回复 %s', 'li-cw' ), '<span class="reply-to-name">' . esc_html( $parent_author ) . '</span>' ); ?>
                    </span>
                <?php endif; ?>
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
<?php
/**
 * 评论表单模态框（文章、友链页、说说页共用）
 * 依赖当前全局 $post；说说页会临时切换 $post 并由 JS 覆盖 comment_post_ID。
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
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
                'comment_field' => '<p class="comment-form-comment"><label>' . esc_html__( '评论内容', 'li-cw' ) . ' *</label><span class="comment-input"><textarea name="comment" id="comment" rows="4" autocomplete="off" required></textarea><button type="button" class="emoji-toggle" aria-label="' . esc_attr__( '插入表情', 'li-cw' ) . '" aria-expanded="false" aria-controls="emojiPanel"><img src="' . esc_url( LI_CW_THEME_URI . '/assets/images/wechat/smile.png' ) . '" alt="" width="20" height="20"></button><span class="emoji-panel" id="emojiPanel" hidden role="dialog" aria-label="' . esc_attr__( '表情', 'li-cw' ) . '"></span></span></p>',
                'label_submit'  => esc_html__( '', 'li-cw' ),
                'submit_button' => '<button type="submit" class="btn-primary">%4$s</button>',
                'must_log_in'   => '<p class="must-log-in" style="font-size:0.9rem; color:var(--text-secondary);">' . esc_html__( '请先登录后发表评论', 'li-cw' ) . '</p>',
            ));

            // 表情数据（微信 + Apple，紧凑 JSON，前端首次展开时才生成按钮）
            echo '<script type="application/json" id="liCwWxEmojiData">'
                . wp_json_encode( array( 'groups' => li_cw_emoji_groups() ) )
                . '</script>';
            ?>
        </div>
    </div>
</div>

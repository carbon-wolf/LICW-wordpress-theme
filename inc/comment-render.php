<?php
/**
 * 评论渲染
 * 自定义评论项输出 + 说说逐条评论列表。
 * 独立成模块，便于文章、友链页、说说页共用同一套评论样式与回调。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 自定义精简评论项输出
 *
 * @param WP_Comment $comment 评论对象
 * @param array      $args    参数
 * @param int        $depth   层级
 */
function li_cw_simple_comment( $comment, $args, $depth ) {
    $is_reply  = ( $depth > 1 );
    $is_pinned = ! $is_reply && get_comment_meta( $comment->comment_ID, 'li_cw_pinned', true );

    // 检测评论者是否为文章作者（支持登录用户 + 访客邮箱匹配）
    $post_author_id    = get_post_field( 'post_author', $comment->comment_post_ID );
    $post_author_email = get_the_author_meta( 'user_email', $post_author_id );
    $is_post_author    = (
        ( ! empty( $comment->user_id ) && (int) $comment->user_id === (int) $post_author_id ) ||
        ( ! empty( $comment->comment_author_email ) && strtolower( $comment->comment_author_email ) === strtolower( $post_author_email ) )
    );

    $parent_author = '';
    if ( $is_reply ) {
        $parent_comment = get_comment( $comment->comment_parent );
        if ( $parent_comment ) {
            $parent_author = $parent_comment->comment_author;
        }
    }
    ?>
    <li <?php comment_class( $is_pinned ? 'is-pinned' : '' ); ?> id="comment-<?php comment_ID(); ?>">
        <div class="comment-body">
            <div class="comment-author">
                <?php echo get_avatar( $comment, 32 ); ?>
                <span class="fn"><?php echo esc_html( get_comment_author() ); ?></span>
                <?php if ( $is_post_author ) : ?>
                    <span class="comment-author-badge"><?php esc_html_e( '博主', 'li-cw' ); ?></span>
                <?php endif; ?>
                <?php if ( $is_pinned ) : ?>
                    <span class="comment-pinned-badge"><?php esc_html_e( '置顶', 'li-cw' ); ?></span>
                <?php endif; ?>
            </div>
            <div class="comment-meta">
                <?php echo get_comment_date(); ?>
                <?php if ( $is_reply && $parent_author ) : ?>
                    <span class="comment-reply-to">
                        <?php printf( esc_html__( '回复 %s', 'li-cw' ), '<span class="reply-to-name">' . esc_html( $parent_author ) . '</span>' ); ?>
                    </span>
                <?php endif; ?>
                <?php
                if ( li_cw_comment_ip_enabled() ) {
                    $ip_loc = li_cw_get_comment_ip_location( $comment->comment_ID );
                    if ( $ip_loc ) {
                        echo ' · <span class="comment-ip" title="' . esc_attr__( 'IP 属地', 'li-cw' ) . '">' . esc_html( $ip_loc ) . '</span>';
                    }
                }
                ?>
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
                echo li_cw_comment_like_button( $comment->comment_ID );
                ?>
            </div>
        </div>
    <?php
}

/**
 * 输出单条说说的评论列表（供说说页内联展示）
 *
 * @param int        $post_id  说说 ID
 * @param array|null $comments 已获取的评论，传入可避免重复查询
 */
function li_cw_shuoshuo_comments( $post_id, $comments = null ) {
    $post_id = absint( $post_id );
    if ( ! $post_id ) {
        return;
    }

    if ( null === $comments ) {
        $comments = get_comments( array(
            'post_id' => $post_id,
            'status'  => 'approve',
            'order'   => 'ASC',
        ) );
    }

    if ( ! $comments ) {
        return;
    }
    ?>
    <div class="shuoshuo-comments" data-comment-post="<?php echo esc_attr( $post_id ); ?>">
        <ol class="comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 32,
                'type'        => 'comment',
                'callback'    => 'li_cw_simple_comment',
                'max_depth'   => 5,
            ), $comments );
            ?>
        </ol>
    </div>
    <?php
}

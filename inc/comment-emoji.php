<?php
/**
 * 表情支持（评论 / 正文）
 * 两套可切换的表情：微信（短码 :微笑:，本地打包）与 Apple（精选 emoji 字符，本地打包）。
 * 仅命中白名单才会替换，且跳过 <pre>/<code> 代码块，避免误伤技术文章。
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 微信表情白名单：短码名 => 图片文件名
 * 顺序为微信经典表情顺序，前端选择器直接复用。
 *
 * @return array
 */
function li_cw_wechat_emoji_map() {
    static $map = null;
    if ( null !== $map ) {
        return $map;
    }

    $map = array(
        '微笑' => 'smile',
        '撇嘴' => 'grimance',
        '色' => 'drool',
        '发呆' => 'scowl',
        '得意' => 'cool_guy',
        '流泪' => 'sob',
        '害羞' => 'shy',
        '闭嘴' => 'silent',
        '睡' => 'sleep',
        '大哭' => 'cry',
        '尴尬' => 'akward',
        '发怒' => 'angry',
        '调皮' => 'tongue',
        '呲牙' => 'grin',
        '惊讶' => 'surprise',
        '难过' => 'frown',
        '酷' => 'ruthless',
        '冷汗' => 'blush',
        '抓狂' => 'scream',
        '吐' => 'puke',
        '偷笑' => 'chuckle',
        '愉快' => 'joyful',
        '白眼' => 'slight',
        '傲慢' => 'smug',
        '饥饿' => 'hungry',
        '困' => 'drowsy',
        '惊恐' => 'panic',
        '流汗' => 'sweat',
        '憨笑' => 'laugh',
        '悠闲' => 'commando',
        '奋斗' => 'determined',
        '咒骂' => 'scold',
        '疑问' => 'shocked',
        '嘘' => 'shhh',
        '晕' => 'dizzy',
        '疯了' => 'tormented',
        '衰' => 'toasted',
        '骷髅' => 'skull',
        '敲打' => 'hammer',
        '再见' => 'wave',
        '擦汗' => 'speechless',
        '抠鼻' => 'nose_pick',
        '鼓掌' => 'clap',
        '糗大了' => 'shame',
        '坏笑' => 'trick',
        '左哼哼' => 'bah_l',
        '右哼哼' => 'bah_r',
        '哈欠' => 'yawn',
        '鄙视' => 'pooh_pooh',
        '委屈' => 'shrunken',
        '快哭了' => 'tearing_up',
        '阴险' => 'sly',
        '亲亲' => 'kiss',
        '吓' => 'wrath',
        '可怜' => 'whimper',
        '菜刀' => 'cleaver',
        '西瓜' => 'watermelon',
        '啤酒' => 'beer',
        '篮球' => 'basketball',
        '乒乓' => 'ping_pong',
        '咖啡' => 'coffee',
        '饭' => 'rice',
        '猪头' => 'pig',
        '玫瑰' => 'rose',
        '凋谢' => 'wilt',
        '嘴唇' => 'lips',
        '爱心' => 'heart',
        '心碎' => 'broken_heart',
        '蛋糕' => 'cake',
        '闪电' => 'lightning',
        '炸弹' => 'bomb',
        '刀' => 'dagger',
        '足球' => 'soccer',
        '瓢虫' => 'lady_bug',
        '便便' => 'poop',
        '月亮' => 'moon',
        '太阳' => 'sun',
        '礼物' => 'gift',
        '拥抱' => 'hug',
        '强' => 'thumbs_up',
        '弱' => 'thumbs_down',
        '握手' => 'shake',
        '胜利' => 'peace',
        '抱拳' => 'fight',
        '勾引' => 'beckon',
        '拳头' => 'fist',
        '差劲' => 'pinky',
        '爱你' => 'rock_on',
        'NO' => 'nuh_uh',
        'OK' => 'ok',
        '爱情' => 'in_love',
        '飞吻' => 'blowkiss',
        '跳跳' => 'waddle',
        '发抖' => 'tremble',
        '怄火' => 'aaagh',
        '转圈' => 'twirl',
        '磕头' => 'kotow',
        '回头' => 'dramatic',
        '跳绳' => 'jump_rope',
        '投降' => 'surrender',
        '欢呼' => 'hooray',
        '冥想' => 'meditate',
        '么么哒' => 'smooch',
        '左太极' => 'taichi_l',
        '右太极' => 'taichi_r',
    );

    return $map;
}

/**
 * 表情图片 URL
 *
 * @param string $file 文件名（不含扩展名）
 * @return string
 */
function li_cw_wechat_emoji_url( $file ) {
    return LI_CW_THEME_URI . '/assets/images/wechat/' . $file . '.png';
}

/**
 * 生成单个表情的 <img> 标签
 *
 * @param string $name 短码名
 * @return string
 */
function li_cw_wechat_emoji_img( $name ) {
    $map = li_cw_wechat_emoji_map();
    if ( ! isset( $map[ $name ] ) ) {
        return '';
    }
    return '<img class="li-cw-wxemoji" src="' . esc_url( li_cw_wechat_emoji_url( $map[ $name ] ) ) . '"'
        . ' alt=":' . esc_attr( $name ) . ':" width="24" height="24"'
        . ' loading="lazy" decoding="async">';
}

/**
 * 在非代码片段中执行替换（跳过 <pre>/<code>）
 *
 * @param string   $text     内容
 * @param callable $callback 接收非代码片段，返回处理后片段
 * @return string
 */
function li_cw_emoji_replace_outside_code( $text, $callback ) {
    $parts = preg_split(
        '#(<pre\b[^>]*>.*?</pre>|<code\b[^>]*>.*?</code>)#is',
        $text,
        -1,
        PREG_SPLIT_DELIM_CAPTURE
    );

    if ( false === $parts ) {
        return $text;
    }

    foreach ( $parts as $i => $part ) {
        // 奇数下标为代码块（分隔符），原样保留
        if ( 0 !== $i % 2 || '' === $part ) {
            continue;
        }
        $parts[ $i ] = call_user_func( $callback, $part );
    }

    return implode( '', $parts );
}

/**
 * 把 :名称: 短码解析为微信表情图片
 *
 * @param string $text 内容
 * @return string
 */
function li_cw_parse_wechat_emoji( $text ) {
    if ( ! is_string( $text ) || false === strpos( $text, ':' ) ) {
        return $text;
    }

    return li_cw_emoji_replace_outside_code( $text, function ( $part ) {
        return preg_replace_callback(
            '#:([^:\s<>&]{1,12}):#u',
            function ( $m ) {
                $img = li_cw_wechat_emoji_img( $m[1] );
                return $img ? $img : $m[0];
            },
            $part
        );
    } );
}

/**
 * Apple 表情图片 URL
 *
 * @param string $file 文件名（不含扩展名）
 * @return string
 */
function li_cw_apple_emoji_url( $file ) {
    return LI_CW_THEME_URI . '/assets/images/apple/' . $file . '.png';
}

/**
 * 生成单个 Apple 表情的 <img>（$char 为 emoji 字符）
 *
 * @param string $char emoji 字符
 * @return string
 */
function li_cw_apple_emoji_img( $char ) {
    $map = li_cw_apple_emoji_map();
    if ( ! isset( $map[ $char ] ) ) {
        return '';
    }
    return '<img class="li-cw-appleemoji" src="' . esc_url( li_cw_apple_emoji_url( $map[ $char ] ) ) . '"'
        . ' alt="' . esc_attr( $char ) . '" width="24" height="24"'
        . ' loading="lazy" decoding="async">';
}

/**
 * Apple 表情匹配正则（按字节长度降序，长序列优先）
 *
 * @return string
 */
function li_cw_apple_emoji_regex() {
    static $regex = null;
    if ( null !== $regex ) {
        return $regex;
    }

    $keys = array_keys( li_cw_apple_emoji_map() );
    usort( $keys, function ( $a, $b ) {
        return strlen( $b ) - strlen( $a );
    } );

    $regex = '#(' . implode( '|', array_map( function ( $key ) {
        return preg_quote( $key, '#' );
    }, $keys ) ) . ')#u';

    return $regex;
}

/**
 * 把精选 Apple 表情字符解析为图片
 *
 * @param string $text 内容
 * @return string
 */
function li_cw_parse_apple_emoji( $text ) {
    if ( ! is_string( $text ) || '' === $text ) {
        return $text;
    }

    // 不做预检：regex 未命中时 callback 原样返回，预检只是双倍正则开销
    $regex = li_cw_apple_emoji_regex();

    return li_cw_emoji_replace_outside_code( $text, function ( $part ) use ( $regex ) {
        return preg_replace_callback( $regex, function ( $m ) {
            $img = li_cw_apple_emoji_img( $m[1] );
            return $img ? $img : $m[0];
        }, $part );
    } );
}

/**
 * 表情选择器分组数据（供前端生成 tab 与网格）
 *
 * @return array
 */
function li_cw_emoji_groups() {
    return array(
        array(
            'id'    => 'wechat',
            'label' => __( '微信', 'li-cw' ),
            'base'  => LI_CW_THEME_URI . '/assets/images/wechat/',
            'mode'  => 'token',
            'items' => li_cw_wechat_emoji_map(),
        ),
        array(
            'id'    => 'apple',
            'label' => __( 'Apple', 'li-cw' ),
            'base'  => LI_CW_THEME_URI . '/assets/images/apple/',
            'mode'  => 'char',
            'items' => li_cw_apple_emoji_map(),
        ),
    );
}

// 评论与正文都支持（微信短码 + Apple 表情）
add_filter( 'comment_text', 'li_cw_parse_wechat_emoji', 20 );
add_filter( 'the_content', 'li_cw_parse_wechat_emoji', 20 );
add_filter( 'the_excerpt', 'li_cw_parse_wechat_emoji', 20 );
add_filter( 'comment_text', 'li_cw_parse_apple_emoji', 21 );
add_filter( 'the_content', 'li_cw_parse_apple_emoji', 21 );
add_filter( 'the_excerpt', 'li_cw_parse_apple_emoji', 21 );

// 关闭 WordPress 将 :) 转成旧笑脸图片的功能，避免与表情风格冲突
remove_filter( 'comment_text', 'convert_smilies', 20 );
remove_filter( 'the_content', 'convert_smilies', 20 );
remove_filter( 'the_excerpt', 'convert_smilies' );

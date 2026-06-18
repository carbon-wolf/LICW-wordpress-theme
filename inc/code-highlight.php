<?php
/**
 * 代码高亮模块
 * 按需加载：仅单篇文章且有代码块时加载资源
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function li_cw_enqueue_code_highlight() {
    // 只在单篇文章页加载
    if ( ! is_single() ) return;

    global $post;
    if ( ! is_a( $post, 'WP_Post' ) ) return;

    // 兼容三种代码形式：古腾堡原生代码块、pre标签、code短代码
    $has_code = false;
    if ( function_exists('has_block') && has_block( 'core/code', $post ) ) $has_code = true;
    if ( has_shortcode( $post->post_content, 'code' ) ) $has_code = true;
    if ( strpos( $post->post_content, '<pre' ) !== false ) $has_code = true;

    if ( ! $has_code ) return;

    // 高亮核心JS
    wp_enqueue_script(
        'highlight-js',
        LI_CW_THEME_URI . '/assets/js/highlight.min.js',
        array(),
        '11.9.0',
        true
    );

    // 初始化脚本
    wp_add_inline_script( 'highlight-js', '
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll("pre code").forEach(block => {
                hljs.highlightElement(block);
                wrapCodeLines(block);
                addCopyButton(block);
            });
        });

        // 拆分代码行 - 优化版：兼容所有换行格式，处理首尾空行
        function wrapCodeLines(block) {
            const pre = block.parentElement;
            pre.classList.add("code-with-lines");

            // 兼容 Windows(\r\n) / Unix(\n) 换行
            const html = block.innerHTML;
            const lines = html.split(/\r?\n/);
            
            // 移除首尾空行，避免多算一行
            if (lines.length && lines[0].trim() === "") lines.shift();
            if (lines.length && lines[lines.length - 1].trim() === "") lines.pop();

            // 每行包裹独立元素，配合CSS计数
            block.innerHTML = lines.map(line => {
                return `<span class="code-line">${line || "&nbsp;"}</span>`;
            }).join("");
        }

        // 一键复制按钮
        function addCopyButton(block) {
            const pre = block.parentElement;
            const btn = document.createElement("button");
            btn.className = "code-copy-btn";
            btn.textContent = "复制";
            btn.setAttribute("aria-label", "复制代码");

            btn.addEventListener("click", async function() {
                try {
                    await navigator.clipboard.writeText(block.innerText);
                    btn.textContent = "已复制";
                    setTimeout(() => btn.textContent = "复制", 2000);
                } catch(e) {
                    btn.textContent = "复制失败";
                }
            });

            pre.appendChild(btn);
        }
    ');
}
add_action( 'wp_enqueue_scripts', 'li_cw_enqueue_code_highlight' );
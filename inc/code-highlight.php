<?php
/**
 * 代码高亮模块
 * 按需加载：仅单篇文章且有代码块时加载资源
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function li_cw_enqueue_code_highlight() {
    // 单篇内容页（文章/页面/照片等）加载
    if ( ! is_singular() ) return;

    global $post;
    if ( ! is_a( $post, 'WP_Post' ) ) return;

    // 兼容三种代码形式：古腾堡原生代码块、pre标签
    $has_code = false;
    if ( function_exists('has_block') && has_block( 'core/code', $post ) ) $has_code = true;
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
                enhanceCodeBlock(block);
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

        // 档案编目卡：头部工具栏（语言标签 + 复制按钮）
        function enhanceCodeBlock(block) {
            const pre = block.parentElement;

            // 创建 wrapper：头部工具栏 + pre
            const wrapper = document.createElement("div");
            wrapper.className = "code-block-wrapper";
            pre.parentNode.insertBefore(wrapper, pre);

            // 语言标签 — highlight.js 检测结果（language-xxx 类）或显式标记
            const langClass = (block.className.match(/language-([\w-]+)/) || [])[1];
            if (langClass) {
                const header = document.createElement("div");
                header.className = "code-block-header";

                const label = document.createElement("span");
                label.className = "code-lang-label";
                // 语言名映射（常见缩写给全称，其余原样大写展示）
                const langNames = { js: "JavaScript", ts: "TypeScript", tsx: "TSX", jsx: "JSX",
                    py: "Python", sh: "Shell", bash: "Bash", zsh: "Zsh", shell: "Shell",
                    html: "HTML", xml: "XML", css: "CSS", scss: "SCSS", less: "Less",
                    php: "PHP", sql: "SQL", json: "JSON", yaml: "YAML", md: "Markdown",
                    c: "C", cpp: "C++", cs: "C#", go: "Go", rust: "Rust", java: "Java",
                    kt: "Kotlin", swift: "Swift", rb: "Ruby", vim: "Vim" };
                const known = langNames[langClass.toLowerCase()];
                label.textContent = known || langClass.toUpperCase();

                header.appendChild(label);
                wrapper.appendChild(header);
            }

            wrapper.appendChild(pre);

            // 复制按钮 — 放在头部（无头部时退回 wrapper 右上角浮动）
            const btn = document.createElement("button");
            btn.className = "code-copy-btn";
            btn.type = "button";
            btn.textContent = "复制";
            btn.setAttribute("aria-label", "复制代码");

            const header = wrapper.querySelector(".code-block-header");
            if (header) {
                header.appendChild(btn);
            } else {
                btn.classList.add("is-floating");
                wrapper.appendChild(btn);
            }

            btn.addEventListener("click", async function() {
                try {
                    await navigator.clipboard.writeText(block.innerText);
                    btn.textContent = "已复制";
                    btn.classList.add("is-copied");
                    setTimeout(() => { btn.textContent = "复制"; btn.classList.remove("is-copied"); }, 2000);
                } catch(e) {
                    btn.textContent = "复制失败";
                    setTimeout(() => { btn.textContent = "复制"; }, 2000);
                }
            });
        }
    ');
}
add_action( 'wp_enqueue_scripts', 'li_cw_enqueue_code_highlight' );
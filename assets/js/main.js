/**
 * 全局轻量交互脚本
 * 修复：搜索弹窗交互、回到顶部、图片淡入
 */
document.addEventListener('DOMContentLoaded', function() {

    // ========== 移动端汉堡菜单 ==========
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function() {
            const isOpen = mainNav.classList.toggle('is-open');
            navToggle.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // 点击菜单链接后自动关闭
        mainNav.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                mainNav.classList.remove('is-open');
                navToggle.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // 点击页面其他区域关闭
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !mainNav.contains(e.target)) {
                mainNav.classList.remove('is-open');
                navToggle.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ========== 搜索弹窗 ==========
    const searchToggle = document.querySelector('.search-toggle');
    const searchModal = document.getElementById('searchModal');
    const searchClose = document.querySelector('.search-close');
    const searchInput = searchModal ? searchModal.querySelector('input') : null;

    if (searchToggle && searchModal) {
        // 打开搜索
        searchToggle.addEventListener('click', function(e) {
            e.preventDefault();
            searchModal.classList.add('is-open');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 300);
            }
        });

        // 关闭搜索
        if (searchClose) {
            searchClose.addEventListener('click', function(e) {
                e.preventDefault();
                searchModal.classList.remove('is-open');
            });
        }

        // ESC键关闭
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchModal.classList.remove('is-open');
            }
        });
    }

    // ========== 滚动揭示动画 ==========
    const revealEls = document.querySelectorAll('.reveal');
    if (revealEls.length && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -30px 0px'
        });

        revealEls.forEach(function(el) {
            revealObserver.observe(el);
        });
    } else if (revealEls.length) {
        // 无 IntersectionObserver 支持时直接显示
        revealEls.forEach(function(el) {
            el.classList.add('is-revealed');
        });
    }

    // ========== 回到顶部 ==========
    const backToTop = document.querySelector('.back-to-top');
    if (backToTop) {
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ========== 图片加载淡入 ==========
    const images = document.querySelectorAll('img');
    images.forEach(function(img) {
        img.style.opacity = '0';
        img.style.transition = 'opacity 0.3s ease';
        if (img.complete) {
            img.style.opacity = '1';
        } else {
            img.addEventListener('load', function() {
                img.style.opacity = '1';
            });
            img.addEventListener('error', function() {
                img.style.opacity = '1';
            });
        }
    });
    // ========== 评论表单模态框 ==========
    const openCommentBtn = document.getElementById('openCommentForm');
    const commentModal = document.getElementById('commentModal');
    const closeCommentBtn = document.getElementById('closeCommentForm');
    const commentMask = commentModal ? commentModal.querySelector('.comment-modal-mask') : null;
    const commentParent = document.getElementById('comment_parent');
    const modalTitle = commentModal ? commentModal.querySelector('.comment-modal-title') : null;
    const cancelReplyWrap = document.getElementById('cancelReplyInModal') ? document.getElementById('cancelReplyInModal').parentNode : null;
    const defaultTitle = modalTitle ? modalTitle.textContent : '';

    if (openCommentBtn && commentModal) {
        function openModal() {
            commentModal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            commentModal.classList.remove('is-open');
            document.body.style.overflow = '';
            // 关闭时重置回复状态
            resetReplyState();
        }
        function resetReplyState() {
            if (commentParent) commentParent.value = '0';
            if (modalTitle) modalTitle.textContent = defaultTitle;
            if (cancelReplyWrap) cancelReplyWrap.style.display = 'none';
        }

        openCommentBtn.addEventListener('click', openModal);
        if (closeCommentBtn) closeCommentBtn.addEventListener('click', closeModal);
        if (commentMask) commentMask.addEventListener('click', closeModal);

        // ESC关闭
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && commentModal.classList.contains('is-open')) {
                closeModal();
            }
        });

        // 回复链接 — 委托监听，阻止默认 moveForm，改为打开模态框
        document.addEventListener('click', function(e) {
            var replyLink = e.target.closest('.comment-reply-link');
            if (!replyLink) return;

            e.preventDefault();

            // 从 href 提取回复的评论 ID（格式：?replytocom=123）
            var href = replyLink.getAttribute('href');
            var match = href && href.match(/replytocom=(\d+)/);
            if (!match) return;

            var commentId = match[1];
            var author = replyLink.closest('.comment-body');
            var authorName = '';
            if (author) {
                var fn = author.querySelector('.fn');
                if (fn) authorName = fn.textContent.trim();
            }

            // 设置父评论 ID
            if (commentParent) commentParent.value = commentId;

            // 更新标题
            if (modalTitle) {
                modalTitle.textContent = authorName
                    ? '回复 ' + authorName   // "回复 xxx"
                    : '回复评论';     // "回复评论"
            }

            // 显示取消回复
            if (cancelReplyWrap) cancelReplyWrap.style.display = '';

            // 打开模态框
            openModal();
        });

        // 取消回复
        var cancelReplyBtn = document.getElementById('cancelReplyInModal');
        if (cancelReplyBtn) {
            cancelReplyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                resetReplyState();
            });
        }
    }
        // ========== 评论草稿自动保存 ==========
    const commentTextarea = document.getElementById('comment');
    const STORAGE_KEY = 'li_cw_comment_draft';

    if (commentTextarea) {
        // 页面加载时恢复草稿
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) commentTextarea.value = saved;

        // 输入时自动保存
        commentTextarea.addEventListener('input', function() {
            localStorage.setItem(STORAGE_KEY, this.value);
        });

        // 提交表单后清空草稿
        const commentForm = commentTextarea.closest('form');
        if (commentForm) {
            commentForm.addEventListener('submit', function() {
                localStorage.removeItem(STORAGE_KEY);
            });
        }
    }

    // ========== 评论提交按钮反馈 ==========
    const commentFormEl = document.querySelector('#commentform');
    if (commentFormEl) {
        commentFormEl.addEventListener('submit', function () {
            var btn = commentFormEl.querySelector('button[type="submit"]');
            if (btn && !btn.classList.contains('is-submitting')) {
                btn.classList.add('is-submitting');
                btn.textContent = '发送中…';
            }
        });
    }

    // 清理回复链接的 onclick 属性（comment-reply.js 未加载，避免 addComment.moveForm 报错）
    document.querySelectorAll('.comment-reply-link').forEach(function (link) {
        link.removeAttribute('onclick');
    });

    // ========== 说说点赞 ==========
    const LIKE_COOKIE = 'li_cw_likes';

    function getLikedPosts() {
        const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + LIKE_COOKIE + '=([^;]*)'));
        if (match) {
            try { return JSON.parse(decodeURIComponent(match[1])); } catch (e) { return []; }
        }
        return [];
    }

    function setLikedPosts(ids) {
        const d = new Date();
        d.setFullYear(d.getFullYear() + 1);
        document.cookie = LIKE_COOKIE + '=' + encodeURIComponent(JSON.stringify(ids))
            + ';path=/;expires=' + d.toUTCString() + ';SameSite=Lax';
    }

    const likedPosts = getLikedPosts();

    document.querySelectorAll('.like-btn').forEach(function(btn) {
        const postId = parseInt(btn.getAttribute('data-post-id'));
        const countEl = btn.querySelector('.like-count');
        const initialLikes = parseInt(btn.getAttribute('data-likes')) || 0;

        // 初始化计数与状态
        countEl.textContent = initialLikes;
        if (likedPosts.indexOf(postId) !== -1) {
            btn.classList.add('is-liked');
        }

        btn.addEventListener('click', function() {
            const already = btn.classList.contains('is-liked');
            const action = already ? 'unlike' : 'like';

            // 乐观更新
            btn.classList.toggle('is-liked');
            const currentCount = parseInt(countEl.textContent) || 0;
            countEl.textContent = action === 'like' ? currentCount + 1 : Math.max(0, currentCount - 1);

            // 更新 cookie
            let ids = getLikedPosts();
            if (action === 'like') {
                if (ids.indexOf(postId) === -1) ids.push(postId);
            } else {
                ids = ids.filter(function(id) { return id !== postId; });
            }
            setLikedPosts(ids);

            // 请求服务端
            fetch('/wp-json/licw/v1/shuoshuo/' + postId + '/like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: action })
            }).catch(function() {
                // 网络失败时回滚
                btn.classList.toggle('is-liked');
                countEl.textContent = currentCount;
                setLikedPosts(action === 'like' ? ids.filter(function(id) { return id !== postId; }) : ids.concat(postId));
            });
        });
    });
});

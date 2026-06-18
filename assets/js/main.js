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

    if (openCommentBtn && commentModal) {
        function openModal() {
            commentModal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            commentModal.classList.remove('is-open');
            document.body.style.overflow = '';
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
});

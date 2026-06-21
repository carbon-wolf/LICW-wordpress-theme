/**
 * 图片灯箱
 * 点击正文图片弹出全屏查看，支持左右键导航、计数器、加载动画
 * 支持鼠标滚轮缩放、双指缩放旋转、按钮/键盘旋转
 */
(function () {
    'use strict';

    var overlay = null;
    var gallery = [];       // [{ src, el }]
    var currentIndex = -1;

    /* ---- 变换状态 ---- */
    var currentZoom = 1;
    var currentRotation = 0;
    var touchData = null;   // 双指触摸起始数据
    var zoomIndicatorTimer = null;

    /* ---- 工具：去 WordPress 尺寸后缀 ---- */
    function getFullSrc(src) {
        if (!src) return '';
        return src.replace(/-\d+x\d+(\.\w+)(\?.*)?$/i, '$1$2');
    }

    /* ---- 收集页面内所有可灯箱的图片 ---- */
    function collectGallery() {
        gallery = [];
        var seen = {};

        // 1. PHP 已处理的 [data-lightbox] 链接
        var links = document.querySelectorAll('a[data-lightbox]');
        for (var i = 0; i < links.length; i++) {
            var href = links[i].getAttribute('href');
            if (href && !seen[href]) {
                seen[href] = true;
                gallery.push({ src: href, el: links[i] });
            }
        }

        // 2. 正文独立图片
        var imgs = document.querySelectorAll('.single-content img, .single-hero-img, .single-photo-img');
        for (var j = 0; j < imgs.length; j++) {
            if (imgs[j].closest('a[data-lightbox]')) continue;
            var src = imgs[j].currentSrc || imgs[j].src;
            if (!src) continue;
            var full = getFullSrc(src);
            if (!seen[full]) {
                seen[full] = true;
                gallery.push({ src: full, el: imgs[j] });
            }
        }
    }

    /* ---- 查找元素在 gallery 中的索引 ---- */
    function findIndex(el) {
        for (var i = 0; i < gallery.length; i++) {
            if (gallery[i].el === el) return i;
        }
        return 0;
    }

    /* ---- 获取灯箱图片元素 ---- */
    function getImg() {
        return overlay ? overlay.querySelector('.lightbox-img') : null;
    }

    /* ---- 显式缩放百分比 ---- */
    function showZoomIndicator() {
        var indicator = overlay.querySelector('.lightbox-zoom-indicator');
        if (!indicator) return;
        indicator.textContent = Math.round(currentZoom * 100) + '%';
        indicator.classList.add('is-visible');
        clearTimeout(zoomIndicatorTimer);
        zoomIndicatorTimer = setTimeout(function () {
            indicator.classList.remove('is-visible');
        }, 1200);
    }

    /* ---- 应用变换 ---- */
    function updateTransform() {
        var img = getImg();
        if (!img) return;
        img.style.transition = 'none';
        img.style.transform = 'scale(' + currentZoom + ') rotate(' + currentRotation + 'deg)';
        showZoomIndicator();
    }

    /* ---- 重置变换 ---- */
    function resetTransform() {
        currentZoom = 1;
        currentRotation = 0;
        var img = getImg();
        if (img) {
            img.style.transform = '';
            img.style.transition = '';
        }
        touchData = null;
    }

    /* ---- 创建遮罩 DOM（单例）---- */
    function createOverlay() {
        if (overlay) return overlay;

        overlay = document.createElement('div');
        overlay.className = 'lightbox-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-label', '图片查看');

        overlay.innerHTML =
            '<img class="lightbox-img" alt="" />' +
            '<div class="lightbox-spinner"></div>' +
            '<button class="lightbox-close" aria-label="关闭">&#215;</button>' +
            '<button class="lightbox-prev" aria-label="上一张">&#8249;</button>' +
            '<button class="lightbox-next" aria-label="下一张">&#8250;</button>' +
            '<div class="lightbox-toolbar">' +
                '<button class="lightbox-rotate-btn lightbox-rotate-ccw" aria-label="逆时针旋转">&#8634;</button>' +
                '<span class="lightbox-counter"></span>' +
                '<button class="lightbox-rotate-btn lightbox-rotate-cw" aria-label="顺时针旋转">&#8635;</button>' +
            '</div>' +
            '<span class="lightbox-zoom-indicator"></span>';

        document.body.appendChild(overlay);

        // 关闭事件
        var closeBtn = overlay.querySelector('.lightbox-close');
        var prevBtn  = overlay.querySelector('.lightbox-prev');
        var nextBtn  = overlay.querySelector('.lightbox-next');

        function close() {
            overlay.classList.remove('is-open');
            document.body.style.overflow = '';
            currentIndex = -1;
            resetTransform();
        }

        function prev(e) { e.stopPropagation(); navigate(-1); }
        function next(e) { e.stopPropagation(); navigate(1); }

        closeBtn.addEventListener('click', close);
        prevBtn.addEventListener('click', prev);
        nextBtn.addEventListener('click', next);

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) close();
        });

        // 旋转按钮
        overlay.querySelector('.lightbox-rotate-ccw').addEventListener('click', function (e) {
            e.stopPropagation();
            currentRotation -= 90;
            updateTransform();
        });
        overlay.querySelector('.lightbox-rotate-cw').addEventListener('click', function (e) {
            e.stopPropagation();
            currentRotation += 90;
            updateTransform();
        });

        // 滚轮缩放 / Alt+滚轮旋转
        var img = overlay.querySelector('.lightbox-img');
        img.addEventListener('wheel', function (e) {
            e.preventDefault();
            if (e.altKey) {
                // Alt+滚轮 → 旋转
                currentRotation += e.deltaY > 0 ? 5 : -5;
            } else {
                // 滚轮 → 缩放（Ctrl+滚轮为触控板双指缩放，灵敏度更高）
                var sensitivity = e.ctrlKey ? 0.01 : 0.0015;
                currentZoom *= Math.exp(-e.deltaY * sensitivity);
                currentZoom = Math.min(5, Math.max(0.3, currentZoom));
            }
            updateTransform();
        }, { passive: false });

        // 双击重置
        img.addEventListener('dblclick', function () {
            resetTransform();
            var indicator = overlay.querySelector('.lightbox-zoom-indicator');
            if (indicator) indicator.classList.remove('is-visible');
        });

        // 双指触摸：缩放 + 旋转
        img.addEventListener('touchstart', function (e) {
            if (e.touches.length === 2) {
                var dx = e.touches[0].clientX - e.touches[1].clientX;
                var dy = e.touches[0].clientY - e.touches[1].clientY;
                touchData = {
                    startDist: Math.hypot(dx, dy),
                    startAngle: Math.atan2(dy, dx) * 180 / Math.PI,
                    startZoom: currentZoom,
                    startRotation: currentRotation
                };
            }
        }, { passive: true });

        img.addEventListener('touchmove', function (e) {
            if (e.touches.length === 2 && touchData) {
                var dx = e.touches[0].clientX - e.touches[1].clientX;
                var dy = e.touches[0].clientY - e.touches[1].clientY;
                var newDist = Math.hypot(dx, dy);
                var newAngle = Math.atan2(dy, dx) * 180 / Math.PI;

                currentZoom = Math.min(5, Math.max(0.3, touchData.startZoom * (newDist / touchData.startDist)));
                currentRotation = touchData.startRotation + (newAngle - touchData.startAngle);
                updateTransform();
                e.preventDefault();
            }
        }, { passive: false });

        img.addEventListener('touchend', function (e) {
            if (e.touches.length < 2) touchData = null;
        });

        return overlay;
    }

    /* ---- 加载指定索引的图片 ---- */
    function loadImage(index) {
        if (index < 0 || index >= gallery.length) return;
        currentIndex = index;
        var item = gallery[index];

        var img     = overlay.querySelector('.lightbox-img');
        var spinner = overlay.querySelector('.lightbox-spinner');
        var counter = overlay.querySelector('.lightbox-counter');
        var prevBtn = overlay.querySelector('.lightbox-prev');
        var nextBtn = overlay.querySelector('.lightbox-next');
        var toolbar = overlay.querySelector('.lightbox-toolbar');

        // 切换图片时重置缩放旋转
        resetTransform();

        // 加载状态
        spinner.classList.add('is-active');
        img.style.opacity = '0';

        var loader = new Image();
        loader.onload = function () {
            img.src = item.src;
            spinner.classList.remove('is-active');
            img.style.opacity = '1';
        };
        loader.onerror = function () {
            spinner.classList.remove('is-active');
            img.style.opacity = '1';
        };
        loader.src = item.src;

        // 导航 UI
        if (gallery.length > 1) {
            prevBtn.style.display = '';
            nextBtn.style.display = '';
            counter.textContent = (index + 1) + ' / ' + gallery.length;
            counter.style.display = '';
            toolbar.style.display = '';
        } else {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            counter.style.display = 'none';
            // 单图时工具栏仍然显示（旋转按钮可用）
            toolbar.style.display = '';
        }
    }

    /* ---- 导航偏移 ---- */
    function navigate(delta) {
        if (gallery.length < 2) return;
        var idx = currentIndex + delta;
        if (idx < 0) idx = gallery.length - 1;
        if (idx >= gallery.length) idx = 0;
        loadImage(idx);
    }

    /* ---- 打开灯箱 ---- */
    function open(src, triggerEl) {
        if (!src) return;
        collectGallery();
        var idx = triggerEl ? findIndex(triggerEl) : 0;

        createOverlay();
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        loadImage(idx);
    }

    /* ---- 全局点击委托 ---- */
    document.addEventListener('click', function (e) {
        // 1. [data-lightbox] 链接
        var link = e.target.closest('a[data-lightbox]');
        if (link) {
            e.preventDefault();
            open(link.getAttribute('href'), link);
            return;
        }

        // 2. 正文图片
        var img = e.target.closest('.single-content img, .single-hero-img, .single-photo-img');
        if (!img) return;

        // 包裹在普通链接中 → 用链接 href
        var parentLink = img.closest('a');
        if (parentLink) {
            var linkHref = parentLink.getAttribute('href');
            if (linkHref && /\.(jpg|jpeg|png|gif|webp|svg|avif)(\?.*)?$/i.test(linkHref)) {
                e.preventDefault();
                open(getFullSrc(linkHref), parentLink);
                return;
            }
        }

        // 独立图片
        var src = img.currentSrc || img.src;
        if (src) open(getFullSrc(src), img);
    });

    /* ---- 键盘导航 ---- */
    document.addEventListener('keydown', function (e) {
        if (!overlay || !overlay.classList.contains('is-open')) return;

        if (e.key === 'Escape') {
            overlay.classList.remove('is-open');
            document.body.style.overflow = '';
            currentIndex = -1;
            resetTransform();
            return;
        }

        if (e.key === 'ArrowLeft')  { e.preventDefault(); navigate(-1); return; }
        if (e.key === 'ArrowRight') { e.preventDefault(); navigate(1); return; }

        // 旋转快捷键
        if (e.key === 'r' || e.key === 'R') {
            if (e.shiftKey) {
                currentRotation -= 90;
            } else {
                currentRotation += 90;
            }
            updateTransform();
            return;
        }

        // 数字 0 重置变换
        if (e.key === '0') {
            resetTransform();
            var indicator = overlay.querySelector('.lightbox-zoom-indicator');
            if (indicator) indicator.classList.remove('is-visible');
        }
    });
})();

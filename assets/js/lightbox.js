/**
 * 图片灯箱
 * 点击正文图片弹出全屏查看，支持左右键导航、计数器、加载动画
 * 支持鼠标滚轮缩放、双指缩放旋转、按钮/键盘旋转
 * 支持鼠标拖拽平移（放大后）、单指滑动切换图片、双击缩放切换
 */
(function () {
    'use strict';

    var overlay = null;
    var gallery = [];       // [{ src, el }]
    var currentIndex = -1;

    /* ---- 变换状态 ---- */
    var currentZoom = 1;
    var currentRotation = 0;
    var currentX = 0;       // 平移偏移 px
    var currentY = 0;
    var touchData = null;   // 双指触摸起始数据
    var dragData = null;    // 鼠标拖拽 { startX, startY, originX, originY }
    var swipeData = null;   // 单指滑动 { startX, startY, startTime, moved }
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
        img.style.transform = 'translate(' + currentX + 'px, ' + currentY + 'px) scale(' + currentZoom + ') rotate(' + currentRotation + 'deg)';
        if (!dragData) img.style.cursor = currentZoom > 1.05 ? 'grab' : '';
        showZoomIndicator();
    }

    /* ---- 重置变换 ---- */
    function resetTransform() {
        currentZoom = 1;
        currentRotation = 0;
        currentX = 0;
        currentY = 0;
        var img = getImg();
        if (img) {
            img.style.transform = '';
            img.style.transition = '';
            img.style.cursor = '';
        }
        touchData = null;
        dragData = null;
        swipeData = null;
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

        // 禁用原生图片拖拽，避免干扰灯箱内的平移/滑动操作
        img.draggable = false;
        img.addEventListener('dragstart', function (e) {
            e.preventDefault();
        });
        img.addEventListener('wheel', function (e) {
            e.preventDefault();
            if (e.altKey) {
                // Alt+滚轮 → 旋转
                currentRotation += e.deltaY > 0 ? 5 : -5;
            } else {
                // 滚轮 → 缩放（Ctrl+滚轮为触控板双指缩放，灵敏度更高）
                var sensitivity = e.ctrlKey ? 0.01 : 0.0015;
                currentZoom *= Math.exp(-e.deltaY * sensitivity);
                currentZoom = Math.min(8, Math.max(0.3, currentZoom));
            }
            updateTransform();
        }, { passive: false });

        // 双击：未放大→放大到 2x，已放大→重置
        img.addEventListener('dblclick', function () {
            if (currentZoom > 1.05) {
                resetTransform();
            } else {
                currentZoom = 2;
                currentX = 0;
                currentY = 0;
                updateTransform();
            }
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

                currentZoom = Math.min(8, Math.max(0.3, touchData.startZoom * (newDist / touchData.startDist)));
                currentRotation = touchData.startRotation + (newAngle - touchData.startAngle);
                updateTransform();
                e.preventDefault();
            }
        }, { passive: false });

        img.addEventListener('touchend', function (e) {
            if (e.touches.length < 2) touchData = null;
        });

        // --- 单指触摸：未放大时左右滑切换图片，放大后拖动平移 ---
        img.addEventListener('touchstart', function (e) {
            if (e.touches.length !== 1 || touchData) return;
            swipeData = {
                startX: e.touches[0].clientX,
                startY: e.touches[0].clientY,
                startTime: Date.now(),
                moved: false,
                dragging: false,
                originX: currentX,
                originY: currentY
            };
        }, { passive: true });

        img.addEventListener('touchmove', function (e) {
            if (!swipeData || e.touches.length !== 1) return;

            var dx = e.touches[0].clientX - swipeData.startX;
            var dy = e.touches[0].clientY - swipeData.startY;

            // 判断方向：首次位移超 10px 时决定是水平滑动还是垂直拖拽
            if (!swipeData.moved && (Math.abs(dx) > 10 || Math.abs(dy) > 10)) {
                swipeData.moved = true;
                // 放大后 → 拖拽模式；未放大且水平为主 → 滑动模式
                swipeData.dragging = (currentZoom > 1.05);
                if (!swipeData.dragging && Math.abs(dx) > Math.abs(dy)) {
                    // 水平滑动：阻止默认滚动
                    e.preventDefault();
                }
            }

            if (!swipeData.moved) return;

            if (swipeData.dragging) {
                // 拖拽平移
                e.preventDefault();
                currentX = swipeData.originX + dx;
                currentY = swipeData.originY + dy;
                updateTransform();
            } else if (Math.abs(dx) > Math.abs(dy)) {
                // 水平滑动视觉反馈（轻微跟随手指）
                img.style.transition = 'none';
                img.style.transform = 'translateX(' + dx * 0.4 + 'px) scale(1)';
            }
        }, { passive: false });

        img.addEventListener('touchend', function (e) {
            if (!swipeData) return;
            var dx = 0, elapsed = 0;
            if (swipeData.moved && !swipeData.dragging) {
                dx = (e.changedTouches[0] ? e.changedTouches[0].clientX : 0) - swipeData.startX;
                elapsed = Date.now() - swipeData.startTime;
            }

            var sd = swipeData;
            swipeData = null;

            if (sd.dragging) return; // 拖拽模式不触发导航

            // 快速短滑 或 长距离滑动 → 切换图片
            if (Math.abs(dx) > 50 || (Math.abs(dx) > 20 && elapsed < 300)) {
                navigate(dx > 0 ? -1 : 1);
            } else {
                // 未触发导航，恢复原位
                img.style.transition = '';
                img.style.transform = '';
            }
        }, { passive: true });

        // --- 鼠标拖拽：放大后拖动平移 ---
        img.addEventListener('mousedown', function (e) {
            if (currentZoom <= 1.05) return; // 未放大时不拖拽
            e.preventDefault();
            img.style.cursor = 'grabbing';
            dragData = {
                startX: e.clientX,
                startY: e.clientY,
                originX: currentX,
                originY: currentY
            };
        });

        document.addEventListener('mousemove', function (e) {
            if (!dragData) return;
            e.preventDefault();
            currentX = dragData.originX + (e.clientX - dragData.startX);
            currentY = dragData.originY + (e.clientY - dragData.startY);
            updateTransform();
        });

        document.addEventListener('mouseup', function () {
            if (!dragData) return;
            dragData = null;
            var imgEl = getImg();
            if (imgEl) imgEl.style.cursor = currentZoom > 1.05 ? 'grab' : '';
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

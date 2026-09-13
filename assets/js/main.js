/**
 * 全局轻量交互脚本
 * 修复：搜索弹窗交互、回到顶部、图片淡入
 */
document.addEventListener('DOMContentLoaded', function() {

    // ========== 日夜切换平滑过渡 ==========
    // 在 darkmode.js 切换 class 之前（捕获阶段，先于其冒泡阶段处理）
    // 给 <html> 挂上 .is-theming，配合 style.css 的统一过渡规则，
    // 让全站表面与文字同节奏平滑换色；约 0.3s 后移除以免影响日常动效。
    const rootEl = document.documentElement;
    const themeToggle = document.querySelector('.dark-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            rootEl.classList.add('is-theming');
            clearTimeout(window.__liCwThemeTimer);
            window.__liCwThemeTimer = setTimeout(function() {
                rootEl.classList.remove('is-theming');
            }, 320);
        }, true);
    }

    // ========== 移动端汉堡菜单 ==========
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function() {
            const isOpen = mainNav.classList.toggle('is-open');
            navToggle.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // 点击菜单链接后自动关闭（父级菜单项改为展开子菜单）
        mainNav.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function(e) {
                var parentItem = link.parentNode;
                var isParent = parentItem.classList.contains('menu-item-has-children');
                var navOpen = mainNav.classList.contains('is-open');

                if (isParent && navOpen) {
                    // 移动端：切换子菜单展开/折叠，不关闭导航面板
                    e.preventDefault();
                    parentItem.classList.toggle('is-expanded');
                    return;
                }

                // 普通链接或子菜单链接：关闭导航面板后正常跳转
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
    // ========== 评论微信表情选择器 ==========
    const emojiToggle = document.querySelector('.emoji-toggle');
    const emojiPanel = document.getElementById('emojiPanel');
    if (emojiToggle && emojiPanel) {
        const emojiTextarea = document.getElementById('comment');
        const emojiModal = document.getElementById('commentModal');
        let emojiBuilt = false;

        // 首次展开时才根据 JSON 生成按钮，避免每页冗余 HTML / 图片请求
        function buildEmojiPanel() {
            if (emojiBuilt) return;
            emojiBuilt = true;

            const dataEl = document.getElementById('liCwWxEmojiData');
            if (!dataEl) return;

            let data;
            try {
                data = JSON.parse(dataEl.textContent);
            } catch (e) {
                return;
            }

            const groups = (data && data.groups) ? data.groups : [];
            if (!groups.length) return;

            const tabs = document.createElement('span');
            tabs.className = 'emoji-tabs';

            const grids = document.createElement('span');
            grids.className = 'emoji-grids';

            const built = {};
            let activeId = groups[0].id;

            function buildGrid(group) {
                if (built[group.id]) return;
                built[group.id] = true;

                const grid = document.createElement('span');
                grid.className = 'emoji-grid';
                grid.setAttribute('data-group', group.id);
                grid.hidden = true;

                const items = group.items || {};
                Object.keys(items).forEach(function(key) {
                    const btn = document.createElement('button');
                    const insert = group.mode === 'token' ? (':' + key + ':') : key;

                    btn.type = 'button';
                    btn.className = 'emoji-item';
                    btn.setAttribute('data-emoji', insert);
                    btn.setAttribute('title', insert);
                    btn.setAttribute('aria-label', key);

                    const img = document.createElement('img');
                    img.src = (group.base || '') + items[key] + '.png';
                    img.alt = key;
                    img.width = 24;
                    img.height = 24;
                    img.loading = 'lazy';

                    btn.appendChild(img);
                    grid.appendChild(btn);
                });

                grids.appendChild(grid);
            }

            function activate(groupId) {
                const group = groups.find(function(g) { return g.id === groupId; });
                if (!group) return;
                activeId = groupId;
                buildGrid(group);

                tabs.querySelectorAll('.emoji-tab').forEach(function(t) {
                    t.classList.toggle('is-active', t.getAttribute('data-target') === groupId);
                });
                grids.querySelectorAll('.emoji-grid').forEach(function(g) {
                    g.hidden = g.getAttribute('data-group') !== groupId;
                });
            }

            groups.forEach(function(group, idx) {
                const tab = document.createElement('button');
                tab.type = 'button';
                tab.className = 'emoji-tab' + (idx === 0 ? ' is-active' : '');
                tab.setAttribute('data-target', group.id);
                tab.textContent = group.label || group.id;
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    activate(group.id);
                });
                tabs.appendChild(tab);
            });

            emojiPanel.appendChild(tabs);
            emojiPanel.appendChild(grids);
            activate(activeId);
        }

        function closeEmojiPanel() {
            emojiPanel.hidden = true;
            emojiToggle.classList.remove('is-open');
            emojiToggle.setAttribute('aria-expanded', 'false');
        }

        function openEmojiPanel() {
            buildEmojiPanel();
            emojiPanel.hidden = false;
            emojiToggle.classList.add('is-open');
            emojiToggle.setAttribute('aria-expanded', 'true');
        }

        emojiToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (emojiPanel.hidden) {
                openEmojiPanel();
            } else {
                closeEmojiPanel();
            }
        });

        // 点击表情 → 在光标处插入 :名称:
        emojiPanel.addEventListener('click', function(e) {
            const item = e.target.closest('.emoji-item');
            if (!item || !emojiTextarea) return;
            e.preventDefault();

            const token = item.getAttribute('data-emoji') || '';
            const val = emojiTextarea.value;
            const start = (typeof emojiTextarea.selectionStart === 'number') ? emojiTextarea.selectionStart : val.length;
            const end = (typeof emojiTextarea.selectionEnd === 'number') ? emojiTextarea.selectionEnd : start;

            emojiTextarea.value = val.slice(0, start) + token + val.slice(end);
            const caret = start + token.length;
            emojiTextarea.selectionStart = emojiTextarea.selectionEnd = caret;
            emojiTextarea.focus();
            // 触发 input，复用现有的草稿自动保存
            emojiTextarea.dispatchEvent(new Event('input', { bubbles: true }));
        });

        // 点击面板/按钮以外区域关闭
        document.addEventListener('click', function(e) {
            if (emojiPanel.hidden) return;
            if (emojiPanel.contains(e.target) || emojiToggle.contains(e.target)) return;
            closeEmojiPanel();
        });

        // ESC 优先关闭表情面板
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !emojiPanel.hidden) {
                closeEmojiPanel();
                e.stopImmediatePropagation();
            }
        });

        // 关闭评论弹窗时一并收起
        const emojiCloseBtn = document.getElementById('closeCommentForm');
        if (emojiCloseBtn) emojiCloseBtn.addEventListener('click', closeEmojiPanel);
        const emojiMask = emojiModal ? emojiModal.querySelector('.comment-modal-mask') : null;
        if (emojiMask) emojiMask.addEventListener('click', closeEmojiPanel);
    }

    // ========== 评论表单模态框 ==========
    const openCommentBtn = document.getElementById('openCommentForm');
    const commentModal = document.getElementById('commentModal');
    const closeCommentBtn = document.getElementById('closeCommentForm');
    const commentMask = commentModal ? commentModal.querySelector('.comment-modal-mask') : null;
    const commentParent = document.getElementById('comment_parent');
    const modalTitle = commentModal ? commentModal.querySelector('.comment-modal-title') : null;
    const cancelReplyWrap = document.getElementById('cancelReplyInModal') ? document.getElementById('cancelReplyInModal').parentNode : null;
    const defaultTitle = modalTitle ? modalTitle.textContent : '';

    if (commentModal) {
        function setModalPostId(postId) {
            var field = commentModal.querySelector('input[name="comment_post_ID"]');
            if (field && postId) {
                field.value = postId;
            }
        }
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

        if (openCommentBtn) openCommentBtn.addEventListener('click', openModal);
        if (closeCommentBtn) closeCommentBtn.addEventListener('click', closeModal);
        if (commentMask) commentMask.addEventListener('click', closeModal);

        // ESC关闭
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && commentModal.classList.contains('is-open')) {
                closeModal();
            }
        });

        // 说说页：点击某条说说的评论按钮，切换评论对象并打开弹窗
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.shuoshuo-comment-btn');
            if (!btn) return;
            e.preventDefault();
            resetReplyState();
            setModalPostId(btn.getAttribute('data-comment-post'));
            openModal();
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

            // 说说页：把评论对象切到该条说说
            var scope = replyLink.closest('[data-comment-post]');
            if (scope) {
                setModalPostId(scope.getAttribute('data-comment-post'));
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
        // ========== 评论草稿自动保存（按评论对象隔离，避免跨文章串扰） ==========
    const commentTextarea = document.getElementById('comment');
    const draftBaseKey = 'li_cw_comment_draft';

    if (commentTextarea) {
        // 草稿键拼入评论对象 ID：普通文章各自独立，无 comment_post_ID 时（异常兜底）退回全局键
        const formPostIdEl = document.querySelector('#commentform input[name="comment_post_ID"]');
        const draftKey = formPostIdEl ? draftBaseKey + '_' + formPostIdEl.value : draftBaseKey;

        // 页面加载时恢复草稿
        const saved = localStorage.getItem(draftKey);
        if (saved) commentTextarea.value = saved;

        // 输入时自动保存
        commentTextarea.addEventListener('input', function() {
            localStorage.setItem(draftKey, this.value);
        });

        // 提交表单后清空草稿
        const commentForm = commentTextarea.closest('form');
        if (commentForm) {
            commentForm.addEventListener('submit', function() {
                localStorage.removeItem(draftKey);
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

    // ========== 点赞（说说 / 评论） ==========
    // 接口地址与 nonce 由 functions.php wp_localize_script 下发
    const likeCfg = window.liCwLikeCfg || null;

    function getLikedIds(cookieName) {
        const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + cookieName + '=([^;]*)'));
        if (match) {
            try { return JSON.parse(decodeURIComponent(match[1])); } catch (e) { return []; }
        }
        return [];
    }

    function setLikedIds(cookieName, ids) {
        const d = new Date();
        d.setFullYear(d.getFullYear() + 1);
        document.cookie = cookieName + '=' + encodeURIComponent(JSON.stringify(ids))
            + ';path=/;expires=' + d.toUTCString() + ';SameSite=Lax';
    }

    function initLikeButtons(selector, idAttr, countSelector, cookieName, endpointPath) {
        const liked = getLikedIds(cookieName);

        document.querySelectorAll(selector).forEach(function(btn) {
            const id = parseInt(btn.getAttribute(idAttr));
            if (!id) return;
            const countEl = btn.querySelector(countSelector);
            if (!countEl) return;
            const initialLikes = parseInt(btn.getAttribute('data-likes')) || 0;

            // 初始化计数与状态
            countEl.textContent = initialLikes;
            if (liked.indexOf(id) !== -1) {
                btn.classList.add('is-liked');
            }

            btn.addEventListener('click', function() {
                // 请求锁：进行中直接忽略，防止连点并发
                if (btn.dataset.busy) return;

                if (!likeCfg) {
                    // 配置缺失（极端兜底）：仅本地视觉反馈
                    btn.classList.toggle('is-liked');
                    return;
                }

                const already = btn.classList.contains('is-liked');
                const action = already ? 'unlike' : 'like';
                btn.dataset.busy = '1';

                // 乐观更新
                btn.classList.toggle('is-liked');
                const currentCount = parseInt(countEl.textContent) || 0;
                countEl.textContent = action === 'like' ? currentCount + 1 : Math.max(0, currentCount - 1);

                // 更新 cookie
                let ids = getLikedIds(cookieName);
                if (action === 'like') {
                    if (ids.indexOf(id) === -1) ids.push(id);
                } else {
                    ids = ids.filter(function(x) { return x !== id; });
                }
                setLikedIds(cookieName, ids);

                // 请求服务端（带 nonce、校验 HTTP 状态）
                fetch(likeCfg.restUrl + endpointPath.replace('__ID__', id), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Li-Cw-Nonce': likeCfg.nonce
                    },
                    body: JSON.stringify({ action: action })
                }).then(function(r) {
                    if (!r.ok) {
                        throw new Error('HTTP ' + r.status);
                    }
                    return r.json().then(function(data) {
                        // 服务端计数回写：窗口内幂等或正常响应均以服务端为准
                        if (data && typeof data.likes === 'number') {
                            countEl.textContent = data.likes;
                        }
                    });
                }).catch(function() {
                    // 网络失败 / HTTP 错误：回滚视觉与 cookie
                    btn.classList.toggle('is-liked');
                    countEl.textContent = currentCount;
                    setLikedIds(cookieName, action === 'like' ? ids.filter(function(x) { return x !== id; }) : ids.concat(id));
                }).finally(function() {
                    delete btn.dataset.busy;
                });
            });
        });
    }

    // 说说点赞
    initLikeButtons('.like-btn', 'data-post-id', '.like-count', 'li_cw_likes', 'shuoshuo/__ID__/like');
    // 评论点赞
    initLikeButtons('.comment-like-btn', 'data-comment-id', '.comment-like-count', 'li_cw_comment_likes', 'comment/__ID__/like');

    // ========== 照片墙瀑布流 (Masonry) ==========
    var masonryGrid = document.getElementById('masonry-grid');
    if (masonryGrid && typeof Masonry !== 'undefined') {
        var masonryImgs = masonryGrid.querySelectorAll('img');
        var loadedCount = 0;
        var totalImages = masonryImgs.length;

        function initMasonry() {
            var msnry = new Masonry(masonryGrid, {
                itemSelector: '.masonry-item',
                columnWidth: '.grid-sizer',
                gutter: '.gutter-sizer',
                percentPosition: true,
                stagger: 30,
                transitionDuration: '0.4s'
            });

            // 图片加载完成后重新布局
            masonryImgs.forEach(function(img) {
                if (img.complete) {
                    loadedCount++;
                } else {
                    img.addEventListener('load', function() {
                        loadedCount++;
                        if (loadedCount >= totalImages) msnry.layout();
                    });
                    img.addEventListener('error', function() {
                        loadedCount++;
                        if (loadedCount >= totalImages) msnry.layout();
                    });
                }
            });

            if (loadedCount >= totalImages) {
                msnry.layout();
            }

            // 延迟重布局，处理异步加载
            setTimeout(function() { msnry.layout(); }, 500);

            // 窗口 resize 时重新计算布局
            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() { msnry.layout(); }, 250);
            });

            // Masonry 布局完成后触发 reveal
            setTimeout(function() {
                masonryGrid.querySelectorAll('.reveal:not(.is-revealed)').forEach(function(el) {
                    el.classList.add('is-revealed');
                });
            }, 600);

            // 骨架屏：图片加载完成后标记 is-loaded
            masonryGrid.querySelectorAll('.photo-thumb img').forEach(function(img) {
                if (img.complete) {
                    var thumb = img.closest('.photo-thumb');
                    if (thumb) thumb.classList.add('is-loaded');
                } else {
                    img.addEventListener('load', function() {
                        var thumb = img.closest('.photo-thumb');
                        if (thumb) thumb.classList.add('is-loaded');
                    });
                    img.addEventListener('error', function() {
                        var thumb = img.closest('.photo-thumb');
                        if (thumb) thumb.classList.add('is-loaded');
                    });
                }
            });
        }

        if ('requestIdleCallback' in window) {
            requestIdleCallback(initMasonry);
        } else {
            setTimeout(initMasonry, 100);
        }
    }
});

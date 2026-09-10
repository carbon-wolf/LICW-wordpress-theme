/**
 * 友链文章懒加载
 * 页面只渲染缓存内容；未缓存的友链在页面加载后分批请求 REST 接口补齐。
 * 抓取失败（无文章或请求出错）时，自动回退为卡片，并排到该分类气泡之后。
 * 不依赖 WP-Cron，也不阻塞首屏。
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        var nodes = Array.prototype.slice.call(document.querySelectorAll('[data-li-cw-feed]'));
        if (!nodes.length) {
            return;
        }

        var cfg = window.liCwLinkFeed || {};
        if (!cfg.restUrl) {
            return;
        }

        var CONCURRENCY = 3;
        var queue = nodes.slice();

        function renderItems(el, items) {
            el.classList.remove('is-pending', 'is-loading');
            el.textContent = '';

            var ul = document.createElement('ul');
            ul.className = 'link-bubble-feed';

            items.forEach(function (it) {
                var li = document.createElement('li');
                li.className = 'link-feed-item';

                var a = document.createElement('a');
                a.href = it.link;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';

                var dot = document.createElement('span');
                dot.className = 'link-feed-dot';
                dot.setAttribute('aria-hidden', 'true');
                a.appendChild(dot);

                var title = document.createElement('span');
                title.className = 'link-feed-title';
                title.textContent = it.title;
                a.appendChild(title);

                if (it.date_human) {
                    var time = document.createElement('time');
                    time.className = 'link-feed-date';
                    if (it.date_iso) {
                        time.setAttribute('datetime', it.date_iso);
                    }
                    time.textContent = it.date_human;
                    a.appendChild(time);
                }

                li.appendChild(a);
                ul.appendChild(li);
            });

            el.appendChild(ul);
        }

        function findFallbackGrid(dialogue) {
            var sibling = dialogue.nextElementSibling;
            if (sibling && sibling.classList.contains('links-grid') && sibling.classList.contains('links-fallback')) {
                return sibling;
            }
            var grid = document.createElement('div');
            grid.className = 'links-grid links-fallback';
            dialogue.parentNode.insertBefore(grid, dialogue.nextSibling);
            return grid;
        }

        function bubbleToCard(el) {
            var article = el.closest('.link-bubble');
            if (!article) {
                el.classList.remove('is-pending', 'is-loading');
                el.textContent = '';
                return;
            }

            var dialogue = article.parentNode;
            var nameLink = article.querySelector('.link-bubble-name');
            var descEl = article.querySelector('.link-bubble-desc');
            var avatarImg = article.querySelector('.link-bubble-avatar img');
            var initialEl = article.querySelector('.link-bubble-initial');

            var card = document.createElement('a');
            card.className = 'link-card reveal is-revealed';
            card.href = nameLink ? nameLink.getAttribute('href') : '#';
            card.target = '_blank';
            card.rel = 'noopener noreferrer';

            if (avatarImg) {
                var img = document.createElement('img');
                img.className = 'link-avatar';
                img.src = avatarImg.getAttribute('src');
                img.alt = avatarImg.getAttribute('alt') || '';
                img.loading = 'lazy';
                card.appendChild(img);
            } else {
                var ph = document.createElement('div');
                ph.className = 'link-avatar link-avatar-text';
                ph.setAttribute('aria-hidden', 'true');
                ph.textContent = initialEl ? initialEl.textContent : '';
                card.appendChild(ph);
            }

            var info = document.createElement('div');
            info.className = 'link-info';

            var name = document.createElement('div');
            name.className = 'link-name';
            name.textContent = nameLink ? nameLink.textContent : '';
            info.appendChild(name);

            if (descEl && descEl.textContent) {
                var desc = document.createElement('div');
                desc.className = 'link-desc';
                desc.textContent = descEl.textContent;
                info.appendChild(desc);
            }

            card.appendChild(info);

            var grid = findFallbackGrid(dialogue);
            grid.appendChild(card);
            article.remove();

            // 没有气泡了就连同空容器一起移除
            if (dialogue && !dialogue.querySelector('.link-bubble')) {
                dialogue.remove();
            }
        }

        function load(el) {
            var url = cfg.restUrl
                + (cfg.restUrl.indexOf('?') === -1 ? '?' : '&')
                + 'link_id=' + encodeURIComponent(el.getAttribute('data-link-id'))
                + '&count=' + encodeURIComponent(el.getAttribute('data-count') || '3');

            return fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            }).then(function (r) {
                return r.json();
            }).then(function (res) {
                if (res && res.items && res.items.length) {
                    renderItems(el, res.items);
                } else {
                    bubbleToCard(el);
                }
            }).catch(function () {
                bubbleToCard(el);
            });
        }

        function worker() {
            if (!queue.length) {
                return;
            }
            var el = queue.shift();
            el.classList.add('is-loading');
            load(el).then(worker, worker);
        }

        for (var i = 0; i < Math.min(CONCURRENCY, queue.length); i++) {
            worker();
        }
    });
})();

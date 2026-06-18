(function() {
    const STORAGE_KEY = 'li_cw_dark_mode';
    const root = document.documentElement;

    // 渲染前直接设置class，杜绝闪烁
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'true') {
        root.classList.add('dark');
    } else if (saved === null && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        root.classList.add('dark');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.querySelector('.dark-toggle');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const isDark = root.classList.toggle('dark');
            localStorage.setItem(STORAGE_KEY, isDark ? 'true' : 'false');
        });

        // 监听系统主题变化
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (localStorage.getItem(STORAGE_KEY) === null) {
                    root.classList.toggle('dark', e.matches);
                }
            });
        }
    });
})();
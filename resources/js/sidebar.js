document.addEventListener('alpine:init', () => {
    const storageKey = 'acalis-sidebar-collapsed';

    const applyCollapsedClass = (collapsed) => {
        document.documentElement.classList.toggle('vx-sidebar-is-collapsed', collapsed);
        document.body.classList.toggle('vx-sidebar-is-collapsed', collapsed);
    };

    Alpine.store('sidebar', {
        collapsed: localStorage.getItem(storageKey) === '1',

        init() {
            applyCollapsedClass(this.collapsed);
        },

        toggle() {
            this.collapsed = ! this.collapsed;
            localStorage.setItem(storageKey, this.collapsed ? '1' : '0');
            applyCollapsedClass(this.collapsed);
        },

        expand() {
            this.collapsed = false;
            localStorage.setItem(storageKey, '0');
            applyCollapsedClass(false);
        },

        collapse() {
            this.collapsed = true;
            localStorage.setItem(storageKey, '1');
            applyCollapsedClass(true);
        },
    });

    Alpine.store('sidebar').init();
});

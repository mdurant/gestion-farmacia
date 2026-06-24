<script>
    (function () {
        var t = localStorage.getItem('acalis-theme');
        document.documentElement.setAttribute('data-theme', t === 'acalis-dark' ? 'acalis-dark' : 'acalis-light');
        if (localStorage.getItem('acalis-sidebar-collapsed') === '1') {
            document.documentElement.classList.add('vx-sidebar-is-collapsed');
            document.addEventListener('DOMContentLoaded', function () {
                document.body.classList.add('vx-sidebar-is-collapsed');
            });
        }
    })();
</script>

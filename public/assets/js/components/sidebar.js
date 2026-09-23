(function () {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const labels = document.querySelectorAll('.sidebar-label');
    const header = document.getElementById('sidebar-header');

    function setCollapsed(collapsed) {
        sidebar.classList.toggle('w-20', collapsed);
        sidebar.classList.toggle('w-64', !collapsed);
        header.classList.toggle('hidden', collapsed);
        labels.forEach(function (el) {
            el.classList.toggle('hidden', collapsed);
        });
        localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
    }

    toggleBtn.addEventListener('click', function () {
        const collapsed = !sidebar.classList.contains('w-20');
        setCollapsed(collapsed);
    });

    // Restore last state on load
    setCollapsed(localStorage.getItem('sidebarCollapsed') === '1');
})();
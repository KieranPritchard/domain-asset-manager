(function () {
    // Targets the elements on the sidebar
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const labels = document.querySelectorAll('.sidebar-label');
    const header = document.getElementById('sidebar-header');
    const divider = document.getElementById('sidebar-divider')

    // Sets the collasped elements classes
    function setCollapsed(collapsed) {
        sidebar.classList.toggle('w-20', collapsed);
        sidebar.classList.toggle('w-64', !collapsed);
        header.classList.toggle('hidden', collapsed);
        divider.classList.toggle('border-t', !collapsed)
        labels.forEach(function (el) {
            el.classList.toggle('hidden', collapsed);
        });
        localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
    }

    // Listens for a toggle click
    toggleBtn.addEventListener('click', function () {
        const collapsed = !sidebar.classList.contains('w-20');
        setCollapsed(collapsed);
    });

    // Restore the saved state, or default to a collapsed sidebar on narrow screens
    const savedState = localStorage.getItem('sidebarCollapsed');
    const collapsed = window.matchMedia('(max-width: 767px)').matches || savedState === '1';
    setCollapsed(collapsed);
})();
<?php
// Admin/layout/footer.php
// Este archivo cierra los tags abiertos en header.php y agrega JS global.
?>
    </main><!-- /#main-content -->
</div><!-- /.flex.min-h wrapper -->

<!-- ═══════════════════════════════════════════════
     SCRIPTS GLOBALES
═══════════════════════════════════════════════ -->
<script>
(function () {
    // ── Toggle Sidebar ───────────────────────────────────────────────────────
    const btnToggle   = document.getElementById('btnToggleSidebar');
    const sidebar     = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const sideItems   = document.querySelectorAll('.sidebar-content');

    function abrirSidebar() {
        sidebar.classList.remove('w-0', '-translate-x-full');
        sidebar.classList.add('w-72');
        mainContent.classList.remove('lg:pl-0');
        mainContent.classList.add('lg:pl-72');
        setTimeout(() => sideItems.forEach(el => el.classList.remove('opacity-0')), 150);
        localStorage.setItem('sidebarOpen', '1');
    }

    function cerrarSidebar() {
        sideItems.forEach(el => el.classList.add('opacity-0'));
        sidebar.classList.remove('w-72');
        sidebar.classList.add('w-0', '-translate-x-full');
        mainContent.classList.remove('lg:pl-72');
        mainContent.classList.add('lg:pl-0');
        localStorage.setItem('sidebarOpen', '0');
    }

    document.addEventListener('click', function(e) {
        if (
            !sidebar.contains(e.target) &&
            !btnToggle.contains(e.target) &&
            !sidebar.classList.contains('w-0')
        ) {
            cerrarSidebar();
        }
    });

    btnToggle.addEventListener('click', () => {
        sidebar.classList.contains('w-0') ? abrirSidebar() : cerrarSidebar();
    });

    // Restaurar estado del sidebar entre páginas
   // Sidebar siempre cerrado al cargar por primera vez (sin valor guardado)
    if (localStorage.getItem('sidebarOpen') === '1') {
        abrirSidebar();
    } else {
        localStorage.setItem('sidebarOpen', '0');
    }

    // ── Cerrar Sesión ────────────────────────────────────────────────────────
    window.confirmarCerrarSesion = function () {
        if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            window.location.href = '../Login/cerrarSesion.php';
        }
    };

    // ── Bloquear botón Atrás (anti-caché de sesión) solo cuando esta en inicioAdmin.php──────────────────────────
   if (window.location.href.includes('inicioAdmin.php')) {
        history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function () {
            history.pushState(null, null, window.location.href);
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../Login/cerrarSesion.php';
            }
        });
    }
})();
</script>

<?php if (isset($scriptsExtra)) echo $scriptsExtra; ?>

</body>
</html>

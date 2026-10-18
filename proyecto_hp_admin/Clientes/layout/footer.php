<!-- ✅ DESPUÉS -->
<footer class="mt-auto border-t border-zinc-200 pt-12 pb-8 w-full">
    <div class="max-w-[1440px] mx-auto px-6 sm:px-10">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
        <div>
            <h3 class="text-lg font-black tracking-tighter text-zinc-900 uppercase mb-4">Hospedaje Ayacucho</h3>
            <p class="text-sm text-zinc-500 leading-relaxed">Brindando hospitalidad de lujo y confort en el corazón de la ciudad desde 2010.</p>
        </div>
        <div>
            <h4 class="font-bold text-zinc-900 mb-4 uppercase text-xs tracking-widest">Navegación</h4>
            <ul class="space-y-2 text-sm text-zinc-600">
                <li><a href="#" class="hover:text-primary transition-colors">Inicio</a></li>
                <li><a href="habitaciones.php" class="hover:text-primary transition-colors">Habitaciones</a></li>
                <li><a href="#" class="hover:text-primary transition-colors">Servicios</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold text-zinc-900 mb-4 uppercase text-xs tracking-widest">Contacto</h4>
            <ul class="space-y-3 text-sm text-zinc-600">
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">mail</span> recepcion@hayacucho.com</li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">call</span> +51 987 654 321</li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold text-zinc-900 mb-4 uppercase text-xs tracking-widest">Síguenos</h4>
            <div class="flex gap-4">
                <a href="#" class="w-10 h-10 rounded-full bg-zinc-100 flex items-center justify-center hover:bg-primary-container transition-colors"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/facebook.svg" class="w-5 h-5 opacity-70" alt="FB"></a>
                <a href="#" class="w-10 h-10 rounded-full bg-zinc-100 flex items-center justify-center hover:bg-primary-container transition-colors"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/instagram.svg" class="w-5 h-5 opacity-70" alt="IG"></a>
            </div>
        </div>
    </div>
    <div class="border-t border-zinc-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-xs text-zinc-400">© 2026 Hospedaje Ayacucho. Todos los derechos reservados.</p>
    </div>
</footer>

</main>
<!-- Cierre del wrapper flex abierto en header.php -->
</div>

<script>
    const btnToggleSidebar = document.getElementById('toggle-menu');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('content-main');
    const sideContents = document.querySelectorAll('.sidebar-content');

    function openSidebar() {
        sidebar.classList.remove('w-0', '-translate-x-full');
        sidebar.classList.add('w-64');
        mainContent.classList.remove('lg:pl-0');
        mainContent.classList.add('lg:pl-64');
        setTimeout(() => {
            sideContents.forEach(el => el.classList.remove('opacity-0'));
        }, 150);
        localStorage.setItem('sidebarStatus', 'open');
    }

    function closeSidebar() {
        sideContents.forEach(el => el.classList.add('opacity-0'));
        sidebar.classList.remove('w-64');
        sidebar.classList.add('w-0', '-translate-x-full');
        mainContent.classList.remove('lg:pl-64');
        mainContent.classList.add('lg:pl-0');
        localStorage.setItem('sidebarStatus', 'closed');
    }

    document.addEventListener("DOMContentLoaded", () => {
        const savedStatus = localStorage.getItem('sidebarStatus');
        if (savedStatus === 'open') {
            sidebar.classList.remove('w-0', '-translate-x-full');
            sidebar.classList.add('w-64');
            mainContent.classList.remove('lg:pl-0');
            mainContent.classList.add('lg:pl-64');
            sideContents.forEach(el => el.classList.remove('opacity-0'));
        }
    });

    btnToggleSidebar.addEventListener('click', (e) => {
        e.stopPropagation();
        if (sidebar.classList.contains('w-0')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });

    mainContent.addEventListener('click', () => {
        if (!sidebar.classList.contains('w-0')) {
            closeSidebar();
        }
    });
</script>
<script>
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        const paginaActual = window.location.pathname;
        
        if (paginaActual.includes('inicioClientes.php')) {
            if (confirm("¿Deseas cerrar tu sesión y volver al login?")) {
                window.location.replace("../Login/cerrarSesion.php");
            } else {
                history.pushState(null, null, location.href);
            }
        }
    };
</script>
</body>
</html>
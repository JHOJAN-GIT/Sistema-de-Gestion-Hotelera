<?php
// Detecta el nombre del archivo actual para saber en qué pestaña estamos parado
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<aside id="sidebar" class="sidebar-transition bg-zinc-50 w-0 -translate-x-full border-r border-zinc-200 fixed left-0 top-16 h-[calc(100vh-64px)] z-40 flex flex-col pt-8 overflow-hidden">
    <div class="px-6 mb-8 sidebar-content opacity-0 whitespace-nowrap">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-zinc-900 flex items-center justify-center rounded-sm shrink-0">
                <span class="text-yellow-400 material-symbols-outlined">apartment</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-zinc-900 leading-tight">H. Ayacucho</h2>
                <p class="text-[10px] text-zinc-500 uppercase tracking-widest">Panel Cliente</p>
            </div>
        </div>
    </div>
    
    <nav class="flex-1 sidebar-content opacity-0 whitespace-nowrap">
        <!-- Enlace: Inicio -->
        <a class="<?php echo ($pagina_actual == 'inicioClientes.php') ? 'border-l-4 border-yellow-400 bg-white text-black font-bold' : 'text-zinc-500'; ?> px-6 py-4 flex items-center gap-3 hover:bg-zinc-100 transition-all" href="inicioClientes.php">
            <span class="material-symbols-outlined <?php echo ($pagina_actual == 'inicioClientes.php') ? 'text-yellow-400' : ''; ?>">home</span>
            Inicio
        </a>

        <!-- Enlace: Habitaciones para ti -->
        <a class="<?php echo ($pagina_actual == 'habitaciones.php') ? 'border-l-4 border-yellow-400 bg-white text-black font-bold' : 'text-zinc-500'; ?> px-6 py-4 flex items-center gap-3 hover:bg-zinc-100 transition-all" href="habitaciones.php">
            <span class="material-symbols-outlined <?php echo ($pagina_actual == 'habitaciones.php') ? 'text-yellow-400' : ''; ?>">bed</span>
            Habitaciones para ti
        </a>
        
        <!-- Enlace: Mis Reservas -->
        <a class="<?php echo ($pagina_actual == 'misReservas.php') ? 'border-l-4 border-yellow-400 bg-white text-black font-bold' : 'text-zinc-500'; ?> px-6 py-4 flex items-center gap-3 hover:bg-zinc-100 transition-all" href="misReservas.php">
            <span class="material-symbols-outlined <?php echo ($pagina_actual == 'misReservas.php') ? 'text-yellow-400' : ''; ?>">calendar_today</span>
            Mis Reservas
        </a>
        
        <!-- Enlace: Servicios (TEMPORALMENTE DESHABILITADO) -->
        <!-- 
        <a class="<?php echo ($pagina_actual == 'servicios.php') ? 'border-l-4 border-yellow-400 bg-white text-black font-bold' : 'text-zinc-500'; ?> px-6 py-4 flex items-center gap-3 hover:bg-zinc-100 transition-all" href="servicios.php">
            <span class="material-symbols-outlined <?php echo ($pagina_actual == 'servicios.php') ? 'text-yellow-400' : ''; ?>">room_service</span>
            Servicios
        </a>
        -->
    </nav>
    
    <div class="mt-auto border-t border-zinc-200 sidebar-content opacity-0 whitespace-nowrap">
        <a class="flex items-center gap-3 px-6 py-6 font-bold text-error hover:bg-red-50 transition-colors" href="../Login/cerrarSesion.php">
            <span class="material-symbols-outlined">logout</span>
            Cerrar Sesión
        </a>
    </div>
</aside>
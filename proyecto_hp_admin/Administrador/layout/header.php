<?php
// Admin/layout/header.php
// Requiere que $paginaActual esté definida antes de incluir este archivo.
// Ejemplo: $paginaActual = 'inicio'; (valores: inicio, registros, reservas, huespedes)
// También requiere que $tituloPagina esté definida. Ejemplo: $tituloPagina = 'Panel de Administración';

require_once __DIR__ . '/seguridad.php'; // Seguridad centralizada: sesión, caché, rol

if (!isset($paginaActual)) $paginaActual = '';
if (!isset($tituloPagina)) $tituloPagina = 'Hospedaje Ayacucho';

$menu = [
    ['key' => 'inicio',     'href' => 'inicioAdmin.php',        'icon' => 'dashboard',       'label' => 'Inicio'],
    ['key' => 'registros',  'href' => 'registroHuespedes.php',  'icon' => 'database',         'label' => 'Registros'],
    ['key' => 'reservas',   'href' => 'reservas.php',           'icon' => 'event_available',  'label' => 'Reservas'],
    ['key' => 'huespedes',  'href' => 'huespedesActuales.php',  'icon' => 'group',            'label' => 'Huéspedes Actuales'],
];
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($tituloPagina) ?> - Hospedaje Ayacucho</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#705d00",
                        "error": "#ba1a1a",
                        "on-background": "#1a1c1c",
                        "surface": "#f9f9f9",
                        "primary-container": "#ffd600",
                        "on-primary-container": "#705d00"
                    },
                    fontFamily: {
                        "inter": ["Inter", "sans-serif"]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9f9;
        }
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .content-transition {
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-content {
            transition: opacity 0.2s ease-in-out;
        }
        /* Bento grid global */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 24px;
        }
    </style>
    <?php if (isset($estilosExtra)) echo $estilosExtra; ?>
</head>
<body class="text-on-background bg-surface antialiased font-inter">

<!-- ═══════════════════════════════════════════════
     HEADER
═══════════════════════════════════════════════ -->
<header class="bg-white border-b border-zinc-100 shadow-sm flex justify-between items-center h-16 px-6 w-full sticky top-0 z-50 tracking-tight">
    <div class="flex items-center gap-3">
        <!-- Toggle Sidebar -->
        <button id="btnToggleSidebar"
                title="Abrir/Cerrar menú"
                class="material-symbols-outlined text-zinc-700 hover:text-black p-2 rounded-sm hover:bg-zinc-100 transition-colors">
            menu
        </button>
        <!-- Logo + Nombre -->
        <a href="inicioAdmin.php" class="flex items-center gap-2">
            <img src="../Imagenes/logo.png" alt="Logo Hospedaje Ayacucho" class="h-10 w-auto object-contain">
            <span class="text-xl font-black uppercase tracking-widest hidden sm:block">HOSPEDAJE AYACUCHO</span>
        </a>
    </div>

    <!-- Navegación horizontal (md+) -->
    <div class="hidden md:flex items-center gap-8">
        <nav class="flex gap-5">
            <?php foreach ($menu as $item): ?>
                <a href="<?= $item['href'] ?>"
                   class="text-sm font-medium transition-colors duration-150 active:scale-95
                          <?= $paginaActual === $item['key']
                              ? 'text-black font-bold border-b-2 border-yellow-400'
                              : 'text-zinc-500 hover:text-yellow-500' ?>">
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Perfil -->
        <div class="flex items-center gap-4 border-l border-zinc-200 pl-6">
            <a href="perfilAdmin.php" class="flex items-center gap-3 group transition-transform active:scale-95" title="Ver Perfil">
                <div class="h-9 w-9 rounded-full bg-black flex items-center justify-center border-2 border-transparent group-hover:border-yellow-400 shadow-sm transition-all overflow-hidden">
                    <?php if (!empty($_SESSION['avatar'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['avatar']) ?>" alt="Admin" class="h-full w-full object-cover">
                    <?php else: ?>
                        <span class="material-symbols-outlined text-yellow-400 text-xl" style="font-variation-settings:'FILL' 1;">person</span>
                    <?php endif; ?>
                </div>
                <span class="text-sm font-bold text-zinc-700 group-hover:text-black hidden lg:block">
                    <?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?>
                </span>
            </a>
        </div>
    </div>

    <!-- Menú móvil: solo icono de perfil -->
    <div class="flex md:hidden items-center gap-2">
        <a href="perfilAdmin.php" title="Perfil" class="p-2">
            <span class="material-symbols-outlined text-zinc-700" style="font-variation-settings:'FILL' 1;">person</span>
        </a>
    </div>
</header>

<!-- ═══════════════════════════════════════════════
     WRAPPER principal
═══════════════════════════════════════════════ -->
<div class="flex min-h-[calc(100vh-64px)] relative overflow-x-hidden">

    <!-- ══════════════════════════
         SIDEBAR
    ══════════════════════════ -->
    <aside id="sidebar"
           class="sidebar-transition bg-zinc-50 w-0 -translate-x-full border-r border-zinc-200
                  fixed left-0 top-16 h-[calc(100vh-64px)] z-40 flex flex-col pt-8 overflow-hidden">

        <!-- Cabecera sidebar -->
        <div class="px-6 mb-8 sidebar-content opacity-0 whitespace-nowrap">
            <div class="flex items-center gap-3 mb-2">
                <div class="h-10 w-10 bg-black flex items-center justify-center rounded-sm shrink-0">
                    <span class="material-symbols-outlined text-yellow-400" style="font-variation-settings:'FILL' 1;">hotel</span>
                </div>
                <div>
                    <h2 class="font-black text-black leading-tight">Admin Panel</h2>
                    <p class="text-zinc-500 text-xs">Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Manager') ?></p>
                </div>
            </div>
        </div>

        <!-- Nav sidebar -->
        <nav class="flex-1 sidebar-content opacity-0 whitespace-nowrap">
            <?php foreach ($menu as $item):
                $activo = $paginaActual === $item['key'];
            ?>
                <a href="<?= $item['href'] ?>"
                   class="px-6 py-4 flex items-center gap-3 transition-all
                          <?= $activo
                              ? 'border-l-4 border-yellow-400 bg-white text-black font-bold'
                              : 'text-zinc-500 hover:bg-zinc-100 border-l-4 border-transparent' ?>">
                    <span class="material-symbols-outlined <?= $activo ? 'text-yellow-400' : '' ?>">
                        <?= $item['icon'] ?>
                    </span>
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Pie sidebar -->
        <div class="mt-auto border-t border-zinc-200 sidebar-content opacity-0 whitespace-nowrap">
            <a href="perfilAdmin.php"
               class="text-zinc-500 px-6 py-4 flex items-center gap-3 hover:bg-zinc-100 transition-all
                      <?= $paginaActual === 'perfil' ? 'border-l-4 border-yellow-400 bg-white text-black font-bold' : '' ?>">
                <span class="material-symbols-outlined">manage_accounts</span>
                Mi Perfil
            </a>
            <a href="#" class="text-zinc-500 px-6 py-4 flex items-center gap-3 hover:bg-zinc-100 transition-all">
                <span class="material-symbols-outlined">settings</span>
                Configuración
            </a>
            <button onclick="confirmarCerrarSesion()"
                    class="w-full text-left px-6 py-5 font-bold text-error flex items-center gap-3 hover:bg-red-50 transition-colors">
                <span class="material-symbols-outlined">logout</span>
                Cerrar Sesión
            </button>
        </div>
    </aside>

    <!-- ══════════════════════════
         CONTENIDO PRINCIPAL
    ══════════════════════════ -->
    <main id="main-content"
          class="content-transition flex-1 p-6 sm:p-10 max-w-[1440px] mx-auto w-full lg:pl-0">
<!-- El contenido de cada página va aquí. El footer cierra los tags. -->
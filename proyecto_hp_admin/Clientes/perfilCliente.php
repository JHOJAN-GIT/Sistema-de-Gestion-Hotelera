<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['id']) || strtolower($_SESSION['rol']) !== 'cliente') {
    header("Location: ../Login/login.php");
    exit();
}

$nombreUsuario = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Invitado';
// Datos dummy simulados que se conectarían con la BD
$dniUsuario = "74839201";
$correoUsuario = "cliente@hayacucho.com";
$telefonoUsuario = "+51 987 654 321";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Mi Perfil - Hospedaje Ayacucho</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    "colors": {
                        "primary": "#705d00",
                        "background": "#f9f9f9",
                        "surface": "#ffffff",
                        "on-surface": "#1a1c1c",
                        "primary-container": "#ffd600",
                        "on-primary-container": "#705d00"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem"
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { background-color: #f9f9f9; }
        .custom-card-shadow { box-shadow: 0px 4px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="antialiased text-on-surface bg-background font-['Inter']">

<header class="flex justify-between items-center h-16 px-8 w-full sticky top-0 z-[60] bg-white border-b border-zinc-200">
    <div class="flex items-center gap-4">
        <a href="mis_reservas.php" class="p-2 text-zinc-600 hover:bg-zinc-100 rounded-lg flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <span class="text-xl font-black tracking-tighter text-zinc-900 uppercase">Hospedaje Ayacucho</span>
    </div>
    <div class="flex items-center gap-4">
        <span class="text-xs font-bold bg-zinc-100 text-zinc-800 px-3 py-1.5 rounded-full uppercase tracking-wider">Perfil verificado</span>
    </div>
</header>

<main class="w-full max-w-[1200px] mx-auto p-6 md:p-10 space-y-8">
    
    <section class="bg-white border border-zinc-200 rounded-sm p-6 flex flex-col sm:flex-row items-center gap-6 custom-card-shadow">
        <div class="relative">
            <img class="w-24 h-24 rounded-full object-cover border-4 border-primary-container shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC16TfY01oOdIaH3i31RrxVSnrlD3jvPOi5PMb8k5tuUkT-B2BXN0y-PP_-s9Yh0s8YiblB10OiSK_3ekizOjT2DaYhtACs5u3qd5mawCr5_JbLABzvnBgLFs_30GBrK_B6fuwHAwMQACDn0QnsTpIt2_UN892V_vTvm_4X88XGs3Kc0cz2_uwF_NbGi9p0Yx0ct1KBhpNOo4bsQJYCNpLAAOdnQgxBckd76uJlqXOQCBwK2XSTkosIsD5JwXmwIvm_g98QWJ4Rlv4" alt="Avatar"/>
            <button class="absolute bottom-0 right-0 bg-zinc-900 text-white p-1.5 rounded-full hover:bg-primary transition-colors flex items-center justify-center shadow" title="Cambiar Foto">
                <span class="material-symbols-outlined text-xs">photo_camera</span>
            </button>
        </div>
        <div class="text-center sm:text-left flex-1">
            <h1 class="text-2xl font-black text-zinc-900 tracking-tight"><?php echo htmlspecialchars($nombreUsuario); ?></h1>
            <p class="text-sm text-zinc-500 font-mono mt-1">Cliente ID: #HA-<?php echo htmlspecialchars($dniUsuario); ?></p>
            <div class="mt-3 flex flex-wrap gap-2 justify-center sm:justify-start">
                <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-800 text-xs px-2.5 py-1 font-bold border border-yellow-200 rounded-sm">
                    <span class="material-symbols-outlined text-sm">stars</span> Huésped VIP Oro
                </span>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <section class="lg:col-span-7 bg-white border border-zinc-200 rounded-sm p-6 custom-card-shadow space-y-6">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 tracking-tight flex items-center gap-2">
                    <span class="material-symbols-outlined text-zinc-600">manage_accounts</span>
                    Información Personal y de Contacto
                </h2>
                <p class="text-xs text-zinc-400 mt-0.5">Mantén tus datos actualizados para agilizar los procesos de Check-in físico.</p>
            </div>

            <form action="#" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1">Nombre Completo</label>
                        <input type="text" value="<?php echo htmlspecialchars($nombreUsuario); ?>" class="w-full border-zinc-200 bg-zinc-50 focus:border-primary focus:ring-0 rounded-sm text-sm" readonly/>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1">DNI / Pasaporte</label>
                        <input type="text" value="<?php echo htmlspecialchars($dniUsuario); ?>" class="w-full border-zinc-200 bg-zinc-50 focus:border-primary focus:ring-0 rounded-sm text-sm" readonly/>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1">Correo Electrónico</label>
                        <input type="email" value="<?php echo htmlspecialchars($correoUsuario); ?>" class="w-full border-zinc-200 focus:border-primary focus:ring-0 rounded-sm text-sm"/>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1">Teléfono Móvil</label>
                        <input type="text" value="<?php echo htmlspecialchars($telefonoUsuario); ?>" class="w-full border-zinc-200 focus:border-primary focus:ring-0 rounded-sm text-sm"/>
                    </div>
                </div>

                <hr class="border-zinc-100 my-2" />

                <div>
                    <h3 class="text-sm font-bold text-zinc-900 mb-3 flex items-center gap-1">
                        <span class="material-symbols-outlined text-zinc-600 text-sm">tune</span> Preferencias de Habitación
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs text-zinc-500 mb-1">Tipo de Almohada</label>
                            <select class="w-full border-zinc-200 rounded-sm text-xs focus:ring-0 focus:border-primary">
                                <option>Plumas (Suave)</option>
                                <option selected>Viscoelástica (Firme)</option>
                                <option>Antialérgica</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-500 mb-1">Preferencia de Piso</label>
                            <select class="w-full border-zinc-200 rounded-sm text-xs focus:ring-0 focus:border-primary">
                                <option>Pisos Bajos (3)</option>
                                <option selected>Pisos Altos (5 o 6)</option>
                                <option>Indiferente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-500 mb-1">Temperatura Ideal</label>
                            <select class="w-full border-zinc-200 rounded-sm text-xs focus:ring-0 focus:border-primary">
                                <option>21°C</option>
                                <option selected>23°C</option>
                                <option>Sin preferencia</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="button" class="bg-zinc-900 text-white hover:bg-primary px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-sm transition-all shadow-sm">
                        Guardar Cambios de Perfil
                    </button>
                </div>
            </form>
        </section>

        <div class="lg:col-span-5 space-y-6">
            <section class="bg-white border border-zinc-200 rounded-sm p-6 custom-card-shadow">
                <h2 class="text-md font-bold text-zinc-900 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-zinc-600">history</span>
                    Historial de Estadías Recientes
                </h2>
                <div class="divide-y divide-zinc-100">
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-bold text-zinc-800">Habitación 502 (Matrimonial)</p>
                            <p class="text-xs text-zinc-400">02 May 2026 - 05 May 2026</p>
                        </div>
                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase border border-emerald-200">Completada</span>
                    </div>
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-bold text-zinc-800">Habitación 301 (Momentáneo)</p>
                            <p class="text-xs text-zinc-400">14 Abr 2026 • 3 horas</p>
                        </div>
                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase border border-emerald-200">Completada</span>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-zinc-200 rounded-sm p-6 custom-card-shadow space-y-4">
                <h2 class="text-md font-bold text-zinc-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-zinc-600">lock</span>
                    Seguridad de la Cuenta
                </h2>
                <p class="text-xs text-zinc-500">¿Deseas cambiar tu clave de acceso al portal de huéspedes?</p>
                <button type="button" class="w-full text-center border border-zinc-200 bg-zinc-50 hover:bg-zinc-100 text-zinc-800 font-bold py-2 text-xs uppercase tracking-wider rounded-sm transition-all shadow-sm">
                    Restablecer Contraseña
                </button>
            </section>
        </div>
    </div>
</main>

</body>
</html>
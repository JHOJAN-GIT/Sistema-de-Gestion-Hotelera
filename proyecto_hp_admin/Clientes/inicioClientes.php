<?php
// 1. Destruir caché para seguridad (Obligatorio mantenerlo al inicio de cada archivo final)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 2. Importamos la Cabecera (Esto ya incluye session_start, verificación de rol y los estilos)
require_once 'layout/header.php';

// 3. Importamos el Menú Lateral
require_once 'layout/sidebar.php';
?>

<!-- 4. CONTENIDO EXCLUSIVO DEL DASHBOARD -->
<main id="content-main" class="content-transition flex-1 pt-6 pb-6 sm:pt-10 sm:pb-10 w-full lg:pl-0">
    <div class="max-w-[1440px] w-full mx-auto px-6 sm:px-10 flex flex-col flex-1">
        
        <!-- Sección de Bienvenida -->
        <section class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
            <div>
                <h1 class="text-4xl font-extrabold text-zinc-900 tracking-tight">
                    Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?>
                </h1>
                <p class="text-lg text-zinc-500 mt-2">Disfruta de todo lo que H. Ayacucho tiene para ti.</p>
            </div>
            <div class="flex gap-4">
                
                <a href="reservar.php" class="flex items-center gap-2 bg-primary-container text-zinc-900 px-6 py-3 font-bold active:scale-95 duration-150 text-sm rounded-sm shadow-sm">
                    <span class="material-symbols-outlined">add_circle</span>
                    Nueva Reserva
                </a>
            </div>
        </section>

        <!-- Grid Principal de Contenidos -->
        <div class="grid grid-cols-12 gap-8 mb-16">
            
            <!-- Columna Izquierda: Espacios Destacados -->
            <div class="col-span-12 lg:col-span-8 space-y-6">
                <div class="flex justify-between items-center bg-zinc-50 p-4 rounded-sm border border-zinc-100 shadow-sm">
                    <h2 class="text-xl font-bold text-zinc-900 tracking-tight">Espacios Destacados</h2>
                    <a class="inline-flex items-center gap-2 bg-white border border-zinc-200 text-zinc-900 hover:bg-zinc-900 hover:text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-sm shadow-sm transition-all duration-200 group" href="habitaciones.php">
                        <span>Ver todas las habitaciones</span>
                        <span class="material-symbols-outlined text-sm text-yellow-500 transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- MATRIMONIAL PREMIUM — bloque grande -->
                    <a href="habitaciones.php?filtro=matrimonial" class="relative group overflow-hidden bg-zinc-200 aspect-[4/5] md:aspect-auto block">
                        <img alt="Matrimonial Premium" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800"/>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-6 text-white">
                            <span class="bg-yellow-400 text-black text-[10px] font-bold uppercase px-2 py-1 w-fit mb-2">Disponible</span>
                            <h3 class="text-xl font-bold">Matrimonial Premium</h3>
                            <p class="text-sm text-zinc-300">Ambiente romántico • Cama King</p>
                        </div>
                    </a>

                    <div class="grid grid-rows-2 gap-4">

                        <!-- INDIVIDUAL -->
                        <a href="habitaciones.php?filtro=individual" class="relative group overflow-hidden bg-zinc-200 aspect-video md:aspect-auto block">
                            <img alt="Individual" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1631049552240-59c37f38802b?w=600"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-4 text-white">
                                <h3 class="font-bold">Individual</h3>
                            </div>
                        </a>

                        <!-- DOBLE -->
                        <a href="habitaciones.php?filtro=doble" class="relative group overflow-hidden bg-zinc-200 aspect-video md:aspect-auto block">
                            <img alt="Doble" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=600"/>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-4 text-white">
                                <h3 class="font-bold">Doble</h3>
                            </div>
                        </a>

                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Programa de Fidelidad -->
            <div class="col-span-12 lg:col-span-4 space-y-6">
                <div class="bg-primary-container p-8 relative overflow-hidden flex flex-col justify-between min-h-[240px]">
                    <div class="relative z-10">
                        <h2 class="text-2xl font-black mb-2 text-zinc-900 tracking-tight">Programa de Fidelidad</h2>
                        <p class="text-zinc-800 text-sm leading-relaxed">Los huéspedes VIP ahora disfrutan de salida tardía garantizada (Late Checkout) hasta las 4:00 PM sin costo.</p>
                    </div>
                    <span class="absolute -right-8 -bottom-8 material-symbols-outlined text-[160px] opacity-10 text-zinc-900 rotate-12">stars</span>
                </div>

                <!-- Beneficios por Tipo de Habitación -->
                <div class="bg-white p-6 custom-card-shadow border border-zinc-100 space-y-4">
                    <h2 class="text-lg font-bold mb-4 text-zinc-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">card_giftcard</span>
                        Beneficios por Habitación
                    </h2>

                    <!-- Momentáneos -->
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-blue-600 text-xl">schedule</span>
                            <div>
                                <h3 class="font-bold text-sm text-zinc-900">Momentáneas</h3>
                                <p class="text-xs text-zinc-600 flex items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-xs">water_drop</span>
                                    Agua de cortesía
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Individual -->
                    <div class="border-l-4 border-green-500 pl-4 py-2">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-green-600 text-xl">person</span>
                            <div>
                                <h3 class="font-bold text-sm text-zinc-900">Individual</h3>
                                <p class="text-xs text-zinc-600 flex items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-xs">water_drop</span>
                                    Agua + Pastel
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Doble -->
                    <div class="border-l-4 border-purple-500 pl-4 py-2">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-purple-600 text-xl">group</span>
                            <div>
                                <h3 class="font-bold text-sm text-zinc-900">Doble</h3>
                                <p class="text-xs text-zinc-600 flex items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-xs">restaurant</span>
                                    Agua + Desayuno
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Matrimonial -->
                    <div class="border-l-4 border-yellow-500 pl-4 py-2">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-yellow-600 text-xl">favorite</span>
                            <div>
                                <h3 class="font-bold text-sm text-zinc-900">Matrimonial</h3>
                                <p class="text-xs text-zinc-600 flex items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-xs">coffee</span>
                                    Cafés + Comedor VIP
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

<?php
// 5. Cierre de Estructura, Footer y Scripts (Cierra las etiquetas main, body y html automáticamente)
require_once 'layout/footer.php';
?>

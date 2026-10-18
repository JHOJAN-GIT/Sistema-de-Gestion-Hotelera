<?php
session_start();
// Control de seguridad idéntico al tuyo
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . "/../Conexion/conexion.php");
$conn = Cconexion::ConexionBD();

// Consulta estructurada para listar cada habitación disponible con los datos de su tipo
$sql = "SELECT h.NumeroHabitacion, h.Piso, h.Estado, 
               th.IdTipo, th.NombreTipo, th.Precio, th.Descripcion 
        FROM dbo.Habitaciones h
        INNER JOIN dbo.TiposHabitacion th ON h.IdTipo = th.IdTipo
        WHERE h.Estado = 'Libre'
        ORDER BY h.NumeroHabitacion ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupamos las habitaciones por su Tipo para crear las filas ordenadas en el Frontend
$gruposHabitaciones = [];
foreach ($habitaciones as $hab) {
    $gruposHabitaciones[$hab['NombreTipo']][] = $hab;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Explora Nuestras Habitaciones - Hospedaje Ayacucho</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary-container": "#ffd600",
                        "surface": "#f9f9f9",
                        "on-surface": "#1a1c1c"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { background-color: #f9f9f9; }
        .custom-card-shadow { box-shadow: 0px 4px 20px rgba(0,0,0,0.03); }
    </style>
</head>
<body class="antialiased font-['Inter'] text-on-surface flex flex-col min-h-screen relative">

<!-- Cabecera Superior -->
<header class="flex justify-between items-center h-16 px-8 w-full sticky top-0 z-[50] bg-white border-b border-zinc-200">
    <div class="flex items-center gap-4"> 
        <span class="text-xl font-black tracking-tighter text-zinc-900 uppercase">Hospedaje Ayacucho</span>
    </div>
</header>

<!-- Contenido Principal -->
<main class="flex-1 p-10 max-w-[1440px] mx-auto w-full flex flex-col">
    
    <!-- Sección del Título principal -->
    <section class="mb-12">
        <h1 class="text-4xl font-extrabold text-zinc-900 tracking-tight">Explora Nuestras Habitaciones</h1>
        <p class="text-lg text-zinc-500 mt-2">Selecciona la habitación de tu preferencia agrupadas por categorías y niveles.</p>
    </section>

    <!-- Listado Dinámico por Grupos/Filas -->
    <?php if (count($gruposHabitaciones) > 0): ?>
        <?php foreach ($gruposHabitaciones as $nombreTipo => $listaHabitaciones): ?>
            
            <section class="mb-12">
                <!-- Encabezado de Fila (Tipo de Habitación) -->
                <div class="flex items-center gap-3 mb-6 border-b border-zinc-200 pb-3">
                    <span class="material-symbols-outlined text-zinc-800 text-2xl">bed</span>
                    <h2 class="text-2xl font-black tracking-tight text-zinc-900 uppercase text-sm"><?php echo htmlspecialchars($nombreTipo); ?></h2>
                    <span class="bg-zinc-100 text-zinc-600 text-xs font-bold px-2.5 py-0.5 rounded-full ml-2">
                        <?php echo count($listaHabitaciones); ?> disponibles
                    </span>
                </div>

                <!-- Grid de las Habitaciones Físicas de este Tipo -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    <?php foreach ($listaHabitaciones as $habitacion): 
                        // Definición dinámica de la imagen según el número de habitación mediante PHP
                        $numero = $habitacion['NumeroHabitacion'];
                        $rutaImagen = "imagenes/default.jpg";
                        
                        if ($numero >= 300 && $numero <= 399) {
                            $rutaImagen = "imagenes/momentanea.jpg";
                        } elseif ($numero >= 400 && $numero <= 499) {
                            $rutaImagen = "imagenes/individual.jpg";
                        } elseif ($numero >= 500 && $numero <= 599) {
                            $rutaImagen = "imagenes/doble.jpg";
                        } elseif ($numero >= 600 && $numero <= 699) {
                            $rutaImagen = "imagenes/premium.jpg";
                        }
                    ?>
                        <div class="bg-white border border-zinc-200 rounded-sm flex flex-col justify-between custom-card-shadow overflow-hidden group relative">
                            
                            <!-- Indicador Flotante de Número de Cuarto -->
                            <div class="absolute top-3 left-3 z-10 bg-zinc-900 text-white font-black text-sm px-3 py-1 rounded-sm shadow-sm tracking-wide">
                                H-<?php echo $numero; ?>
                            </div>

                            <!-- Contenedor de la Imagen (Al hacer clic abre el Modal detallado) -->
                            <div onclick="abrirModal(<?php echo htmlspecialchars(json_encode(array_merge($habitacion, ['RutaImg' => $rutaImagen]))); ?>)" 
                                 class="relative overflow-hidden aspect-video bg-zinc-100 cursor-pointer">
                                <img src="http://localhost/phpSQLserver/<?php echo $rutaImagen; ?>" 
                                     alt="Habitación <?php echo $numero; ?>"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                     onerror="this.onerror=null;this.src=''">
                                <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="bg-white/90 text-zinc-900 text-xs font-bold px-3 py-1.5 shadow-md uppercase tracking-wider rounded-sm">Ver detalles</span>
                                </div>
                            </div>
                            
                            <!-- Información de la Tarjeta -->
                            <div class="p-4 flex flex-col justify-between flex-1">
                                <div class="mb-4">
                                    <div class="flex justify-between items-baseline mb-2">
                                        <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Piso <?php echo $habitacion['Piso']; ?></span>
                                        <span class="text-base font-black text-zinc-900">S/ <?php echo number_format($habitacion['Precio'], 2); ?></span>
                                    </div>
                                    <p class="text-xs text-zinc-500 line-clamp-2 min-h-[2rem] leading-relaxed">
                                        <?php echo htmlspecialchars($habitacion['Descripcion']); ?>
                                    </p>
                                </div>
                                
                                <!-- Botón de Acción Directa -->
                                <div class="mt-auto">
                                    <a href="reservar.php?habitacion=<?php echo $numero; ?>" class="block w-full text-center bg-zinc-100 text-zinc-900 py-2 font-bold active:scale-95 duration-150 text-xs uppercase tracking-wider rounded-sm hover:bg-primary-container transition-colors">
                                        Reservar Cuarto
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-zinc-50 border border-zinc-200 rounded-sm p-12 text-center max-w-xl mx-auto my-12">
            <span class="material-symbols-outlined text-zinc-400 text-6xl mb-4">bed</span>
            <p class="text-lg font-semibold text-zinc-700">No hay habitaciones disponibles en este momento.</p>
            <p class="text-sm text-zinc-400 mt-1">Por favor, verifique el estado con administración o regrese más tarde.</p>
        </div>
    <?php endif; ?>

    <!-- Botón Inferior para Regresar -->
    <div class="flex justify-center mt-8 mb-16">
        <a href="inicio.php" class="inline-flex items-center gap-2 bg-zinc-900 text-white px-6 py-3 font-bold active:scale-95 duration-150 text-sm rounded-sm shadow-sm hover:bg-zinc-800 transition-colors">
            <span class="material-symbols-outlined text-yellow-400">arrow_back</span>
            Volver al Inicio
        </a>
    </div>

    <!-- Footer -->
    <footer class="mt-auto border-t border-zinc-200 pt-12 pb-8 w-full">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <div>
                <h3 class="text-lg font-black tracking-tighter text-zinc-900 uppercase mb-4">Hospedaje Ayacucho</h3>
                <p class="text-sm text-zinc-500 leading-relaxed">Brindando hospitalidad de lujo y confort en el corazón de la ciudad desde 2010.</p>
            </div>
            <div>
                <h4 class="font-bold text-zinc-900 mb-4 uppercase text-xs tracking-widest">Navegación</h4>
                <ul class="space-y-2 text-sm text-zinc-600">
                    <li><a href="#" class="hover:text-primary transition-colors">Inicio</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Habitaciones</a></li>
                    <li><a href="servicios.php" class="hover:text-primary transition-colors">Servicios</a></li>
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

<!-- VENTANA MODAL EMERGENTE (DETALLES COMPLETOS Y TÉRMINOS) -->
<div id="modalDetalle" class="fixed inset-0 bg-black/60 z-[100] hidden backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300">
    <div class="bg-white border border-zinc-200 max-w-2xl w-full rounded-sm overflow-hidden shadow-2xl relative flex flex-col animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Botón de Cierre del Modal -->
        <button onclick="cerrarModal()" class="absolute top-3 right-3 z-50 bg-white/80 hover:bg-white text-zinc-900 w-8 h-8 rounded-full flex items-center justify-center transition-colors shadow">
            <span class="material-symbols-outlined text-sm font-bold">close</span>
        </button>

        <!-- Imagen Destacada del Modal -->
        <div class="relative w-full aspect-video bg-zinc-100">
            <img id="modalImagen" src="" alt="Habitación" class="w-full h-full object-cover">
            <div class="absolute bottom-4 left-4 bg-zinc-900/90 text-white font-black text-lg px-4 py-1.5 rounded-sm shadow">
                Habitación <span id="modalNumero"></span>
            </div>
        </div>

        <!-- Contenido Detallado -->
        <div class="p-6 overflow-y-auto max-h-[50vh]">
            <div class="flex justify-between items-center mb-4">
                <span id="modalTipo" class="text-xs font-black uppercase tracking-widest bg-zinc-100 px-3 py-1 text-zinc-800 rounded-sm"></span>
                <span class="text-2xl font-black text-zinc-950">S/ <span id="modalPrecio"></span></span>
            </div>

            <h3 class="font-bold text-zinc-900 text-sm uppercase tracking-wide mb-2">Descripción General</h3>
            <p id="modalDescripcion" class="text-sm text-zinc-600 leading-relaxed mb-6"></p>

            <hr class="border-zinc-100 mb-5" />

            <!-- Políticas de Hospedaje y Horarios Estructurados Profesionalmente -->
            <h3 class="font-bold text-zinc-900 text-sm uppercase tracking-wide mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500 text-lg">gavel</span>
                Términos, Condiciones y Normas
            </h3>
            <div class="bg-zinc-50 border border-zinc-200 p-4 rounded-sm text-xs text-zinc-600 space-y-3 leading-relaxed">
                <p><strong>• Control Horario de Estancia (Check-In / Check-Out):</strong> El ciclo operativo diario da inicio estrictamente a las <strong>08:00 AM</strong>. La hora límite estipulada para la desocupación de la habitación (Check-Out) corresponde invariablemente a las <strong>12:00 PM (Mediodía)</strong> del día posterior al registro de entrada.</p>
                <p><strong>• Cláusula de Ingreso Anticipado:</strong> En caso de efectuar el ingreso antes del inicio de la jornada hotelera habitual (ej. 07:59 AM), la reserva vencerá indefectiblemente a las 12:00 PM de ese mismo día en curso. Todo ingreso posterior a las 08:00 AM prorroga la salida hasta el mediodía de la fecha subsiguiente.</p>
                <p><strong>• Prolongación de Estadía:</strong> Para extensiones de tiempo de servicio en habitaciones momentáneas, el cliente deberá gestionar la adición de horas por medio de este portal web institucional o dar aviso inmediato al personal de recepción con antelación.</p>
            </div>
        </div>

        <!-- Barra de Acción del Modal -->
        <div class="p-4 bg-zinc-50 border-t border-zinc-200 flex justify-end gap-3">
            <button onclick="cerrarModal()" class="px-4 py-2 text-xs font-bold text-zinc-500 uppercase tracking-wider hover:text-zinc-800 transition-colors">
                Cerrar
            </button>
            <a id="modalLinkReservar" href="" class="bg-zinc-950 text-white px-6 py-2 text-xs font-bold uppercase tracking-wider rounded-sm shadow hover:bg-zinc-800 transition-colors flex items-center gap-2">
                Proceder Reserva
            </a>
        </div>
    </div>
</div>

<!-- LÓGICA DE CONTROL DEL MODAL POR JAVASCRIPT -->
<script>
function abrirModal(datos) {
    const modal = document.getElementById('modalDetalle');
    
    // Inyección dinámica de variables
    document.getElementById('modalImagen').src = 'http://localhost/phpSQLserver/' + datos.RutaImg;
    document.getElementById('modalNumero').innerText = datos.NumeroHabitacion;
    document.getElementById('modalTipo').innerText = datos.NombreTipo;
    document.getElementById('modalPrecio').innerText = parseFloat(datos.Precio).toFixed(2);
    document.getElementById('modalDescripcion').innerText = datos.Descripcion;
    
    // Construcción del hipervínculo dinámico de reserva con el número de habitación específico
    document.getElementById('modalLinkReservar').href = 'reservar.php?habitacion=' + datos.NumeroHabitacion;
    
    // Remoción de la clase hidden para visualizar la interfaz modal
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden'); // Evita scroll de fondo
}

function cerrarModal() {
    const modal = document.getElementById('modalDetalle');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Cierre del modal alternativo si el usuario presiona fuera del recuadro blanco
window.onclick = function(event) {
    const modal = document.getElementById('modalDetalle');
    if (event.target == modal) {
        cerrarModal();
    }
}
</script>

</body>
</html>
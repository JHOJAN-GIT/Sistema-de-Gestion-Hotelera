<?php
// 1. Cabecera modular (Maneja session_start, rol check, Tailwind y estilos del head)
require_once 'layout/header.php';

// 2. Conexión y consulta a la Base de Datos (Exclusivo de esta vista)
require_once("../Conexion/conexion.php");
$conn = Cconexion::ConexionBD();

$correoUsuario = isset($_SESSION['correo']) ? $_SESSION['correo'] : '';
$nombreUsuario = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Invitado';

$reservas = [];

if (!empty($correoUsuario)) {
    try {
        // ✅ Ahora usa IdUsuario directamente desde la sesión
        $idUsuario = $_SESSION['id'];
        
        $sql = "SELECT IdReserva, TipoHabitacion, NombreCompleto, DNI, DiasHospedaje, 
                       FechaHoraLlegada, Costo, NumeroHabitacion, ServicioEspecial
                FROM ReservasClientes 
                WHERE IdUsuario = :idUsuario
                ORDER BY IdReserva DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        echo "<div style='padding:20px; background:#ffdad6; color:#93000a; font-family:sans-serif;'>";
        echo "<b>Error de Base de Datos:</b> No se pudieron cargar las reservas. " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

// 3. Menú lateral modular
require_once 'layout/sidebar.php';
?>

<!-- 4. CONTENIDO EXCLUSIVO DE LA VISTA DE MIS RESERVAS -->
<main id="content-main" class="content-transition flex-1 w-full flex flex-col lg:pl-0">
    <div class="max-w-[1440px] w-full mx-auto px-6 sm:px-10 py-6 sm:py-10 flex flex-col flex-1">
    
        <section class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
            <div>
                <h1 class="text-4xl font-extrabold text-zinc-900 tracking-tight">Historial de Reservas</h1>
                <p class="text-lg text-zinc-500 mt-2">Revisa tus estancias pasadas y activas. Haz clic en cualquier tarjeta para ver los detalles completos.</p>
            </div>
        </section>

        <div class="space-y-4 mb-12">
            <?php if (count($reservas) > 0): ?>
                <?php foreach ($reservas as $reserva): 
                    $estado = isset($reserva['Estado']) ? strtolower($reserva['Estado']) : 'pendiente';
                    $badgeClass = "bg-zinc-100 text-zinc-800";
                    if($estado == 'confirmada' || $estado == 'activo') { $badgeClass = "bg-green-100 text-green-800"; }
                    elseif($estado == 'cancelada') { $badgeClass = "bg-red-100 text-red-800"; }
                ?>
                    <div onclick="abrirModal(<?php echo htmlspecialchars(json_encode($reserva)); ?>)" 
                        class="bg-white border border-zinc-200 p-6 rounded-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 custom-card-shadow hover:border-zinc-400 cursor-pointer transition-all active:scale-[0.99] duration-100">
                        
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-zinc-100 text-zinc-800 flex items-center justify-center rounded-sm shrink-0">
                                <span class="material-symbols-outlined text-2xl">hotel</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-zinc-900 tracking-tight">Reserva Código #<?php echo htmlspecialchars($reserva['IdReserva']); ?></h3>
                                <p class="text-sm text-zinc-500 mt-0.5">
                                    Habitación: <span class="font-semibold text-zinc-700"><?php echo htmlspecialchars($reserva['TipoHabitacion']); ?></span>
                                    <?php if (!empty($reserva['NumeroHabitacion'])): ?>
                                     | N°: <span class="font-semibold text-zinc-700"><?php echo htmlspecialchars($reserva['NumeroHabitacion']); ?></span>
                                    <?php endif; ?>
                                    | Llegada: <span class="font-semibold text-zinc-700"><?php echo htmlspecialchars($reserva['FechaHoraLlegada']); ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end border-t md:border-none pt-4 md:pt-0">
                            <div class="text-left md:text-right">
                                <p class="text-xs text-zinc-400 font-bold uppercase tracking-wider">Costo Total</p>
                                <p class="text-xl font-black text-zinc-900 tracking-tight">S/ <?php echo number_format($reserva['Costo'], 2); ?></p>
                            </div>
                            <span class="px-3 py-1 text-xs font-bold uppercase rounded-full bg-green-100 text-green-800">
                                Registrado
                            </span>
                            <span class="material-symbols-outlined text-zinc-400 hidden md:block">chevron_right</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-zinc-50 border border-zinc-200 rounded-sm p-12 text-center max-w-xl mx-auto my-12">
                    <span class="material-symbols-outlined text-zinc-400 text-6xl mb-4">calendar_today</span>
                    <p class="text-lg font-semibold text-zinc-700">No registras ninguna reserva en nuestro sistema.</p>
                    <p class="text-sm text-zinc-400 mt-1">Si crees que esto es un error, por favor ponte en contacto con administración.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal estructural -->
        <div id="modal-detalle" class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white border border-zinc-200 w-full max-w-xl p-8 rounded-sm custom-card-shadow relative transform scale-95 transition-transform duration-300" id="modal-box">
                
                <button onclick="cerrarModal()" class="absolute top-6 right-6 text-zinc-400 hover:text-black transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <h2 class="text-2xl font-black text-zinc-900 tracking-tight mb-6 uppercase pb-2 border-b border-zinc-100">Detalles Expandidos de Reserva</h2>
                
                <div class="space-y-4 text-sm" id="modal-content-placeholder"></div>

                <div class="mt-8 pt-4 border-t border-zinc-100 flex justify-end">
                    <button onclick="cerrarModal()" class="bg-zinc-900 text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-sm active:scale-95 duration-150 shadow-sm">
                        Cerrar Detalles
                    </button>
                </div>
            </div>
        </div>

    </div>
    <!-- ✅ NO se cierra </main> aquí, lo hace footer.php -->

<script>
function abrirModal(datos) {
    const modal = document.getElementById('modal-detalle');
    const modalBox = document.getElementById('modal-box');
    const placeholder = document.getElementById('modal-content-placeholder');

    let html = '';
    const etiquetas = {
        'IdReserva': 'Código de Reserva',
        'NombreCompleto': 'Nombre Completo',
        'DNI': 'DNI del Huésped',
        'TipoHabitacion': 'Tipo de Habitación',
        'NumeroHabitacion': 'Número de Habitación',
        'DiasHospedaje': 'Días de Hospedaje',
        'FechaHoraLlegada': 'Fecha y Hora de Llegada',
        'Costo': 'Costo Total',
        'ServicioEspecial': 'Servicio Especial'
    };

    for (const [clave, etiqueta] of Object.entries(etiquetas)) {
        if (datos[clave] !== undefined && datos[clave] !== null && datos[clave] !== '') {
            let valor = datos[clave];
            if (clave === 'Costo') valor = 'S/ ' + parseFloat(valor).toFixed(2);
            html += `
                <div class="flex justify-between items-start gap-4 py-3 border-b border-zinc-100 last:border-none">
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider w-40 shrink-0">${etiqueta}</span>
                    <span class="text-sm font-semibold text-zinc-800 text-right">${valor}</span>
                </div>`;
        }
    }

    placeholder.innerHTML = html || '<p class="text-zinc-400 text-sm">No hay detalles adicionales disponibles.</p>';

    modal.classList.remove('opacity-0', 'pointer-events-none');
    modalBox.classList.remove('scale-95');
    modalBox.classList.add('scale-100');
    document.body.classList.add('overflow-hidden');
}

function cerrarModal() {
    const modal = document.getElementById('modal-detalle');
    const modalBox = document.getElementById('modal-box');

    modal.classList.add('opacity-0', 'pointer-events-none');
    modalBox.classList.remove('scale-100');
    modalBox.classList.add('scale-95');
    document.body.classList.remove('overflow-hidden');
}

window.onclick = function(event) {
    const modal = document.getElementById('modal-detalle');
    if (event.target == modal) { cerrarModal(); }
}
</script>

<?php
// 5. Cierre estructural, Footer y Scripts JS
require_once 'layout/footer.php';
?>
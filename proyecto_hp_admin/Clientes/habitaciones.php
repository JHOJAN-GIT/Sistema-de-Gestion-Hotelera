<?php
require_once 'layout/header.php';
require_once(__DIR__ . "/../Conexion/conexion.php");
$conn = Cconexion::ConexionBD();

$sql = "SELECT h.NumeroHabitacion, h.Piso, h.Estado, 
                th.IdTipo, th.NombreTipo, th.Precio, th.Descripcion, th.TipoHabitacion
        FROM dbo.Habitaciones h
        INNER JOIN dbo.TiposHabitacion th ON h.IdTipo = th.IdTipo
        WHERE h.Estado = 'Libre'
        ORDER BY h.NumeroHabitacion ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$gruposHabitaciones = [];
foreach ($habitaciones as $hab) {
    // Normalizar: "Simple" e "Individual" se agrupan bajo "Individual"
    $nombreMostrar = $hab['NombreTipo'];
    if (strtolower(trim($nombreMostrar)) === 'simple') {
        $nombreMostrar = 'Individual';
    }
    $gruposHabitaciones[$nombreMostrar][] = $hab;
}

require_once 'layout/sidebar.php';
?>

<main id="content-main" class="content-transition flex-1 w-full flex flex-col lg:pl-0">
    <div class="w-full px-8 py-6 flex flex-col flex-1">

        <section class="mb-8 flex justify-between items-end gap-4">
            <div>
                <h1 class="text-4xl font-extrabold text-zinc-900 tracking-tight uppercase">Inventario de Habitaciones</h1>
                <p class="text-base text-zinc-500 mt-1">Monitoreo técnico de unidades disponibles en tiempo real. Haz clic en las especificaciones para ver detalles.</p>
            </div>
            <div>
                <a href="reservar.php" class="inline-flex items-center gap-2 bg-zinc-950 text-white px-5 py-2.5 text-xs font-bold uppercase tracking-wider rounded-sm shadow-md hover:bg-zinc-800 transition-colors active:scale-95 duration-150">
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    Nueva Reserva Directa
                </a>
            </div>
        </section>

        <!-- FILTROS — sin Suite -->
        <div class="bg-white border border-zinc-200 p-4 rounded-sm shadow-sm mb-8 flex gap-2 items-center flex-wrap">
            <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider mr-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">filter_alt</span> Filtrar Categoría:
            </span>
            <button onclick="filtrarHabitaciones('todas')" id="btn-filtro-todas" class="btn-filtro px-4 py-1.5 text-xs font-extrabold uppercase tracking-wider rounded-sm bg-zinc-900 text-white transition-all shadow-sm">
                Todas
            </button>
            <button onclick="filtrarHabitaciones('individual')" id="btn-filtro-individual" class="btn-filtro px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-sm bg-zinc-100 text-zinc-600 hover:bg-zinc-200 transition-all">
                Individual
            </button>
            <button onclick="filtrarHabitaciones('doble')" id="btn-filtro-doble" class="btn-filtro px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-sm bg-zinc-100 text-zinc-600 hover:bg-zinc-200 transition-all">
                Doble
            </button>
            <button onclick="filtrarHabitaciones('matrimonial')" id="btn-filtro-matrimonial" class="btn-filtro px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-sm bg-zinc-100 text-zinc-600 hover:bg-zinc-200 transition-all">
                Matrimonial
            </button>
        </div>

        <!-- INVENTARIO -->
        <div id="contenedor-inventario" class="space-y-10 mb-12">
            <?php if (count($gruposHabitaciones) > 0): ?>
                <?php
                $imagenesHabitacion = [
                    301 => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=600',
                    302 => 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=600',
                    303 => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600',
                    304 => 'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=600',
                    305 => 'https://images.unsplash.com/photo-1464146072230-91cabc968266?w=600',

                    401 => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=600',
                    402 => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=600',
                    403 => 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?w=600',
                    404 => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600',
                    405 => 'https://images.unsplash.com/photo-1631049552240-59c37f38802b?w=600',

                    501 => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=600',
                    502 => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=600',
                    503 => 'https://images.unsplash.com/photo-1560347876-aeef00ee58a1?w=600',
                    504 => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?w=600',
                    505 => 'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?w=600',

                    601 => 'https://images.unsplash.com/photo-1549294413-26f195200c16?w=600',
                    602 => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600',
                    603 => 'https://images.unsplash.com/photo-1587985064135-0366536eab42?w=600',
                    604 => 'https://images.unsplash.com/photo-1561501878-aabd62634533?w=600',
                    605 => 'https://images.unsplash.com/photo-1576354302919-96748cb8299e?w=600',
                ];
                
                $descripcionesEspecificas = [
                    301 => 'Vista panorámica a la calle principal. Ambiente luminoso con ventana amplia. Ideal para estadías cortas.',
                    302 => 'Habitación interior tranquila. Cuenta con escritorio funcional. Perfecta para descanso sin ruidos.',
                    303 => 'Vista lateral con balcón privado. Incluye mini-refrigerador. Espacio acogedor y ventilado.',
                    304 => 'Habitación esquinera con doble ventana. Iluminación natural excepcional. Zona de lectura incluida.',
                    305 => 'Vista al jardín interno. Ambiente fresco y relajante. Cuenta con ventilador de techo adicional.',
                    
                    401 => 'Vista privilegiada a la plaza. Jacuzzi privado incluido. Decoración moderna y elegante.',
                    402 => 'Habitación amplia con sala de estar. Smart TV 43". Ideal para parejas o ejecutivos.',
                    403 => 'Vista a la calle con balcón francés. Cama king size premium. Baño con ducha de lluvia.',
                    404 => 'Habitación con terraza privada. Incluye mesa y sillas exteriores. Vista despejada del centro.',
                    405 => 'Suite junior con jacuzzi. Iluminación LED ambiental. Minibar y cafetera incluidos.',
                    
                    501 => 'Vista panorámica 180°. Jacuzzi doble con hidromasaje. Decoración romántica con luces LED.',
                    502 => 'Habitación premium con vestidor. Espejo de cuerpo completo. Baño con bañera y ducha separadas.',
                    503 => 'Vista al atardecer espectacular. Cama doble ortopédica. Sistema de sonido Bluetooth integrado.',
                    504 => 'Habitación con balcón amplio. Incluye hamaca paraguaya. Perfecta para relajación total.',
                    505 => 'Suite con sala de estar independiente. Smart TV 50". Ideal para estadías prolongadas.',
                    
                    601 => 'Habitación matrimonial de lujo. Jacuzzi con cromoterapia. Vista privilegiada de la ciudad.',
                    602 => 'Suite premium con chimenea eléctrica. Cama king con colchón memory foam. Baño con sauna.',
                    603 => 'Habitación con terraza jardín. Incluye columpio decorativo. Ambiente bohemio y acogedor.',
                    604 => 'Vista panorámica de 270°. Jacuzzi exterior privado. Decoración minimalista de lujo.',
                    605 => 'Suite presidencial. Sala, comedor y dormitorio separados. Jacuzzi doble y ducha escocesa.',
                ];
                
                $imgDefault = 'imagenes/default.jpg';
                ?>
                <?php foreach ($gruposHabitaciones as $nombreTipo => $listaHabitaciones):
                    $slugTipo = strtolower(trim($nombreTipo));
                ?>
                    <section data-tipo="<?php echo $slugTipo; ?>" class="seccion-habitacion transition-all duration-300">

                        <div class="flex items-center justify-between mb-5 border-b border-zinc-200 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-zinc-800 text-xl">bed</span>
                                <h2 class="text-sm font-black tracking-tight text-zinc-900 uppercase"><?php echo htmlspecialchars($nombreTipo); ?></h2>
                                <span class="bg-zinc-200 text-zinc-700 text-[11px] font-bold px-2 py-0.5 rounded-full ml-1">
                                    <?php echo count($listaHabitaciones); ?> Libres
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-5 gap-5">
                            <?php foreach ($listaHabitaciones as $habitacion):
                                $numero = $habitacion['NumeroHabitacion'];
                                $rutaImagen = $imagenesHabitacion[$numero] ?? $imgDefault;
                                $descripcionEspecifica = $descripcionesEspecificas[$numero] ?? $habitacion['Descripcion'];
                            ?>
                                <div class="bg-white border border-zinc-200 rounded-sm flex flex-col overflow-hidden group relative transition-all duration-200 hover:border-zinc-400 hover:shadow-md">

                                    <div class="absolute top-2.5 left-2.5 z-10 bg-zinc-900 text-white font-black text-xs px-2.5 py-1 rounded-sm tracking-wider">
                                        N° <?php echo $numero; ?>
                                    </div>

                                    <div onclick="abrirModal(<?php echo htmlspecialchars(json_encode(array_merge($habitacion, ['RutaImg' => $rutaImagen, 'NombreTipo' => $nombreTipo, 'DescripcionEspecifica' => $descripcionEspecifica]))); ?>)"
                                         class="relative overflow-hidden bg-zinc-100 cursor-pointer"
                                         style="height: 200px;">
                                        <img src="<?php echo $rutaImagen; ?>"
                                             alt="Habitación <?php echo $numero; ?>"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                             onerror="this.onerror=null;this.src='imagenes/default.jpg'">
                                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <span class="bg-white text-zinc-900 text-[10px] font-black px-2.5 py-1 shadow-md uppercase tracking-wider rounded-sm">Especificaciones</span>
                                        </div>
                                    </div>

                                    <div class="p-4 flex flex-col flex-1">
                                        <div class="flex justify-between items-baseline mb-1">
                                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wide">Piso <?php echo $habitacion['Piso']; ?></span>
                                            <span class="text-sm font-black text-zinc-950">S/ <?php echo number_format($habitacion['Precio'], 2); ?></span>
                                        </div>
                                        <p class="text-xs text-zinc-500 leading-relaxed mb-4" style="min-height: 2.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo htmlspecialchars($descripcionEspecifica); ?>
                                        </p>
                                        <div class="mt-auto">
                                            <a href="reservar.php?num_habitacion=<?php echo $numero; ?>&costo_base=<?php echo $habitacion['Precio']; ?>&tipo_estadia=<?php echo $habitacion['TipoHabitacion']; ?>" class="block w-full text-center bg-zinc-100 text-zinc-800 py-2 font-bold text-[11px] uppercase tracking-wider rounded-sm hover:bg-zinc-950 hover:text-white transition-all active:scale-[0.98] duration-100">
                                                Seleccionar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <div id="alerta-vacio" class="bg-zinc-50 border border-zinc-200 rounded-sm p-12 text-center max-w-xl mx-auto my-12">
                    <span class="material-symbols-outlined text-zinc-400 text-6xl mb-4">bed</span>
                    <p class="text-lg font-semibold text-zinc-700">No se detectaron unidades con el estado 'Libre' en el sistema.</p>
                </div>
            <?php endif; ?>

            <div id="alerta-filtro-vacio" class="hidden bg-zinc-50 border border-zinc-200 rounded-sm p-12 text-center max-w-xl mx-auto my-12">
                <span class="material-symbols-outlined text-zinc-400 text-6xl mb-4">search_off</span>
                <p class="text-lg font-semibold text-zinc-700">Búsqueda sin resultados.</p>
            </div>
        </div>

        <!-- MODAL -->
        <div id="modalDetalle" class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div id="modal-box" class="bg-white border border-zinc-200 w-full max-w-xl rounded-sm overflow-hidden custom-card-shadow relative transform scale-95 transition-transform duration-300 flex flex-col">
                <button onclick="cerrarModal()" class="absolute top-4 right-4 z-50 bg-white text-zinc-400 hover:text-black w-8 h-8 rounded-full flex items-center justify-center border border-zinc-200 shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>

                <div class="relative w-full bg-zinc-100" style="height: 260px;">
                    <img id="modalImagen" src="" alt="Habitación" class="w-full h-full object-cover">
                    <div class="absolute bottom-4 left-4 bg-zinc-900 text-white font-black text-sm px-3 py-1 rounded-sm shadow-md">
                        Habitación <span id="modalNumero"></span>
                    </div>
                </div>

                <div class="p-6 overflow-y-auto max-h-[40vh]">
                    <div class="flex justify-between items-center mb-4">
                        <span id="modalTipo" class="text-[10px] font-black uppercase tracking-widest bg-zinc-100 px-2.5 py-1 text-zinc-700 rounded-sm border border-zinc-200"></span>
                        <span class="text-xl font-black text-zinc-950">S/ <span id="modalPrecio"></span></span>
                    </div>
                    <h3 class="font-bold text-zinc-900 text-xs uppercase tracking-wider mb-2">Descripción Técnica</h3>
                    <p id="modalDescripcion" class="text-xs text-zinc-600 leading-relaxed mb-6"></p>
                    <hr class="border-zinc-100 mb-4" />
                    <h3 class="font-bold text-zinc-900 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-zinc-800 text-base">gavel</span>
                        Términos y Normas de Estancia
                    </h3>
                    <div class="bg-zinc-50 border border-zinc-200 p-3 rounded-sm text-[11px] text-zinc-600 space-y-2 leading-relaxed">
                        <p><strong>• Check-In / Check-Out:</strong> El ciclo operativo diario inicia a las <strong>08:00 AM</strong>. La hora de salida (Check-Out) es a las <strong>12:00 PM (Mediodía)</strong> del día posterior al ingreso.</p>
                    </div>
                </div>

                <div class="p-4 bg-zinc-50 border-t border-zinc-200 flex justify-end gap-3">
                    <button onclick="cerrarModal()" class="px-4 py-2 text-xs font-bold text-zinc-500 uppercase tracking-wider hover:text-zinc-800 transition-colors">
                        Regresar
                    </button>
                    <a id="modalLinkReservar" href="" class="bg-zinc-900 text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-sm shadow-sm hover:bg-zinc-800 transition-colors active:scale-95 duration-150 flex items-center gap-1.5">
                        Confirmar Reserva
                    </a>
                </div>
            </div>
        </div>

    </div>

<script>
    // Leer filtro desde URL si viene desde el dashboard
window.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const filtro = params.get('filtro');
    if (filtro) {
        filtrarHabitaciones(filtro);
    }
});
function filtrarHabitaciones(categoria) {
    const secciones = document.querySelectorAll('.seccion-habitacion');
    const alertaVacio = document.getElementById('alerta-filtro-vacio');
    let seccionesVisibles = 0;

    document.querySelectorAll('.btn-filtro').forEach(btn => {
        btn.classList.remove('bg-zinc-900', 'text-white', 'shadow-sm');
        btn.classList.add('bg-zinc-100', 'text-zinc-600');
    });

    const botonActivo = document.getElementById(`btn-filtro-${categoria}`);
    if (botonActivo) {
        botonActivo.classList.remove('bg-zinc-100', 'text-zinc-600');
        botonActivo.classList.add('bg-zinc-900', 'text-white', 'shadow-sm');
    }

    secciones.forEach(seccion => {
        const tipoSeccion = seccion.getAttribute('data-tipo');
        if (categoria === 'todas' || tipoSeccion.includes(categoria)) {
            seccion.style.display = 'block';
            seccionesVisibles++;
        } else {
            seccion.style.display = 'none';
        }
    });

    if (seccionesVisibles === 0 && secciones.length > 0) {
        alertaVacio.classList.remove('hidden');
    } else {
        alertaVacio.classList.add('hidden');
    }
}

function abrirModal(datos) {
    const modal = document.getElementById('modalDetalle');
    const modalBox = document.getElementById('modal-box');

    document.getElementById('modalImagen').src = datos.RutaImg;
    document.getElementById('modalNumero').innerText = datos.NumeroHabitacion;
    document.getElementById('modalTipo').innerText = datos.NombreTipo;
    document.getElementById('modalPrecio').innerText = parseFloat(datos.Precio).toFixed(2);
    document.getElementById('modalDescripcion').innerText = datos.DescripcionEspecifica || datos.Descripcion;
    document.getElementById('modalLinkReservar').href = 'reservar.php?num_habitacion=' + datos.NumeroHabitacion + '&costo_base=' + datos.Precio + '&tipo_estadia=' + datos.TipoHabitacion;

    modal.classList.remove('opacity-0', 'pointer-events-none');
    modalBox.classList.remove('scale-95');
    modalBox.classList.add('scale-100');
    document.body.classList.add('overflow-hidden');
}

function cerrarModal() {
    const modal = document.getElementById('modalDetalle');
    const modalBox = document.getElementById('modal-box');

    modal.classList.add('opacity-0', 'pointer-events-none');
    modalBox.classList.remove('scale-100');
    modalBox.classList.add('scale-95');
    document.body.classList.remove('overflow-hidden');
}

window.onclick = function(event) {
    const modal = document.getElementById('modalDetalle');
    if (event.target == modal) { cerrarModal(); }
}
</script>

<?php require_once 'layout/footer.php'; ?>
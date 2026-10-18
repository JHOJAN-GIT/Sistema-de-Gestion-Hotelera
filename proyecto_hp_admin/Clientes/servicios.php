<?php
require_once 'layout/header.php';
require_once("../Conexion/conexion.php");
$conn = Cconexion::ConexionBD();

$sql = "SELECT NombreServicio, Descripcion, Precio, Categoria, Imagen 
        FROM Servicios 
        ORDER BY Categoria DESC, NombreServicio ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categorias = [];
foreach ($servicios as $serv) {
    $categorias[$serv['Categoria']][] = $serv;
}

require_once 'layout/sidebar.php';
?>

<main id="content-main" class="content-transition flex-1 w-full flex flex-col lg:pl-0">
    <div class="max-w-[1440px] mx-auto w-full px-6 sm:px-10 py-6 sm:py-10 flex flex-col flex-1">

        <section class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
            <div>
                <h1 class="text-4xl font-extrabold text-zinc-900 tracking-tight">Servicios a la Habitación</h1>
                <p class="text-lg text-zinc-500 mt-2">Disfruta de la mejor experiencia y confort sin salir de tu habitación.</p>
            </div>
        </section>

        <?php if (count($categorias) > 0): ?>
            <?php foreach ($categorias as $nombreCategoria => $items): ?>
                <div class="mb-12">
                    <div class="flex justify-between items-center bg-zinc-50 p-4 rounded-sm border border-zinc-100 mb-6 shadow-sm">
                        <h2 class="text-xl font-bold text-zinc-900 tracking-tight flex items-center gap-2">
                            <span class="material-symbols-outlined text-zinc-700">restaurant_menu</span>
                            <?php echo htmlspecialchars($nombreCategoria); ?>
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php foreach ($items as $servicio): ?>
                            <div class="bg-white border border-zinc-200 rounded-sm flex flex-col justify-between custom-card-shadow overflow-hidden group">
                                <div class="relative overflow-hidden aspect-video bg-zinc-100">
                                    <!-- IMAGEN DESDE JS, no desde BD -->
                                    <img data-nombre="<?php echo htmlspecialchars($servicio['NombreServicio']); ?>"
                                         alt="Imagen de <?php echo htmlspecialchars($servicio['NombreServicio']); ?>"
                                         class="img-servicio-card w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                </div>
                                
                                <div class="p-5 flex flex-col justify-between flex-1">
                                    <div class="mb-4">
                                        <h3 class="text-lg font-bold text-zinc-900 tracking-tight mb-1"><?php echo htmlspecialchars($servicio['NombreServicio']); ?></h3>
                                        <p class="text-sm text-zinc-500 line-clamp-3 leading-relaxed"><?php echo htmlspecialchars($servicio['Descripcion']); ?></p>
                                    </div>
                                    
                                    <div class="mt-auto">
                                        <div class="flex items-baseline mb-3">
                                            <span class="text-xs font-bold text-zinc-400 mr-1">S/</span>
                                            <span class="text-2xl font-black text-zinc-900 tracking-tight"><?php echo number_format($servicio['Precio'], 2); ?></span>
                                        </div>
                                        <button 
                                            onclick="abrirModalServicio('<?php echo htmlspecialchars($servicio['NombreServicio'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($servicio['Descripcion'], ENT_QUOTES); ?>', <?php echo $servicio['Precio']; ?>)" 
                                            class="btn-solicitar block w-full text-center bg-primary-container text-zinc-900 py-2.5 font-bold active:scale-95 duration-150 text-xs uppercase tracking-wider rounded-sm shadow-sm hover:bg-zinc-900 hover:text-white transition-colors"
                                            data-servicio="<?php echo htmlspecialchars($servicio['NombreServicio']); ?>">
                                            Solicitar al Cuarto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-zinc-50 border border-zinc-200 rounded-sm p-12 text-center max-w-xl mx-auto my-12">
                <span class="material-symbols-outlined text-zinc-400 text-6xl mb-4">room_service</span>
                <p class="text-lg font-semibold text-zinc-700">Por el momento no hay servicios de habitación disponibles.</p>
                <p class="text-sm text-zinc-400 mt-1">Vuelve a consultar más tarde o comunícate con recepción.</p>
            </div>
        <?php endif; ?>

    </div>

<!-- MODAL -->
<div id="modalServicio" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="cerrarModalServicio(event)">
    <div class="bg-white rounded-sm shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-primary to-amber-600 p-6 text-white relative">
            <button onclick="cerrarModalServicio()" class="absolute top-4 right-4 text-white hover:bg-white/20 rounded-full p-2 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h2 id="modalTitulo" class="text-2xl font-black tracking-tight pr-12">Detalles del Servicio</h2>
            <p class="text-sm opacity-90 mt-1">Revisa los detalles antes de confirmar tu pedido</p>
        </div>

        <div class="p-6">
            <div class="mb-6 rounded-sm overflow-hidden border border-zinc-200 shadow-sm">
                <img id="modalImagen" src="" alt="Imagen del servicio" class="w-full h-64 object-cover">
            </div>

            <div class="space-y-4 mb-6">
                <div class="flex items-start gap-3 p-4 bg-zinc-50 rounded-sm border border-zinc-200">
                    <span class="material-symbols-outlined text-primary text-2xl">description</span>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Descripción</p>
                        <p id="modalDescripcion" class="text-sm text-zinc-700 leading-relaxed"></p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-zinc-50 rounded-sm border border-zinc-200">
                    <span class="material-symbols-outlined text-emerald-600 text-2xl">payments</span>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Precio</p>
                        <p class="text-2xl font-black text-zinc-900">S/ <span id="modalPrecio">0.00</span></p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-amber-50 rounded-sm border border-amber-200">
                    <span class="material-symbols-outlined text-amber-600 text-2xl">schedule</span>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-1">Tiempo de Entrega</p>
                        <p class="text-lg font-bold text-amber-900"><span id="modalTiempo">15</span> minutos aproximadamente</p>
                        <p class="text-xs text-amber-600 mt-1">El pedido llegará a tu habitación en este tiempo estimado</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="cerrarModalServicio()" class="flex-1 border border-zinc-300 text-zinc-700 py-3 font-bold text-sm rounded-sm hover:bg-zinc-50 transition-colors">
                    Cancelar
                </button>
                <button onclick="confirmarPedido()" class="flex-1 bg-primary-container text-zinc-900 py-3 font-bold text-sm rounded-sm hover:bg-zinc-900 hover:text-white transition-colors shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    Confirmar Pedido
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let servicioActual = '';
let tiempoEntrega = 15;
let precioActual = 0;
let descripcionActual = '';

const imagenesReferencia = {
    'Lomo Saltado':       'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800',
    'Ceviche':            'https://images.unsplash.com/photo-1535399831218-d5bd36d1a6b3?w=800',
    'Pisco Sour':         'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800',
    'Piña Colada':        'https://images.unsplash.com/photo-1587996632438-d6b2e0a4a3f0?w=800',
    'Ají de Gallina':     'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=800',
    'Chicharrón de Cerdo':'https://images.unsplash.com/photo-1544025162-d76538740d1a?w=800',
    'Rocoto Relleno':     'https://images.unsplash.com/photo-1590080875515-8a3a8dc5735e?w=800',
    'Chilcano':           'https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=800',
    'Maracuyá Sour':      'https://images.unsplash.com/photo-1546171753-97d7676e4602?w=800',
    'Algarrobina':        'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800',
    'default':            'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'
};

function obtenerImagenReferencia(nombreServicio) {
    // Primero busca coincidencia exacta
    if (imagenesReferencia[nombreServicio]) return imagenesReferencia[nombreServicio];
    // Luego busca coincidencia parcial
    for (let key in imagenesReferencia) {
        if (key === 'default') continue;
        if (nombreServicio.toLowerCase().includes(key.toLowerCase()) ||
            key.toLowerCase().includes(nombreServicio.toLowerCase())) {
            return imagenesReferencia[key];
        }
    }
    return imagenesReferencia['default'];
}

function obtenerTiempoEntrega(nombreServicio) {
    const tiempos = {
        'lomo': 25, 'ceviche': 20, 'aji': 25, 'chicharron': 25,
        'rocoto': 20, 'sopa': 20, 'ensalada': 10, 'sandwich': 10,
        'hamburguesa': 15, 'pizza': 20, 'postre': 10, 'helado': 5,
        'cafe': 5, 'jugo': 5, 'agua': 3, 'refresco': 3,
        'cerveza': 5, 'vino': 5, 'pisco': 5, 'sour': 5,
        'colada': 5, 'chilcano': 5, 'algarrobina': 5, 'maracuya': 5
    };
    const nombre = nombreServicio.toLowerCase();
    for (let key in tiempos) {
        if (nombre.includes(key)) return tiempos[key];
    }
    return 15;
}

// Al cargar la página, asignar imágenes a las tarjetas
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.img-servicio-card').forEach(img => {
        img.src = obtenerImagenReferencia(img.dataset.nombre);
    });
});

function abrirModalServicio(nombre, descripcion, precio) {
    servicioActual = nombre;
    precioActual = precio;
    descripcionActual = descripcion;
    tiempoEntrega = obtenerTiempoEntrega(nombre);

    document.getElementById('modalTitulo').textContent = nombre;
    document.getElementById('modalDescripcion').textContent = descripcion;
    document.getElementById('modalPrecio').textContent = parseFloat(precio).toFixed(2);
    document.getElementById('modalTiempo').textContent = tiempoEntrega;

    const imgEl = document.getElementById('modalImagen');
    imgEl.src = obtenerImagenReferencia(nombre);
    imgEl.alt = nombre;

    document.getElementById('modalServicio').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function cerrarModalServicio(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('modalServicio').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function confirmarPedido() {
    cerrarModalServicio();

    const botones = document.querySelectorAll('.btn-solicitar');
    let botonActual = null;
    botones.forEach(btn => {
        if (btn.getAttribute('data-servicio') === servicioActual) botonActual = btn;
    });

    if (!botonActual) return;

    botonActual.innerHTML = '<span class="material-symbols-outlined text-base align-middle">hourglass_top</span> Procesando...';
    botonActual.className = 'btn-solicitar block w-full text-center bg-blue-500 text-white py-2.5 font-bold text-xs uppercase tracking-wider rounded-sm shadow-sm cursor-wait';
    botonActual.disabled = true;

    mostrarNotificacion(`Pedido confirmado: ${servicioActual}. Llegará en ${tiempoEntrega} minutos.`, 'success');

    let minutosRestantes = tiempoEntrega;
    const intervalo = setInterval(() => {
        minutosRestantes--;
        if (minutosRestantes > 0) {
            botonActual.innerHTML = `<span class="material-symbols-outlined text-base align-middle">schedule</span> Espera ~${minutosRestantes} min`;
        } else {
            clearInterval(intervalo);
        }
    }, 60000);

    const nombreGuardado = servicioActual;
    const precioGuardado = precioActual;
    const descripcionGuardada = descripcionActual;

    setTimeout(() => {
        clearInterval(intervalo);
        botonActual.innerHTML = '<span class="material-symbols-outlined text-base align-middle">refresh</span> Volver a Pedir';
        botonActual.className = 'btn-solicitar block w-full text-center bg-emerald-500 text-white py-2.5 font-bold text-xs uppercase tracking-wider rounded-sm shadow-sm hover:bg-emerald-600 transition-colors';
        botonActual.disabled = false;
        botonActual.onclick = () => abrirModalServicio(nombreGuardado, descripcionGuardada, precioGuardado);

        mostrarNotificacion(`¡Tu pedido de ${nombreGuardado} ha sido entregado!`, 'success');

        setTimeout(() => {
            botonActual.innerHTML = 'Solicitar al Cuarto';
            botonActual.className = 'btn-solicitar block w-full text-center bg-primary-container text-zinc-900 py-2.5 font-bold active:scale-95 duration-150 text-xs uppercase tracking-wider rounded-sm shadow-sm hover:bg-zinc-900 hover:text-white transition-colors';
            botonActual.onclick = null;
        }, 10000);

    }, tiempoEntrega * 60 * 1000);
}

function mostrarNotificacion(mensaje, tipo = 'info') {
    const notif = document.createElement('div');
    notif.className = `fixed top-4 right-4 z-[60] px-6 py-4 rounded-sm shadow-2xl border-l-4 flex items-center gap-3 animate-slide-in max-w-md ${
        tipo === 'success' ? 'bg-emerald-50 border-emerald-500 text-emerald-900' :
        tipo === 'error'   ? 'bg-red-50 border-red-500 text-red-900' :
                             'bg-blue-50 border-blue-500 text-blue-900'
    }`;
    notif.innerHTML = `
        <span class="material-symbols-outlined text-2xl">${tipo === 'success' ? 'check_circle' : tipo === 'error' ? 'error' : 'info'}</span>
        <p class="text-sm font-semibold flex-1">${mensaje}</p>
        <button onclick="this.parentElement.remove()" class="text-zinc-400 hover:text-zinc-600">
            <span class="material-symbols-outlined text-lg">close</span>
        </button>
    `;
    document.body.appendChild(notif);
    setTimeout(() => {
        notif.style.opacity = '0';
        notif.style.transform = 'translateX(100%)';
        setTimeout(() => notif.remove(), 300);
    }, 5000);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModalServicio();
});
</script>

<style>
@keyframes slide-in {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>

<?php require_once 'layout/footer.php'; ?>
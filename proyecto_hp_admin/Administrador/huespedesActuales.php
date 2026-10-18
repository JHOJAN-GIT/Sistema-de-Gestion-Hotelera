<?php
// Admin/huespedesActuales.php

$paginaActual = 'huespedes';
$tituloPagina = 'Huéspedes Actuales';

require_once 'layout/header.php';

include_once("../Conexion/conexion.php");
$conn = Cconexion::ConexionBD();

$registros = [];
if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM Registros WHERE FechaHoraSalida IS NULL ORDER BY FechaHoraIngreso DESC");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$todasHabitaciones   = array_merge(range(301, 305), range(401, 405), range(501, 505), range(601, 605));
$habitacionesOcupadas = [];
if ($conn) {
    $stmt = $conn->prepare("SELECT NumeroHabitacion FROM Registros WHERE FechaHoraSalida IS NULL");
    $stmt->execute();
    $habitacionesOcupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$totalRegistros = count($registros);
$primeros20     = array_slice($registros, 0, 20);
$restantes      = array_slice($registros, 20);

date_default_timezone_set('America/Lima');

function renderFilaHuesped($reg, $esOculto = false) {
    $tipo         = $reg['TipoAlojamiento'];
    $fechaIngreso = new DateTime($reg['FechaHoraIngreso']);
    $ahora        = new DateTime();

    if ($tipo === 'Momentaneo') {
        $fechaLimite = (clone $fechaIngreso)->modify('+3 hours');
    } else {
        $fechaLimite = new DateTime($fechaIngreso->format('Y-m-d') . ' 12:00:00');
        $fechaLimite->modify('+1 day');
    }

    if ($ahora < $fechaLimite) {
        $intervalo      = $ahora->diff($fechaLimite);
        $totalHoras     = ($intervalo->days * 24) + $intervalo->h;
        $tiempoRestante = $totalHoras . "h " . $intervalo->i . "m";
        $badgeColor     = "bg-zinc-100 text-zinc-800";
    } else {
        $tiempoRestante = "Plazo Expirado";
        $badgeColor     = "bg-amber-100 text-amber-800 font-bold";
    }

    $filaClase = $esOculto ? 'fila-anterior hidden bg-zinc-50/50' : 'hover:bg-zinc-50/50';
    ?>
    <tr class="<?= $filaClase ?>">
        <td class="py-4 px-4 text-center font-bold text-black"><?= htmlspecialchars($reg['NumeroHabitacion']) ?></td>
        <td class="py-4 px-4">
            <div class="font-semibold text-black"><?= htmlspecialchars($reg['NombreCompleto']) ?></div>
            <div class="text-xs text-zinc-400 font-mono mt-0.5">DNI: <?= htmlspecialchars($reg['DNI']) ?></div>
        </td>
        <td class="py-4 px-4 text-center font-mono font-medium text-zinc-900">S/. <?= number_format($reg['Monto'], 2) ?></td>
        <td class="py-4 px-4 text-center">
            <span class="px-2 py-1 text-xs rounded-full font-medium <?= ($tipo === 'Diario') ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' ?>">
                <?= htmlspecialchars($tipo) ?>
            </span>
        </td>
        <td class="py-4 px-4 text-xs text-zinc-500 font-mono"><?= date('d/m H:i', strtotime($reg['FechaHoraIngreso'])) ?></td>
        <td class="py-4 px-4">
    <span class="px-2 py-1 text-xs rounded-sm countdown-badge"
          data-checkout="<?= htmlspecialchars($reg['CheckoutPronosticado']) ?>">
        Calculando...
    </span>
</td>
    </tr>
    <?php
}
?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

    <!-- Mapa de habitaciones -->
    <section class="lg:col-span-4 bg-white border border-zinc-100 shadow-sm rounded-sm p-6">
        <div class="mb-5">
            <h2 class="text-lg font-bold text-black tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-zinc-600">bed</span>
                Estado de Habitaciones
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Mapa en tiempo real de ocupación física en el hotel.</p>
        </div>
        <div class="flex gap-4 mb-6 border-b border-zinc-100 pb-4 text-xs font-semibold">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                <span class="text-zinc-600">Disponible</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                <span class="text-zinc-600">Ocupada</span>
            </div>
        </div>
        <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
            <?php foreach ($todasHabitaciones as $hab):
                $esOcupada   = in_array($hab, $habitacionesOcupadas);
                $claseEstado = $esOcupada
                    ? 'bg-rose-50 border-rose-200 text-rose-700 font-bold'
                    : 'bg-emerald-50 border-emerald-200 text-emerald-700 font-semibold';
            ?>
                <div class="border text-center py-3 rounded-sm text-sm tracking-tight transition-all <?= $claseEstado ?>">
                    <?= $hab ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Tabla de huéspedes -->
    <section class="lg:col-span-8 bg-white border border-zinc-100 shadow-sm rounded-sm p-6 overflow-hidden">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-lg font-bold text-black tracking-tight flex items-center gap-2">
                    <span class="material-symbols-outlined text-zinc-600">group</span>
                    Monitoreo de Huéspedes Actuales
                </h2>
                <p class="text-xs text-zinc-500 mt-0.5">Registros activos. Los ingresos más recientes encabezan la lista.</p>
            </div>
        </div>

        <div class="overflow-x-auto w-full border border-zinc-100 rounded-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200 text-zinc-700 text-xs font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center">Nº Hab.</th>
                        <th class="py-3.5 px-4">Huésped / Identidad</th>
                        <th class="py-3.5 px-4 text-center">Monto</th>
                        <th class="py-3.5 px-4 text-center">Alojamiento</th>
                        <th class="py-3.5 px-4">Ingreso</th>
                        <th class="py-3.5 px-4">Tiempo Restante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 text-sm tracking-tight text-zinc-600">
                    <?php foreach ($primeros20 as $reg) renderFilaHuesped($reg, false); ?>
                    <?php foreach ($restantes  as $reg) renderFilaHuesped($reg, true);  ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalRegistros > 20): ?>
            <div class="mt-4 flex justify-center">
                <button id="btn-cargar-anteriores" onclick="mostrarRegistrosAnteriores()"
                        class="w-full sm:w-auto border border-zinc-200 text-zinc-600 bg-white hover:bg-zinc-50
                               px-6 py-2.5 text-xs font-bold rounded-sm transition-all tracking-tight
                               flex items-center justify-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-sm">history</span>
                    Ver registros anteriores (+<?= count($restantes) ?>)
                </button>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php require_once 'layout/footer.php'; ?>

<script>
function mostrarRegistrosAnteriores() {
    document.querySelectorAll('.fila-anterior').forEach(f => f.classList.remove('hidden'));
    const btn = document.getElementById('btn-cargar-anteriores');
    if (btn) btn.style.display = 'none';
}
function actualizarCountdowns() {
    document.querySelectorAll('.countdown-badge').forEach(badge => {
        const checkout = new Date(badge.dataset.checkout.replace(' ', 'T'));
        const ahora    = new Date();
        const diff     = checkout - ahora;

        if (diff <= 0) {
            badge.textContent = 'Plazo Expirado';
            badge.className   = 'px-2 py-1 text-xs rounded-sm bg-amber-100 text-amber-800 font-bold countdown-badge';
        } else {
            const horas   = Math.floor(diff / 3600000);
            const minutos = Math.floor((diff % 3600000) / 60000);
            const segundos = Math.floor((diff % 60000) / 1000);
            badge.textContent = `${horas}h ${minutos}m ${segundos}s`;
            badge.className   = 'px-2 py-1 text-xs rounded-sm bg-zinc-100 text-zinc-800 countdown-badge';
        }
    });
}

actualizarCountdowns();
setInterval(actualizarCountdowns, 1000);
</script>
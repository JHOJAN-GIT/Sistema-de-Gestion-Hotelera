<?php
// Admin/reservas.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'layout/seguridad.php';
    include_once("../Conexion/conexion.php");
    $conn = Cconexion::ConexionBD();

    $accion    = $_POST['accion']    ?? '';
    $idReserva = $_POST['idReserva'] ?? '';
    $motivo    = $_POST['motivo']    ?? '';
    $tabla     = $_POST['tabla']     ?? 'Reservas'; // 'Reservas' o 'ReservasClientes'

    $tablaCanceladas = ($tabla === 'ReservasClientes') ? 'ReservasCanceladasClientes' : 'ReservasCanceladas';

    if ($conn && $accion === 'cancelar') {
        $stmt = $conn->prepare("SELECT * FROM {$tabla} WHERE IdReserva = ?");
        $stmt->execute([$idReserva]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reserva) {
            $conn->prepare("INSERT INTO {$tablaCanceladas}
                (IdReserva, TipoHabitacion, NombreCompleto, DNI, DiasHospedaje, FechaHoraLlegada, Costo, motivoCancelacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([
                    $reserva['IdReserva'], $reserva['TipoHabitacion'], $reserva['NombreCompleto'],
                    $reserva['DNI'], $reserva['DiasHospedaje'], $reserva['FechaHoraLlegada'],
                    $reserva['Costo'], $motivo
                ]);

            $conn->prepare("UPDATE {$tabla} SET Estado = 'Cancelada' WHERE IdReserva = ?")
                ->execute([$idReserva]);
        }
    }

    header("Location: reservas.php");
    exit();
}

$paginaActual = 'reservas';
$tituloPagina = 'Gestión de Reservas';

require_once 'layout/header.php';
include_once("../Conexion/conexion.php");
$conn = Cconexion::ConexionBD();

$reservas          = [];
$reservasClientes  = [];
$nombresRegistrados = [];

if ($conn) {
    $stmt = $conn->query("SELECT * FROM Reservas ORDER BY FechaHoraLlegada DESC");
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->query("SELECT * FROM ReservasClientes ORDER BY FechaHoraLlegada DESC");
    $reservasClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $conn->query("SELECT NombreCompleto FROM Registros WHERE FechaHoraSalida IS NULL");
    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $reg) {
        $nombresRegistrados[] = $reg['NombreCompleto'];
    }
}

// Función para renderizar tabla de reservas
function renderTablaReservas($reservas, $nombresRegistrados, $tablaId, $tablaNombre) {
    $ahora = new DateTime();
    $totalFilas = count($reservas);
    ?>
    <div class="bg-white border border-zinc-100 shadow-[0px_4px_20px_rgba(0,0,0,0.02)] overflow-hidden rounded-sm">
        <div class="overflow-y-auto transition-all" id="contenedor-<?= $tablaId ?>" style="max-height: 380px;">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-black border-b border-zinc-200">
                            <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider">Tipo</th>
                            <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider">Huésped</th>
                            <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">DNI</th>
                            <?php if ($tablaNombre === 'ReservasClientes'): ?>
                            <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">N° Hab.</th>
                            <?php endif; ?>
                            <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Días</th>
                            <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Fecha Llegada</th>
                            <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-right">Costo</th>
                            <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Estado</th>
                            <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <?php if (empty($reservas)): ?>
                            <tr>
                                <td colspan="<?= $tablaNombre === 'ReservasClientes' ? '9' : '8' ?>" class="p-8 text-center text-sm text-zinc-400">
                                    No hay reservas activas actualmente.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservas as $res):
                                $estado        = $res['Estado'] ?? null;
                                $checkInHecho  = in_array($res['NombreCompleto'], $nombresRegistrados);
                                $fechaLlegada  = new DateTime($res['FechaHoraLlegada']);
                                $limiteLlegada = (clone $fechaLlegada)->modify('+2 hours');
                                $noLlego       = (!$checkInHecho && $ahora > $limiteLlegada && $estado !== 'Cancelada');
                                $esCancelada   = ($estado === 'Cancelada');
                                $esRegistrada  = ($estado === 'Registrada');

                                // Params GET para nuevoRegistro.php
                                $params = http_build_query([
                                    'nombreCompleto'  => $res['NombreCompleto'],
                                    'dni'             => $res['DNI'],
                                    'monto'           => $res['Costo'],
                                    'tipoAlojamiento' => $res['TipoHabitacion'],
                                    'diasHospedaje'   => $res['DiasHospedaje'],
                                    'idReserva'       => $res['IdReserva'],
                                    'tabla'           => $tablaNombre,
                                    'numeroHabitacion' => $res['NumeroHabitacion'] ?? $res['NumHabitacion'] ?? '',
                                    'servicioEspecial' => $res['ServicioEspecial'] ?? ''
                                ]);
                            ?>
                                <tr class="hover:bg-zinc-50/80 transition-colors <?= $esCancelada ? 'opacity-50' : '' ?>">
                                    <td class="p-4 text-sm font-semibold text-zinc-900">
                                        <span class="bg-zinc-100 text-zinc-800 text-[11px] px-2.5 py-1 font-medium rounded-full">
                                            <?= htmlspecialchars($res['TipoHabitacion']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-sm font-bold text-zinc-800"><?= htmlspecialchars($res['NombreCompleto']) ?></td>
                                    <td class="p-4 text-sm text-zinc-600 font-mono text-center"><?= htmlspecialchars($res['DNI']) ?></td>
                                    <?php if ($tablaNombre === 'ReservasClientes'): ?>
                                    <td class="p-4 text-sm text-zinc-900 font-bold text-center">
                                        <?= !empty($res['NumeroHabitacion']) ? htmlspecialchars($res['NumeroHabitacion']) : (!empty($res['NumHabitacion']) ? htmlspecialchars($res['NumHabitacion']) : '-') ?>
                                    </td>
                                    <?php endif; ?>
                                    <td class="p-4 text-sm text-zinc-700 text-center font-semibold"><?= htmlspecialchars($res['DiasHospedaje']) ?></td>
                                    <td class="p-4 text-sm text-zinc-500 font-mono text-center whitespace-nowrap"><?= htmlspecialchars($res['FechaHoraLlegada']) ?></td>
                                    <td class="p-4 text-sm text-zinc-900 font-extrabold text-right">S/ <?= number_format($res['Costo'], 2) ?></td>
                                    <td class="p-4 text-center">
                                        <?php if ($esCancelada): ?>
                                            <span class="bg-red-100 text-red-700 text-[10px] px-2 py-1 font-bold uppercase rounded-sm">Cancelada</span>
                                        <?php elseif ($esRegistrada || $checkInHecho): ?>
                                            <span class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-1 font-bold uppercase rounded-sm">Registrada</span>
                                        <?php elseif ($noLlego): ?>
                                            <span class="bg-amber-100 text-amber-700 text-[10px] px-2 py-1 font-bold uppercase rounded-sm">No Llegó</span>
                                        <?php else: ?>
                                            <span class="bg-zinc-100 text-zinc-600 text-[10px] px-2 py-1 font-bold uppercase rounded-sm">Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-sm text-center whitespace-nowrap">
                                        <div class="inline-flex gap-2 items-center">
                                            <?php if ($esCancelada || $esRegistrada || $checkInHecho): ?>
                                                <span class="bg-zinc-100 text-zinc-400 text-xs font-bold px-3 py-1.5 rounded-sm inline-flex items-center gap-1 border border-zinc-200 cursor-not-allowed select-none">
                                                    <span class="material-symbols-outlined text-[14px]">block</span> Check-In
                                                </span>
                                            <?php elseif ($noLlego): ?>
                                                <a href="nuevoRegistro.php?<?= $params ?>"
                                                   class="bg-amber-600 text-white text-xs font-bold px-3 py-1.5 rounded-sm hover:bg-amber-700 transition-all shadow-sm active:scale-95 inline-flex items-center gap-1"
                                                   onclick="return confirm('¿Registrar arribo tardío?')">
                                                    <span class="material-symbols-outlined text-[14px]">warning</span> No Llegó
                                                </a>
                                            <?php else: ?>
                                                <a href="nuevoRegistro.php?<?= $params ?>"
                                                   class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-sm hover:bg-emerald-700 transition-all shadow-sm active:scale-95 inline-flex items-center gap-1"
                                                   onclick="return confirm('¿Registrar Check-In?')">
                                                    <span class="material-symbols-outlined text-[14px]">login</span> Check-In
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!$esCancelada && !$esRegistrada && !$checkInHecho): ?>
                                                <button onclick="mostrarMotivoCancelacion('<?= $res['IdReserva'] ?>', '<?= $tablaNombre ?>')"
                                                        class="border border-error text-error text-xs font-bold px-3 py-1.5 rounded-sm bg-white hover:bg-red-50 transition-all shadow-sm active:scale-95 inline-flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[14px]">delete</span> Cancelar
                                                </button>
                                            <?php else: ?>
                                                <button disabled
                                                        class="border border-zinc-200 text-zinc-300 text-xs font-bold px-3 py-1.5 rounded-sm bg-zinc-50 cursor-not-allowed inline-flex items-center gap-1 select-none">
                                                    <span class="material-symbols-outlined text-[14px]">block</span> Cancelar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Botón expandir -->
        <?php if ($totalFilas > 5): ?>
        <div class="border-t border-zinc-100 p-3 flex justify-between items-center">
            <p class="text-xs text-zinc-400"><?= $totalFilas ?> reservas en total</p>
            <button onclick="toggleExpandir('<?= $tablaId ?>', this)"
                    class="text-xs font-bold text-zinc-600 hover:text-black border border-zinc-200 px-4 py-1.5 rounded-sm hover:bg-zinc-50 transition-all flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">expand_more</span>
                Expandir
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php
}
?>

<!-- Encabezado -->
<section class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-black tracking-tight">Gestión de Reservas</h1>
        <p class="text-sm text-zinc-500 mt-1">Administre las solicitudes activas de hospedaje.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <button onclick="location.href='reservasCanceladas.php'"
                class="border border-zinc-300 text-zinc-700 px-4 py-2 font-semibold text-sm rounded-sm bg-white hover:bg-zinc-50 transition-all inline-flex items-center gap-2 shadow-sm active:scale-95">
            <span class="material-symbols-outlined text-base">cancel_presentation</span>
            Ver Canceladas
        </button>
        <button onclick="location.href='nuevaReserva.php'"
                class="bg-black text-white px-4 py-2 font-semibold text-sm rounded-sm hover:bg-zinc-800 transition-all inline-flex items-center gap-2 shadow-sm active:scale-95">
            <span class="material-symbols-outlined text-base">add_box</span>
            Nueva Reserva
        </button>
    </div>
</section>

<!-- Tabla Reservas (Admin) -->
<div class="mb-4">
    <h2 class="text-base font-bold text-black mb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-yellow-500">admin_panel_settings</span>
        Reservas — Panel Admin
    </h2>
    <?php renderTablaReservas($reservas, $nombresRegistrados, 'admin', 'Reservas'); ?>
</div>

<!-- Tabla ReservasClientes -->
<div class="mt-8">
    <div class="mb-3 flex justify-between items-center">
        <h2 class="text-base font-bold text-black flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-500">person</span>
            Reservas — Clientes
        </h2>
        <button onclick="location.href='reservasCanceladasClientes.php'"
                class="border border-zinc-300 text-zinc-700 px-3 py-1.5 font-semibold text-xs rounded-sm bg-white hover:bg-zinc-50 transition-all inline-flex items-center gap-2 shadow-sm active:scale-95">
            <span class="material-symbols-outlined text-sm">cancel_presentation</span>
            Ver Canceladas Clientes
        </button>
    </div>
    <?php renderTablaReservas($reservasClientes, $nombresRegistrados, 'clientes', 'ReservasClientes'); ?>
</div>

<?php require_once 'layout/footer.php'; ?>

<script>
function toggleExpandir(tablaId, btn) {
    const contenedor = document.getElementById('contenedor-' + tablaId);
    const expandido  = contenedor.style.maxHeight === 'none';

    if (expandido) {
        contenedor.style.maxHeight = '380px';
        btn.innerHTML = '<span class="material-symbols-outlined text-sm">expand_more</span> Expandir';
    } else {
        contenedor.style.maxHeight = 'none';
        btn.innerHTML = '<span class="material-symbols-outlined text-sm">expand_less</span> Contraer';
    }
}

function mostrarMotivoCancelacion(idReserva, tabla) {
    if (!confirm("¿Está seguro de cancelar esta reserva?")) return;

    const motivo = prompt("Ingrese el motivo de cancelación:");
    if (motivo && motivo.trim() !== "") {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "";

        [['accion', 'cancelar'], ['idReserva', idReserva], ['motivo', motivo], ['tabla', tabla]].forEach(([name, value]) => {
            const input = document.createElement("input");
            input.type  = "hidden";
            input.name  = name;
            input.value = value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    } else if (motivo !== null) {
        alert("Debe ingresar un motivo para cancelar la reserva.");
    }
}
</script>
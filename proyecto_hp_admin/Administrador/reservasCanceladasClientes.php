<?php
// Admin/reservasCanceladasClientes.php

$paginaActual = 'reservas';
$tituloPagina = 'Reservas Canceladas - Clientes';

require_once 'layout/header.php';
include_once("../Conexion/conexion.php");

$conn = Cconexion::ConexionBD();
$reservasCanceladasClientes = [];

if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM ReservasCanceladasClientes ORDER BY FechaHoraLlegada DESC");
    $stmt->execute();
    $reservasCanceladasClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-black tracking-tight">Reservas Canceladas - Clientes</h1>
        <p class="text-sm text-zinc-500 mt-2 leading-relaxed">
            Historial completo de reservas de clientes anuladas junto a los motivos especificados para auditoría interna.
        </p>
    </div>
    <button onclick="location.href='reservas.php'"
            class="border border-zinc-300 text-zinc-700 px-4 py-2 font-semibold text-sm rounded-sm bg-white hover:bg-zinc-50 transition-all inline-flex items-center gap-2 shadow-sm active:scale-95">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        Volver a Reservas
    </button>
</div>

<div class="bg-white border border-zinc-200 shadow-[0px_6px_30px_rgba(0,0,0,0.04)] rounded-sm overflow-hidden w-full">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="bg-black border-b border-zinc-200">
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Tipo Habitación</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider">Nombre Completo</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">DNI / CE</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">N° Hab.</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Días Hospedaje</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Fecha y Hora Llegada</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-right">Costo Total</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider pl-6">Motivo de Cancelación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (empty($reservasCanceladasClientes)): ?>
                    <tr>
                        <td colspan="8" class="p-8 text-center text-zinc-400 font-medium bg-zinc-50/50">
                            No se encontraron registros de reservas canceladas de clientes.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservasCanceladasClientes as $res): ?>
                        <tr class="hover:bg-zinc-50/80 transition-colors">
                            <td class="p-4 text-center">
                                <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-sm bg-zinc-100 text-zinc-800 border border-zinc-200">
                                    <?= htmlspecialchars($res['TipoHabitacion']) ?>
                                </span>
                            </td>
                            <td class="p-4 font-bold text-black"><?= htmlspecialchars($res['NombreCompleto']) ?></td>
                            <td class="p-4 text-center font-mono text-zinc-600"><?= htmlspecialchars($res['DNI']) ?></td>
                            <td class="p-4 text-center font-bold text-zinc-900">
                                <?= !empty($res['NumeroHabitacion']) ? htmlspecialchars($res['NumeroHabitacion']) : '-' ?>
                            </td>
                            <td class="p-4 text-center font-medium"><?= htmlspecialchars($res['DiasHospedaje']) ?> noches</td>
                            <td class="p-4 text-center font-mono text-zinc-600"><?= htmlspecialchars($res['FechaHoraLlegada']) ?></td>
                            <td class="p-4 text-right font-bold text-zinc-900">S/. <?= number_format($res['Costo'], 2) ?></td>
                            <td class="p-4 pl-6 text-zinc-600 italic font-medium max-w-xs cursor-pointer transition-all select-none group" onclick="toggleMotivo(this)">
                                <div class="truncate block max-w-[240px] pointer-events-none group-hover:text-black">
                                    <?= htmlspecialchars($res['motivoCancelacion']) ?>
                                </div>
                                <div class="hidden text-xs text-zinc-800 bg-zinc-100 p-2.5 mt-1 border border-zinc-200 rounded-sm whitespace-pre-wrap pointer-events-none">
                                    <?= htmlspecialchars($res['motivoCancelacion']) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>

<script>
function toggleMotivo(elemento) {
    const truncado  = elemento.querySelector('div:first-child');
    const completo  = elemento.querySelector('div:last-child');
    if (completo.classList.contains('hidden')) {
        truncado.classList.add('hidden');
        completo.classList.remove('hidden');
    } else {
        completo.classList.add('hidden');
        truncado.classList.remove('hidden');
        truncado.classList.add('truncate', 'block');
    }
}
</script>

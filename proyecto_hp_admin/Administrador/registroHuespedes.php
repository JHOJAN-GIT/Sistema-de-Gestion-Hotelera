<?php
// Admin/registroHuespedes.php

$paginaActual = 'registros';
$tituloPagina = 'Historial de Ocupación';

require_once 'layout/header.php';

include_once("../Conexion/conexion.php");
$conn = Cconexion::ConexionBD();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

$registros = [];
$total_registros = 0;
$estadosHab = [];

if ($conn) {
    $count_stmt = $conn->query("SELECT COUNT(*) FROM Registros");
    $total_registros = $count_stmt->fetchColumn();

    $query = "SELECT * FROM Registros 
              ORDER BY IdRegistro DESC 
              OFFSET 0 ROWS 
              FETCH NEXT :limit ROWS ONLY";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtHab = $conn->query("SELECT NumeroHabitacion, Estado FROM Habitaciones");
    foreach ($stmtHab->fetchAll(PDO::FETCH_ASSOC) as $h) {
        $estadosHab[$h['NumeroHabitacion']] = $h['Estado'];
    }
}
?>

<section class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-3xl font-extrabold text-black tracking-tight">Historial de Ocupación</h1>
        <p class="text-sm text-zinc-500 mt-1">Consulta, filtra y gestiona los ingresos y salidas globales del establecimiento.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <button onclick="location.href='nuevoRegistro.php'"
                class="bg-black text-white px-4 py-2 font-semibold text-sm rounded-sm hover:bg-zinc-800 transition-all inline-flex items-center gap-2 shadow-sm active:scale-95">
            <span class="material-symbols-outlined text-base">add_circle</span>
            Apertura de Folio
        </button>
    </div>
</section>

<div class="bg-white border border-zinc-100 shadow-[0px_4px_20px_rgba(0,0,0,0.02)] overflow-hidden rounded-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-black border-b border-zinc-200">
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">N° Hab.</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider">Huésped Titular</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">DNI</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-right">Tarifa Total</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Régimen</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Check-In</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Check-Out</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider">Observaciones</th>
                    <th class="p-4 text-xs font-bold text-yellow-400 uppercase tracking-wider text-center">Estado / Operación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="9" class="p-8 text-center text-sm text-zinc-400">
                            No se encontraron registros de alojamiento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registros as $reg): ?>
                        <tr class="hover:bg-zinc-50/80 transition-colors">
                            <td class="p-4 text-sm font-bold text-black text-center"><?= htmlspecialchars($reg['NumeroHabitacion']) ?></td>
                            <td class="p-4 text-sm font-semibold text-zinc-800"><?= htmlspecialchars($reg['NombreCompleto']) ?></td>
                            <td class="p-4 text-sm text-zinc-600 font-mono text-center"><?= htmlspecialchars($reg['DNI']) ?></td>
                            <td class="p-4 text-sm text-zinc-900 font-bold text-right">S/ <?= number_format($reg['Monto'], 2) ?></td>
                            <td class="p-4 text-sm text-center">
                                <span class="bg-zinc-100 text-zinc-800 text-[11px] px-2 py-0.5 font-medium rounded-full">
                                    <?= htmlspecialchars($reg['TipoAlojamiento']) ?>
                                </span>
                            </td>
                            <td class="p-4 text-sm text-zinc-500 font-mono text-center whitespace-nowrap"><?= htmlspecialchars($reg['FechaHoraIngreso']) ?></td>
                            <td class="p-4 text-sm text-zinc-500 font-mono text-center whitespace-nowrap"><?= htmlspecialchars($reg['FechaHoraSalida'] ?? '—') ?></td>
                            <td class="p-4 text-xs text-zinc-500 max-w-xs truncate" title="<?= htmlspecialchars($reg['Observaciones']) ?>">
                                <?= htmlspecialchars($reg['Observaciones'] ?: 'Sin novedades') ?>
                            </td>
                            <td class="p-4 text-sm text-center whitespace-nowrap">
                                <?php
                                $estadoHab = $estadosHab[$reg['NumeroHabitacion']] ?? null;
                                $ocupada   = ($estadoHab === 'Ocupado');
                                ?>
                                <?php if (empty($reg['FechaHoraSalida'])): ?>
                                    <div class="flex flex-col items-center gap-1.5">
                                        <?php if ($ocupada): ?>
                                            <span class="inline-flex items-center gap-1.5 text-red-600 text-xs font-semibold bg-red-50 border border-red-200 px-2.5 py-1 rounded-sm">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Ocupado
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 text-emerald-700 text-xs font-semibold bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-sm">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Libre
                                            </span>
                                        <?php endif; ?>
                                        <form action="checkOut.php" method="post"
                                              onsubmit="return confirm('¿Confirmar Check Out? Esta acción es irreversible.')"
                                              class="inline-block">
                                            <input type="hidden" name="id" value="<?= $reg['IdRegistro'] ?>">
                                            <button type="submit"
                                                    class="bg-error text-white text-xs font-bold px-3 py-1.5 rounded-sm hover:bg-[#93000a] transition-all shadow-sm active:scale-95">
                                                Cerrar Folio
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 text-zinc-400 text-xs font-semibold bg-zinc-100 px-2.5 py-1 rounded-sm">
                                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>Concluido
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_registros > $limit): ?>
        <div class="p-4 bg-zinc-50 border-t border-zinc-100 text-center">
            <a href="?limit=<?= $limit + 20 ?>"
               class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:text-black transition-colors py-2 px-4 border border-zinc-200 bg-white shadow-sm rounded-sm hover:border-zinc-300">
                <span class="material-symbols-outlined text-sm">expand_more</span>
                Cargar registros anteriores
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="mt-4 text-right">
    <p class="text-xs text-zinc-400 font-medium">
        Mostrando <span class="font-bold text-zinc-600"><?= min(count($registros), $total_registros) ?></span>
        de <span class="font-bold text-zinc-600"><?= $total_registros ?></span> registros auditados.
    </p>
</div>

<?php require_once 'layout/footer.php'; ?>
<?php
// Admin/inicioAdmin.php

$paginaActual = 'inicio';
$tituloPagina = 'Panel de Administración';

require_once 'layout/header.php'; // Incluye seguridad.php internamente

include_once("../Conexion/conexion.php");
$conn = Cconexion::ConexionBD();

date_default_timezone_set('America/Lima');

// ── Próximos Check-ins ───────────────────────────────────────────────────────
// 3 reservas con FechaHoraLlegada más cercana que aún no tienen check-in hecho
$proximosCheckins = [];
if ($conn) {
    $stmt  = $conn->query("SELECT TOP 3 * FROM Reservas ORDER BY FechaHoraLlegada ASC");
    $todasReservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2     = $conn->query("SELECT NombreCompleto FROM Registros");
    $registrados = $stmt2->fetchAll(PDO::FETCH_COLUMN);

    foreach ($todasReservas as $r) {
        if (!in_array($r['NombreCompleto'], $registrados)) {
            $proximosCheckins[] = $r;
        }
        if (count($proximosCheckins) === 3) break;
    }
}

// ── Próximos Check-outs ──────────────────────────────────────────────────────
// 3 registros activos con menor tiempo restante
$proximosCheckouts = [];
if ($conn) {
    $stmt   = $conn->query("SELECT * FROM Registros WHERE FechaHoraSalida IS NULL");
    $activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ahora  = new DateTime();

    $conTiempo = [];
    foreach ($activos as $reg) {
        $tipo         = $reg['TipoAlojamiento'];
        $fechaIngreso = new DateTime($reg['FechaHoraIngreso']);

        if ($tipo === 'Momentaneo') {
            $fechaLimite = (clone $fechaIngreso)->modify('+3 hours');
        } else {
            $fechaLimite = new DateTime($fechaIngreso->format('Y-m-d') . ' 12:00:00');
            $fechaLimite->modify('+1 day');
        }

        $segundosRestantes = $ahora < $fechaLimite
            ? $fechaLimite->getTimestamp() - $ahora->getTimestamp()
            : -1 * ($ahora->getTimestamp() - $fechaLimite->getTimestamp());

        $conTiempo[] = [
            'reg'         => $reg,
            'fechaLimite' => $fechaLimite,
            'segundos'    => $segundosRestantes,
        ];
    }

    usort($conTiempo, fn($a, $b) => $a['segundos'] - $b['segundos']);
    $proximosCheckouts = array_slice($conTiempo, 0, 3);
}
// ── Ocupación ────────────────────────────────────────────────────────────────
$porcentajeOcupacion = 0;
if ($conn) {
    $stmt = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN Estado = 'Ocupado' THEN 1 ELSE 0 END) as ocupadas FROM Habitaciones");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $porcentajeOcupacion = $row['total'] > 0 
        ? round(($row['ocupadas'] / $row['total']) * 100) 
        : 0;
}

// ── Check-ins Hoy ─────────────────────────────────────────────────────────────
$checkinsHoy = 0;
if ($conn) {
    $stmt = $conn->query("
        SELECT COUNT(*) as total FROM Registros
        WHERE CAST(FechaHoraIngreso AS DATE) = CAST(GETDATE() AS DATE)
        AND FechaHoraSalida IS NULL
    ");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $checkinsHoy = $row['total'];
}
?>

<!-- ═══════════════════════════════════════════════
     CONTENIDO: INICIO ADMIN
═══════════════════════════════════════════════ -->

<!-- Encabezado de bienvenida + métricas rápidas -->
<section class="mb-12 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
    <div>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-black tracking-tight mb-2">
            Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?>
        </h1>
        <p class="text-base sm:text-lg text-zinc-500 max-w-2xl leading-relaxed">
            Gestiona las operaciones diarias de <span class="font-bold text-black">Hospedaje Ayacucho</span> con precisión y eficiencia.
        </p>
    </div>
    <div class="bg-white border border-zinc-200 p-4 shadow-sm flex gap-8 items-center rounded-sm">
        <div class="text-center">
            <p class="text-xs text-zinc-400 font-bold uppercase tracking-widest mb-1">Ocupación</p>
        <p class="text-2xl font-black text-primary"><?= $porcentajeOcupacion ?>%</p>      
        </div>
        <div class="h-10 w-px bg-zinc-200"></div>
        <div class="text-center">
            <p class="text-xs text-zinc-400 font-bold uppercase tracking-widest mb-1">Check-ins Hoy</p>
            <p class="text-2xl font-black text-black"><?= $checkinsHoy ?></p>
        </div>
    </div>
</section>

<!-- Bento Grid de acciones rápidas -->
<div class="bento-grid">

    <!-- Nuevo Registro -->
    <div onclick="location.href='nuevoRegistro.php'"
         class="col-span-12 lg:col-span-3 bg-gradient-to-br from-yellow-400 to-amber-500 p-8
                shadow-[0px_4px_20px_rgba(0,0,0,0.05)] group hover:from-yellow-500 hover:to-amber-600
                transition-all cursor-pointer relative overflow-hidden rounded-sm">
        <div class="absolute -bottom-6 -right-6 w-32 h-32 opacity-15">
            <span class="material-symbols-outlined text-[120px] text-black">add_box</span>
        </div>
        <div class="mb-8 relative z-10">
            <span class="material-symbols-outlined text-5xl text-black" style="font-variation-settings:'FILL' 1;">add_box</span>
        </div>
        <div class="relative z-10">
            <h3 class="text-2xl font-black text-black mb-2">Nuevo Registro</h3>
            <p class="text-sm sm:text-base text-black/80 mb-6 leading-relaxed">
                Aperturar una nueva entrada de huésped, asignar habitaciones y registrar datos en tiempo real.
            </p>
            <div class="flex items-center gap-2 text-sm font-bold text-black group-hover:gap-4 transition-all uppercase tracking-wider">
                Registrar Entrada
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </div>
    </div>

    <!-- Registros -->
    <div onclick="location.href='registroHuespedes.php'"
         class="col-span-12 lg:col-span-3 bg-white border border-zinc-200 p-8
                shadow-[0px_4px_20px_rgba(0,0,0,0.03)] group hover:border-black
                transition-all cursor-pointer relative overflow-hidden rounded-sm">
        <div class="absolute top-0 right-0 w-32 h-32 bg-zinc-100 -mr-16 -mt-16 rounded-full
                    group-hover:scale-150 transition-transform duration-500"></div>
        <div class="mb-8 relative z-10">
            <span class="material-symbols-outlined text-5xl text-black group-hover:text-primary transition-colors"
                  style="font-variation-settings:'FILL' 1;">database</span>
        </div>
        <div class="relative z-10">
            <h3 class="text-2xl font-bold text-black mb-2">Registros</h3>
            <p class="text-sm sm:text-base text-zinc-500 mb-6 leading-relaxed">
                Visualizar historial completo, filtrar listados de entradas pasadas y auditar la base de datos.
            </p>
            <div class="flex items-center gap-2 text-sm font-bold text-black group-hover:gap-4 transition-all uppercase tracking-wider">
                Ver Historial
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </div>
    </div>

    <!-- Reservas -->
    <div onclick="location.href='reservas.php'"
         class="col-span-12 lg:col-span-3 bg-black p-8
                shadow-[0px_4px_20px_rgba(0,0,0,0.03)] group hover:bg-zinc-900
                transition-all cursor-pointer relative overflow-hidden rounded-sm">
        <div class="absolute -bottom-10 -right-10 w-48 h-48 opacity-20">
            <span class="material-symbols-outlined text-[160px] text-yellow-400">calendar_month</span>
        </div>
        <div class="mb-8 relative z-10">
            <span class="material-symbols-outlined text-5xl text-yellow-400" style="font-variation-settings:'FILL' 1;">calendar_month</span>
        </div>
        <div class="relative z-10">
            <h3 class="text-2xl font-bold text-white mb-2">Reservas</h3>
            <p class="text-sm sm:text-base text-zinc-400 mb-6 leading-relaxed">
                Administra el calendario de disponibilidad, cancelaciones y depósitos de garantía.
            </p>
            <div class="flex items-center gap-2 text-sm font-bold text-yellow-400 group-hover:gap-4 transition-all uppercase tracking-wider">
                Ver Calendario
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </div>
    </div>

    <!-- Huéspedes Actuales -->
    <div onclick="location.href='huespedesActuales.php'"
         class="col-span-12 lg:col-span-3 bg-primary-container p-8
                shadow-[0px_4px_20px_rgba(0,0,0,0.03)] group hover:bg-[#ffd000]
                transition-all cursor-pointer relative overflow-hidden rounded-sm">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full opacity-10 flex items-center justify-center">
            <span class="material-symbols-outlined text-[200px] text-black">groups</span>
        </div>
        <div class="mb-8 relative z-10">
            <span class="material-symbols-outlined text-5xl text-black" style="font-variation-settings:'FILL' 1;">groups</span>
        </div>
        <div class="relative z-10">
            <h3 class="text-2xl font-bold text-black mb-2">Huéspedes</h3>
            <p class="text-sm sm:text-base text-black/70 mb-6 leading-relaxed">
                Listado de huéspedes en casa, consumos adicionales y servicios pendientes.
            </p>
            <div class="flex items-center gap-2 text-sm font-bold text-black group-hover:gap-4 transition-all uppercase tracking-wider">
                Lista Maestra
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </div>
        </div>
    </div>

   

    <!-- ── Panel lateral de servicios ─────────────────────────────────────── -->
    <div class="col-span-12 lg:col-span-4 space-y-6">
        
        <button onclick="abrirModalReporte()"
                class="w-full bg-black text-white py-4 font-bold text-sm uppercase tracking-wide
                    flex items-center justify-center gap-3 hover:bg-zinc-800 transition-colors
                    rounded-sm shadow-md active:scale-[0.99]">
            <span class="material-symbols-outlined">print</span>
            Generar Reporte
        </button>
    </div>

</div><!-- /bento-grid -->

<!-- Banner inferior -->
<section class="mt-16 h-64 relative bg-zinc-900 overflow-hidden flex items-center px-12 rounded-sm">
    <img alt="Hotel Lobby"
         class="absolute inset-0 w-full h-full object-cover opacity-30 mix-blend-overlay"
         src="https://lh3.googleusercontent.com/aida-public/AB6AXuCHr0-pUv5HdYDLJ82X6zTQwqeR2FikQSBflwP-hRn2bjLjz8nUW9E1fWBshkDWsgGmA10Rq7VeSqGxTDGvlylmmySqX-sY0-29jMfMpnYmuHcJ8Dl2Kq5wFDgT-cjXHujBr7xaOfhmF2COET4-n-oxx1VKdfK_4mT2_t3NEy3kO9e1P6314VykgPN3zOrRr6MA4eQw51Gzz1_JMDHCDEa4YXVdxH_w1GBV2V8cbnQwUKemD8sM_cWgqDwSO8_HOp6AF3e5FDgSM1M"/>
    <div class="relative z-10 max-w-xl">
        <h2 class="text-white text-2xl sm:text-3xl font-extrabold mb-4 tracking-tight">
            Excelencia en el Servicio
        </h2>
        <p class="text-zinc-300 text-sm sm:text-base leading-relaxed">
            Optimice la experiencia de sus huéspedes con herramientas de gestión en tiempo real.
            Hospedaje Ayacucho: Calidad y precisión en cada detalle.
        </p>
    </div>
</section>

<!-- ── Modal de verificación de código ── -->
<div id="modalReporte"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white rounded-sm shadow-2xl w-full max-w-md mx-4 p-8 relative">

        <!-- Cerrar -->
        <button onclick="cerrarModalReporte()"
                class="absolute top-4 right-4 text-zinc-400 hover:text-black transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>

        <!-- Icono + título -->
        <div class="flex items-center gap-3 mb-2">
            <div class="h-10 w-10 bg-black flex items-center justify-center rounded-sm shrink-0">
                <span class="material-symbols-outlined text-yellow-400">shield_lock</span>
            </div>
            <h3 class="text-xl font-black text-black">Verificación de Acceso</h3>
        </div>
        <p class="text-sm text-zinc-500 mb-6">
            Se ha enviado un código de 6 dígitos a
            <span class="font-bold text-black">jhojan****@gmail.com</span>.
            Ingrésalo para acceder al reporte diario.
        </p>

        <!-- Estado de envío -->
        <div id="estadoEnvio" class="mb-4 text-sm text-zinc-500 flex items-center gap-2">
            <span class="material-symbols-outlined text-base animate-spin">progress_activity</span>
            Enviando código...
        </div>

        <!-- Input del código -->
        <input id="inputCodigo"
               type="text" maxlength="6" placeholder="000000"
               class="w-full border-2 border-zinc-200 focus:border-black outline-none rounded-sm
                      px-4 py-3 text-center text-3xl font-black tracking-[12px] text-black
                      transition-colors mb-2"
               oninput="this.value=this.value.replace(/\D/g,'')"/>

        <!-- Mensaje de error -->
        <p id="errorCodigo" class="text-red-500 text-xs font-bold mb-4 hidden"></p>

        <!-- Botones -->
        <div class="flex gap-3 mt-4">
            <button onclick="reenviarCodigo()"
                    class="flex-1 border border-zinc-200 text-zinc-600 py-3 text-sm font-bold
                           hover:border-black hover:text-black transition-colors rounded-sm">
                Reenviar código
            </button>
            <button onclick="verificarCodigo()"
                    class="flex-1 bg-black text-white py-3 text-sm font-bold
                           hover:bg-zinc-800 transition-colors rounded-sm">
                Verificar
            </button>
        </div>

    </div>
</div>

<script>
function abrirModalReporte() {
    document.getElementById('modalReporte').classList.remove('hidden');
    document.getElementById('modalReporte').classList.add('flex');
    document.getElementById('inputCodigo').value = '';
    document.getElementById('errorCodigo').classList.add('hidden');
    enviarCodigo();
}

function cerrarModalReporte() {
    document.getElementById('modalReporte').classList.add('hidden');
    document.getElementById('modalReporte').classList.remove('flex');
}

async function enviarCodigo() {
    const estado = document.getElementById('estadoEnvio');
    estado.innerHTML = '<span class="material-symbols-outlined text-base animate-spin">progress_activity</span> Enviando código...';

    const res  = await fetch('enviar_codigo.php');
    const data = await res.json();

    if (data.ok) {
        estado.innerHTML = '<span class="material-symbols-outlined text-base text-emerald-500">check_circle</span> Código enviado correctamente.';
    } else {
        estado.innerHTML = '<span class="material-symbols-outlined text-base text-red-500">error</span> Error al enviar: ' + (data.error ?? '');
    }
}

function reenviarCodigo() {
    document.getElementById('inputCodigo').value = '';
    document.getElementById('errorCodigo').classList.add('hidden');
    enviarCodigo();
}

async function verificarCodigo() {
    const codigo = document.getElementById('inputCodigo').value.trim();
    const error  = document.getElementById('errorCodigo');

    if (codigo.length !== 6) {
        error.textContent = 'Ingresa los 6 dígitos del código.';
        error.classList.remove('hidden');
        return;
    }

    const res  = await fetch('verificar_codigo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ codigo })
    });
    const data = await res.json();

    if (data.ok) {
        cerrarModalReporte();
        location.href = 'reportes.php';
    } else {
        error.textContent = data.error ?? 'Código incorrecto.';
        error.classList.remove('hidden');
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalReporte').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalReporte();
});

</script>

<?php require_once 'layout/footer.php'; ?>




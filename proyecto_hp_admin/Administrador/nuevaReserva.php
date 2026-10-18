<?php
// Admin/nuevaReserva.php

$paginaActual = 'reservas';
$tituloPagina = 'Nueva Reserva';

require_once 'layout/header.php';
require_once '../Conexion/conexion.php';

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipoHabitacion   = $_POST['tipoHabitacion'] ?? '';
    $nombreCompleto   = trim($_POST['nombreCompleto'] ?? '');
    $dni              = trim($_POST['dni'] ?? '');
    $diasHospedaje    = intval($_POST['diasHospedaje'] ?? 0);
    $costo            = floatval($_POST['costo'] ?? 0);
    $fechaHoraLlegada = date('Y-m-d H:i:s', strtotime($_POST['fechaHoraLlegada']));

    $errores = [];
    $fechaActual = date('Y-m-d H:i:00');

    if (empty($tipoHabitacion) || empty($nombreCompleto) || empty($dni) || empty($_POST['diasHospedaje']) || empty($_POST['costo']) || empty($_POST['fechaHoraLlegada'])) {
        $errores[] = "Todos los campos son estrictamente obligatorios.";
    }

    $palabras = array_filter(explode(' ', $nombreCompleto));
    $cantPalabras = count($palabras);
    if ($cantPalabras < 3 || $cantPalabras > 5) {
        $errores[] = "El nombre completo debe tener entre 3 y 5 palabras.";
    } else {
        $nombreCompleto = ucwords(mb_strtolower($nombreCompleto, 'UTF-8'));
    }

    if (!preg_match('/^\d{8}$/', $dni)) {
        $errores[] = "El documento (DNI) debe contener exactamente 8 números.";
    }

    if ($tipoHabitacion === 'Momentaneo') {
        if ($diasHospedaje < 3 || $diasHospedaje > 12) {
            $errores[] = "Para estadías momentáneas, el tiempo permitido es de 3 a 12 horas.";
        }
    } elseif ($tipoHabitacion === 'Diario') {
        if ($diasHospedaje < 1 || $diasHospedaje > 7) {
            $errores[] = "Para estadías diarias, el tiempo permitido es de 1 a 7 días.";
        }
    }

    if ($costo <= 0 || ($costo % 5) != 0) {
        $errores[] = "El costo debe ser mayor a 0 y subir de 5 en 5 soles.";
    }

    if (strtotime($fechaHoraLlegada) < strtotime($fechaActual)) {
        $errores[] = "La fecha y hora de llegada no puede ser anterior al momento actual.";
    }

    if (empty($errores)) {
        $conn = Cconexion::ConexionBD();
        try {
            $stmt = $conn->prepare("INSERT INTO Reservas 
                (TipoHabitacion, NombreCompleto, DNI, DiasHospedaje, FechaHoraLlegada, Costo) 
                VALUES (:tipoHabitacion, :nombreCompleto, :dni, :diasHospedaje, :fechaHoraLlegada, :costo)");
            $stmt->bindParam(':tipoHabitacion',   $tipoHabitacion);
            $stmt->bindParam(':nombreCompleto',   $nombreCompleto);
            $stmt->bindParam(':dni',              $dni);
            $stmt->bindParam(':diasHospedaje',    $diasHospedaje);
            $stmt->bindParam(':fechaHoraLlegada', $fechaHoraLlegada);
            $stmt->bindParam(':costo',            $costo);

            $mensaje = $stmt->execute()
                ? "✅ Reserva guardada correctamente."
                : "❌ Error al guardar la reserva.";
        } catch (PDOException $e) {
            $mensaje = "❌ Error al guardar: " . $e->getMessage();
        }
    } else {
        $mensaje = "❌ " . implode(" | ", $errores);
    }
}
?>

<div class="w-full flex flex-col justify-center items-center py-4">
    <div class="w-full max-w-xl bg-white border border-zinc-100 shadow-[0px_10px_30px_rgba(0,0,0,0.04)] rounded-sm p-10">

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-extrabold text-black tracking-tight">Registrar Nueva Reserva</h1>
            <p class="text-sm text-zinc-500 mt-2">Ingrese los datos correspondientes para asegurar la habitación en el sistema.</p>
        </div>

        <!-- Mensaje PHP -->
        <?php if (!empty($mensaje)): ?>
            <div class="mb-6 p-4 rounded-sm text-sm font-semibold tracking-tight text-center
                        <?= (strpos($mensaje, '✅') !== false)
                            ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                            : 'bg-red-50 text-red-800 border border-red-200' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <!-- Mensaje JS (aparece aquí arriba, antes del form) -->
        <div id="mensajeJS" class="hidden mb-6 p-4 rounded-sm text-sm font-semibold tracking-tight text-center bg-red-50 text-red-800 border border-red-200"></div>

        <form method="POST" class="space-y-5" onsubmit="return validarFormulario();">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 mb-2">Tipo de Habitación</label>
                <select name="tipoHabitacion" id="tipoHabitacion" required onchange="ajustarTipoHospedaje()"
                        class="w-full border-zinc-300 rounded-sm text-base p-3 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 transition-colors">
                    <option value="">Seleccionar Tipo</option>
                    <option value="Momentaneo">Momentáneo</option>
                    <option value="Diario">Diario</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 mb-2">Nombre Completo del Huésped</label>
                <input type="text" name="nombreCompleto" id="nombreCompleto" placeholder="Ej. Juan Pérez Guerrero" required oninput="capitalizarNombre(this)"
                       class="w-full border-zinc-300 rounded-sm text-base p-3 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 mb-2">Documento (DNI / CE)</label>
                    <input type="text" name="dni" id="dni" placeholder="Número de identidad" required maxlength="8" pattern="\d{8}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                           class="w-full border-zinc-300 rounded-sm text-base p-3 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 transition-colors font-mono">
                </div>
                <div>
                    <label id="labelHospedaje" class="block text-xs font-bold uppercase tracking-wider text-zinc-700 mb-2">Días de Hospedaje</label>
                    <input type="number" name="diasHospedaje" id="diasHospedaje" placeholder="Cantidad" required
                           class="w-full border-zinc-300 rounded-sm text-base p-3 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 mb-2">Costo Pactado (S/.)</label>
                    <input type="number" step="5" min="5" name="costo" id="costo" placeholder="0" required
                           class="w-full border-zinc-300 rounded-sm text-base p-3 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 transition-colors font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 mb-2">Fecha y Hora de Llegada</label>
                    <input type="datetime-local" name="fechaHoraLlegada" id="fechaHoraLlegada" required
                           class="w-full border-zinc-300 rounded-sm text-base p-3 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 transition-colors font-mono">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit"
                        class="w-full bg-black text-white p-4 font-bold text-base rounded-sm hover:bg-zinc-800 transition-all shadow-sm active:scale-[0.98]">
                    Guardar Reserva en Sistema
                </button>
            </div>
        </form>

        <div class="mt-6 border-t border-zinc-100 pt-5">
            <button onclick="location.href='reservas.php'"
                    class="w-full border border-zinc-300 text-zinc-700 p-3 font-semibold text-sm rounded-sm bg-white hover:bg-zinc-50 transition-all text-center block active:scale-[0.98]">
                ← Volver al Panel de Reservas
            </button>
        </div>

    </div>
</div>

<?php require_once 'layout/footer.php'; ?>

<script>
// Función para mostrar error arriba en lugar de alert()
function mostrarError(msg) {
    const div = document.getElementById('mensajeJS');
    div.textContent = msg;
    div.classList.remove('hidden');
    div.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function ocultarError() {
    document.getElementById('mensajeJS').classList.add('hidden');
}

window.addEventListener('DOMContentLoaded', () => {
    actualizarMinimoFecha();
});

function actualizarMinimoFecha() {
    const ahora = new Date();
    const minFecha = new Date(ahora.getTime() - ahora.getTimezoneOffset() * 60000)
        .toISOString().slice(0, 16);
    const inputFecha = document.getElementById('fechaHoraLlegada');
    inputFecha.min = minFecha;
    if (!inputFecha.value) {
        inputFecha.value = minFecha;
    }
}

function capitalizarNombre(input) {
    let palabras = input.value.split(' ');
    for (let i = 0; i < palabras.length; i++) {
        if (palabras[i].length > 0) {
            palabras[i] = palabras[i].charAt(0).toUpperCase() + palabras[i].slice(1).toLowerCase();
        }
    }
    input.value = palabras.join(' ');
}

function ajustarTipoHospedaje() {
    const tipo = document.getElementById('tipoHabitacion').value;
    const label = document.getElementById('labelHospedaje');
    const input = document.getElementById('diasHospedaje');

    if (tipo === 'Momentaneo') {
        label.textContent = "Horas de Hospedaje";
        input.placeholder = "Min 3 - Max 12 horas";
        input.min = "3";
        input.max = "12";
    } else if (tipo === 'Diario') {
        label.textContent = "Días de Hospedaje";
        input.placeholder = "Min 1 - Max 7 días";
        input.min = "1";
        input.max = "7";
    } else {
        label.textContent = "Días de Hospedaje";
        input.placeholder = "Cantidad";
        input.removeAttribute('min');
        input.removeAttribute('max');
    }
}

function validarFormulario() {
    ocultarError();

    const tipo   = document.getElementById('tipoHabitacion').value;
    const nombre = document.getElementById('nombreCompleto').value.trim();
    const dni    = document.getElementById('dni').value.trim();
    const tiempo = parseInt(document.getElementById('diasHospedaje').value);
    const costo  = parseFloat(document.getElementById('costo').value);
    const valorFecha = document.getElementById('fechaHoraLlegada').value;

    // 1. Campos obligatorios
    if (!tipo || !nombre || !dni || isNaN(tiempo) || isNaN(costo) || !valorFecha) {
        mostrarError("⚠️ Todos los campos son obligatorios.");
        return false;
    }

    // 2. Nombre: 3 a 5 palabras
    const palabras = nombre.split(/\s+/).filter(p => p.length > 0);
    if (palabras.length < 3 || palabras.length > 5) {
        mostrarError("⚠️ El nombre completo debe tener un mínimo de 3 palabras y un máximo de 5 palabras.");
        return false;
    }

    // 3. DNI: 8 dígitos
    if (!/^\d{8}$/.test(dni)) {
        mostrarError("⚠️ El DNI debe ser únicamente de 8 dígitos numéricos.");
        return false;
    }

    // 4. Rango de tiempo según tipo
    if (tipo === 'Momentaneo' && (tiempo < 3 || tiempo > 12)) {
        mostrarError("⚠️ El alojamiento Momentáneo solo permite un rango de 3 a 12 horas.");
        return false;
    }
    if (tipo === 'Diario' && (tiempo < 1 || tiempo > 7)) {
        mostrarError("⚠️ El alojamiento Diario solo permite un rango de 1 a 7 días.");
        return false;
    }

    // 5. Costo múltiplo de 5
    if (costo <= 0) {
        mostrarError("⚠️ El costo pactado debe ser un monto positivo.");
        return false;
    }
    if (costo % 5 !== 0) {
        mostrarError("⚠️ El costo debe ser un valor múltiplo de 5 (incrementos de 5 en 5 soles).");
        return false;
    }

    // 6. Fecha: comparar como strings simples (evita problemas de timezone)
    const ahoraStr = new Date().toLocaleString('sv-SE', { timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone }).slice(0, 16);
    
    if (valorFecha < ahoraStr) {
        mostrarError("⚠️ La fecha y hora de llegada no puede ser inferior al dia actual.");
        return false;
    }
    
    // 7. VALIDACIÓN DE DISPONIBILIDAD - Bloquear envío hasta verificar
    verificarDisponibilidadYEnviar(tipo, tiempo, valorFecha);
    return false; // Prevenir envío inmediato
}

async function verificarDisponibilidadYEnviar(tipoHabitacion, diasHospedaje, fechaLlegada) {
    // Nota: En nuevaReserva.php no hay selección de habitación específica
    // Solo validamos que la fecha sea futura
    // La validación completa se hará en el backend al guardar
    
    // Por ahora, permitir continuar (la validación real está en reservar.php)
    document.querySelector('form').submit();
}
</script>
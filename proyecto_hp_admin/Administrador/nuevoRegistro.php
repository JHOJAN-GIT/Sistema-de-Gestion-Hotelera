<?php
// Admin/nuevoRegistro.php

$paginaActual = 'registros';
$tituloPagina = 'Nuevo Registro de Huésped';

require_once 'layout/header.php';

include_once("../Conexion/conexion.php");
$conn    = Cconexion::ConexionBD();
$idReservaOrigen = isset($_GET['idReserva']) ? intval($_GET['idReserva']) : 0;
$tablaOrigen     = isset($_GET['tabla']) ? $_GET['tabla'] : '';
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $conn) {
    $numeroHabitacion = intval($_POST['numeroHabitacion'] ?? 0);
    $nombreCompleto   = trim($_POST['nombreCompleto'] ?? '');
    $dni              = trim($_POST['dni'] ?? '');
    $monto            = floatval($_POST['monto'] ?? 0);
    $tipoAlojamiento  = $_POST['tipoAlojamiento'] ?? '';
    $diasHospedaje    = intval($_POST['diasHospedaje'] ?? 0);
    $observaciones    = $_POST['observaciones'] ?? '';
    $idReservaOrigen = intval($_POST['idReserva'] ?? $_GET['idReserva'] ?? 0);
    $tablaOrigen     = $_POST['tabla'] ?? $_GET['tabla'] ?? '';

    $errores = [];

    if (empty($numeroHabitacion) || empty($nombreCompleto) || empty($dni) || empty($monto) || empty($tipoAlojamiento)) {
        $errores[] = "Todos los campos obligatorios deben ser completados.";
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

    if (!is_numeric($monto) || $monto < 0 || ($monto % 5) != 0) {
        $errores[] = "El monto debe ser un número positivo y avanzar de 5 en 5 soles.";
    }

    if ($tipoAlojamiento === 'Diario' && ($diasHospedaje < 1 || $diasHospedaje > 7)) {
        $errores[] = "Para estadía diaria, los días deben ser entre 1 y 7.";
    }
    if ($tipoAlojamiento === 'Momentaneo' && ($diasHospedaje < 3 || $diasHospedaje > 12)) {
        $errores[] = "Para estadía momentánea, las horas deben ser entre 3 y 12.";
    }

    if (empty($errores)) {
        try {
            date_default_timezone_set('America/Lima');
            $ahora        = new DateTime('now', new DateTimeZone('America/Lima'));
            $fechaIngreso = $ahora->format('Y-m-d H:i:s');

            // Calcular FechaHoraSalida
            if ($tipoAlojamiento === 'Momentaneo') {
                $salida = (clone $ahora)->modify("+{$diasHospedaje} hours");
                $fechaSalida = $salida->format('Y-m-d H:i:s');
            } else {
                // Diario: punto de corte 8:00am
                $corte = new DateTime($ahora->format('Y-m-d') . ' 08:00:00');
                if ($ahora < $corte) {
                    // Ingresó antes de las 8am: días - 1 días después a las 12pm
                    $diasSumar = $diasHospedaje - 1;
                } else {
                    // Ingresó a las 8am o después: días completos después a las 12pm
                    $diasSumar = $diasHospedaje;
                }
                $salida = new DateTime($ahora->format('Y-m-d') . ' 12:00:00');
                $salida->modify("+{$diasSumar} days");
                $fechaSalida = $salida->format('Y-m-d H:i:s');
            }

           $stmt = $conn->prepare("INSERT INTO Registros 
    (NumeroHabitacion, NombreCompleto, DNI, Monto, TipoAlojamiento, DiasHospedaje, FechaHoraIngreso, CheckoutPronosticado, Observaciones) 
    VALUES (:numeroHabitacion, :nombreCompleto, :dni, :monto, :tipoAlojamiento, :diasHospedaje, 
            CONVERT(datetime, :fechaIngreso, 120), 
            CONVERT(datetime, :checkoutPronosticado, 120), 
            :observaciones)");
$stmt->bindParam(':numeroHabitacion',      $numeroHabitacion, PDO::PARAM_INT);
$stmt->bindParam(':nombreCompleto',        $nombreCompleto);
$stmt->bindParam(':dni',                   $dni);
$stmt->bindParam(':monto',                 $monto);
$stmt->bindParam(':tipoAlojamiento',       $tipoAlojamiento);
$stmt->bindParam(':diasHospedaje',         $diasHospedaje, PDO::PARAM_INT);
$stmt->bindParam(':fechaIngreso',          $fechaIngreso);
$stmt->bindParam(':checkoutPronosticado',  $fechaSalida);
$stmt->bindParam(':observaciones',         $observaciones);
            $stmt->execute();

            $stmtHab = $conn->prepare("UPDATE Habitaciones SET Estado = 'Ocupado' WHERE NumeroHabitacion = :numeroHabitacion");
            $stmtHab->bindParam(':numeroHabitacion', $numeroHabitacion);
            $stmtHab->execute();


// Actualizar estado en tabla de reservas origen
if ($idReservaOrigen > 0 && in_array($tablaOrigen, ['Reservas', 'ReservasClientes'])) {
    $stmtEstado = $conn->prepare("UPDATE {$tablaOrigen} SET Estado = 'Registrada' WHERE IdReserva = :id");
    $stmtEstado->bindParam(':id', $idReservaOrigen, PDO::PARAM_INT);
    $stmtEstado->execute();
}

            $mensaje = "✅ Registro guardado correctamente.";
        } catch (PDOException $e) {
            $mensaje = "❌ Error al guardar: " . $e->getMessage();
        }
    } else {
        $mensaje = "❌ " . implode(" | ", $errores);
    }
}

$todasLasHabitaciones    = array_merge(range(301, 305), range(401, 405), range(501, 505), range(601, 605));
$habitacionesOcupadas    = [];
$habitacionesDisponibles = [];

if ($conn) {
    try {
        $stmt = $conn->query("SELECT NumeroHabitacion FROM Registros WHERE FechaHoraSalida IS NULL");
        $habitacionesOcupadas    = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $habitacionesDisponibles = array_diff($todasLasHabitaciones, $habitacionesOcupadas);
    } catch (PDOException $e) {
        $mensaje = "❌ Error al cargar habitaciones: " . $e->getMessage();
    }
}
?>

<div class="w-full flex flex-col justify-center items-center py-4">
    <div class="w-full max-w-xl bg-white border border-zinc-100 shadow-[0px_10px_30px_rgba(0,0,0,0.04)] rounded-sm p-10">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-extrabold text-black tracking-tight">Registrar un Huésped</h1>
            <p class="text-sm text-zinc-500 mt-2 leading-relaxed">
                Ingresa los datos personales del cliente, selecciona su tipo de alojamiento y asígnale una habitación libre.
            </p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="mb-6 p-4 rounded-sm text-sm font-bold flex items-center justify-center gap-3
                        <?= (strpos($mensaje, '✅') !== false)
                            ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                            : 'bg-zinc-900 text-yellow-400' ?>">
                <span class="material-symbols-outlined text-base">info</span>
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5" onsubmit="return validarFormulario();">

            <!-- 1. Tipo de Alojamiento (primero) -->
            <div>
                <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Tipo de Alojamiento</label>
                <select name="tipoAlojamiento" id="tipoAlojamiento" required onchange="actualizarTipo()"
                        class="w-full border-zinc-300 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 p-3 text-base rounded-sm bg-white">
                    <option value="">Seleccionar...</option>
                    <option value="Momentaneo" <?= (isset($_GET['tipoAlojamiento']) && $_GET['tipoAlojamiento'] === 'Momentaneo') ? 'selected' : '' ?>>Momentáneo</option>
                    <option value="Diario"     <?= (isset($_GET['tipoAlojamiento']) && $_GET['tipoAlojamiento'] === 'Diario')     ? 'selected' : '' ?>>Diario</option>
                </select>
            </div>

            <!-- 2. Habitaciones -->
            <div>
                <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-3">Selecciona una habitación disponible:</label>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    <?php 
                    $numeroHabitacionPreseleccionado = isset($_GET['numeroHabitacion']) ? intval($_GET['numeroHabitacion']) : 0;
                    foreach ($todasLasHabitaciones as $hab):
                        $ocupada = !in_array($hab, $habitacionesDisponibles);
                        $preseleccionado = ($hab == $numeroHabitacionPreseleccionado);
                        
                        if ($ocupada && !$preseleccionado): ?>
                            <label class="flex items-center justify-center p-3 border border-zinc-200 bg-zinc-100 text-zinc-400 cursor-not-allowed select-none text-base font-bold rounded-sm gap-1 shadow-sm">
                                <span class="material-symbols-outlined text-xs text-zinc-400">lock</span> <?= $hab ?>
                            </label>
                        <?php else: ?>
                            <label class="flex items-center justify-center p-3 border border-zinc-200 <?= $preseleccionado ? 'bg-yellow-400 border-black' : 'bg-white' ?> text-zinc-800 hover:border-black cursor-pointer text-base font-bold rounded-sm transition-all relative group dynamic-radio shadow-sm hover:shadow">
                                <input type="radio" name="numeroHabitacion" value="<?= $hab ?>" <?= $preseleccionado ? 'checked' : '' ?> class="absolute opacity-0 pointer-events-none" required>
                                <span><?= $hab ?></span>
                            </label>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" name="idReserva" value="<?= isset($_GET['idReserva']) ? intval($_GET['idReserva']) : 0 ?>">
            <input type="hidden" name="tabla"     value="<?= isset($_GET['tabla']) ? htmlspecialchars($_GET['tabla']) : '' ?>">

            <!-- 3. Días / Horas de hospedaje (dinámico) -->
            <div id="campoDias" class="hidden">
                <label id="labelDias" class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Días de Hospedaje</label>
                <input type="number" name="diasHospedaje" id="diasHospedaje" placeholder="Cantidad"
                       value="<?= isset($_GET['diasHospedaje']) ? htmlspecialchars($_GET['diasHospedaje']) : '' ?>"
                       class="w-full border-zinc-300 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 p-3 text-base rounded-sm">
            </div>

            <!-- 4. Nombre -->
            <div>
                <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Nombre Completo</label>
                <input type="text" name="nombreCompleto" id="nombreCompleto"
                       value="<?= isset($_GET['nombreCompleto']) ? htmlspecialchars($_GET['nombreCompleto']) : '' ?>"
                       class="w-full border-zinc-300 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 placeholder-zinc-400 p-3 text-base rounded-sm"
                       placeholder="Ej. Juan Pérez García" required oninput="capitalizarNombre(this)">
            </div>

            <!-- 5. DNI + Monto -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Documento (DNI / CE)</label>
                    <input type="text" name="dni" id="dni" maxlength="8" pattern="\d{8}"
                           value="<?= isset($_GET['dni']) ? htmlspecialchars($_GET['dni']) : '' ?>"
                           class="w-full border-zinc-300 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 placeholder-zinc-400 p-3 text-base rounded-sm font-mono"
                           placeholder="Número de documento" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Monto de Pago (S/.)</label>
                    <input type="number" step="5" min="0" name="monto" id="monto"
                           value="<?= isset($_GET['monto']) ? htmlspecialchars($_GET['monto']) : '' ?>"
                           class="w-full border-zinc-300 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 placeholder-zinc-400 p-3 text-base rounded-sm font-semibold"
                           placeholder="0" required>
                </div>
            </div>

            <!-- 6. Observaciones -->
            <div>
                <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Observaciones</label>
                <textarea name="observaciones" rows="3"
                          class="w-full border-zinc-300 focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400 placeholder-zinc-400 p-3 text-base rounded-sm"
                          placeholder="Detalles adicionales sobre el huésped..."><?= isset($_GET['servicioEspecial']) && !empty($_GET['servicioEspecial']) ? htmlspecialchars($_GET['servicioEspecial']) : '' ?></textarea>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full bg-black text-white p-4 font-bold text-base rounded-sm hover:bg-zinc-800 transition-all shadow-sm active:scale-[0.98] flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base text-yellow-400">save</span>
                    Guardar Registro
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>

<script>
document.querySelectorAll('.dynamic-radio').forEach(label => {
    label.addEventListener('click', () => {
        document.querySelectorAll('.dynamic-radio').forEach(l => l.classList.remove('bg-yellow-400', 'border-black'));
        label.classList.add('bg-yellow-400', 'border-black');
    });
});

function actualizarTipo() {
    const tipo      = document.getElementById('tipoAlojamiento').value;
    const campo     = document.getElementById('campoDias');
    const label     = document.getElementById('labelDias');
    const input     = document.getElementById('diasHospedaje');

    if (tipo === 'Diario') {
        campo.classList.remove('hidden');
        label.textContent = 'Días de Hospedaje (Mín. 1 - Máx. 7)';
        input.min = '1'; input.max = '7'; input.placeholder = 'Entre 1 y 7 días';
        input.required = true;
    } else if (tipo === 'Momentaneo') {
        campo.classList.remove('hidden');
        label.textContent = 'Horas de Hospedaje (Mín. 3 - Máx. 12)';
        input.min = '3'; input.max = '12'; input.placeholder = 'Entre 3 y 12 horas';
        input.required = true;
    } else {
        campo.classList.add('hidden');
        input.required = false;
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

function validarFormulario() {
    const nombre          = document.getElementById('nombreCompleto').value.trim();
    const dni             = document.getElementById('dni').value.trim();
    const monto           = parseFloat(document.getElementById('monto').value);
    const habitacion      = document.querySelector('input[name="numeroHabitacion"]:checked');
    const tipoAlojamiento = document.getElementById('tipoAlojamiento').value;
    const diasVal         = parseInt(document.getElementById('diasHospedaje').value);

    if (!tipoAlojamiento) {
        alert("Debe seleccionar el tipo de alojamiento.");
        return false;
    }
    if (!habitacion) {
        alert("Debe seleccionar una habitación disponible.");
        return false;
    }
    if (isNaN(diasVal)) {
        alert("Debe ingresar los días/horas de hospedaje.");
        return false;
    }
    if (tipoAlojamiento === 'Diario' && (diasVal < 1 || diasVal > 7)) {
        alert("Para estadía diaria, ingrese entre 1 y 7 días.");
        return false;
    }
    if (tipoAlojamiento === 'Momentaneo' && (diasVal < 3 || diasVal > 12)) {
        alert("Para estadía momentánea, ingrese entre 3 y 12 horas.");
        return false;
    }
    if (nombre === "" || dni === "" || isNaN(monto)) {
        alert("Todos los campos obligatorios deben ser completados.");
        return false;
    }
    const cantidadPalabras = nombre.split(/\s+/).filter(p => p.length > 0).length;
    if (cantidadPalabras < 3 || cantidadPalabras > 5) {
        alert("El nombre completo debe tener entre 3 y 5 palabras.");
        return false;
    }
    if (!/^\d{8}$/.test(dni)) {
        alert("El documento (DNI) debe tener exactamente 8 cifras numéricas.");
        return false;
    }
    if (monto < 0 || monto % 5 !== 0) {
        alert("El monto debe ser positivo y múltiplo de 5.");
        return false;
    }
    return true;
}

// Preseleccionar tipo si viene por GET
window.addEventListener('DOMContentLoaded', () => {
    const tipo = document.getElementById('tipoAlojamiento').value;
    if (tipo) actualizarTipo();
});
</script>
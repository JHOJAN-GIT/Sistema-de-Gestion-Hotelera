<?php
session_start();
if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'cliente') {
    header("Location: ../Login/login.php");
    exit();
}

require_once "../Conexion/conexion.php";

$resumen = null;
$error   = null;

// Parámetros GET
$habitacionSeleccionada = $_GET['num_habitacion'] ?? '';
$costoBaseHabitacion    = $_GET['costo_base']     ?? 0;

// Determinar TipoHabitacion según número de habitación
// Momentaneo: 301-305 | Diario: todo lo demás
function getTipoHabitacion($numHabitacion) {
    $num = intval($numHabitacion);
    if ($num >= 301 && $num <= 305) {
        return 'Momentaneo';
    }
    return 'Diario';
}

$tipoEstadia = !empty($habitacionSeleccionada) ? getTipoHabitacion($habitacionSeleccionada) : 'Diario';

// Cargar habitaciones disponibles para el modal
$gruposModal = [];
try {
    $dbHabs  = Cconexion::ConexionBD();
    $sqlHabs = "SELECT h.NumeroHabitacion, h.Piso, h.Estado,
                       th.IdTipo, th.NombreTipo, th.Precio, th.Descripcion, th.TipoHabitacion
                FROM dbo.Habitaciones h
                INNER JOIN dbo.TiposHabitacion th ON h.IdTipo = th.IdTipo
                WHERE h.Estado = 'Libre'
                ORDER BY h.NumeroHabitacion ASC";
    $stmtHabs = $dbHabs->prepare($sqlHabs);
    $stmtHabs->execute();
    $habitacionesDisponibles = $stmtHabs->fetchAll(PDO::FETCH_ASSOC);

    foreach ($habitacionesDisponibles as $hab) {
        $nombreMostrar = $hab['NombreTipo'];
        if (strtolower(trim($nombreMostrar)) === 'simple') $nombreMostrar = 'Individual';
        $gruposModal[$nombreMostrar][] = $hab;
    }
} catch (Exception $ex) { }

// POST — procesar reserva
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $db = Cconexion::ConexionBD();

        $dni              = trim($_POST['dni']             ?? '');
        $nombreCliente    = trim($_POST['nombre_cliente']  ?? '');
        $numHabitacion    = $_POST['num_habitacion']       ?? '';
        $fechaLlegada     = $_POST['fecha_llegada']        ?? '';
        $costoFinal       = $_POST['costo_final']          ?? 0;
        $servicioEspecial = trim($_POST['servicio_especial'] ?? 'Ninguno');

        // Calcular TipoHabitacion según regla 301-305
        $tipoReserva = getTipoHabitacion($numHabitacion);

        // Calcular tiempo de estadía según tipo
        $tiempoEstadia = "";
        if ($tipoReserva === "Diario") {
            $tiempoEstadia = intval($_POST['cant_dias']  ?? 1);
        } else {
            $tiempoEstadia = intval($_POST['cant_horas'] ?? 3);
        }

        // Validaciones
        if (empty($numHabitacion) || empty($fechaLlegada)) {
            throw new Exception("Por favor, complete todos los campos obligatorios.");
        }
        if (strlen($dni) !== 8 || !ctype_digit($dni)) {
            throw new Exception("El DNI debe tener exactamente 8 dígitos numéricos.");
        }

        // Validar NombreCompleto: mínimo 3 palabras, máximo 5
        $palabrasNombre = array_filter(explode(' ', $nombreCliente));
        $cantPalabras   = count($palabrasNombre);
        if ($cantPalabras < 3) {
            throw new Exception("El nombre completo debe tener mínimo 3 palabras.");
        }
        if ($cantPalabras > 5) {
            throw new Exception("El nombre completo debe tener máximo 5 palabras.");
        }

        // Validar FechaHoraLlegada > fecha actual
        $fechaIngresada = new DateTime($fechaLlegada);
        $fechaActual    = new DateTime();
        if ($fechaIngresada <= $fechaActual) {
            throw new Exception("La fecha y hora de llegada debe ser mayor a la fecha y hora actual.");
        }

        // Verificar conflicto de reserva
        $sqlConflicto = "SELECT COUNT(*) FROM [BD_HOSPEDAJE].[dbo].[ReservasClientes]
                         WHERE NumeroHabitacion = :numHab
                         AND FechaHoraLlegada = :fecha";
        $stmtC = $db->prepare($sqlConflicto);
        $stmtC->bindParam(':numHab', $numHabitacion);
        $stmtC->bindParam(':fecha',  $fechaLlegada);
        $stmtC->execute();
        $conflicto = $stmtC->fetchColumn();

        if ($conflicto > 0) {
            throw new Exception("La habitación N° $numHabitacion ya tiene una reserva para esa fecha y hora. Elige otra.");
        }

        // INSERT hacia ReservasClientes
        // Estado se envía como NULL
        $query = "INSERT INTO [BD_HOSPEDAJE].[dbo].[ReservasClientes]
                    (TipoHabitacion, NombreCompleto, DNI, DiasHospedaje, FechaHoraLlegada,
                     Costo, NumHabitacion, ServicioEspecial, IdUsuario, NumeroHabitacion, Estado)
                  VALUES
                    (:tipoHab, :nombre, :dni, :estadia, :fecha,
                     :costo, :numHab, :servicio, :idUsuario, :numeroHabitacion, NULL)";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':tipoHab',          $tipoReserva);          // 'Momentaneo' o 'Diario'
        $stmt->bindParam(':nombre',           $nombreCliente);
        $stmt->bindParam(':dni',              $dni);
        $stmt->bindParam(':estadia',          $tiempoEstadia);
        $stmt->bindParam(':fecha',            $fechaLlegada);
        $stmt->bindParam(':costo',            $costoFinal);
        $stmt->bindParam(':numHab',           $numHabitacion);
        $stmt->bindParam(':servicio',         $servicioEspecial);
        $stmt->bindValue(':idUsuario',        $_SESSION['id'], PDO::PARAM_INT);
        $stmt->bindParam(':numeroHabitacion', $numHabitacion); // NumeroHabitacion = número elegido
        $stmt->execute();

        $resumen = [
            'nombre'     => $nombreCliente,
            'dni'        => $dni,
            'tipo'       => $tipoReserva,
            'habitacion' => $numHabitacion,
            'fecha'      => $fechaLlegada,
            'estadia'    => $tiempoEstadia,
            'costo'      => $costoFinal
        ];

    } catch (Exception $e) {
        $error = "❌ " . $e->getMessage();
    }
}

$hayHabitacion = !empty($habitacionSeleccionada);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Solicitar Reserva - Hospedaje Ayacucho</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9f9f9; }
        .custom-shadow { box-shadow: 0px 10px 30px rgba(0,0,0,0.04); }
    </style>
</head>
<body class="text-zinc-900 antialiased min-h-screen flex flex-col justify-between">

<header class="flex justify-between items-center h-16 px-8 w-full bg-white border-b border-zinc-100 shadow-sm">
    <div class="flex items-center gap-3">
        <span class="text-lg font-black tracking-widest text-zinc-900 uppercase">Hospedaje Ayacucho</span>
    </div>
    <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest">Módulo de Reservas</span>
</header>

<main class="flex-1 flex justify-center items-start p-4 sm:p-8 my-6">
    <div class="bg-white border border-zinc-200 rounded-sm custom-shadow max-w-xl w-full overflow-hidden">

        <div class="bg-zinc-950 p-6 text-white flex items-center gap-3">
            <div class="w-10 h-10 bg-yellow-400 flex items-center justify-center rounded-sm text-zinc-950">
                <span class="material-symbols-outlined font-bold">room_preferences</span>
            </div>
            <div>
                <h1 class="text-xl font-extrabold tracking-tight">Configura tu Estadía</h1>
                <p class="text-xs text-zinc-400 uppercase tracking-widest mt-0.5">Completa los detalles de tu reservación</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border-b border-red-200 p-4 text-red-700 text-xs font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">error</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form id="formReserva" method="POST" action="" class="p-6 sm:p-8 flex flex-col gap-6">

            <!-- 1. DNI y Nombre -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">DNI (8 dígitos)</label>
                    <input type="text" name="dni" id="dni" maxlength="8" placeholder="12345678" required
                           value="<?php echo htmlspecialchars($_SESSION['dni'] ?? ''); ?>"
                           class="w-full rounded-sm border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900 text-sm">
                    <p id="dniError" class="text-xs text-red-600 font-medium mt-1 hidden">Solo 8 números exactos.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Nombre Completo</label>
                    <input type="text" name="nombre_cliente" id="nombre_cliente" placeholder="Nombres y Apellidos" required
                           value="<?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?>"
                           class="w-full rounded-sm border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900 text-sm">
                    <p id="nombreError" class="text-xs text-red-600 font-medium mt-1 hidden">Mínimo 3 palabras y máximo 5.</p>
                </div>
            </div>

            <hr class="border-zinc-100">

            <!-- 2. Habitación -->
            <div>
                <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Habitación Solicitada</label>

                <?php if (!$hayHabitacion): ?>
                <div id="bloqueBotonSeleccionar">
                    <button type="button" onclick="abrirModalHabitaciones()"
                            class="flex items-center justify-center gap-2 w-full py-4 border-2 border-dashed border-zinc-300 hover:border-zinc-900 text-zinc-500 hover:text-zinc-900 font-bold text-sm transition-all rounded-sm bg-zinc-50">
                        <span class="material-symbols-outlined">bed</span>
                        Seleccionar Habitación Disponible
                    </button>
                </div>
                <?php endif; ?>

                <div id="bloqueHabitacion" class="<?php echo $hayHabitacion ? '' : 'hidden'; ?> bg-zinc-50 border border-zinc-300 p-4 rounded-sm flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-zinc-800 text-3xl">meeting_room</span>
                        <div>
                            <p class="text-sm font-black text-zinc-900">
                                Habitación N° <span id="txtNumHab"><?php echo htmlspecialchars($habitacionSeleccionada); ?></span>
                            </p>
                            <p class="text-xs text-zinc-500">
                                Precio Base: S/. <span id="txtCostoBase"><?php echo htmlspecialchars($costoBaseHabitacion); ?></span>.00
                                &nbsp;|&nbsp; Tipo:
                                <span class="font-bold" id="txtTipoEstadia">
                                    <?php echo htmlspecialchars($tipoEstadia); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="abrirModalHabitaciones()"
                            class="text-xs font-bold uppercase tracking-wider text-zinc-500 hover:text-red-600 underline transition-colors">
                        Cambiar
                    </button>
                </div>

                <input type="hidden" name="num_habitacion" id="input_num_habitacion"
                       value="<?php echo htmlspecialchars($habitacionSeleccionada); ?>">
            </div>

            <hr class="border-zinc-100">

            <!-- 3. Tipo reserva hidden (Momentaneo/Diario según habitación) -->
            <input type="hidden" name="tipo_reserva" id="tipo_reserva"
                   value="<?php echo htmlspecialchars($tipoEstadia); ?>">

            <!-- 4. Slider días u horas -->
            <div id="contenedorTiempoEstadia">
                <?php if ($hayHabitacion && $tipoEstadia === 'Diario'): ?>
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Cantidad de Días a Hospedarse</label>
                    <div class="flex items-center gap-3">
                        <input type="range" id="slider_dias" name="cant_dias" min="1" max="7" value="1"
                               class="w-full h-2 bg-zinc-200 accent-zinc-900 rounded-lg cursor-pointer">
                        <span class="text-sm font-black border border-zinc-200 px-3 py-1 bg-white shrink-0 min-w-[60px] text-center" id="txt_dias">1 día</span>
                    </div>
                    <p class="text-[10px] text-zinc-400 mt-1">Mínimo 1 día — máximo 7 días.</p>
                <?php elseif ($hayHabitacion): ?>
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Duración del Alquiler (Horas)</label>
                    <div class="flex items-center gap-3">
                        <input type="range" id="slider_horas" name="cant_horas" min="3" max="12" value="3"
                               class="w-full h-2 bg-zinc-200 accent-zinc-900 rounded-lg cursor-pointer">
                        <span class="text-sm font-black border border-zinc-200 px-3 py-1 bg-white shrink-0 min-w-[60px] text-center" id="txt_horas">3 hrs</span>
                    </div>
                    <p class="text-[10px] text-zinc-400 mt-1">Mínimo 3 horas — máximo 12 horas.</p>
                <?php else: ?>
                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1.5">Cantidad de Días / Horas</label>
                    <div class="flex items-center gap-3">
                        <input type="range" id="slider_dias" name="cant_dias" min="1" max="7" value="1" disabled
                               class="w-full h-2 bg-zinc-200 accent-zinc-300 rounded-lg cursor-not-allowed opacity-40">
                        <span class="text-sm font-black border border-zinc-200 px-3 py-1 bg-zinc-50 text-zinc-300 shrink-0 min-w-[60px] text-center" id="txt_dias">—</span>
                    </div>
                    <p class="text-[10px] text-zinc-400 mt-1">Selecciona primero una habitación.</p>
                <?php endif; ?>
            </div>

            <!-- 5. Fecha y hora -->
            <div>
                <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Fecha y Hora de Llegada</label>
                <input type="datetime-local" name="fecha_llegada" id="fecha_llegada" required
                       <?php echo !$hayHabitacion ? 'disabled' : ''; ?>
                       class="w-full rounded-sm border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900 text-sm <?php echo !$hayHabitacion ? 'opacity-40 cursor-not-allowed bg-zinc-50' : ''; ?>">
                <p id="fechaError" class="text-xs text-red-600 font-medium mt-1 hidden"></p>
            </div>

            <!-- 6. Servicio especial -->
            <div>
                <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Solicitud especial (Opcional)</label>
                <textarea name="servicio_especial" rows="2"
                          placeholder="Ej: Habitación silenciosa, almohadas extras..."
                          <?php echo !$hayHabitacion ? 'disabled' : ''; ?>
                          class="w-full rounded-sm border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900 text-xs <?php echo !$hayHabitacion ? 'opacity-40 cursor-not-allowed bg-zinc-50' : ''; ?>"></textarea>
            </div>

            <!-- 7. Total -->
            <div class="bg-zinc-900 text-white p-5 rounded-sm flex justify-between items-center shadow-inner">
                <div>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block">Total Neto a Liquidar</span>
                    <span class="text-xs text-yellow-400" id="desgloseCalculo">
                        <?php echo $hayHabitacion ? ($tipoEstadia === 'Diario' ? '1 día' : '3 hrs') . ' x S/. ' . htmlspecialchars($costoBaseHabitacion) : '— Selecciona una habitación'; ?>
                    </span>
                </div>
                <div class="text-2xl font-black tracking-tight text-white">
                    S/. <span id="monto_pantalla"><?php echo $hayHabitacion ? htmlspecialchars($costoBaseHabitacion) : '0'; ?></span>.00
                </div>
                <input type="hidden" name="costo_final" id="costo_final"
                       value="<?php echo $hayHabitacion ? htmlspecialchars($costoBaseHabitacion) : '0'; ?>">
            </div>

            <!-- 8. Botón enviar -->
            <button type="submit" id="btnSubmit"
                    <?php echo !$hayHabitacion ? 'disabled' : ''; ?>
                    class="w-full bg-zinc-900 hover:bg-zinc-800 text-white py-3.5 font-bold text-xs uppercase tracking-widest rounded-sm active:scale-[0.99] transition-all shadow-md <?php echo !$hayHabitacion ? 'opacity-40 cursor-not-allowed' : ''; ?>">
                Confirmar y Finalizar Reserva
            </button>

        </form>
    </div>
</main>

<!-- ══════════ MODAL OVERLAY — SELECCIONAR HABITACIÓN ══════════ -->
<div id="modalHabitaciones"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-zinc-200 rounded-sm shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">

        <div class="bg-zinc-950 px-6 py-4 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-yellow-400">bed</span>
                <div>
                    <h2 class="text-base font-extrabold text-white tracking-tight">Selecciona tu Habitación</h2>
                    <p class="text-[10px] text-zinc-400 uppercase tracking-widest">Unidades disponibles en este momento</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalHabitaciones()"
                    class="w-8 h-8 bg-zinc-800 hover:bg-red-600 rounded-sm flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-white text-sm">close</span>
            </button>
        </div>

        <div class="bg-zinc-50 border-b border-zinc-200 px-6 py-3 flex gap-2 items-center flex-wrap shrink-0">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">filter_alt</span> Filtrar:
            </span>
            <button onclick="filtrarModal('todas')" id="mfiltro-todas"
                    class="mfiltro px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider rounded-sm bg-zinc-900 text-white transition-all">Todas</button>
            <button onclick="filtrarModal('individual')" id="mfiltro-individual"
                    class="mfiltro px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-sm bg-zinc-200 text-zinc-600 hover:bg-zinc-300 transition-all">Individual</button>
            <button onclick="filtrarModal('doble')" id="mfiltro-doble"
                    class="mfiltro px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-sm bg-zinc-200 text-zinc-600 hover:bg-zinc-300 transition-all">Doble</button>
            <button onclick="filtrarModal('matrimonial')" id="mfiltro-matrimonial"
                    class="mfiltro px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-sm bg-zinc-200 text-zinc-600 hover:bg-zinc-300 transition-all">Matrimonial</button>
        </div>

        <div class="overflow-y-auto flex-1 p-6 space-y-8">
            <?php if (count($gruposModal) > 0): ?>
                <?php foreach ($gruposModal as $nombreTipo => $listaHabitaciones):
                    $slugTipo = strtolower(trim($nombreTipo));
                ?>
                    <section data-tipo="<?php echo $slugTipo; ?>" class="mseccion-hab">
                        <div class="flex items-center gap-2 mb-4 border-b border-zinc-200 pb-2">
                            <span class="material-symbols-outlined text-zinc-700 text-lg">bed</span>
                            <h3 class="text-xs font-black tracking-wider text-zinc-900 uppercase"><?php echo htmlspecialchars($nombreTipo); ?></h3>
                            <span class="bg-zinc-200 text-zinc-700 text-[10px] font-bold px-2 py-0.5 rounded-full ml-1">
                                <?php echo count($listaHabitaciones); ?> libres
                            </span>
                        </div>
                        <?php
                        $imagenesHabitacion = [
                            300 => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600',
                            301 => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=600',
                            302 => 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=600',
                            303 => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600',
                            304 => 'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=600',
                            305 => 'https://images.unsplash.com/photo-1464146072230-91cabc968266?w=600',
                            400 => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=600',
                            401 => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=600',
                            402 => 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?w=600',
                            403 => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600',
                            404 => 'https://images.unsplash.com/photo-1631049552240-59c37f38802b?w=600',
                            405 => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=600',
                            500 => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=600',
                            501 => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=600',
                            502 => 'https://images.unsplash.com/photo-1560347876-aeef00ee58a1?w=600',
                            503 => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?w=600',
                            504 => 'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?w=600',
                            505 => 'https://images.unsplash.com/photo-1631049552240-59c37f38802b?w=600',
                            600 => 'https://images.unsplash.com/photo-1549294413-26f195200c16?w=600',
                            601 => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600',
                            602 => 'https://images.unsplash.com/photo-1587985064135-0366536eab42?w=600',
                            603 => 'https://images.unsplash.com/photo-1561501878-aabd62634533?w=600',
                            604 => 'https://images.unsplash.com/photo-1576354302919-96748cb8299e?w=600',
                            605 => 'https://images.unsplash.com/photo-1568495248636-6432b97bd949?w=600',
                        ];
                        $imgDefault = 'imagenes/default.jpg';
                        ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <?php foreach ($listaHabitaciones as $hab):
                                $num  = $hab['NumeroHabitacion'];
                                $img  = $imagenesHabitacion[$num] ?? $imgDefault;
                                $tipoHabJs = (intval($num) >= 301 && intval($num) <= 305) ? 'Momentaneo' : 'Diario';
                                $datosJs = htmlspecialchars(json_encode([
                                    'num'         => $num,
                                    'precio'      => $hab['Precio'],
                                    'tipo'        => $tipoHabJs,
                                    'nombreTipo'  => $nombreTipo,
                                    'piso'        => $hab['Piso'],
                                    'descripcion' => $hab['Descripcion'],
                                    'img'         => $img,
                                ]), ENT_QUOTES);
                            ?>
                                <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden group relative hover:border-zinc-500 hover:shadow-lg transition-all duration-200 cursor-pointer"
                                     onclick="seleccionarHabitacion(<?php echo $datosJs; ?>)">
                                    <div class="absolute top-2 left-2 z-10 bg-zinc-900 text-white font-black text-[10px] px-2 py-0.5 rounded-sm">
                                        N° <?php echo $num; ?>
                                    </div>
                                    <!-- Badge Momentaneo/Diario -->
                                    <div class="absolute top-2 right-2 z-10 <?php echo $tipoHabJs === 'Momentaneo' ? 'bg-amber-500' : 'bg-blue-600'; ?> text-white font-black text-[9px] px-1.5 py-0.5 rounded-sm uppercase">
                                        <?php echo $tipoHabJs; ?>
                                    </div>
                                    <div class="relative overflow-hidden bg-zinc-100" style="height:130px;">
                                        <img src="<?php echo $img; ?>"
                                             alt="Hab <?php echo $num; ?>"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                             onerror="this.onerror=null;this.src='imagenes/default.jpg'">
                                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <span class="bg-white text-zinc-900 text-[9px] font-black px-2 py-0.5 rounded-sm uppercase tracking-wider shadow">
                                                Seleccionar
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="flex justify-between items-baseline mb-1">
                                            <span class="text-[9px] font-bold text-zinc-400 uppercase">Piso <?php echo $hab['Piso']; ?></span>
                                            <span class="text-xs font-black text-zinc-950">S/ <?php echo number_format($hab['Precio'], 2); ?></span>
                                        </div>
                                        <p class="text-[10px] text-zinc-500 leading-snug"
                                           style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:1.8rem;">
                                            <?php echo htmlspecialchars($hab['Descripcion']); ?>
                                        </p>
                                        <div class="mt-2 w-full bg-zinc-900 text-white py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-sm text-center group-hover:bg-yellow-400 group-hover:text-zinc-900 transition-colors">
                                            Elegir habitación
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-16">
                    <span class="material-symbols-outlined text-zinc-300 text-6xl">bed</span>
                    <p class="text-sm font-semibold text-zinc-500 mt-3">No hay habitaciones disponibles en este momento.</p>
                </div>
            <?php endif; ?>
            <div id="modalAlertaVacio" class="hidden text-center py-12">
                <span class="material-symbols-outlined text-zinc-300 text-5xl">search_off</span>
                <p class="text-sm font-semibold text-zinc-500 mt-2">Sin resultados para ese filtro.</p>
            </div>
        </div>

        <div class="bg-zinc-50 border-t border-zinc-200 px-6 py-3 flex justify-between items-center shrink-0">
            <span class="text-[10px] text-zinc-400">Haz clic en cualquier tarjeta para seleccionarla</span>
            <button type="button" onclick="cerrarModalHabitaciones()"
                    class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-zinc-500 hover:text-zinc-900 transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>

<!-- ══════════ MODAL RESUMEN RESERVA EXITOSA ══════════ -->
<?php if ($resumen): ?>
<div id="modalResumen" class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm flex justify-center items-center z-50 p-4">
    <div class="bg-white border border-zinc-200 rounded-sm shadow-2xl max-w-md w-full overflow-hidden">
        <div class="bg-zinc-900 p-5 text-white flex items-center gap-3">
            <span class="material-symbols-outlined text-yellow-400">task_alt</span>
            <h3 class="font-extrabold text-base tracking-tight">¡Tu reserva fue registrada!</h3>
        </div>
        <div class="p-6 flex flex-col gap-3 text-xs text-zinc-700">
            <p class="text-[11px] text-zinc-400 pb-2 border-b border-zinc-100">Comprobante informativo de reserva activa:</p>
            <div><span class="font-bold text-zinc-900 block uppercase text-[10px] tracking-wider text-zinc-400">Titular:</span>
                <?php echo htmlspecialchars($resumen['nombre']); ?> (DNI: <?php echo htmlspecialchars($resumen['dni']); ?>)
            </div>
            <div class="grid grid-cols-2 gap-4 bg-zinc-50 p-3 rounded-sm border border-zinc-200 my-1">
                <div><span class="font-bold block text-[10px] tracking-wider text-zinc-400 uppercase">Habitación:</span> N° <?php echo htmlspecialchars($resumen['habitacion']); ?></div>
                <div><span class="font-bold block text-[10px] tracking-wider text-zinc-400 uppercase">Régimen:</span> <?php echo htmlspecialchars($resumen['tipo']); ?></div>
            </div>
            <div><span class="font-bold block uppercase text-[10px] tracking-wider text-zinc-400">Llegada:</span> <?php echo htmlspecialchars($resumen['fecha']); ?></div>
            <div><span class="font-bold block uppercase text-[10px] tracking-wider text-zinc-400">Permanencia:</span> <?php echo htmlspecialchars($resumen['estadia']); ?></div>
            <div class="text-right text-xl font-black text-zinc-900 border-t border-zinc-100 pt-3 mt-2">
                Total: S/. <?php echo htmlspecialchars($resumen['costo']); ?>.00
            </div>
        </div>
        <div class="bg-zinc-50 p-4 flex justify-end border-t border-zinc-200">
            <a href="misReservas.php" class="bg-zinc-900 text-white px-5 py-2.5 rounded-sm font-bold text-xs uppercase tracking-wider hover:bg-zinc-800 transition-colors">
                Ir a Mis Reservas
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="py-4 text-center text-xs text-zinc-400 border-t border-zinc-100 bg-white w-full">
    © 2026 Hospedaje Ayacucho. Todos los derechos reservados.
</footer>

<script>
    let costoBase = parseFloat(<?php echo json_encode((float)$costoBaseHabitacion); ?>) || 0;
    let tipoEst   = <?php echo json_encode($tipoEstadia); ?>;

    const txtDesglose        = document.getElementById('desgloseCalculo');
    const txtMontoPantalla   = document.getElementById('monto_pantalla');
    const inputCostoFinal    = document.getElementById('costo_final');
    const inputTipoReserva   = document.getElementById('tipo_reserva');
    const inputNumHabitacion = document.getElementById('input_num_habitacion');
    const btnSubmit          = document.getElementById('btnSubmit');
    const fechaInput         = document.getElementById('fecha_llegada');
    const textareaServicio   = document.querySelector('textarea[name="servicio_especial"]');

    // Bloquear fechas pasadas: establece el mínimo al datetime actual
    function setFechaMinima() {
        const ahora = new Date();
        // Sumar 1 minuto para asegurar que sea mayor a la fecha actual
        ahora.setMinutes(ahora.getMinutes() + 1);
        const yyyy = ahora.getFullYear();
        const mm   = String(ahora.getMonth() + 1).padStart(2, '0');
        const dd   = String(ahora.getDate()).padStart(2, '0');
        const hh   = String(ahora.getHours()).padStart(2, '0');
        const min  = String(ahora.getMinutes()).padStart(2, '0');
        fechaInput.min = `${yyyy}-${mm}-${dd}T${hh}:${min}`;
    }
    setFechaMinima();

    // Función para determinar tipo de habitación (Momentaneo/Diario)
    function calcularTipoHabitacion(numHab) {
        const num = parseInt(numHab);
        return (num >= 301 && num <= 305) ? 'Momentaneo' : 'Diario';
    }

    // ── Modal habitaciones ──────────────────────────────────────────────────────
    function abrirModalHabitaciones() {
        document.getElementById('modalHabitaciones').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    function cerrarModalHabitaciones() {
        document.getElementById('modalHabitaciones').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
    document.getElementById('modalHabitaciones').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalHabitaciones();
    });

    // ── Seleccionar habitación ──────────────────────────────────────────────────
    function seleccionarHabitacion(datos) {
        costoBase = parseFloat(datos.precio);

        // Calcular tipo según número de habitación (301-305 = Momentaneo, resto = Diario)
        tipoEst = calcularTipoHabitacion(datos.num);

        document.getElementById('txtNumHab').innerText    = datos.num;
        document.getElementById('txtCostoBase').innerText = parseFloat(datos.precio).toFixed(0);
        document.getElementById('txtTipoEstadia').innerText = tipoEst;

        inputNumHabitacion.value = datos.num;
        inputTipoReserva.value   = tipoEst;

        const botonDashed = document.getElementById('bloqueBotonSeleccionar');
        if (botonDashed) botonDashed.classList.add('hidden');
        document.getElementById('bloqueHabitacion').classList.remove('hidden');

        renderizarSlider(tipoEst);
        habilitarCampos();
        calcularPrecio();
        cerrarModalHabitaciones();
    }

    function renderizarSlider(tipo) {
        const c = document.getElementById('contenedorTiempoEstadia');
        if (tipo === 'Diario') {
            c.innerHTML = `
                <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Cantidad de Días a Hospedarse</label>
                <div class="flex items-center gap-3">
                    <input type="range" id="slider_dias" name="cant_dias" min="1" max="7" value="1"
                           class="w-full h-2 bg-zinc-200 accent-zinc-900 rounded-lg cursor-pointer">
                    <span class="text-sm font-black border border-zinc-200 px-3 py-1 bg-white shrink-0 min-w-[60px] text-center" id="txt_dias">1 día</span>
                </div>
                <p class="text-[10px] text-zinc-400 mt-1">Mínimo 1 día — máximo 7 días.</p>`;
            document.getElementById('slider_dias').addEventListener('input', function() {
                document.getElementById('txt_dias').innerText = `${this.value} día${this.value > 1 ? 's' : ''}`;
                calcularPrecio();
            });
        } else {
            c.innerHTML = `
                <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Duración del Alquiler (Horas)</label>
                <div class="flex items-center gap-3">
                    <input type="range" id="slider_horas" name="cant_horas" min="3" max="12" value="3"
                           class="w-full h-2 bg-zinc-200 accent-zinc-900 rounded-lg cursor-pointer">
                    <span class="text-sm font-black border border-zinc-200 px-3 py-1 bg-white shrink-0 min-w-[60px] text-center" id="txt_horas">3 hrs</span>
                </div>
                <p class="text-[10px] text-zinc-400 mt-1">Mínimo 3 horas — máximo 12 horas.</p>`;
            document.getElementById('slider_horas').addEventListener('input', function() {
                document.getElementById('txt_horas').innerText = `${this.value} hrs`;
                calcularPrecio();
            });
        }
    }

    function habilitarCampos() {
        fechaInput.disabled = false;
        fechaInput.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-zinc-50');
        setFechaMinima(); // Re-aplicar mínimo al habilitar
        if (textareaServicio) {
            textareaServicio.disabled = false;
            textareaServicio.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-zinc-50');
        }
        btnSubmit.disabled = false;
        btnSubmit.classList.remove('opacity-40', 'cursor-not-allowed');
    }

    // ── Filtros del modal ──────────────────────────────────────────────────────
    function filtrarModal(categoria) {
        const secciones = document.querySelectorAll('.mseccion-hab');
        const alerta    = document.getElementById('modalAlertaVacio');
        let visibles = 0;

        document.querySelectorAll('.mfiltro').forEach(btn => {
            btn.classList.remove('bg-zinc-900', 'text-white');
            btn.classList.add('bg-zinc-200', 'text-zinc-600');
        });
        const boton = document.getElementById('mfiltro-' + categoria);
        if (boton) {
            boton.classList.remove('bg-zinc-200', 'text-zinc-600');
            boton.classList.add('bg-zinc-900', 'text-white');
        }

        secciones.forEach(sec => {
            const tipo = sec.getAttribute('data-tipo');
            if (categoria === 'todas' || tipo.includes(categoria)) {
                sec.style.display = 'block'; visibles++;
            } else {
                sec.style.display = 'none';
            }
        });
        alerta.classList.toggle('hidden', visibles > 0);
    }

    // ── Calcular precio ────────────────────────────────────────────────────────
    function calcularPrecio() {
        if (!costoBase || costoBase <= 0) return;
        let total = 0;
        if (tipoEst === 'Diario') {
            const sd   = document.getElementById('slider_dias');
            const dias = parseInt(sd ? sd.value : 1);
            total = costoBase * dias;
            txtDesglose.innerText = `${dias} día(s) x S/. ${costoBase}.00`;
        } else {
            const sh    = document.getElementById('slider_horas');
            const horas = parseInt(sh ? sh.value : 3);
            let pph = costoBase * 0.15;
            total = Math.round((pph * horas) / 5) * 5;
            if (total < 20) total = 20;
            txtDesglose.innerText = `${horas} hora(s) — tarifa fraccionada`;
        }
        txtMontoPantalla.innerText = total;
        inputCostoFinal.value = total;
    }

    // ── Listeners sliders si vienen por GET ───────────────────────────────────
    (function() {
        const sd = document.getElementById('slider_dias');
        if (sd && !sd.disabled) sd.addEventListener('input', function() {
            document.getElementById('txt_dias').innerText = `${this.value} día${this.value > 1 ? 's' : ''}`;
            calcularPrecio();
        });
        const sh = document.getElementById('slider_horas');
        if (sh && !sh.disabled) sh.addEventListener('input', function() {
            document.getElementById('txt_horas').innerText = `${this.value} hrs`;
            calcularPrecio();
        });
    })();

    // ── DNI: solo números, exactamente 8 ──────────────────────────────────────
    document.getElementById('dni').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        document.getElementById('dniError').classList.toggle('hidden', this.value.length === 8 || this.value.length === 0);
    });

    // ── Nombre: mínimo 3, máximo 5 palabras ───────────────────────────────────
    document.getElementById('nombre_cliente').addEventListener('input', function() {
        let val = this.value.replace(/\b\w/g, c => c.toUpperCase());
        const palabras = val.trimStart().split(/\s+/).filter(Boolean);
        if (palabras.length > 5) val = palabras.slice(0, 5).join(' ');
        this.value = val;
        const count = val.trim() === '' ? 0 : val.trim().split(/\s+/).filter(Boolean).length;
        document.getElementById('nombreError').classList.toggle('hidden', count === 0 || (count >= 3 && count <= 5));
    });

    // ── Validación final al enviar ─────────────────────────────────────────────
    document.getElementById('formReserva').addEventListener('submit', async function(e) {
        e.preventDefault(); // Prevenir envío inmediato
        
        const dni    = document.getElementById('dni').value;
        const nombre = document.getElementById('nombre_cliente').value.trim();
        const fecha  = document.getElementById('fecha_llegada').value;
        const numHab = inputNumHabitacion.value;
        const fechaError = document.getElementById('fechaError');

        if (!numHab) {
            abrirModalHabitaciones();
            return;
        }

        if (dni.length !== 8 || !/^\d{8}$/.test(dni)) {
            document.getElementById('dniError').classList.remove('hidden');
            document.getElementById('dni').focus();
            return;
        }

        const palabras = nombre.split(/\s+/).filter(Boolean);
        if (palabras.length < 3 || palabras.length > 5) {
            document.getElementById('nombreError').classList.remove('hidden');
            document.getElementById('nombre_cliente').focus();
            return;
        }

        if (!fecha) {
            fechaError.innerText = 'Debes ingresar una fecha y hora de llegada.';
            fechaError.classList.remove('hidden');
            return;
        }

        const fechaIngresada = new Date(fecha);
        const ahora          = new Date();
        if (fechaIngresada <= ahora) {
            fechaError.innerText = 'La fecha y hora de llegada debe ser mayor a la fecha y hora actual.';
            fechaError.classList.remove('hidden');
            return;
        }

        fechaError.classList.add('hidden');
        
        // VALIDAR DISPONIBILIDAD antes de enviar
        const tipoReserva = document.getElementById('tipo_reserva').value;
        let diasHospedaje = 1;
        
        if (tipoReserva === 'Diario') {
            const sliderDias = document.getElementById('slider_dias');
            diasHospedaje = sliderDias ? parseInt(sliderDias.value) : 1;
        } else {
            const sliderHoras = document.getElementById('slider_horas');
            diasHospedaje = sliderHoras ? parseInt(sliderHoras.value) : 3;
        }
        
        try {
            const response = await fetch(`verificar_disponibilidad.php?numeroHabitacion=${numHab}&fechaLlegada=${encodeURIComponent(fecha)}&tipoHabitacion=${tipoReserva}&diasHospedaje=${diasHospedaje}`);
            const data = await response.json();
            
            if (!data.disponible) {
                fechaError.innerText = data.mensaje;
                fechaError.classList.remove('hidden');
                return;
            }
            
            // Si está disponible, enviar el formulario
            this.submit();
            
        } catch (error) {
            console.error('Error al verificar disponibilidad:', error);
            // En caso de error, permitir continuar (la validación también está en el backend)
            this.submit();
        }
    });

    if (costoBase > 0) calcularPrecio();
</script>
</body>
</html>

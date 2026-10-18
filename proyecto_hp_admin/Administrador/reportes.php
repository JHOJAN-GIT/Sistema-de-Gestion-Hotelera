<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

include_once("../Conexion/conexion.php");
$conn = Cconexion::ConexionBD();

date_default_timezone_set('America/Lima');

// Fechas solo para mostrar en el filtro (no afectan los datos)
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

// ═══════════════════════════════════════════════════════════════════════════
// CONSULTAS PARA MÉTRICAS Y REPORTES (SIN FILTRO DE FECHAS)
// ═══════════════════════════════════════════════════════════════════════════

// 1. INGRESOS TOTALES (TODOS LOS REGISTROS)
$ingresosTotales = 0;
$cantidadRegistros = 0;
if ($conn) {
    $sql = "SELECT SUM(Monto) as Total, COUNT(*) as Cantidad FROM Registros";
    $stmt = $conn->query($sql);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $ingresosTotales = $resultado['Total'] ?? 0;
    $cantidadRegistros = $resultado['Cantidad'] ?? 0;
}

// 2. RESERVAS ACTIVAS (TODAS)
$reservasActivas = 0;
$montoReservas = 0;
if ($conn) {
    $sql = "SELECT COUNT(*) as Total, SUM(Costo) as Monto FROM Reservas";
    $stmt = $conn->query($sql);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $reservasActivas = $resultado['Total'] ?? 0;
    $montoReservas = $resultado['Monto'] ?? 0;
}

// 3. RESERVAS CANCELADAS (TODAS)
$reservasCanceladas = 0;
if ($conn) {
    $sql = "SELECT COUNT(*) as Total FROM ReservasCanceladas";
    $stmt = $conn->query($sql);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $reservasCanceladas = $resultado['Total'] ?? 0;
}

// 4. OCUPACIÓN ACTUAL
$habitacionesOcupadas = 0;
$totalHabitaciones = 20;
if ($conn) {
    $sql = "SELECT COUNT(*) as Ocupadas FROM Habitaciones WHERE Estado = 'Ocupado'";
    $stmt = $conn->query($sql);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $habitacionesOcupadas = $resultado['Ocupadas'] ?? 0;
}
$porcentajeOcupacion = $totalHabitaciones > 0 ? ($habitacionesOcupadas / $totalHabitaciones) * 100 : 0;

// 5. INGRESOS POR DÍA (ÚLTIMOS 7 REGISTROS AGRUPADOS)
$ingresosPorDia = [];
if ($conn) {
    $sql = "SELECT TOP 7 CONVERT(VARCHAR(10), FechaHoraIngreso, 120) as Fecha, 
            SUM(Monto) as Total, COUNT(*) as Cantidad
            FROM Registros 
            GROUP BY CONVERT(VARCHAR(10), FechaHoraIngreso, 120)
            ORDER BY Fecha DESC";
    $stmt = $conn->query($sql);
    $ingresosPorDia = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 6. TOP 5 DÍAS CON MAYORES VENTAS (HISTÓRICO)
$topDiasVentas = [];
if ($conn) {
    $sql = "SELECT TOP 5 CONVERT(VARCHAR(10), FechaHoraIngreso, 120) as Fecha, 
            SUM(Monto) as TotalVentas, 
            COUNT(*) as CantidadRegistros
            FROM Registros 
            GROUP BY CONVERT(VARCHAR(10), FechaHoraIngreso, 120)
            ORDER BY TotalVentas DESC";
    $stmt = $conn->query($sql);
    $topDiasVentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 7. DISTRIBUCIÓN POR TIPO DE ALOJAMIENTO (TODOS)
$distribucionTipo = [];
if ($conn) {
    $sql = "SELECT TipoAlojamiento, COUNT(*) as Cantidad, SUM(Monto) as Total
            FROM Registros 
            GROUP BY TipoAlojamiento";
    $stmt = $conn->query($sql);
    $distribucionTipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 8. HABITACIONES MÁS RENTABLES
$habitacionesRentables = [];
if ($conn) {
    $sql = "SELECT TOP 5 NumeroHabitacion, COUNT(*) as Veces, SUM(Monto) as TotalGenerado
            FROM Registros 
            GROUP BY NumeroHabitacion
            ORDER BY TotalGenerado DESC";
    $stmt = $conn->query($sql);
    $habitacionesRentables = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 9. PROMEDIO DE ESTADÍA (en horas)
$promedioEstadia = 0;
if ($conn) {
    $sql = "SELECT AVG(DATEDIFF(hour, FechaHoraIngreso, ISNULL(FechaHoraSalida, GETDATE()))) as Promedio
            FROM Registros 
            WHERE FechaHoraSalida IS NOT NULL";
    $stmt = $conn->query($sql);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $promedioEstadia = round($resultado['Promedio'] ?? 0, 1);
}

// 10. TICKET PROMEDIO
$ticketPromedio = $cantidadRegistros > 0 ? $ingresosTotales / $cantidadRegistros : 0;
?>

<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Dashboard de Reportes - Hospedaje Ayacucho</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#705d00",
                        "error": "#ba1a1a",
                        "on-background": "#1a1c1c",
                        "surface": "#f9f9f9",
                        "primary-container": "#ffd600",
                        "on-primary-container": "#705d00"
                    },
                    "fontFamily": {
                        "inter": ["Inter", "sans-serif"]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9f9;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
        .metric-card {
            transition: all 0.3s ease;
        }
        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="text-on-background bg-background antialiased font-inter">

<header class="bg-white border-b border-zinc-100 shadow-sm flex justify-between items-center h-16 px-8 w-full sticky top-0 z-50 tracking-tight no-print">
    <div class="flex items-center gap-4">
        <button onclick="window.history.back()" class="material-symbols-outlined text-zinc-700 hover:text-black p-2 rounded-sm hover:bg-zinc-100 transition-colors">
            arrow_back
        </button>
        <div class="flex items-center gap-2">
            <img src="../Imagenes/logo.png" alt="Logo Hospedaje Ayacucho" class="h-10 w-auto object-contain">
            <span class="text-xl font-black uppercase tracking-widest">HOSPEDAJE AYACUCHO</span>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <button onclick="window.print()" class="flex items-center gap-2 bg-primary-container text-on-primary-container px-4 py-2 font-bold text-sm rounded-sm hover:brightness-95 transition-all shadow-sm">
            <span class="material-symbols-outlined text-base">print</span>
            Imprimir Reporte
        </button>
        <a href="inicioAdmin.php" class="flex items-center gap-2 border border-zinc-300 text-zinc-700 px-4 py-2 font-semibold text-sm rounded-sm bg-white hover:bg-zinc-50 transition-all">
            <span class="material-symbols-outlined text-base">home</span>
            Volver al Inicio
        </a>
    </div>
</header>

<main class="p-6 sm:p-10 max-w-[1600px] mx-auto">
    
    <!-- Encabezado del Reporte -->
    <section class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-4xl font-extrabold text-black tracking-tight flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary-container text-5xl" style="font-variation-settings: 'FILL' 1;">analytics</span>
                    Dashboard de Reportes
                </h1>
                <p class="text-sm text-zinc-500 mt-2">Análisis detallado de operaciones y métricas de rendimiento</p>
            </div>
            <div class="bg-white border border-zinc-200 px-4 py-2 rounded-sm shadow-sm">
                <p class="text-xs text-zinc-400 font-bold uppercase tracking-wider">Generado el</p>
                <p class="text-sm font-black text-black"><?php echo date('d/m/Y H:i'); ?></p>
            </div>
        </div>

        <!-- Filtro de Fechas -->
        <form method="GET" class="bg-white border border-zinc-200 p-6 rounded-sm shadow-sm no-print">
            <div class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicio); ?>" 
                           class="w-full border-zinc-300 rounded-sm text-sm focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Fecha Fin</label>
                    <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fechaFin); ?>" 
                           class="w-full border-zinc-300 rounded-sm text-sm focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <button type="submit" class="bg-black text-white px-6 py-2.5 font-bold text-sm rounded-sm hover:bg-zinc-800 transition-all shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">filter_alt</span>
                    Filtrar
                </button>
                <a href="reportes.php" class="border border-zinc-300 text-zinc-700 px-6 py-2.5 font-semibold text-sm rounded-sm bg-white hover:bg-zinc-50 transition-all">
                    Limpiar
                </a>
            </div>
            <p class="text-xs text-zinc-400 mt-3">
                <span class="material-symbols-outlined text-xs align-middle">info</span>
                Mostrando datos desde <strong><?php echo date('d/m/Y', strtotime($fechaInicio)); ?></strong> 
                hasta <strong><?php echo date('d/m/Y', strtotime($fechaFin)); ?></strong>
            </p>
        </form>
    </section>


    <!-- Métricas Principales -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Ingresos Totales -->
        <div class="metric-card bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 rounded-sm shadow-lg text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined text-4xl opacity-80">payments</span>
                    <span class="bg-white/20 px-2 py-1 rounded-sm text-xs font-bold">INGRESOS</span>
                </div>
                <p class="text-3xl font-black mb-1">S/ <?php echo number_format($ingresosTotales, 2); ?></p>
                <p class="text-xs opacity-90"><?php echo $cantidadRegistros; ?> registros completados</p>
            </div>
        </div>

        <!-- Reservas Activas -->
        <div class="metric-card bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-sm shadow-lg text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined text-4xl opacity-80">event_available</span>
                    <span class="bg-white/20 px-2 py-1 rounded-sm text-xs font-bold">RESERVAS</span>
                </div>
                <p class="text-3xl font-black mb-1"><?php echo $reservasActivas; ?></p>
                <p class="text-xs opacity-90">S/ <?php echo number_format($montoReservas, 2); ?> proyectado</p>
            </div>
        </div>

        <!-- Ocupación -->
        <div class="metric-card bg-gradient-to-br from-amber-500 to-amber-600 p-6 rounded-sm shadow-lg text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined text-4xl opacity-80">hotel</span>
                    <span class="bg-white/20 px-2 py-1 rounded-sm text-xs font-bold">OCUPACIÓN</span>
                </div>
                <p class="text-3xl font-black mb-1"><?php echo round($porcentajeOcupacion, 1); ?>%</p>
                <p class="text-xs opacity-90"><?php echo $habitacionesOcupadas; ?> de <?php echo $totalHabitaciones; ?> habitaciones</p>
            </div>
        </div>

        <!-- Ticket Promedio -->
        <div class="metric-card bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-sm shadow-lg text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined text-4xl opacity-80">receipt_long</span>
                    <span class="bg-white/20 px-2 py-1 rounded-sm text-xs font-bold">TICKET</span>
                </div>
                <p class="text-3xl font-black mb-1">S/ <?php echo number_format($ticketPromedio, 2); ?></p>
                <p class="text-xs opacity-90">Promedio por registro</p>
            </div>
        </div>
    </section>

    <!-- Gráficos y Tablas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Ingresos por Día (Últimos 7 días) -->
        <div class="bg-white border border-zinc-200 p-6 rounded-sm shadow-sm">
            <h3 class="text-lg font-bold text-black mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">show_chart</span>
                Ingresos Últimos 7 Días
            </h3>
            <div style="position: relative; height: 300px;">
                <canvas id="chartIngresosDiarios"></canvas>
            </div>
        </div>

        <!-- Distribución por Tipo de Alojamiento -->
        <div class="bg-white border border-zinc-200 p-6 rounded-sm shadow-sm">
            <h3 class="text-lg font-bold text-black mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">pie_chart</span>
                Distribución por Tipo
            </h3>
            <div style="position: relative; height: 300px;">
                <canvas id="chartTipoAlojamiento"></canvas>
            </div>
        </div>
    </div>


    <!-- Top 5 Días con Mayores Ventas -->
    <section class="bg-gradient-to-r from-primary to-amber-600 p-8 rounded-sm shadow-lg text-white mb-8">
        <h3 class="text-2xl font-black mb-6 flex items-center gap-3">
            <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">emoji_events</span>
            Top 5 Días con Mayores Ventas (Histórico)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <?php 
            $medallas = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
            foreach ($topDiasVentas as $index => $dia): 
            ?>
                <div class="bg-white/10 backdrop-blur-sm p-4 rounded-sm border border-white/20">
                    <div class="text-3xl mb-2"><?php echo $medallas[$index]; ?></div>
                    <p class="text-xs opacity-80 mb-1"><?php echo date('d/m/Y', strtotime($dia['Fecha'])); ?></p>
                    <p class="text-2xl font-black">S/ <?php echo number_format($dia['TotalVentas'], 2); ?></p>
                    <p class="text-xs opacity-80 mt-1"><?php echo $dia['CantidadRegistros']; ?> registros</p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Tablas Detalladas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Habitaciones Más Rentables -->
        <div class="bg-white border border-zinc-200 rounded-sm shadow-sm overflow-hidden">
            <div class="bg-black p-4 text-white">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-yellow-400">star</span>
                    Habitaciones Más Rentables
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-zinc-50 border-b border-zinc-200">
                        <tr>
                            <th class="p-3 text-xs font-bold text-zinc-700 uppercase">Habitación</th>
                            <th class="p-3 text-xs font-bold text-zinc-700 uppercase text-center">Usos</th>
                            <th class="p-3 text-xs font-bold text-zinc-700 uppercase text-right">Total Generado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <?php foreach ($habitacionesRentables as $hab): ?>
                            <tr class="hover:bg-zinc-50">
                                <td class="p-3 font-bold text-black">N° <?php echo htmlspecialchars($hab['NumeroHabitacion']); ?></td>
                                <td class="p-3 text-center text-zinc-600"><?php echo $hab['Veces']; ?> veces</td>
                                <td class="p-3 text-right font-bold text-emerald-600">S/ <?php echo number_format($hab['TotalGenerado'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Estadísticas Adicionales -->
        <div class="bg-white border border-zinc-200 rounded-sm shadow-sm overflow-hidden">
            <div class="bg-black p-4 text-white">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-yellow-400">insights</span>
                    Estadísticas Adicionales
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-zinc-100">
                    <span class="text-sm text-zinc-600">Promedio de Estadía</span>
                    <span class="text-lg font-black text-black"><?php echo $promedioEstadia; ?> horas</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-zinc-100">
                    <span class="text-sm text-zinc-600">Reservas Canceladas</span>
                    <span class="text-lg font-black text-red-600"><?php echo $reservasCanceladas; ?></span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-zinc-100">
                    <span class="text-sm text-zinc-600">Tasa de Conversión</span>
                    <span class="text-lg font-black text-blue-600">
                        <?php 
                        $totalReservas = $reservasActivas + $reservasCanceladas;
                        $tasaConversion = $totalReservas > 0 ? ($reservasActivas / $totalReservas) * 100 : 0;
                        echo round($tasaConversion, 1); 
                        ?>%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Ingresos Proyectados</span>
                    <span class="text-lg font-black text-purple-600">S/ <?php echo number_format($montoReservas, 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por Tipo de Alojamiento -->
    <section class="bg-white border border-zinc-200 rounded-sm shadow-sm overflow-hidden mb-8">
        <div class="bg-zinc-900 p-4 text-white">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-yellow-400">category</span>
                Resumen por Tipo de Alojamiento (Período Seleccionado)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-zinc-50 border-b border-zinc-200">
                    <tr>
                        <th class="p-4 text-xs font-bold text-zinc-700 uppercase">Tipo</th>
                        <th class="p-4 text-xs font-bold text-zinc-700 uppercase text-center">Cantidad</th>
                        <th class="p-4 text-xs font-bold text-zinc-700 uppercase text-right">Total Ingresos</th>
                        <th class="p-4 text-xs font-bold text-zinc-700 uppercase text-right">Promedio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    <?php if (empty($distribucionTipo)): ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-zinc-400">No hay datos para el período seleccionado</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($distribucionTipo as $tipo): ?>
                            <tr class="hover:bg-zinc-50">
                                <td class="p-4 font-bold text-black"><?php echo htmlspecialchars($tipo['TipoAlojamiento']); ?></td>
                                <td class="p-4 text-center text-zinc-600"><?php echo $tipo['Cantidad']; ?></td>
                                <td class="p-4 text-right font-bold text-emerald-600">S/ <?php echo number_format($tipo['Total'], 2); ?></td>
                                <td class="p-4 text-right text-zinc-600">S/ <?php echo number_format($tipo['Total'] / $tipo['Cantidad'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Footer del Reporte -->
    <footer class="text-center py-6 border-t border-zinc-200">
        <p class="text-xs text-zinc-400">
            © <?php echo date('Y'); ?> Hospedaje Ayacucho - Sistema de Gestión Hotelera
        </p>
        <p class="text-xs text-zinc-400 mt-1">
            Reporte generado el <?php echo date('d/m/Y'); ?> a las <?php echo date('H:i'); ?> hrs
        </p>
    </footer>

</main>


<script>
// ═══════════════════════════════════════════════════════════════════════════
// GRÁFICO 1: INGRESOS POR DÍA (ÚLTIMOS 7 DÍAS)
// ═══════════════════════════════════════════════════════════════════════════
const ctxIngresos = document.getElementById('chartIngresosDiarios').getContext('2d');
const chartIngresos = new Chart(ctxIngresos, {
    type: 'line',
    data: {
        labels: [
            <?php 
            foreach (array_reverse($ingresosPorDia) as $dia) {
                echo "'" . date('d/m', strtotime($dia['Fecha'])) . "',";
            }
            ?>
        ],
        datasets: [{
            label: 'Ingresos (S/)',
            data: [
                <?php 
                foreach (array_reverse($ingresosPorDia) as $dia) {
                    echo $dia['Total'] . ",";
                }
                ?>
            ],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: {
                        family: 'Inter',
                        size: 12,
                        weight: 'bold'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleFont: {
                    family: 'Inter',
                    size: 13,
                    weight: 'bold'
                },
                bodyFont: {
                    family: 'Inter',
                    size: 12
                },
                padding: 12,
                cornerRadius: 4,
                callbacks: {
                    label: function(context) {
                        return 'Ingresos: S/ ' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'S/ ' + value;
                    },
                    font: {
                        family: 'Inter',
                        size: 11
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                ticks: {
                    font: {
                        family: 'Inter',
                        size: 11,
                        weight: 'bold'
                    }
                },
                grid: {
                    display: false
                }
            }
        }
    }
});

// ═══════════════════════════════════════════════════════════════════════════
// GRÁFICO 2: DISTRIBUCIÓN POR TIPO DE ALOJAMIENTO
// ═══════════════════════════════════════════════════════════════════════════
const ctxTipo = document.getElementById('chartTipoAlojamiento').getContext('2d');
const chartTipo = new Chart(ctxTipo, {
    type: 'doughnut',
    data: {
        labels: [
            <?php 
            foreach ($distribucionTipo as $tipo) {
                echo "'" . htmlspecialchars($tipo['TipoAlojamiento']) . "',";
            }
            ?>
        ],
        datasets: [{
            data: [
                <?php 
                foreach ($distribucionTipo as $tipo) {
                    echo $tipo['Cantidad'] . ",";
                }
                ?>
            ],
            backgroundColor: [
                '#3b82f6',
                '#8b5cf6',
                '#ec4899',
                '#f59e0b',
                '#10b981'
            ],
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    font: {
                        family: 'Inter',
                        size: 12,
                        weight: 'bold'
                    },
                    padding: 15,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleFont: {
                    family: 'Inter',
                    size: 13,
                    weight: 'bold'
                },
                bodyFont: {
                    family: 'Inter',
                    size: 12
                },
                padding: 12,
                cornerRadius: 4,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Animación de entrada para las métricas
document.addEventListener('DOMContentLoaded', function() {
    const metricCards = document.querySelectorAll('.metric-card');
    metricCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
});
</script>

</body>
</html>

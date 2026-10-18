<?php
// Clientes/verificar_disponibilidad.php
// API para verificar disponibilidad de habitaciones

header('Content-Type: application/json');
require_once '../Conexion/conexion.php';

$numeroHabitacion = $_GET['numeroHabitacion'] ?? '';
$fechaLlegada = $_GET['fechaLlegada'] ?? '';
$tipoHabitacion = $_GET['tipoHabitacion'] ?? '';
$diasHospedaje = intval($_GET['diasHospedaje'] ?? 0);

if (empty($numeroHabitacion) || empty($fechaLlegada) || empty($tipoHabitacion) || $diasHospedaje <= 0) {
    echo json_encode(['disponible' => true, 'mensaje' => '']);
    exit;
}

try {
    $conn = Cconexion::ConexionBD();
    
    // Calcular rango de fechas de la nueva reserva
    $fechaInicio = new DateTime($fechaLlegada);
    
    if ($tipoHabitacion === 'Diario') {
        // Sumar días
        $fechaFin = clone $fechaInicio;
        $fechaFin->modify("+{$diasHospedaje} days");
    } else {
        // Sumar horas (Momentaneo)
        $fechaFin = clone $fechaInicio;
        $fechaFin->modify("+{$diasHospedaje} hours");
    }
    
    // Consultar ambas tablas: Reservas y ReservasClientes
    $sql = "
        SELECT FechaHoraLlegada, DiasHospedaje, TipoHabitacion
        FROM (
            SELECT FechaHoraLlegada, DiasHospedaje, TipoHabitacion, NumHabitacion as NumeroHabitacion
            FROM Reservas
            WHERE Estado != 'Cancelada' OR Estado IS NULL
            UNION ALL
            SELECT FechaHoraLlegada, DiasHospedaje, TipoHabitacion, NumeroHabitacion
            FROM ReservasClientes
            WHERE Estado != 'Cancelada' OR Estado IS NULL
        ) AS TodasReservas
        WHERE NumeroHabitacion = :numeroHabitacion
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':numeroHabitacion', $numeroHabitacion);
    $stmt->execute();
    $reservasExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar solapamiento
    foreach ($reservasExistentes as $reserva) {
        $reservaInicio = new DateTime($reserva['FechaHoraLlegada']);
        
        if ($reserva['TipoHabitacion'] === 'Diario') {
            $reservaFin = clone $reservaInicio;
            $reservaFin->modify("+{$reserva['DiasHospedaje']} days");
        } else {
            $reservaFin = clone $reservaInicio;
            $reservaFin->modify("+{$reserva['DiasHospedaje']} hours");
        }
        
        // Verificar si hay solapamiento
        // Solapamiento ocurre si: (inicio1 < fin2) AND (inicio2 < fin1)
        if ($fechaInicio < $reservaFin && $reservaInicio < $fechaFin) {
            $mensaje = "La habitación N° {$numeroHabitacion} ya está reservada ";
            
            if ($reserva['TipoHabitacion'] === 'Diario') {
                $mensaje .= "desde el " . $reservaInicio->format('d/m/Y') . 
                           " hasta el " . $reservaFin->format('d/m/Y') . ".";
            } else {
                $mensaje .= "desde las " . $reservaInicio->format('H:i') . 
                           " hasta las " . $reservaFin->format('H:i') . 
                           " del " . $reservaInicio->format('d/m/Y') . ".";
            }
            
            $mensaje .= " Por favor, elija otra fecha/hora u otra habitación.";
            
            echo json_encode([
                'disponible' => false,
                'mensaje' => $mensaje,
                'reservaInicio' => $reservaInicio->format('Y-m-d H:i:s'),
                'reservaFin' => $reservaFin->format('Y-m-d H:i:s')
            ]);
            exit;
        }
    }
    
    // Si llegamos aquí, está disponible
    echo json_encode(['disponible' => true, 'mensaje' => '']);
    
} catch (Exception $e) {
    echo json_encode([
        'disponible' => false,
        'mensaje' => 'Error al verificar disponibilidad: ' . $e->getMessage()
    ]);
}
?>

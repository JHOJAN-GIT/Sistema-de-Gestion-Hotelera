<?php
include_once("../Conexion/conexion.php");
require_once 'layout/seguridad.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idReserva = $_POST['idReserva'];
    $motivo = $_POST['motivo'];

    $conn = Cconexion::ConexionBD();

    if ($conn) {
        // Obtener los datos de la reserva
        $stmt = $conn->prepare("SELECT * FROM Reservas WHERE IdReserva = ?");
        $stmt->execute([$idReserva]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reserva) {
            // Insertar en la tabla ReservasCanceladas
           // Paso 3: Insertar en ReservasCanceladas incluyendo el IdReserva
$stmtInsert = $conn->prepare("INSERT INTO ReservasCanceladas 
    (IdReserva, TipoHabitacion, NombreCompleto, DNI, DiasHospedaje, FechaHoraLlegada, Costo, motivoCancelacion)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmtInsert->execute([
    $reserva['IdReserva'],
    $reserva['TipoHabitacion'],
    $reserva['NombreCompleto'],
    $reserva['DNI'],
    $reserva['DiasHospedaje'],
    $reserva['FechaHoraLlegada'],
    $reserva['Costo'],
    $motivo
]);


            // Eliminar de la tabla original
            $stmtDelete = $conn->prepare("DELETE FROM Reservas WHERE IdReserva = ?");
            $stmtDelete->execute([$idReserva]);

            // Redirigir de vuelta a la lista de reservas
            header("Location: reservas.php");
            exit();
        } else {
            echo "Reserva no encontrada.";
        }
    } else {
        echo "Error de conexión.";
    }
}
?>

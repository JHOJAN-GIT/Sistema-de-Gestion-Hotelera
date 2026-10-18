<?php
require_once 'layout/seguridad.php';

session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}
include_once("../Conexion/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $idRegistro = $_POST['id'];

    $conn = Cconexion::ConexionBD();

    if ($conn) {
        // Obtener el número de habitación antes de cerrar el folio
        $sqlGet = "SELECT NumeroHabitacion FROM Registros WHERE IdRegistro = ?";
        $stmtGet = $conn->prepare($sqlGet);
        $stmtGet->execute([$idRegistro]);
        $numHab = $stmtGet->fetchColumn();

        // Cerrar el folio
        $sql = "UPDATE Registros SET FechaHoraSalida = GETDATE() WHERE IdRegistro = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$idRegistro]);

        // Marcar habitación como Libre en la tabla Habitaciones
        if ($numHab) {
            $sqlHab = "UPDATE Habitaciones SET Estado = 'Libre' WHERE NumeroHabitacion = ?";
            $stmtHab = $conn->prepare($sqlHab);
            $stmtHab->execute([$numHab]);
        }
    }
}

header("Location: registroHuespedes.php");
exit();
?>

<?php
// Admin/perfilAdmin.php

$paginaActual = 'perfil';
$tituloPagina = 'Mi Perfil';

require_once 'layout/header.php';
require_once "../Conexion/conexion.php";

$admin_nombre = "Administrador No Encontrado";
$admin_correo = "sin-correo@hospedaje.com";
$admin_rol    = "Administrador Global";
$id_usuario_sesion = $_SESSION['id'];

try {
    $conn  = Cconexion::ConexionBD();
    $query = "SELECT TOP 1 nombre, correo FROM usuarios WHERE IdUsuario = :id";
    $stmt  = $conn->prepare($query);
    $stmt->bindParam(':id', $id_usuario_sesion);
    $stmt->execute();

    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($datos) {
        $admin_nombre = $datos['nombre'];
        $admin_correo = $datos['correo'];
    }
} catch (PDOException $e) {
    die("❌ Error en la consulta de perfil: " . $e->getMessage());
}
?>

<section class="mb-8">
    <h1 class="text-3xl font-extrabold text-black tracking-tight mb-2">Mi Perfil</h1>
    <p class="text-zinc-500 text-sm">Información de la cuenta de administración actual obtenida en tiempo real.</p>
</section>

<div class="grid grid-cols-12 gap-6">

    <!-- Avatar -->
    <div class="col-span-12 lg:col-span-4 bg-white border border-zinc-200 p-8 rounded-sm text-center shadow-[0px_4px_20px_rgba(0,0,0,0.02)] flex flex-col items-center justify-center">
        <div class="relative mb-4">
            <div class="h-28 w-28 rounded-full bg-black flex items-center justify-center border-4 border-zinc-100 shadow-inner mx-auto overflow-hidden">
                <?php if (!empty($_SESSION['avatar'])): ?>
                    <img src="<?= htmlspecialchars($_SESSION['avatar']) ?>" alt="Avatar" class="h-full w-full object-cover">
                <?php else: ?>
                    <span class="material-symbols-outlined text-yellow-400 text-6xl" style="font-variation-settings:'FILL' 1;">person</span>
                <?php endif; ?>
            </div>
            <div class="absolute bottom-1 right-1 bg-yellow-400 h-6 w-6 rounded-full flex items-center justify-center shadow border-2 border-white">
                <span class="material-symbols-outlined text-black text-xs font-bold">verified_user</span>
            </div>
        </div>
        <h3 class="text-xl font-black text-black"><?= htmlspecialchars($admin_nombre) ?></h3>
        <p class="text-xs bg-black text-yellow-400 font-extrabold tracking-widest uppercase px-3 py-1 rounded-sm mt-2 inline-block">
            <?= htmlspecialchars($admin_rol) ?>
        </p>
    </div>

    <!-- Datos -->
    <div class="col-span-12 lg:col-span-8 bg-white border border-zinc-200 p-8 rounded-sm shadow-[0px_4px_20px_rgba(0,0,0,0.02)]">
        <h4 class="text-lg font-bold text-black border-b border-zinc-100 pb-3 mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-zinc-400">badge</span>
            Datos de la Cuenta
        </h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">Nombre Completo</label>
                <div class="p-3 bg-zinc-50 border border-zinc-200 text-zinc-800 font-medium rounded-sm">
                    <?= htmlspecialchars($admin_nombre) ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">Correo Electrónico</label>
                <div class="p-3 bg-zinc-50 border border-zinc-200 text-zinc-800 font-medium rounded-sm">
                    <?= htmlspecialchars($admin_correo) ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">ID del Usuario (Base de datos)</label>
                <div class="p-3 bg-zinc-50 border border-zinc-200 text-zinc-700 font-mono text-sm rounded-sm">
                    <?= htmlspecialchars($id_usuario_sesion) ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">Rol de Acceso</label>
                <div class="p-3 bg-zinc-50 border border-zinc-200 text-zinc-800 font-medium rounded-sm capitalize">
                    <?= htmlspecialchars($_SESSION['rol']) ?>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-zinc-100 flex justify-end">
            <button onclick="confirmarCerrarSesion()"
                    class="inline-flex items-center gap-2 bg-red-50 text-error hover:bg-red-100 px-5 py-2.5 font-bold text-sm rounded-sm transition-colors shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-sm">logout</span>
                Cerrar Sesión
            </button>
        </div>
    </div>

</div>

<?php require_once 'layout/footer.php'; ?>
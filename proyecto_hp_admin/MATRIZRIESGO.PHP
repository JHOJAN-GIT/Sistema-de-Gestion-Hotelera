<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Análisis de Seguridad y Diseño Seguro - Hospedaje Ayacucho</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fafafa;
            margin: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #111;
            font-size: 24px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #111111;
            padding: 12px;
            vertical-align: top;
            font-size: 13px;
        }
        th {
            background-color: #000000;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .epic-login {
            background-color: #FFFF00;
            color: #000000;
            font-weight: bold;
            text-align: center;
        }
        .epic-admin {
            background-color: #E2E2E2;
            color: #000000;
            font-weight: bold;
            text-align: center;
        }
        .epic-client {
            background-color: #00FFFF;
            color: #000000;
            font-weight: bold;
            text-align: center;
        }
        .epic-ia {
            background-color: #00FFFF;
            color: #000000;
            font-weight: bold;
            text-align: center;
        }
        ul, ol {
            margin: 0;
            padding-left: 18px;
        }
        li {
            margin-bottom: 4px;
        }
        strong {
            color: #000;
        }
    </style>
</head>
<body>

    <h1>Matriz de Diseño Seguro y Análisis de Riesgos OWASP - Hospedaje Ayacucho</h1>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">EPIC</th>
                <th style="width: 15%;">HISTORIA</th>
                <th style="width: 25%;">PRINCIPIO DE DISEÑO SEGURO APLICADO (OWASP)</th>
                <th style="width: 25%;">RIESGO DE SEGURIDAD IDENTIFICADO (Lo que podría pasar)</th>
                <th style="width: 25%;">MEDIDAS DE MITIGACIÓN (Soluciones sencillas)</th>
            </tr>
        </thead>
        <tbody>
            <!-- LOGIN - CREAR CUENTA -->
            <tr>
                <td class="epic-login">LOGIN</td>
                <td><strong>Crear cuenta</strong></td>
                <td>
                    <strong>A01:2021-Control de Acceso Quebrado</strong> y <br>
                    <strong>A07:2021-Fallas de Identificación y Autenticación</strong>
                </td>
                <td>
                    Un atacante podría intentar registrar cuentas falsas de administrador de forma masiva o adivinar el código de autorización si no está bien protegido, tomando el control del sistema de reservas.
                </td>
                <td>
                    <ul>
                        <li>Bloquear temporalmente el registro si se introducen códigos de autorización incorrectos varias veces.</li>
                        <li>Hacer que el código de verificación por correo expire automáticamente en pocos minutos.</li>
                    </ul>
                </td>
            </tr>
            <!-- LOGIN - INICIAR SESIÓN -->
            <tr>
                <td class="epic-login">LOGIN</td>
                <td><strong>Iniciar Sesión</strong></td>
                <td>
                    <strong>A03:2021-Inyección</strong> (SQL Injection) y <br>
                    <strong>A07:2021-Fallas de Identificación y Autenticación</strong>
                </td>
                <td>
                    Si el formulario de login es vulnerable, intrusos podrían saltarse la contraseña mediante códigos maliciosos en el campo de texto o adivinar claves débiles mediante sistemas automatizados.
                </td>
                <td>
                    <ul>
                        <li>Usar consultas preparadas de forma obligatoria en la base de datos para anular cualquier código extraño.</li>
                        <li>Limitar el número de intentos de inicio de sesión para evitar ataques de adivinación de contraseñas.</li>
                    </ul>
                </td>
            </tr>
            <!-- LOGIN - RECUPERAR CONTRASEÑA -->
            <tr>
                <td class="epic-login">LOGIN</td>
                <td><strong>Recuperar Contraseña</strong></td>
                <td>
                    <strong>A07:2021-Fallas de Identificación y Autenticación</strong>
                </td>
                <td>
                    Cualquier persona podría introducir correos al azar para adivinar cuáles son clientes reales del hotel o interceptar el proceso para cambiar la clave de otra persona sin su permiso.
                </td>
                <td>
                    <ul>
                        <li>Mostrar siempre el mismo mensaje genérico al usuario, exista o no el correo introducido en la base de datos.</li>
                        <li>Asegurar que el cambio final de contraseña requiera obligatoriamente el código correcto guardado temporalmente en el servidor.</li>
                    </ul>
                </td>
            </tr>

            <!-- ADMIN - DASHBOARD PRINCIPAL -->
            <tr>
                <td class="epic-admin">ADMIN</td>
                <td><strong>Dashboard Principal</strong></td>
                <td>
                    <strong>A01:2021-Control de Acceso Quebrado</strong>
                </td>
                <td>
                    Un cliente malintencionado o un usuario no registrado podría escribir directamente la ruta de la página en la barra de direcciones del navegador y ver las métricas de ocupación y alertas de huéspedes.
                </td>
                <td>
                    <ul>
                        <li>Verificar estrictamente al inicio de la página que el usuario tiene una sesión activa y que cuenta con el rol exacto de administrador.</li>
                        <li>Si no es administrador, destruirá la sesión y lo expulsará inmediatamente al Login.</li>
                    </ul>
                </td>
            </tr>
            <!-- ADMIN - NUEVA RESERVA -->
            <tr>
                <td class="epic-admin">ADMIN</td>
                <td><strong>Nueva Reserva Administrativa</strong></td>
                <td>
                    <strong>A03:2021-Inyección</strong> y <br>
                    <strong>A04:2021-Diseño Inseguro</strong>
                </td>
                <td>
                    Un usuario podría manipular el formulario para guardar información basura, saltarse los precios múltiplos de 5, o inyectar código en el campo de comentarios de huéspedes.
                </td>
                <td>
                    <ul>
                        <li>Validar nuevamente en el servidor (con código PHP) que el DNI, nombres y costos cumplen las reglas fijadas antes de guardar.</li>
                        <li>Limpiar y desinfectar los textos de comentarios para que no ejecuten acciones raras en pantalla.</li>
                    </ul>
                </td>
            </tr>
            <!-- ADMIN - REGISTRAR CHECK-IN -->
            <tr>
                <td class="epic-admin">ADMIN</td>
                <td><strong>Registrar Check-In</strong></td>
                <td>
                    <strong>A01:2021-Control de Acceso Quebrado</strong>
                </td>
                <td>
                    Se podrían alterar los parámetros de la dirección web (parámetros GET) para forzar el Check-In de una habitación ocupada o modificar la reserva de otra persona sin autorización.
                </td>
                <td>
                    <ul>
                        <li>Comprobar en la base de datos que la habitación seleccionada esté realmente libre antes de cambiar su estado.</li>
                        <li>Validar que la reserva a procesar corresponda a un estado pendiente válido antes de realizar cualquier actualización.</li>
                    </ul>
                </td>
            </tr>
            <!-- ADMIN - REGISTRAR CHECK-OUT -->
            <tr>
                <td class="epic-admin">ADMIN</td>
                <td><strong>Registrar Check-Out</strong></td>
                <td>
                    <strong>A01:2021-Control de Acceso Quebrado</strong>
                </td>
                <td>
                    Modificando el código identificador enviado en la barra de direcciones, un atacante podría liberar habitaciones al azar o dar de alta la salida de huéspedes equivocados de forma maliciosa.
                </td>
                <td>
                    <ul>
                        <li>Validar internamente en el servidor que el código de registro que se quiere cerrar existe y está actualmente activo.</li>
                        <li>Evitar el uso de enlaces directos expuestos sin una confirmación previa de seguridad en el panel del empleado.</li>
                    </ul>
                </td>
            </tr>
            <!-- ADMIN - CANCELAR RESERVA -->
            <tr>
                <td class="epic-admin">ADMIN</td>
                <td><strong>Cancelar Reserva</strong></td>
                <td>
                    <strong>A04:2021-Diseño Inseguro</strong>
                </td>
                <td>
                    Se podrían enviar peticiones de cancelación vacías o sin motivos reales, borrando información del mapa de reservas del hotel sin dejar rastro de quién realizó la acción.
                </td>
                <td>
                    <ul>
                        <li>Obligar al sistema a rechazar la operación si el campo del motivo de la cancelación llega en blanco.</li>
                        <li>Registrar de forma automática el usuario del administrador que ejecutó la cancelación junto con el motivo en el historial de respaldo.</li>
                    </ul>
                </td>
            </tr>
            <!-- ADMIN - ACCESO CON TOKEN FINANCIERO -->
            <tr>
                <td class="epic-admin">ADMIN</td>
                <td><strong>Acceso con Token Dinámico</strong></td>
                <td>
                    <strong>A02:2021-Fallas Criptográficas</strong> y <br>
                    <strong>A07:2021-Fallas de Identificación y Autenticación</strong>
                </td>
                <td>
                    Si el token de seguridad dura demasiado tiempo activo o se genera de forma predecible, un atacante podría reutilizarlo o adivinarlo para espiar los reportes económicos del hotel.
                </td>
                <td>
                    <ul>
                        <li>Hacer que el código de acceso expire estrictamente a los 30 segundos de haber sido solicitado.</li>
                        <li>Eliminar el código de la memoria del servidor inmediatamente después de ser verificado o fallado.</li>
                    </ul>
                </td>
            </tr>

            <!-- CLIENTES - CATÁLOGO DE HABITACIONES -->
            <tr>
                <td class="epic-client">CLIENTES</td>
                <td><strong>Catálogo de Habitaciones</strong></td>
                <td>
                    <strong>A01:2021-Control de Acceso Quebrado</strong>
                </td>
                <td>
                    Usuarios no clientes podrían entrar a las rutas internas de catálogo o intentar modificar los precios visibles de los cuartos alterando el código visual de la página web.
                </td>
                <td>
                    <ul>
                        <li>Proteger la página exigiendo sesión activa del cliente y cargar los precios directamente desde la base de datos protegida, nunca desde valores del navegador.</li>
                    </ul>
                </td>
            </tr>
            <!-- CLIENTES - GENERAR RESERVA WEB -->
            <tr>
                <td class="epic-client">CLIENTES</td>
                <td><strong>Generar Reserva Web</strong></td>
                <td>
                    <strong>A01:2021-Control de Acceso Quebrado</strong> y <br>
                    <strong>A04:2021-Diseño Inseguro</strong>
                </td>
                <td>
                    Un cliente podría alterar el formulario web para reservar a nombre de otro usuario, o intentar reservar una habitación que ya está ocupada manipulando las solicitudes asíncronas.
                </td>
                <td>
                    <ul>
                        <li>Ignorar el nombre que envíe el formulario y usar siempre el nombre guardado de forma segura en la sesión del servidor.</li>
                        <li>Volver a comprobar la disponibilidad en la base de datos al momento exacto de guardar la reserva para evitar choques de fechas.</li>
                    </ul>
                </td>
            </tr>
            <!-- CLIENTES - HISTORIAL DE RESERVAS -->
            <tr>
                <td class="epic-client">CLIENTES</td>
                <td><strong>Historial Personal de Reservas</strong></td>
                <td>
                    <strong>A01:2021-Control de Acceso Quebrado</strong>
                </td>
                <td>
                    Si la consulta web es insegura, un cliente podría cambiar el identificador de usuario en la petición y espiar el historial de reservas, nombres y gastos de otros huéspedes del hotel.
                </td>
                <td>
                    <ul>
                        <li>Utilizar únicamente el ID de usuario de la sesión interna del servidor para buscar las reservas, impidiendo que el cliente pueda solicitar datos de otros códigos.</li>
                    </ul>
                </td>
            </tr>

            <!-- CLIENTES - ASISTENTE IA -->
            <tr>
                <td class="epic-ia">Asistente IA</td>
                <td><strong>Atención al cliente</strong></td>
                <td>
                    <strong>A05:2021-Configuración de Seguridad Incorrecta</strong> y <br>
                    <strong>OWASP LLM01: Inyección de Prompts</strong>
                </td>
                <td>
                    Un usuario malintencionado podría engañar al Asistente IA mediante textos tramposos (Prompt Injection) para obligarlo a dar información confidencial del hotel, insultar o dar respuestas inapropiadas.
                </td>
                <td>
                    <ul>
                        <li>Configurar instrucciones iniciales muy claras en el asistente que le prohíban revelar datos del sistema o salirse de su rol de atención hotelera.</li>
                        <li>Filtrar y limpiar los textos que introduce el usuario antes de enviárselos a la Inteligencia Artificial.</li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>
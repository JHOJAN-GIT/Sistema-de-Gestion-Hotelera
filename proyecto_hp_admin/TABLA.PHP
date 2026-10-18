<table style="width:100%; border-collapse: collapse; font-family: 'Inter', sans-serif; font-size: 13px; text-align: left; border: 1px solid #111111;">
  <thead>
    <tr style="background-color: #000000; color: #FFFFFF; font-weight: bold;">
      <th style="padding: 12px; border: 1px solid #333333; width: 10%;">epic</th>
      <th style="padding: 12px; border: 1px solid #333333; width: 15%;">Historia</th>
      <th style="padding: 12px; border: 1px solid #333333; width: 25%;">Descripcion historia cliente</th>
      <th style="padding: 12px; border: 1px solid #333333; width: 25%;">Criterios de Aceptación</th>
      <th style="padding: 12px; border: 1px solid #333333; width: 25%;">Criterios de aceptacion en BDD (alternativo)</th>
    </tr>
  </thead>
  <tbody>
    <!-- HISTORIA 1 -->
    <tr>
      <td style="background-color: #FFFF00; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">LOGIN</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Crear cuenta</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> usuario y administrador,<br>
        <strong>Quiero</strong> poder crear mi cuenta<br>
        <strong>Para</strong> acceder al sistema del hospedaje que corresponda.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>El sistema debe permitir elegir entre el rol de "Cliente" y "Administrador".</li>
          <li>El proceso consta de 2 pasos: Paso 1 (Ingreso de nombre completo y correo) y Paso 2 (Verificación de código y contraseña).</li>
          <li>Se debe enviar un código de 6 dígitos mediante PHPMailer al correo. Si es Admin, se validan 2 códigos (usuario + autorización).</li>
          <li>La contraseña debe tener mínimo 8 caracteres, al menos 1 mayúscula, 1 número y 1 símbolo especial.</li>
          <li>El nombre debe tener entre 3 y 5 palabras y se capitalizará automáticamente.</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que soy un nuevo cliente en la plataforma web del hospedaje<br>
        <strong>WHEN</strong> completo el formulario del Paso 1 con mi nombre "Juan Pérez Castro" y correo "juan@mail.com" seleccionando el rol de "Cliente"<br>
        <strong>AND</strong> introduzco el código correcto de 6 dígitos enviado a mi correo<br>
        <strong>AND</strong> defino una contraseña que cumple con los niveles de fortaleza requeridos<br>
        <strong>THEN</strong> el sistema debe registrar mis datos en la tabla Usuarios y redirigirme a la pantalla de Login.
      </td>
    </tr>
    <!-- HISTORIA 2 -->
    <tr>
      <td style="background-color: #FFFF00; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">LOGIN</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Iniciar Sesión</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> usuario registrado (Admin o Cliente),<br>
        <strong>Quiero</strong> ingresar mis credenciales de acceso<br>
        <strong>Para</strong> ingresar a las funciones específicas de mi interfaz de usuario.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>El formulario debe capturar por método POST el correo electrónico y la contraseña.</li>
          <li>Se deben usar Prepared Statements contra la tabla Usuarios para evitar SQL Injection.</li>
          <li>Al autenticarse, se deben guardar en sesión: id, nombre, correo y rol.</li>
          <li>Redirección por rol: admin va a /Administrador/inicioAdmin.php y cliente va a /Clientes/inicioClientes.php.</li>
          <li>Incluye checkbox "Recordar sesión" y toggle JS para mostrar/ocultar contraseña.</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que me encuentro en el formulario de inicio de sesión seguro<br>
        <strong>WHEN</strong> digito mi correo electrónico y contraseña válidos correspondientes al rol "admin"<br>
        <strong>THEN</strong> el sistema debe validar mis credenciales, inicializar mis variables de sesión de forma segura y redirigirme a /Administrador/inicioAdmin.php.
      </td>
    </tr>
    <!-- HISTORIA 3 -->
    <tr>
      <td style="background-color: #FFFF00; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">LOGIN</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Recuperar Contraseña</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> usuario que olvidó su clave de acceso,<br>
        <strong>Quiero</strong> restablecer mi contraseña mediante una validación de identidad por correo<br>
        <strong>Para</strong> recuperar la disponibilidad de mi cuenta de manera autónoma.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>Flujo en 3 pasos: Identificación (correo y rol), Verificación (código de 6 dígitos) y Nueva Contraseña.</li>
          <li>No debe revelar explícitamente si el correo existe en la base de datos (mensaje genérico).</li>
          <li>La nueva contraseña no puede ser igual a la anterior ni igual a la contraseña del otro rol de usuario.</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que no recuerdo mi clave e ingreso a la vista de recuperación<br>
        <strong>WHEN</strong> especifico mi rol, mi correo electrónico asignado y proporciono el código temporal recibido<br>
        <strong>AND</strong> digito una nueva contraseña que pasa los controles de fortaleza obligatorios<br>
        <strong>THEN</strong> el sistema debe actualizar el registro en la BD usando Prepared Statements y permitirme ingresar mediante el Login tradicional.
      </td>
    </tr>
    <!-- HISTORIA 4 -->
    <tr>
      <td style="background-color: #E2E2E2; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">ADMIN</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Dashboard Principal</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> administrador del hospedaje,<br>
        <strong>Quiero</strong> visualizar un panel central de control operativo con métricas en tiempo real<br>
        <strong>Para</strong> supervisar la ocupación y las acciones inmediatas de la jornada hotelera.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>Calcular en tiempo real el % de Ocupación: (Habitaciones Ocupadas / Total) * 100.</li>
          <li>Desplegar Check-ins del día y Bento Grid de accesos rápidos (Nuevo Registro, Registros, Reservas, Huéspedes).</li>
          <li>Mostrar los próximos 3 Check-ins y próximos 3 Check-outs (+3h para momentáneos; 12:00 PM del día siguiente para diarios).</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que inicié sesión como administrador del sistema<br>
        <strong>WHEN</strong> cargo la página principal /Administrador/inicioAdmin.php<br>
        <strong>THEN</strong> el sistema debe ejecutar de forma transparente las consultas SQL de conteo y mostrar los indicadores exactos de ocupación actual junto a las alertas de salidas.
      </td>
    </tr>
    <!-- HISTORIA 5 -->
    <tr>
      <td style="background-color: #E2E2E2; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">ADMIN</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Nueva Reserva Administrativa</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> administrador del hospedaje,<br>
        <strong>Quiero</strong> registrar una reserva de manera manual para un huésped<br>
        <strong>Para</strong> asegurar sus habitaciones cuando la solicitud se realiza por canales físicos o telefónicos.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>Validaciones en backend: DNI de 8 dígitos, nombre completo entre 3 y 5 palabras, costo mayor a 0 y múltiplo de 5.</li>
          <li>Control de tiempo: Momentáneo (3 a 12 horas) y Diario (1 a 7 días).</li>
          <li>La fecha/hora de llegada no puede ser menor a la hora actual del servidor.</li>
          <li>Se debe verificar asíncronamente la disponibilidad llamando a verificar_disponibilidad.php antes del insert en Reservas.</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que estoy completando el formulario de nueva reserva en el panel de administrador<br>
        <strong>WHEN</strong> ingreso un costo de "33" soles para una habitación diaria<br>
        <strong>THEN</strong> el sistema debe frenar la operación informando que el costo ingresado debe ser obligatoriamente un múltiplo de 5.
      </td>
    </tr>
    <!-- HISTORIA 6 -->
    <tr>
      <td style="background-color: #E2E2E2; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">ADMIN</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Registrar Check-In</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> administrador del hospedaje,<br>
        <strong>Quiero</strong> realizar el check-in de una reserva activa o de un huésped directo<br>
        <strong>Para</strong> asignarle formalmente una habitación y dar inicio a su estadía.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>Opera con auto-llenado por parámetros GET o mediante formulario manual limpio para walk-ins.</li>
          <li>Verifica obligatoriamente que el estado de la habitación sea "Disponible" en la BD.</li>
          <li>Al confirmar, inserta en Registros, actualiza la habitación a "Ocupado" y cambia la reserva origen a "Registrada".</li>
          <li>No permite el check-in si el DNI o Nombre ya corresponden a un huésped actualmente activo en el hotel.</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que tengo una reserva con estado "Pendiente" y hago clic en el botón "Check-In"<br>
        <strong>WHEN</strong> valido los campos pre-llenados en la pantalla /Administrador/nuevoRegistro.php y presiono guardar<br>
        <strong>THEN</strong> la base de datos debe actualizar la habitación asignada como "Ocupado" y cambiar el estado de la reserva a "Registrada".
      </td>
    </tr>
    <!-- HISTORIA 7 -->
    <tr>
      <td style="background-color: #E2E2E2; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">ADMIN</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Registrar Check-Out</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> administrador del hospedaje,<br>
        <strong>Quiero</strong> registrar la salida de los huéspedes en el sistema<br>
        <strong>Para</strong> liberar la habitación y mantener actualizado el registro histórico de ingresos.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>Se procesa capturando el IdRegistro mediante parámetro GET en la URL.</li>
          <li>Actualiza el campo FechaHoraSalida en la tabla Registros usando GETDATE() de SQL Server.</li>
          <li>Ejecuta una sentencia UPDATE en la tabla Habitaciones para restablecer el estado a "Disponible".</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que un huésped se retira y visualizo su registro activo en la sección de huéspedes actuales<br>
        <strong>WHEN</strong> presiono la acción de "Check-Out" para dicho registro<br>
        <strong>THEN</strong> el sistema debe almacenar el momento exacto de su salida y habilitar la habitación de inmediato para su posterior comercialización.
      </td>
    </tr>
    <!-- HISTORIA 8 -->
    <tr>
      <td style="background-color: #E2E2E2; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">ADMIN</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Cancelar Reserva</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> administrador del hospedaje,<br>
        <strong>Quiero</strong> dar de baja una reserva agendada indicando un motivo específico<br>
        <strong>Para</strong> liberar el cupo de la habitación ante inasistencias o cancelaciones.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>La acción solo está disponible si la reserva está en estado diferente a "Cancelada" o "Registrada".</li>
          <li>Despliega un cuadro de diálogo nativo (Prompt) para escribir de forma obligatoria el motivo de la cancelación.</li>
          <li>Copia la fila original a las tablas ReservasCanceladas o ReservasCanceladasClientes con la causa/fecha y actualiza el estado a "Cancelada".</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que una reserva se encuentra en estado "No Llegó" tras superar el límite temporal establecido<br>
        <strong>WHEN</strong> el administrador pulsa "Cancelar", escribe el motivo "No se presentó" y confirma<br>
        <strong>THEN</strong> la fila original cambia a "Cancelada" y se traslada un respaldo al registro histórico de cancelaciones administrativas.
      </td>
    </tr>
    <!-- HISTORIA 9 -->
    <tr>
      <td style="background-color: #E2E2E2; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">ADMIN</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Acceso con Token Dinámico</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> administrador principal,<br>
        <strong>Quiero</strong> pasar por un control de doble factor por correo electrónico para abrir el módulo de reportes<br>
        <strong>Para</strong> resguardar la información confidencial de ingresos e indicadores del hospedaje.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>Al pulsar "Generar Reporte" abre un modal y llama por Fetch a enviar_codigo.php.</li>
          <li>Genera un token de 6 dígitos enviado por SMTP a jhojanantezana96@gmail.com.</li>
          <li>El código se valida contra la sesión con un timeout estricto de 30 segundos.</li>
          <li>Si es exitoso, se limpian las variables de verificación de la sesión y se redirige a reportes.php.</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que solicité el código de acceso a reportes y éste llegó a mi bandeja<br>
        <strong>WHEN</strong> transcurren más de 30 segundos antes de que digite el código recibido en la interfaz<br>
        <strong>THEN</strong> el sistema debe responder en formato JSON que el código ha expirado y rechazar el acceso al módulo de analítica.
      </td>
    </tr>
    <!-- HISTORIA 10 -->
    <tr>
      <td style="background-color: #00FFFF; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">CLIENTES</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Catálogo de Habitaciones</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> cliente del hospedaje,<br>
        <strong>Quiero</strong> recorrer de forma visual el listado de las 20 habitaciones con sus detalles individuales<br>
        <strong>Para</strong> seleccionar la habitación específica que mejor se adecúe a mis necesidades.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>Segmentación por rangos establecidos: 301-305 (Momentáneas), 401-405 (Individual), 501-505 (Doble) y 601-605 (Matrimonial).</li>
          <li>Cada habitación renderiza una descripción única mapeada en el backend (ej: jacuzzi, balcón, vista, etc.).</li>
          <li>El botón "Especificaciones" abre un modal JS cargando fotos representativas dinámicas desde Unsplash y el precio unitario.</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que navego en la sección /Clientes/habitaciones.php<br>
        <strong>WHEN</strong> hago clic en "Especificaciones" dentro de la tarjeta informativa de la habitación 302<br>
        <strong>THEN</strong> la interfaz debe remover la clase hidden del componente modal mediante JS para mostrar la descripción de su Jacuzzi privado y su precio correspondiente.
      </td>
    </tr>
    <!-- HISTORIA 11 -->
    <tr>
      <td style="background-color: #00FFFF; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">CLIENTES</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Generar Reserva Web</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> cliente autenticado,<br>
        <strong>Quiero</strong> programar una reserva completando un formulario digital enlazado a la disponibilidad en tiempo real<br>
        <strong>Para</strong> asegurar mi espacio en el hospedaje sin requerir asistencia externa.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>El campo "Nombre Completo" debe auto-llenarse con el valor textual de la sesión del usuario activo.</li>
          <li>El select del número de habitación carga dinámicamente según el tipo seleccionado (filtrando los rangos de habitación).</li>
          <li>Invoca asíncronamente mediante Fetch API la lógica de solapamiento de fechas de verificar_disponibilidad.php antes del submit.</li>
          <li>Se envía a guardar_reserva.php para registrar en la tabla ReservasClientes con estado "Pendiente" y comentarios opcionales.</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que completo los campos para reservar la habitación 401 para una fecha seleccionada<br>
        <strong>WHEN</strong> la validación asíncrona AJAX detecta que dicha habitación ya cuenta con un registro activo para esas fechas<br>
        <strong>THEN</strong> el script detiene la ejecución del formulario desplegando una alerta nativa indicando el periodo no disponible.
      </td>
    </tr>
    <!-- HISTORIA 12 -->
    <tr>
      <td style="background-color: #00FFFF; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">CLIENTES</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Historial Personal de Reservas</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> cliente registrado en el sistema,<br>
        <strong>Quiero</strong> visualizar exclusivamente el listado de las reservas que he realizado<br>
        <strong>Para</strong> monitorear los costos, revisar los números de habitación y verificar el estado actual de mis solicitudes.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>La consulta SQL en misReservas.php debe estar filtrada estrictamente por el IdUsuario de la sesión activa.</li>
          <li>La tabla visible omite las columnas de auditoría interna de la BD (como IdUsuario o el ID numérico en crudo).</li>
          <li>Los estados se representan con Badges de colores: Pendiente (Verde), Registrada (Azul), Cancelada (Rojo).</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que soy un cliente con sesión activa<br>
        <strong>WHEN</strong> accedo a la pestaña /Clientes/misReservas.php<br>
        <strong>THEN</strong> el sistema debe desplegar únicamente los registros que coinciden con mi ID personal de la base de datos de manera ordenada por fecha de llegada.
      </td>
    </tr>
    <!-- HISTORIA 13 -->
    <tr>
      <td style="background-color: #00FFFF; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">CLIENTES</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Pedidos de Room Service</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> cliente hospedado en el hotel,<br>
        <strong>Quiero</strong> realizar solicitudes digitales del catálogo de servicios complementarios<br>
        <strong>Para</strong> que sean entregados directamente a mi habitación con un control de tiempo estimado.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ul>
          <li>Muestra dinámicamente servicios disponibles desde la tabla Servicios (Desayuno, Almuerzo, Cena, Lavandería, Room Service).</li>
          <li>"Solicitar al Cuarto" despliega un modal con el precio y tiempo de entrega estimado en minutos.</li>
          <li>Al confirmar el pedido, el botón cambia a "Procesando Pedido..." e inicia un temporizador regresivo de JS en pantalla.</li>
          <li>Al llegar a cero, el botón cambia a "Pedir de nuevo" y genera una notificación de entrega del servicio.</li>
        </ul>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN</strong> que inicié una solicitud de "Desayuno" desde mi panel<br>
        <strong>WHEN</strong> se ejecuta la función iniciarTimer(15) en JavaScript<br>
        <strong>THEN</strong> el sistema debe restar los segundos de forma continua y, al llegar a cero, notificar el estado de entrega y actualizar el estado interactivo del botón.
      </td>
    </tr>
    <!-- HISTORIA 14: ASISTENTE IA -->
    <tr>
      <td style="background-color: #00FFFF; color: #000000; font-weight: bold; padding: 12px; border: 1px solid #111111; vertical-align: top;">Asistente IA</td>
      <td style="font-weight: bold; text-align: center; padding: 12px; border: 1px solid #111111; vertical-align: top;">Atención al cliente</td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Como</strong> cliente<br>
        <strong>Quiero</strong> recibir respuestas las 24 horas<br>
        <strong>Para</strong> aclarar dudas del momento.
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top;">
        <strong>Criterios de Aceptación</strong><br>
        <ol style="margin-left: 15px; padding-left: 0px;">
          <li>La IA debe responder la pregunta del usuario, esta debe ser clara y precisa.</li>
          <li>La IA debe estar activa las 24 horas del día.</li>
          <li>El bot debe interpretar el lenguaje natural aun si existen errores de ortografía o gramática (ej. "ay abitacion disponible para oy?").</li>
        </ol>
      </td>
      <td style="padding: 12px; border: 1px solid #111111; vertical-align: top; font-family: monospace; font-size: 11px;">
        <strong>GIVEN (Dado que)</strong> estoy interactuando con el chatbot desde mi PC<br>
        <strong>WHEN (Cuando)</strong> escribo una consulta con errores de ortografía o gramática (ej. "ay abitacion disponible para oy?")<br>
        <strong>THEN</strong> el bot debe comprender la intención del mensaje, procesar la consulta utilizando la API y retornar una respuesta clara y precisa sin fallas.
      </td>
    </tr>
  </tbody>
</table>
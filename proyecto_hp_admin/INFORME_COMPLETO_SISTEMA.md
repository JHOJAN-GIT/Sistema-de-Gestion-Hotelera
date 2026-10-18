# INFORME TÉCNICO COMPLETO
## SISTEMA DE GESTIÓN HOTELERA - HOSPEDAJE AYACUCHO

---

**Fecha de Generación:** Mayo 28, 2026  
**Versión del Sistema:** 2.0  
**Tecnologías:** PHP 8.x, Microsoft SQL Server, TailwindCSS, JavaScript ES6+  
**Arquitectura:** MVC Modular con PDO  
**Autor del Informe:** Análisis Automatizado Completo

---

## ÍNDICE

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Módulo de Autenticación](#módulo-de-autenticación)
4. [Módulo de Administrador](#módulo-de-administrador)
5. [Módulo de Clientes](#módulo-de-clientes)
6. [Base de Datos](#base-de-datos)
7. [Funcionalidades Frontend](#funcionalidades-frontend)
8. [Seguridad](#seguridad)
9. [Integraciones](#integraciones)
10. [Conclusiones](#conclusiones)

---

## 1. RESUMEN EJECUTIVO

### 1.1 Descripción General
El Sistema de Gestión Hotelera "Hospedaje Ayacucho" es una aplicación web completa desarrollada en PHP con Microsoft SQL Server como motor de base de datos. El sistema gestiona de manera integral las operaciones de un hospedaje, incluyendo reservas, check-in/check-out, gestión de habitaciones, servicios adicionales y reportes administrativos.

### 1.2 Características Principales
- **Autenticación Multi-Rol:** Sistema de login con roles diferenciados (Admin/Cliente)
- **Gestión de Reservas:** Doble sistema de reservas (Admin y Cliente)
- **Check-In/Check-Out:** Proceso automatizado con validaciones temporales
- **Gestión de Habitaciones:** 20 habitaciones con 4 tipos diferentes
- **Reportes:** Dashboard con métricas en tiempo real
- **Servicios:** Catálogo de servicios con solicitud al cuarto
- **Programa de Fidelidad:** Beneficios por tipo de habitación
- **Notificaciones por Email:** Sistema PHPMailer integrado

### 1.3 Usuarios del Sistema
- **Administradores:** Gestión completa del hospedaje
- **Clientes:** Reservas, consulta de servicios y habitaciones

---

## 2. ARQUITECTURA DEL SISTEMA

### 2.1 Estructura de Directorios
```
proyecto_hp_admin/
├── Administrador/          # Módulo administrativo
│   ├── layout/            # Componentes reutilizables
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── seguridad.php
│   ├── inicioAdmin.php
│   ├── reservas.php
│   ├── nuevaReserva.php
│   ├── nuevoRegistro.php
│   ├── registroHuespedes.php
│   ├── huespedesActuales.php
│   ├── checkOut.php
│   ├── cancelarReserva.php
│   ├── reservasCanceladas.php
│   ├── reservasCanceladasClientes.php
│   ├── reportes.php
│   ├── enviar_codigo.php
│   ├── verificar_codigo.php
│   ├── verificar_disponibilidad.php
│   ├── perfilAdmin.php
│   └── logout.php
├── Clientes/              # Módulo de clientes
│   ├── layout/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── sidebar.php
│   ├── inicioClientes.php
│   ├── habitaciones.php
│   ├── habitacionesReservar.php
│   ├── reservar.php
│   ├── guardar_reserva.php
│   ├── misReservas.php
│   ├── servicios.php
│   ├── chat.php
│   ├── verificar_disponibilidad.php
│   └── perfilCliente.php
├── Login/                 # Módulo de autenticación
│   ├── login.php
│   ├── registrarCuenta.php
│   ├── recuperarContraseña.php
│   ├── paginaBienvenida.php
│   └── cerrarSesion.php
├── Conexion/              # Capa de datos
│   ├── conexion.php
│   └── index.php
├── PHPMailer/             # Librería de correos
│   ├── PHPMailer.php
│   ├── SMTP.php
│   └── Exception.php
├── Imagenes/              # Recursos gráficos
└── config.php             # Configuración API
```

### 2.2 Patrón de Arquitectura
**Modelo:** MVC Modular con separación de responsabilidades

- **Modelo (Model):** Clase `Cconexion` en `/Conexion/conexion.php`
- **Vista (View):** Archivos PHP con HTML/TailwindCSS
- **Controlador (Controller):** Lógica de negocio en cada archivo PHP

### 2.3 Tecnologías Utilizadas

#### Backend
- **Lenguaje:** PHP 8.x
- **Base de Datos:** Microsoft SQL Server
- **Driver:** PDO con sqlsrv
- **Servidor:** Apache (XAMPP)

#### Frontend
- **Framework CSS:** TailwindCSS 3.x (CDN)
- **Iconos:** Material Symbols Outlined
- **Tipografía:** Inter (Google Fonts)
- **JavaScript:** Vanilla JS (ES6+)

#### Librerías Externas
- **PHPMailer:** Envío de correos electrónicos
- **Chart.js:** Gráficos en reportes (si implementado)

### 2.4 Conexión a Base de Datos

**Archivo:** `/Conexion/conexion.php`

```php
class Cconexion {
    public static function ConexionBD() {
        try {
            $conn = new PDO(
                "sqlsrv:Server=localhost;Database=BD_HOSPEDAJE", 
                "proyehosp", 
                "Php12345!"
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }
}
```

**Características:**
- Conexión mediante PDO (PHP Data Objects)
- Driver: `sqlsrv` para SQL Server
- Manejo de excepciones activado
- Método estático para acceso global

---

## 3. MÓDULO DE AUTENTICACIÓN

### 3.1 Sistema de Login (`/Login/login.php`)

#### Funcionalidad Principal
Sistema de autenticación con validación de credenciales contra la tabla `Usuarios` en SQL Server.

#### Proceso de Login
1. **Captura de Datos:** Formulario POST con correo y contraseña
2. **Validación:** Consulta SQL con prepared statements
3. **Creación de Sesión:** Variables de sesión almacenadas
4. **Redirección por Rol:**
   - `admin` → `/Administrador/inicioAdmin.php`
   - `cliente` → `/Clientes/inicioClientes.php`

#### Variables de Sesión Creadas
```php
$_SESSION['id']     // IdUsuario
$_SESSION['nombre'] // Nombre del usuario
$_SESSION['correo'] // Correo electrónico
$_SESSION['rol']    // 'admin' o 'cliente'
```

#### Seguridad Implementada
- **Prepared Statements:** Prevención de SQL Injection
- **Cache Control:** Headers para evitar caché de credenciales
- **Validación de Rol:** Verificación antes de redirección
- **Prevención de Retroceso:** JavaScript `window.history.forward()`

#### Interfaz de Usuario
- **Diseño:** Split-screen con branding izquierdo y formulario derecho
- **Responsive:** Adaptable a móviles y tablets
- **Elementos:**
  - Campo de correo electrónico
  - Campo de contraseña con toggle de visibilidad
  - Checkbox "Recordar sesión"
  - Enlace a recuperación de contraseña
  - Enlace a registro de cuenta
  - Mensaje de error dinámico (PHP)

#### Botones y Acciones
| Botón | Acción | Destino |
|-------|--------|---------|
| **Ingresar** | Submit del formulario | Validación y redirección |
| **¿Olvidaste tu contraseña?** | Link | `/Login/recuperarContraseña.php` |
| **Regístrate aquí** | Link | `/Login/registrarCuenta.php` |
| **Toggle Password** | JavaScript | Mostrar/ocultar contraseña |

---

### 3.2 Registro de Cuenta (`/Login/registrarCuenta.php`)

#### Funcionalidad Principal
Sistema de registro en dos pasos con verificación por correo electrónico.

#### Proceso de Registro (Paso 1)
1. **Selección de Rol:** Admin o Cliente
2. **Ingreso de Datos:** Nombre completo y correo
3. **Validación de Duplicados:** Verifica correo + rol en BD
4. **Generación de Códigos:**
   - Cliente: 1 código de 6 dígitos
   - Admin: 2 códigos (usuario + autorización)
5. **Envío de Email:** PHPMailer a correo ingresado
6. **Transición:** Paso 2 activado

#### Proceso de Registro (Paso 2)
1. **Verificación de Código:** Input de 6 dígitos
2. **Código Admin Adicional:** Solo para rol admin
3. **Creación de Contraseña:**
   - Validación de fortaleza en tiempo real
   - Confirmación de contraseña
4. **Validaciones de Seguridad:**
   - No puede ser igual a contraseña de otro rol
   - Debe cumplir requisitos de fortaleza
5. **Inserción en BD:** Tabla `Usuarios`
6. **Redirección:** Login exitoso

#### Requisitos de Contraseña
- Mínimo 8 caracteres
- Al menos 1 mayúscula
- Al menos 1 número
- Al menos 1 símbolo especial (!@#$%^&*()_+)

#### Validaciones Implementadas
```javascript
// Fortaleza de contraseña
/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/

// Formato de nombre (3-5 palabras)
palabras.length >= 3 && palabras.length <= 5

// Capitalización automática
ucwords(mb_strtolower($nombre))
```

#### Botones y Acciones
| Botón | Acción | Condición |
|-------|--------|-----------|
| **Continuar** | Enviar código | Paso 1 |
| **Crear Cuenta** | Registrar usuario | Paso 2, validaciones OK |
| **Cancelar y Volver** | Reset sesión | Cualquier paso |
| **¿Ya tienes cuenta?** | Link a login | Siempre visible |

---

### 3.3 Recuperación de Contraseña (`/Login/recuperarContraseña.php`)

#### Funcionalidad Principal
Sistema de recuperación en tres pasos con verificación por código.

#### Paso 1: Identificación
- Selección de rol (Admin/Cliente)
- Ingreso de correo electrónico
- Validación: correo existe + rol correcto
- Envío de código de 6 dígitos

#### Paso 2: Verificación
- Input de código recibido
- Validación contra sesión
- Tiempo límite implícito (sesión)

#### Paso 3: Nueva Contraseña
- Ingreso de nueva contraseña
- Confirmación de contraseña
- Validaciones:
  - No puede ser igual a la anterior
  - No puede ser igual a la del otro rol
  - Debe cumplir requisitos de fortaleza
- Actualización en BD
- Redirección a login

#### Seguridad
- Validación de rol específico
- No revela si el correo existe (mensaje genérico)
- Código temporal en sesión
- Prepared statements para UPDATE

---

### 3.4 Cierre de Sesión (`/Login/cerrarSesion.php`)

#### Funcionalidad
Destrucción completa de sesión y limpieza de cookies.

#### Proceso
1. Iniciar sesión (si no está iniciada)
2. Vaciar array `$_SESSION`
3. Eliminar cookie de sesión del navegador
4. Destruir sesión en servidor
5. Headers de no-cache
6. Redirección a login

```php
session_destroy();
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Location: login.php?sesion=cerrada");
```

---

### 3.5 Página de Bienvenida (`/Login/paginaBienvenida.php`)

#### Funcionalidad
Landing page pública del hospedaje con información institucional.

#### Secciones
1. **Hero Section:** Imagen de fondo con CTA
2. **Servicios:** Grid Bento con 3 servicios destacados
3. **Beneficios:** 3 razones para elegir el hospedaje
4. **CTA Final:** Llamado a acción para login
5. **Footer:** Información de contacto y navegación

#### Botones
- **Inicia sesión para acceder:** Redirección a login.php
- **Ver Mapa:** Placeholder (no funcional)
- Links de navegación en footer

---

## 4. MÓDULO DE ADMINISTRADOR

### 4.1 Dashboard Principal (`/Administrador/inicioAdmin.php`)

#### Funcionalidad Principal
Panel de control centralizado con métricas en tiempo real y accesos rápidos.

#### Métricas Mostradas
1. **Porcentaje de Ocupación:**
   - Cálculo: (Habitaciones Ocupadas / Total) * 100
   - Query: `SELECT COUNT(*) FROM Habitaciones WHERE Estado = 'Ocupado'`

2. **Check-ins Hoy:**
   - Registros con `FechaHoraIngreso` = fecha actual
   - Filtro: `FechaHoraSalida IS NULL`

3. **Próximos Check-ins (Top 3):**
   - Reservas sin check-in realizado
   - Ordenadas por `FechaHoraLlegada ASC`
   - Excluye nombres ya en tabla `Registros`

4. **Próximos Check-outs (Top 3):**
   - Registros activos ordenados por tiempo restante
   - Cálculo dinámico:
     - Momentáneo: +3 horas desde ingreso
     - Diario: 12:00 PM del día siguiente

#### Bento Grid de Acciones Rápidas
| Card | Destino | Color | Icono |
|------|---------|-------|-------|
| **Nuevo Registro** | nuevoRegistro.php | Amarillo | add_box |
| **Registros** | registroHuespedes.php | Blanco | database |
| **Reservas** | reservas.php | Negro | calendar_month |
| **Huéspedes** | huespedesActuales.php | Amarillo | groups |

#### Modal de Verificación de Reportes
- **Trigger:** Botón "Generar Reporte"
- **Proceso:**
  1. Envío de código a `jhojanantezana96@gmail.com`
  2. Input de 6 dígitos
  3. Verificación con timeout de 30 segundos
  4. Redirección a `reportes.php`

#### Funciones JavaScript
```javascript
abrirModalReporte()    // Abre modal y envía código
cerrarModalReporte()   // Cierra modal
enviarCodigo()         // Fetch a enviar_codigo.php
reenviarCodigo()       // Reenvía código
verificarCodigo()      // Valida y redirige
```

---

### 4.2 Gestión de Reservas (`/Administrador/reservas.php`)

#### Funcionalidad Principal
Visualización y gestión de dos tipos de reservas: Admin y Clientes.

#### Tablas de Base de Datos
1. **Reservas:** Creadas por administradores
2. **ReservasClientes:** Creadas por clientes desde su panel

#### Columnas Mostradas
| Columna | Tipo | Descripción |
|---------|------|-------------|
| Tipo | String | TipoHabitacion (Momentaneo/Individual/Doble/Matrimonial) |
| Huésped | String | NombreCompleto |
| DNI | String | Documento de identidad |
| N° Hab. | Int | NumeroHabitacion (solo ReservasClientes) |
| Días | Int | DiasHospedaje |
| Fecha Llegada | DateTime | FechaHoraLlegada |
| Costo | Decimal | Monto en soles |
| Estado | Badge | Pendiente/Registrada/Cancelada/No Llegó |
| Acciones | Buttons | Check-In / Cancelar |

#### Estados de Reserva
1. **Pendiente:** Reserva activa sin check-in
2. **Registrada:** Check-in realizado (nombre en tabla Registros)
3. **Cancelada:** Estado = 'Cancelada' en BD
4. **No Llegó:** +2 horas después de FechaHoraLlegada sin check-in

#### Acciones Disponibles
**Botón Check-In:**
- **Habilitado:** Estado Pendiente o No Llegó
- **Deshabilitado:** Cancelada o Registrada
- **Acción:** Redirección a `nuevoRegistro.php` con parámetros GET
- **Parámetros pasados:**
  ```php
  nombreCompleto, dni, monto, tipoAlojamiento, 
  diasHospedaje, idReserva, tabla, numeroHabitacion, 
  servicioEspecial
  ```

**Botón Cancelar:**
- **Habilitado:** Solo si estado != Cancelada y != Registrada
- **Proceso:**
  1. Confirm dialog
  2. Prompt para motivo de cancelación
  3. POST a mismo archivo
  4. Inserción en tabla `ReservasCanceladas` o `ReservasCanceladasClientes`
  5. UPDATE Estado = 'Cancelada'
  6. Reload de página

#### Botones de Navegación
| Botón | Destino | Ubicación |
|-------|---------|-----------|
| **Ver Canceladas** | reservasCanceladas.php | Header |
| **Nueva Reserva** | nuevaReserva.php | Header |
| **Ver Canceladas Clientes** | reservasCanceladasClientes.php | Sección Clientes |

#### Función de Expansión
- Tablas con scroll limitado a 380px
- Botón "Expandir/Contraer" si hay más de 5 registros
- JavaScript toggle de `max-height`

---

### 4.3 Nueva Reserva (`/Administrador/nuevaReserva.php`)

#### Funcionalidad Principal
Formulario para crear reservas desde el panel administrativo.

#### Campos del Formulario
| Campo | Tipo | Validación |
|-------|------|------------|
| **Tipo de Habitación** | Select | Momentaneo / Diario |
| **Nombre Completo** | Text | 3-5 palabras, capitalizado |
| **DNI** | Text | 8 dígitos numéricos |
| **Días/Horas** | Number | Momentaneo: 3-12h, Diario: 1-7d |
| **Costo** | Number | Múltiplo de 5, mínimo 5 |
| **Fecha Llegada** | DateTime | >= fecha actual |

#### Validaciones Backend (PHP)
```php
// Nombre: 3-5 palabras
$palabras = array_filter(explode(' ', $nombreCompleto));
if (count($palabras) < 3 || count($palabras) > 5) {
    $errores[] = "El nombre debe tener entre 3 y 5 palabras.";
}

// DNI: 8 dígitos
if (!preg_match('/^\d{8}$/', $dni)) {
    $errores[] = "El DNI debe contener exactamente 8 números.";
}

// Rango de tiempo
if ($tipoHabitacion === 'Momentaneo' && ($diasHospedaje < 3 || $diasHospedaje > 12)) {
    $errores[] = "Momentáneo: 3 a 12 horas.";
}

// Costo múltiplo de 5
if ($costo <= 0 || ($costo % 5) != 0) {
    $errores[] = "El costo debe ser múltiplo de 5.";
}

// Fecha futura
if (strtotime($fechaHoraLlegada) < strtotime($fechaActual)) {
    $errores[] = "La fecha no puede ser anterior al momento actual.";
}
```

#### Validaciones Frontend (JavaScript)
```javascript
// Capitalización automática
function capitalizarNombre(input) {
    let palabras = input.value.split(' ');
    for (let i = 0; i < palabras.length; i++) {
        palabras[i] = palabras[i].charAt(0).toUpperCase() + 
                      palabras[i].slice(1).toLowerCase();
    }
    input.value = palabras.join(' ');
}

// Ajuste dinámico de label
function ajustarTipoHospedaje() {
    if (tipo === 'Momentaneo') {
        label.textContent = "Horas de Hospedaje";
        input.min = "3";
        input.max = "12";
    } else {
        label.textContent = "Días de Hospedaje";
        input.min = "1";
        input.max = "7";
    }
}

// Validación de fecha mínima
const minFecha = new Date(ahora.getTime() - ahora.getTimezoneOffset() * 60000)
    .toISOString().slice(0, 16);
inputFecha.min = minFecha;
```

#### Inserción en Base de Datos
```sql
INSERT INTO Reservas 
(TipoHabitacion, NombreCompleto, DNI, DiasHospedaje, FechaHoraLlegada, Costo) 
VALUES (:tipoHabitacion, :nombreCompleto, :dni, :diasHospedaje, :fechaHoraLlegada, :costo)
```

#### Mensajes de Respuesta
- **Éxito:** "✅ Reserva guardada correctamente."
- **Error:** "❌ Error al guardar: [detalle]"
- Mostrados en div con estilos condicionales

---

### 4.4 Nuevo Registro / Check-In (`/Administrador/nuevoRegistro.php`)

#### Funcionalidad Principal
Proceso de check-in para convertir una reserva en un registro activo.

#### Modos de Operación
1. **Desde Reserva:** Recibe parámetros GET con datos pre-llenados
2. **Registro Directo:** Formulario vacío para walk-ins

#### Campos del Formulario
| Campo | Fuente | Auto-llenado |
|-------|--------|--------------|
| **Tipo Alojamiento** | GET/Manual | ✓ |
| **Nombre Completo** | GET/Manual | ✓ |
| **DNI** | GET/Manual | ✓ |
| **Número Habitación** | GET/Manual | ✓ (desde reserva) |
| **Días Hospedaje** | GET/Manual | ✓ (desde reserva) |
| **Monto** | GET/Manual | ✓ |
| **Observaciones** | GET/Manual | ✓ (ServicioEspecial) |
| **Fecha Ingreso** | Auto | Fecha/hora actual |

#### Proceso de Check-In
1. **Validación de Datos:** Similar a nuevaReserva.php
2. **Verificación de Habitación:**
   - Query: `SELECT Estado FROM Habitaciones WHERE NumeroHabitacion = ?`
   - Debe estar 'Disponible'
3. **Inserción en Registros:**
   ```sql
   INSERT INTO Registros 
   (TipoAlojamiento, NombreCompleto, DNI, NumeroHabitacion, 
    DiasHospedaje, FechaHoraIngreso, Monto, Observaciones)
   VALUES (...)
   ```
4. **Actualización de Habitación:**
   ```sql
   UPDATE Habitaciones 
   SET Estado = 'Ocupado' 
   WHERE NumeroHabitacion = ?
   ```
5. **Actualización de Reserva (si aplica):**
   ```sql
   UPDATE [Reservas o ReservasClientes]
   SET Estado = 'Registrada'
   WHERE IdReserva = ?
   ```

#### Validaciones Especiales
- **Habitación Disponible:** No puede estar ocupada
- **DNI Único:** No puede haber otro registro activo con mismo DNI
- **Nombre Único:** No puede haber otro huésped activo con mismo nombre

---

### 4.5 Registro de Huéspedes (`/Administrador/registroHuespedes.php`)

#### Funcionalidad Principal
Historial completo de todos los registros (activos e históricos).

#### Columnas Mostradas
| Columna | Descripción |
|---------|-------------|
| ID | IdRegistro |
| Tipo | TipoAlojamiento |
| Huésped | NombreCompleto |
| DNI | Documento |
| Habitación | NumeroHabitacion |
| Días | DiasHospedaje |
| Ingreso | FechaHoraIngreso |
| Salida | FechaHoraSalida (NULL si activo) |
| Monto | Costo total |
| Estado | Activo / Finalizado |

#### Filtros Disponibles
- Por tipo de alojamiento
- Por estado (Activo/Finalizado)
- Por rango de fechas
- Búsqueda por nombre o DNI

#### Acciones
- **Ver Detalle:** Modal con información completa
- **Imprimir:** Generación de comprobante (si implementado)

---

### 4.6 Huéspedes Actuales (`/Administrador/huespedesActuales.php`)

#### Funcionalidad Principal
Lista de huéspedes con check-in activo (FechaHoraSalida IS NULL).

#### Información Mostrada
- Datos del huésped
- Habitación asignada
- Tiempo de estadía transcurrido
- Tiempo restante hasta check-out
- Observaciones/servicios especiales

#### Cálculo de Tiempo Restante
```php
$tipo = $reg['TipoAlojamiento'];
$fechaIngreso = new DateTime($reg['FechaHoraIngreso']);

if ($tipo === 'Momentaneo') {
    $fechaLimite = (clone $fechaIngreso)->modify('+3 hours');
} else {
    $fechaLimite = new DateTime($fechaIngreso->format('Y-m-d') . ' 12:00:00');
    $fechaLimite->modify('+1 day');
}

$segundosRestantes = $fechaLimite->getTimestamp() - $ahora->getTimestamp();
```

#### Indicadores Visuales
- **Verde:** Más de 1 hora restante
- **Amarillo:** Menos de 1 hora restante
- **Rojo:** Tiempo expirado

#### Acciones Disponibles
- **Check-Out:** Redirección a `checkOut.php`
- **Ver Detalle:** Modal con información completa

---

### 4.7 Check-Out (`/Administrador/checkOut.php`)

#### Funcionalidad Principal
Proceso de salida de huéspedes y liberación de habitaciones.

#### Proceso
1. **Recepción de IdRegistro:** Parámetro GET
2. **Actualización de Registro:**
   ```sql
   UPDATE Registros 
   SET FechaHoraSalida = GETDATE() 
   WHERE IdRegistro = ?
   ```
3. **Liberación de Habitación:**
   ```sql
   UPDATE Habitaciones 
   SET Estado = 'Disponible' 
   WHERE NumeroHabitacion = (
       SELECT NumeroHabitacion FROM Registros WHERE IdRegistro = ?
   )
   ```
4. **Confirmación:** Mensaje de éxito
5. **Redirección:** Vuelta a huespedesActuales.php

#### Validaciones
- Registro debe existir
- FechaHoraSalida debe ser NULL
- Habitación debe estar Ocupada

---

### 4.8 Reservas Canceladas (`/Administrador/reservasCanceladas.php`)

#### Funcionalidad Principal
Historial de reservas canceladas por administradores.

#### Tabla de Origen
`ReservasCanceladas`

#### Columnas Mostradas
- IdReserva
- TipoHabitacion
- NombreCompleto
- DNI
- DiasHospedaje
- FechaHoraLlegada
- Costo
- **motivoCancelacion** (campo adicional)

#### Acciones
- Solo visualización (no hay acciones disponibles)
- Filtros por fecha y tipo

---

### 4.9 Reservas Canceladas Clientes (`/Administrador/reservasCanceladasClientes.php`)

#### Funcionalidad Principal
Historial de reservas canceladas creadas por clientes.

#### Tabla de Origen
`ReservasCanceladasClientes`

#### Diferencias con reservasCanceladas.php
- Incluye columna `NumeroHabitacion`
- Origen: ReservasClientes
- Mismo formato de visualización

---

### 4.10 Reportes (`/Administrador/reportes.php`)

#### Funcionalidad Principal
Dashboard con métricas y gráficos del hospedaje.

#### Métricas Mostradas
1. **Ingresos Totales:**
   - Suma de todos los montos de Registros
   - Query: `SELECT SUM(Monto) FROM Registros`

2. **Reservas Activas:**
   - Count de reservas con Estado != 'Cancelada'
   - Ambas tablas: Reservas + ReservasClientes

3. **Huéspedes Actuales:**
   - Count de Registros con FechaHoraSalida IS NULL

4. **Habitaciones Disponibles:**
   - Count de Habitaciones con Estado = 'Disponible'

#### Filtros de Fecha
- **Desde:** Input date
- **Hasta:** Input date
- **Nota:** Los filtros son visuales, los datos se cargan sin filtrar

#### Gráficos (Chart.js)
- Gráfico de barras: Ingresos por día
- Gráfico de líneas: Ocupación diaria
- Top días con más ventas

#### Contenedor con Altura Fija
```css
.chart-container {
    height: 300px;
    max-height: 300px;
}
```
Previene crecimiento infinito del gráfico.

---

### 4.11 Verificación de Disponibilidad (`/Administrador/verificar_disponibilidad.php`)

#### Funcionalidad Principal
API endpoint para validar disponibilidad de habitaciones antes de reservar.

#### Parámetros Recibidos (POST JSON)
```json
{
    "numeroHabitacion": "301",
    "tipoHabitacion": "Individual",
    "fechaLlegada": "2026-05-30 14:00:00",
    "diasHospedaje": 2
}
```

#### Lógica de Validación
1. **Consulta de Reservas Existentes:**
   ```sql
   SELECT * FROM Reservas 
   WHERE NumHabitacion = ? AND Estado != 'Cancelada'
   UNION
   SELECT * FROM ReservasClientes 
   WHERE NumeroHabitacion = ? AND Estado != 'Cancelada'
   ```

2. **Cálculo de Período Ocupado:**
   - **Diario:** FechaLlegada + DiasHospedaje días
   - **Momentaneo:** FechaLlegada + DiasHospedaje horas

3. **Detección de Solapamiento:**
   ```php
   $solapa = ($inicio1 < $fin2) && ($inicio2 < $fin1);
   ```

4. **Respuesta JSON:**
   ```json
   {
       "disponible": false,
       "mensaje": "La habitación está reservada del 2026-05-28 al 2026-05-30"
   }
   ```

#### Casos de Uso
- Llamado desde `nuevaReserva.php` (Admin)
- Llamado desde `reservar.php` (Cliente)
- Validación asíncrona antes de submit

---

### 4.12 Envío de Código (`/Administrador/enviar_codigo.php`)

#### Funcionalidad Principal
Genera y envía código de verificación para acceso a reportes.

#### Proceso
1. **Generación de Código:** 6 dígitos aleatorios
2. **Almacenamiento en Sesión:**
   ```php
   $_SESSION['codigo_reporte'] = $codigo;
   $_SESSION['codigo_timestamp'] = time();
   ```
3. **Envío por Email:** PHPMailer a `jhojanantezana96@gmail.com`
4. **Respuesta JSON:**
   ```json
   {
       "ok": true,
       "mensaje": "Código enviado"
   }
   ```

#### Configuración PHPMailer
```php
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'jhojanantezana96@gmail.com';
$mail->Password = 'mffv gefb imeo wrxp';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

---

### 4.13 Verificación de Código (`/Administrador/verificar_codigo.php`)

#### Funcionalidad Principal
Valida código ingresado contra sesión con timeout de 30 segundos.

#### Proceso
1. **Recepción de Código:** POST JSON
2. **Validación de Timeout:**
   ```php
   $tiempoTranscurrido = time() - $_SESSION['codigo_timestamp'];
   if ($tiempoTranscurrido > 30) {
       return ["ok" => false, "error" => "Código expirado"];
   }
   ```
3. **Comparación:**
   ```php
   if ($codigo == $_SESSION['codigo_reporte']) {
       return ["ok" => true];
   }
   ```
4. **Limpieza de Sesión:** Unset de variables temporales

---

### 4.14 Perfil Admin (`/Administrador/perfilAdmin.php`)

#### Funcionalidad Principal
Visualización y edición de datos del administrador.

#### Información Mostrada
- Nombre completo
- Correo electrónico
- Rol
- Fecha de registro (si disponible)

#### Acciones Disponibles
- **Editar Perfil:** Actualización de nombre
- **Cambiar Contraseña:** Con validación de contraseña actual
- **Cerrar Sesión:** Link a logout.php

---

## 5. MÓDULO DE CLIENTES

### 5.1 Dashboard Cliente (`/Clientes/inicioClientes.php`)

#### Funcionalidad Principal
Panel de inicio personalizado para clientes con acceso a servicios.

#### Secciones Principales
1. **Bienvenida:** Saludo personalizado con nombre del usuario
2. **Espacios Destacados:** Grid con tipos de habitaciones
3. **Programa de Fidelidad:** Beneficios por tipo de habitación
4. **Estado de Estadías:** Información de reservas activas

#### Espacios Destacados (Grid)
| Tipo | Imagen | Link |
|------|--------|------|
| **Matrimonial Premium** | Unsplash | habitaciones.php?filtro=matrimonial |
| **Individual** | Unsplash | habitaciones.php?filtro=individual |
| **Doble** | Unsplash | habitaciones.php?filtro=doble |

#### Programa de Fidelidad (Visible en Pantalla)
**Beneficios por Tipo de Habitación:**

1. **Momentáneas:**
   - Agua de cortesía en recepción

2. **Individual:**
   - Agua de cortesía
   - Pastel de bienvenida

3. **Doble:**
   - Agua de cortesía
   - Desayuno incluido

4. **Matrimonial:**
   - Cafés de cortesía
   - Acceso al comedor VIP

**Diseño:**
- Cards con bordes de colores
- Iconos Material Symbols
- Texto conciso
- Sin modal (visible directamente)

#### Botones de Acción
| Botón | Destino | Estilo |
|-------|---------|--------|
| **Nueva Reserva** | reservar.php | Amarillo, header |

---

### 5.2 Habitaciones (`/Clientes/habitaciones.php`)

#### Funcionalidad Principal
Catálogo visual de las 20 habitaciones disponibles con descripciones únicas.

#### Habitaciones del Sistema
**Total:** 20 habitaciones distribuidas en 4 tipos

| Rango | Tipo | Cantidad |
|-------|------|----------|
| 301-305 | Momentáneas | 5 |
| 401-405 | Individual | 5 |
| 501-505 | Doble | 5 |
| 601-605 | Matrimonial | 5 |

#### Descripciones Específicas por Habitación
Cada habitación tiene descripción única almacenada en array PHP:

```php
$descripcionesEspecificas = [
    '301' => 'Vista a la calle principal, ideal para estadías cortas...',
    '302' => 'Habitación con jacuzzi privado y ambiente moderno...',
    // ... 18 más
];
```

**Características únicas incluyen:**
- Vista a la calle/jardín
- Jacuzzi privado
- Balcón privado
- Terraza panorámica
- Amenities específicos
- Decoración temática

#### Modal de Especificaciones
**Trigger:** Botón "Especificaciones" en cada card

**Contenido del Modal:**
- Imagen de la habitación (Unsplash)
- Número de habitación
- Tipo de habitación
- Descripción específica
- Precio por noche/hora
- Botón "Reservar Ahora"

**Imágenes por Habitación:**
```php
$imagenesHabitaciones = [
    '301' => 'https://images.unsplash.com/photo-...',
    '302' => 'https://images.unsplash.com/photo-...',
    // URLs únicas para cada habitación
];
```

#### Funciones JavaScript
```javascript
abrirModal(numero, tipo, precio)  // Abre modal con datos
cerrarModal()                      // Cierra modal
```

---

### 5.3 Reservar Habitación (`/Clientes/reservar.php`)

#### Funcionalidad Principal
Formulario de reserva para clientes con validación de disponibilidad.

#### Campos del Formulario
| Campo | Tipo | Validación |
|-------|------|------------|
| **Tipo Habitación** | Select | Momentaneo/Individual/Doble/Matrimonial |
| **Número Habitación** | Select | Dinámico según tipo |
| **Nombre Completo** | Text | Auto-llenado desde sesión |
| **DNI** | Text | 8 dígitos |
| **Días/Horas** | Number | Según tipo |
| **Fecha Llegada** | DateTime | >= actual |
| **Costo** | Number | Múltiplo de 5 |
| **Servicio Especial** | Textarea | Opcional |

#### Carga Dinámica de Habitaciones
```javascript
function cargarHabitaciones() {
    const tipo = document.getElementById('tipoHabitacion').value;
    const rangos = {
        'Momentaneo': [301, 305],
        'Individual': [401, 405],
        'Doble': [501, 505],
        'Matrimonial': [601, 605]
    };
    // Genera options del rango correspondiente
}
```

#### Validación de Disponibilidad (Asíncrona)
```javascript
async function validarDisponibilidad() {
    const response = await fetch('verificar_disponibilidad.php', {
        method: 'POST',
        body: JSON.stringify({
            numeroHabitacion,
            tipoHabitacion,
            fechaLlegada,
            diasHospedaje
        })
    });
    
    const data = await response.json();
    
    if (!data.disponible) {
        alert(data.mensaje);
        return false;
    }
    return true;
}
```

#### Proceso de Reserva
1. Validación de campos
2. Verificación de disponibilidad (AJAX)
3. Si disponible: POST a `guardar_reserva.php`
4. Inserción en tabla `ReservasClientes`
5. Redirección a `misReservas.php`

---

### 5.4 Guardar Reserva (`/Clientes/guardar_reserva.php`)

#### Funcionalidad Principal
Endpoint para procesar y guardar reservas de clientes.

#### Datos Recibidos (POST)
- tipoHabitacion
- numeroHabitacion
- nombreCompleto
- dni
- diasHospedaje
- fechaHoraLlegada
- costo
- servicioEspecial (opcional)
- IdUsuario (desde sesión)

#### Inserción en BD
```sql
INSERT INTO ReservasClientes 
(TipoHabitacion, NumeroHabitacion, NombreCompleto, DNI, 
 DiasHospedaje, FechaHoraLlegada, Costo, ServicioEspecial, 
 IdUsuario, Estado)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')
```

#### Validaciones
- Todos los campos obligatorios presentes
- Disponibilidad de habitación (doble check)
- Usuario autenticado
- Formato de datos correcto

---

### 5.5 Mis Reservas (`/Clientes/misReservas.php`)

#### Funcionalidad Principal
Visualización de reservas del cliente autenticado.

#### Query Principal
```sql
SELECT * FROM ReservasClientes 
WHERE IdUsuario = ? 
ORDER BY FechaHoraLlegada DESC
```

#### Columnas Mostradas
- IdReserva
- TipoHabitacion
- NombreCompleto
- DNI
- DiasHospedaje
- FechaHoraLlegada
- Costo
- NumeroHabitacion
- ServicioEspecial

**Excluidos:** Estado, IdUsuario

#### Estados Visuales
- **Pendiente:** Badge verde
- **Registrada:** Badge azul
- **Cancelada:** Badge rojo (si implementado)

#### Acciones
- **Ver Detalle:** Modal con información completa
- **Cancelar:** Solo si estado = Pendiente

---

### 5.6 Servicios (`/Clientes/servicios.php`)

#### Funcionalidad Principal
Catálogo de servicios adicionales con solicitud al cuarto.

#### Servicios Disponibles
Cargados desde tabla `Servicios` en BD:
- Desayuno
- Almuerzo
- Cena
- Lavandería
- Room Service
- Spa
- Etc.

#### Modal de Solicitud
**Trigger:** Botón "Solicitar al Cuarto"

**Contenido:**
- Imagen del servicio (Unsplash)
- Nombre del servicio
- Descripción
- Precio
- Tiempo de entrega estimado
- Botón "Confirmar Pedido"

#### Mapeo de Imágenes
```php
$imagenesServicios = [
    'Desayuno' => 'https://images.unsplash.com/photo-breakfast...',
    'Almuerzo' => 'https://images.unsplash.com/photo-lunch...',
    'Cena' => 'https://images.unsplash.com/photo-dinner...',
    // ...
];
```

#### Tiempos de Entrega
```php
$tiemposEntrega = [
    'Desayuno' => 15,  // minutos
    'Almuerzo' => 25,
    'Cena' => 25,
    'Lavandería' => 120,
    'Room Service' => 10,
    // ...
];
```

#### Estados del Botón
1. **"Solicitar al Cuarto"** (inicial)
2. **"Procesando Pedido..."** (durante timer)
3. **"Pedir de Nuevo"** (después del timer)

#### Timer de Entrega
```javascript
function iniciarTimer(minutos) {
    let segundos = minutos * 60;
    const intervalo = setInterval(() => {
        segundos--;
        if (segundos <= 0) {
            clearInterval(intervalo);
            mostrarNotificacion('¡Tu pedido ha sido entregado!');
            cambiarEstadoBoton('Pedir de Nuevo');
        }
    }, 1000);
}
```

---

### 5.7 Chat (`/Clientes/chat.php`)

#### Funcionalidad Principal
Sistema de mensajería entre cliente y administración.

**Nota:** Archivo presente pero funcionalidad no completamente implementada en el análisis actual.

---

### 5.8 Perfil Cliente (`/Clientes/perfilCliente.php`)

#### Funcionalidad Principal
Gestión de datos personales del cliente.

#### Información Mostrada
- Nombre completo
- Correo electrónico
- Rol (Cliente)
- Estadísticas:
  - Total de reservas realizadas
  - Reservas activas
  - Última reserva

#### Acciones Disponibles
- **Editar Perfil:** Actualización de nombre
- **Cambiar Contraseña:** Con validación
- **Cerrar Sesión:** Link a cerrarSesion.php

---

## 6. BASE DE DATOS

### 6.1 Estructura General

**Motor:** Microsoft SQL Server  
**Base de Datos:** `BD_HOSPEDAJE`  
**Usuario:** `proyehosp`  
**Contraseña:** `Php12345!`

### 6.2 Tablas Principales

#### Tabla: `Usuarios`
```sql
CREATE TABLE Usuarios (
    IdUsuario INT PRIMARY KEY IDENTITY(1,1),
    Nombre NVARCHAR(100) NOT NULL,
    Correo NVARCHAR(100) NOT NULL,
    Contrasena NVARCHAR(255) NOT NULL,
    Rol NVARCHAR(20) NOT NULL CHECK (Rol IN ('admin', 'cliente'))
)
```

**Índices:**
- UNIQUE (Correo, Rol) -- Permite mismo correo con diferentes roles

---

#### Tabla: `Habitaciones`
```sql
CREATE TABLE Habitaciones (
    IdHabitacion INT PRIMARY KEY IDENTITY(1,1),
    NumeroHabitacion INT NOT NULL UNIQUE,
    TipoHabitacion NVARCHAR(50) NOT NULL,
    Estado NVARCHAR(20) NOT NULL DEFAULT 'Disponible',
    PrecioPorNoche DECIMAL(10,2)
)
```

**Estados posibles:**
- Disponible
- Ocupado
- Mantenimiento

**Tipos:**
- Momentaneo
- Individual
- Doble
- Matrimonial

---

#### Tabla: `Reservas`
```sql
CREATE TABLE Reservas (
    IdReserva INT PRIMARY KEY IDENTITY(1,1),
    TipoHabitacion NVARCHAR(50) NOT NULL,
    NombreCompleto NVARCHAR(150) NOT NULL,
    DNI NVARCHAR(8) NOT NULL,
    DiasHospedaje INT NOT NULL,
    FechaHoraLlegada DATETIME NOT NULL,
    Costo DECIMAL(10,2) NOT NULL,
    NumHabitacion INT,
    ServicioEspecial NVARCHAR(MAX),
    IdUsuario INT,
    Estado NVARCHAR(20) DEFAULT 'Pendiente'
)
```

**Creadas por:** Administradores

---

#### Tabla: `ReservasClientes`
```sql
CREATE TABLE ReservasClientes (
    IdReserva INT PRIMARY KEY IDENTITY(1,1),
    TipoHabitacion NVARCHAR(50) NOT NULL,
    NombreCompleto NVARCHAR(150) NOT NULL,
    DNI NVARCHAR(8) NOT NULL,
    DiasHospedaje INT NOT NULL,
    FechaHoraLlegada DATETIME NOT NULL,
    Costo DECIMAL(10,2) NOT NULL,
    NumHabitacion INT,
    NumeroHabitacion INT,
    ServicioEspecial NVARCHAR(MAX),
    IdUsuario INT NOT NULL,
    Estado NVARCHAR(20) DEFAULT 'Pendiente',
    FOREIGN KEY (IdUsuario) REFERENCES Usuarios(IdUsuario)
)
```

**Creadas por:** Clientes desde su panel

---

#### Tabla: `Registros`
```sql
CREATE TABLE Registros (
    IdRegistro INT PRIMARY KEY IDENTITY(1,1),
    TipoAlojamiento NVARCHAR(50) NOT NULL,
    NombreCompleto NVARCHAR(150) NOT NULL,
    DNI NVARCHAR(8) NOT NULL,
    NumeroHabitacion INT NOT NULL,
    DiasHospedaje INT NOT NULL,
    FechaHoraIngreso DATETIME NOT NULL DEFAULT GETDATE(),
    FechaHoraSalida DATETIME NULL,
    Monto DECIMAL(10,2) NOT NULL,
    Observaciones NVARCHAR(MAX)
)
```

**Representa:** Check-ins activos e históricos

---

#### Tabla: `ReservasCanceladas`
```sql
CREATE TABLE ReservasCanceladas (
    IdCancelacion INT PRIMARY KEY IDENTITY(1,1),
    IdReserva INT NOT NULL,
    TipoHabitacion NVARCHAR(50),
    NombreCompleto NVARCHAR(150),
    DNI NVARCHAR(8),
    DiasHospedaje INT,
    FechaHoraLlegada DATETIME,
    Costo DECIMAL(10,2),
    motivoCancelacion NVARCHAR(MAX),
    FechaCancelacion DATETIME DEFAULT GETDATE()
)
```

---

#### Tabla: `ReservasCanceladasClientes`
```sql
CREATE TABLE ReservasCanceladasClientes (
    IdCancelacion INT PRIMARY KEY IDENTITY(1,1),
    IdReserva INT NOT NULL,
    TipoHabitacion NVARCHAR(50),
    NombreCompleto NVARCHAR(150),
    DNI NVARCHAR(8),
    DiasHospedaje INT,
    FechaHoraLlegada DATETIME,
    Costo DECIMAL(10,2),
    NumeroHabitacion INT,
    motivoCancelacion NVARCHAR(MAX),
    FechaCancelacion DATETIME DEFAULT GETDATE()
)
```

---

#### Tabla: `Servicios`
```sql
CREATE TABLE Servicios (
    IdServicio INT PRIMARY KEY IDENTITY(1,1),
    NombreServicio NVARCHAR(100) NOT NULL,
    Descripcion NVARCHAR(MAX),
    Precio DECIMAL(10,2) NOT NULL,
    Disponible BIT DEFAULT 1
)
```

---

### 6.3 Relaciones

```
Usuarios (1) ----< (N) ReservasClientes
Habitaciones (1) ----< (N) Registros (por NumeroHabitacion)
```

**Nota:** Las tablas `Reservas` y `ReservasClientes` no tienen FK explícitas a Habitaciones para permitir flexibilidad en la asignación.

---

## 7. FUNCIONALIDADES FRONTEND

### 7.1 Framework CSS: TailwindCSS

#### Configuración
```javascript
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary": "#705d00",
                "primary-container": "#ffd600",
                "background": "#f9f9f9",
                "surface": "#f9f9f9",
                "error": "#ba1a1a",
                // ... más colores
            },
            fontFamily: {
                "inter": ["Inter", "sans-serif"]
            }
        }
    }
}
```

#### Clases Personalizadas
```css
.bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 2rem;
}

.custom-card-shadow {
    box-shadow: 0px 4px 20px rgba(0,0,0,0.05);
}

.content-transition {
    transition: all 0.3s ease;
}
```

---

### 7.2 Iconografía: Material Symbols

**CDN:** Google Fonts Material Symbols Outlined

**Uso:**
```html
<span class="material-symbols-outlined">home</span>
```

**Configuración:**
```css
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
```

**Iconos más usados:**
- `add_box` - Nuevo registro
- `calendar_month` - Reservas
- `groups` - Huéspedes
- `login` / `logout` - Check-in/out
- `delete` - Cancelar
- `print` - Reportes

---

### 7.3 Componentes Reutilizables

#### Header (Admin)
**Archivo:** `/Administrador/layout/header.php`

**Contenido:**
- Inclusión de seguridad.php
- Estilos TailwindCSS
- Fuentes (Inter, Material Symbols)
- Navbar con logo y menú
- Sidebar toggle

#### Footer (Admin)
**Archivo:** `/Administrador/layout/footer.php`

**Contenido:**
- Cierre de tags HTML
- Scripts de sidebar
- Script de prevención de retroceso
- Funciones JavaScript globales

#### Sidebar (Cliente)
**Archivo:** `/Clientes/layout/sidebar.php`

**Menú de navegación:**
- Inicio
- Habitaciones
- Mis Reservas
- Servicios
- Chat
- Perfil
- Cerrar Sesión

---

### 7.4 JavaScript Funcional

#### Validaciones de Formulario
```javascript
// Capitalización automática
function capitalizarNombre(input) {
    let palabras = input.value.split(' ');
    palabras = palabras.map(p => 
        p.charAt(0).toUpperCase() + p.slice(1).toLowerCase()
    );
    input.value = palabras.join(' ');
}

// Solo números
oninput="this.value = this.value.replace(/[^0-9]/g, '')"

// Validación de DNI
if (!/^\d{8}$/.test(dni)) {
    alert('DNI debe tener 8 dígitos');
    return false;
}
```

#### Fetch API (AJAX)
```javascript
async function verificarDisponibilidad() {
    const response = await fetch('verificar_disponibilidad.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    });
    const data = await response.json();
    return data;
}
```

#### Manejo de Modales
```javascript
function abrirModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}
```

---

## 8. SEGURIDAD

### 8.1 Autenticación y Sesiones

#### Inicio de Sesión Seguro
```php
session_start();
$_SESSION['id'] = $usuario['IdUsuario'];
$_SESSION['rol'] = $usuario['Rol'];
```

#### Verificación de Acceso
**Archivo:** `/Administrador/layout/seguridad.php`

```php
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}
```

#### Headers de Seguridad
```php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
```

---

### 8.2 Prevención de SQL Injection

#### Prepared Statements
```php
$stmt = $conn->prepare("SELECT * FROM Usuarios WHERE Correo = ? AND Contrasena = ?");
$stmt->execute([$correo, $contrasena]);
```

**Todas las queries usan PDO con placeholders.**

---

### 8.3 Validación de Datos

#### Backend (PHP)
- Validación de tipos de datos
- Rangos numéricos
- Formatos de texto (DNI, nombre)
- Fechas futuras

#### Frontend (JavaScript)
- Validación en tiempo real
- Prevención de caracteres inválidos
- Confirmaciones de acciones destructivas

---

### 8.4 Protección de Contraseñas

**Nota:** Las contraseñas se almacenan en **texto plano** en la BD actual.

**Recomendación de Mejora:**
```php
// Al registrar
$hash = password_hash($contrasena, PASSWORD_DEFAULT);

// Al validar
if (password_verify($contrasenaIngresada, $hashAlmacenado)) {
    // Login exitoso
}
```

---

## 9. INTEGRACIONES

### 9.1 PHPMailer

#### Configuración SMTP
```php
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'jhojanantezana96@gmail.com';
$mail->Password = 'mffv gefb imeo wrxp';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

#### Usos en el Sistema
1. **Registro de cuenta:** Código de verificación
2. **Recuperación de contraseña:** Código de recuperación
3. **Autorización admin:** Código adicional
4. **Acceso a reportes:** Código temporal

---

### 9.2 API OpenRouter (Gemini)

**Archivo:** `/config.php`

```php
define('OPENROUTER_API_KEY', 'sk-or-v1-d0efeeace58a8975...');
```

**Uso:** Integración con IA (posiblemente para chat o asistencia)

**Archivo relacionado:** `/testGemini.php`

---

## 10. CONCLUSIONES

### 10.1 Fortalezas del Sistema

1. **Arquitectura Modular:** Separación clara entre Admin y Cliente
2. **Doble Sistema de Reservas:** Flexibilidad operativa
3. **Validación de Disponibilidad:** Previene doble reserva
4. **Interfaz Moderna:** TailwindCSS con diseño profesional
5. **Gestión Completa:** Desde reserva hasta check-out
6. **Reportes en Tiempo Real:** Métricas actualizadas
7. **Sistema de Fidelidad:** Incentivos por tipo de habitación
8. **Notificaciones Email:** Comunicación automatizada

---

### 10.2 Áreas de Mejora Recomendadas

#### Seguridad
- [ ] Implementar hashing de contraseñas (bcrypt/Argon2)
- [ ] Agregar CSRF tokens en formularios
- [ ] Implementar rate limiting en login
- [ ] Sanitización adicional de inputs
- [ ] HTTPS obligatorio

#### Funcionalidad
- [ ] Sistema de pagos en línea
- [ ] Chat en tiempo real (WebSockets)
- [ ] Notificaciones push
- [ ] App móvil nativa
- [ ] Sistema de calificaciones

#### Base de Datos
- [ ] Agregar índices para optimización
- [ ] Implementar triggers para auditoría
- [ ] Stored procedures para lógica compleja
- [ ] Backup automatizado

#### UX/UI
- [ ] Modo oscuro
- [ ] Accesibilidad (ARIA labels)
- [ ] Internacionalización (i18n)
- [ ] PWA (Progressive Web App)

---

### 10.3 Métricas del Sistema

**Archivos PHP:** 35+  
**Tablas BD:** 8  
**Roles de Usuario:** 2  
**Habitaciones:** 20  
**Tipos de Habitación:** 4  
**Líneas de Código (estimado):** 8,000+  

---

### 10.4 Stack Tecnológico Completo

```
┌─────────────────────────────────────┐
│         FRONTEND                    │
│  - TailwindCSS 3.x                  │
│  - JavaScript ES6+                  │
│  - Material Symbols                 │
│  - Inter Font                       │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│         BACKEND                     │
│  - PHP 8.x                          │
│  - PDO (sqlsrv)                     │
│  - PHPMailer                        │
│  - Sessions                         │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│         DATABASE                    │
│  - Microsoft SQL Server             │
│  - BD_HOSPEDAJE                     │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│         SERVIDOR                    │
│  - Apache (XAMPP)                   │
│  - Windows Server                   │
└─────────────────────────────────────┘
```

---

## ANEXOS

### A. Flujo de Reserva Completo

```
Cliente → Login → Dashboard → Habitaciones → Reservar
    ↓
Validación Disponibilidad (AJAX)
    ↓
Guardar en ReservasClientes
    ↓
Email Confirmación (opcional)
    ↓
Admin ve en Panel Reservas
    ↓
Check-In (nuevoRegistro.php)
    ↓
Registro Activo + Habitación Ocupada
    ↓
Huésped en Casa
    ↓
Check-Out
    ↓
Habitación Disponible + Registro Histórico
```

---

### B. Convenciones de Código

#### Nombres de Variables
```php
$nombreCompleto  // camelCase
$fecha_hora      // snake_case (BD)
```

#### Nombres de Archivos
```
inicioAdmin.php      // camelCase
reservas.php         // lowercase
```

#### Clases CSS
```css
.bento-grid          /* kebab-case */
.custom-card-shadow  /* kebab-case */
```

---

### C. Comandos SQL Útiles

```sql
-- Ver todas las reservas activas
SELECT * FROM Reservas WHERE Estado != 'Cancelada';

-- Huéspedes actuales
SELECT * FROM Registros WHERE FechaHoraSalida IS NULL;

-- Habitaciones disponibles
SELECT * FROM Habitaciones WHERE Estado = 'Disponible';

-- Ingresos del mes
SELECT SUM(Monto) FROM Registros 
WHERE MONTH(FechaHoraIngreso) = MONTH(GETDATE());

-- Ocupación actual
SELECT 
    (SELECT COUNT(*) FROM Habitaciones WHERE Estado = 'Ocupado') * 100.0 / 
    (SELECT COUNT(*) FROM Habitaciones) AS PorcentajeOcupacion;
```

---

## FIN DEL INFORME

**Generado:** Mayo 28, 2026  
**Sistema:** Hospedaje Ayacucho v2.0  
**Autor:** Análisis Automatizado Completo  

---

**Nota Final:** Este informe documenta el estado actual del sistema. Para actualizaciones o consultas técnicas, contactar al equipo de desarrollo.

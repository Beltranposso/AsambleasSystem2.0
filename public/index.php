<?php
session_start();

// Mostrar errores para debug (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir funciones auxiliares globales
require_once '../core/helpers.php';
require_once '../core/Router.php';

// Incluir controladores reales
require_once '../controllers/AuthController.php';
require_once '../controllers/AdminController.php';
require_once '../controllers/CoordinatorController.php';
require_once '../controllers/OperatorController.php';
require_once '../controllers/VoterController.php';

$router = new Router();

// ================================
// RUTAS DE AUTENTICACIÓN
// ================================
$router->get('/', 'AuthController', 'login');
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'processLogin');  // Ruta POST para procesar login
$router->get('/logout', 'AuthController', 'logout');
$router->get('/debug', 'AuthController', 'debug'); // Para desarrollo

// ================================
// RUTAS DE DASHBOARD
// ================================
$router->get('/dashboard/admin', 'AdminController', 'dashboard');
$router->get('/dashboard/coordinador', 'CoordinatorController', 'dashboard');
$router->get('/dashboard/operador', 'OperatorController', 'dashboard');
$router->get('/dashboard/votante', 'VoterController', 'dashboard');

// ================================
// RUTAS DE ADMINISTRADOR
// ================================
// Gestión de Asambleas
$router->get('/admin/asambleas', 'AdminController', 'asambleas');
$router->get('/admin/asambleas/crear', 'AdminController', 'crearAsamblea');
$router->get('/admin/asambleas/editar/{id}', 'AdminController', 'editarAsamblea');
$router->get('/admin/asambleas/detalle/{id}', 'AdminController', 'detalleAsamblea');
$router->post('/admin/asambleas/guardar', 'AdminController', 'guardarAsamblea');
$router->post('/admin/asambleas/eliminar/{id}', 'AdminController', 'eliminarAsamblea');

// Gestión de Usuarios
$router->get('/admin/usuarios', 'AdminController', 'usuarios');
$router->get('/admin/usuarios/crear', 'AdminController', 'crearUsuario');
$router->get('/admin/usuarios/editar/{id}', 'AdminController', 'editarUsuario');
$router->post('/admin/usuarios/guardar', 'AdminController', 'guardarUsuario');
$router->post('/admin/usuarios/eliminar/{id}', 'AdminController', 'eliminarUsuario');
$router->post('/admin/usuarios/check-email', 'AdminController', 'checkEmail');
$router->post('/admin/usuarios/check-cedula', 'AdminController', 'checkCedula');

// Gestión de Coordinadores
$router->get('/admin/coordinadores', 'AdminController', 'coordinadores');
$router->get('/admin/coordinadores/crear', 'AdminController', 'crearCoordinador');
$router->get('/admin/coordinadores/editar/{id}', 'AdminController', 'editarCoordinador');
$router->post('/admin/coordinadores/guardar', 'AdminController', 'guardarCoordinador');

// Otras secciones administrativas
$router->get('/admin/conjuntos', 'AdminController', 'conjuntos');
$router->get('/admin/conjuntos/crear', 'AdminController', 'crearConjunto');
$router->post('/admin/conjuntos/guardar', 'AdminController', 'guardarConjunto');

$router->get('/admin/votaciones', 'AdminController', 'votaciones');
$router->get('/admin/reportes', 'AdminController', 'reportes');
$router->get('/admin/configuracion', 'AdminController', 'configuracion');
$router->post('/admin/configuracion/guardar', 'AdminController', 'guardarConfiguracion');


$router->get('/admin/debug-assembly', 'AdminController', 'debugAssembly');


// Gestión de usuarios por conjunto
$router->post('/admin/conjuntos/crear-usuario', 'AdminController', 'crearUsuarioConjunto');
$router->get('/admin/conjuntos/usuarios/{id}', 'AdminController', 'getConjuntoUsers');

// Estadísticas y reportes
$router->get('/admin/conjuntos/stats/{id}', 'AdminController', 'updateConjuntoStats');
$router->get('/admin/conjuntos/estadisticas/{id}', 'AdminController', 'getConjuntoStats');
$router->get('/admin/conjuntos/estadisticas-globales', 'AdminController', 'getGlobalStats');

// Exportación y reportes
$router->get('/admin/conjuntos/export', 'AdminController', 'exportConjuntos');
$router->get('/admin/conjuntos/reporte/{id}', 'AdminController', 'generateConjuntoReport');
$router->get('/admin/conjuntos/reporte-global', 'AdminController', 'generateGlobalReport');

// Eliminación de conjuntos
$router->post('/admin/conjuntos/eliminar/{id}', 'AdminController', 'eliminarConjunto');

// ================================
// RUTAS DE COORDINADOR
// ================================
$router->get('/coordinador/asambleas', 'CoordinatorController', 'asambleas');
$router->get('/coordinador/asistencia', 'CoordinatorController', 'asistencia');
$router->get('/coordinador/participantes', 'CoordinatorController', 'participantes');
$router->get('/coordinador/quorum', 'CoordinatorController', 'quorum');
$router->get('/coordinador/votaciones', 'CoordinatorController', 'votaciones');
$router->get('/coordinador/reportes/participacion', 'CoordinatorController', 'reportesParticipacion');
// Rutas para crear usuarios dentro de asambleas
$router->post('/coordinador/crear-operador', 'CoordinatorController', 'crearOperador');
$router->post('/coordinador/crear-votante', 'CoordinatorController', 'crearVotante');
// AGREGAR ESTA LÍNEA:
$router->get('/coordinador/proyeccion', 'CoordinatorController', 'proyeccion');
// Rutas AJAX para validaciones
$router->post('/coordinador/ajax/check-email', 'CoordinatorController', 'checkEmailAvailable');
$router->post('/coordinador/ajax/check-cedula', 'CoordinatorController', 'checkCedulaAvailable');
$router->get('/coordinador/ajax/available-representantes', 'CoordinatorController', 'getAvailableRepresentantes');

// Rutas para gestión de participantes
$router->post('/coordinador/toggle-attendance', 'CoordinatorController', 'toggleAttendance');
$router->post('/coordinador/update-participant-role', 'CoordinatorController', 'updateParticipantRole');
$router->get('/coordinador/export-participants', 'CoordinatorController', 'exportParticipants');

// Rutas para notificaciones
$router->post('/coordinador/send-credentials', 'CoordinatorController', 'sendCredentials');
$router->post('/coordinador/reset-password', 'CoordinatorController', 'resetUserPassword');
// Rutas POST para acciones del coordinador
$router->post('/coordinador/registrar-asistencia', 'CoordinatorController', 'registrarAsistencia');
$router->post('/coordinador/agregar-participante', 'CoordinatorController', 'agregarParticipante');
$router->post('/coordinador/actualizar-coeficiente', 'CoordinatorController', 'updateCoeficiente');
$router->post('/coordinador/remover-participante/{id}', 'CoordinatorController', 'removeParticipant');

// Rutas para votaciones
$router->post('/coordinador/crear-votacion', 'CoordinatorController', 'crearVotacion');
$router->post('/coordinador/abrir-votacion/{id}', 'CoordinatorController', 'abrirVotacion');
$router->post('/coordinador/cerrar-votacion/{id}', 'CoordinatorController', 'cerrarVotacion');
$router->get('/coordinador/resultados-votacion/{id}', 'CoordinatorController', 'resultadosVotacion');
$router->post('/coordinador/duplicar-votacion/{id}', 'CoordinatorController', 'duplicarVotacion');
$router->post('/coordinador/eliminar-votacion/{id}', 'CoordinatorController', 'eliminarVotacion');
$router->get('/coordinador/exportar-resultados/{id}', 'CoordinatorController', 'exportarResultados');
$router->get('/coordinador/test-votaciones', 'CoordinatorController', 'testVotaciones');
$router->get('/coordinador/debug-route', 'CoordinatorController', 'debugRoute');
$router->post('/coordinador/debug-route', 'CoordinatorController', 'debugRoute');
$router->get('/coordinador/test-simple', 'CoordinatorController', 'testSimple');
$router->get('/coordinador/crear-votacion', 'CoordinatorController', 'mostrarCrearVotacion');
// Rutas para activación/finalización de asambleas
$router->post('/coordinador/activar-asamblea/{id}', 'CoordinatorController', 'activarAsamblea');
$router->post('/coordinador/finalizar-asamblea/{id}', 'CoordinatorController', 'finalizarAsamblea');
$router->get('/coordinador/debug-crear-votacion', 'CoordinatorController', 'debugCrearVotacion');
$router->post('/coordinador/debug-crear-votacion', 'CoordinatorController', 'debugCrearVotacion');
$router->get('/coordinador/ping-test', 'CoordinatorController', 'pingTest');
// Rutas para exportación de reportes
$router->get('/coordinador/reportes/export', 'CoordinatorController', 'exportReport');

$router->get('/coordinador/quorum-data', 'CoordinatorController', 'getQuorumDataAjax');
$router->post('/coordinador/simulate-quorum', 'CoordinatorController', 'simulateQuorum');
$router->get('/coordinador/quorum-history', 'CoordinatorController', 'getQuorumHistory');
$router->get('/coordinador/quorum-time-stats', 'CoordinatorController', 'getQuorumTimeStats');

// Exportación de reportes de quórum
$router->get('/coordinador/export-quorum-report', 'CoordinatorController', 'exportQuorumReport');

// Control de estado de asamblea
$router->post('/coordinador/toggle-assembly-status', 'CoordinatorController', 'toggleAssemblyStatus');
// ================================
// RUTAS DE OPERADOR
// ================================
$router->get('/operador/dashboard', 'OperatorController', 'dashboard');
$router->get('/operador', 'OperatorController', 'dashboard'); // Ruta alternativa

// Rutas existentes (ya las tienes)
$router->get('/operador/asambleas', 'OperatorController', 'asambleas');
$router->get('/operador/registro-asistencia', 'OperatorController', 'registroAsistencia');
$router->get('/operador/verificar-usuarios', 'OperatorController', 'verificarUsuarios');
$router->get('/operador/coeficientes', 'OperatorController', 'coeficientes');
$router->get('/operador/estado-pagos', 'OperatorController', 'estadoPagos');
$router->get('/operador/reportes/asistencia', 'OperatorController', 'reportesAsistencia');

// ================================
// FUNCIONALIDADES DE ASISTENCIA
// ================================

// Registro de entrada y salida
$router->post('/operador/registrar-entrada', 'OperatorController', 'registrarEntrada');
$router->post('/operador/registrar-salida', 'OperatorController', 'registrarSalida');

// ================================
// FUNCIONALIDADES DE VERIFICACIÓN
// ================================

// Rutas existentes (ya las tienes)
$router->post('/operador/verificar-usuario', 'OperatorController', 'verificarUsuario');
$router->post('/operador/agregar-nota', 'OperatorController', 'agregarNota');

// ================================
// FUNCIONALIDADES PARA COEFICIENTES
// ================================

// Ruta existente (ya la tienes)
$router->post('/operador/actualizar-coeficiente', 'OperatorController', 'actualizarCoeficiente');

// ================================
// FUNCIONALIDADES PARA ESTADO DE PAGOS
// ================================

// Ruta existente (ya la tienes)
$router->post('/operador/actualizar-estado-pago', 'OperatorController', 'actualizarEstadoPago');

// ================================
// RUTAS AJAX
// ================================

// Detalles de asamblea
$router->post('/operador/ajax/assembly-details', 'OperatorController', 'getAssemblyDetailsAjax');

// Búsqueda de participantes
$router->post('/operador/ajax/buscar-participante', 'OperatorController', 'buscarParticipante');

// Validación de participantes
$router->post('/operador/ajax/validar-participante', 'OperatorController', 'validateParticipant');

// Actualización de asistencia
$router->post('/operador/ajax/update-attendance', 'OperatorController', 'updateAttendanceStatus');

// Estadísticas de asistencia
$router->get('/operador/ajax/attendance-stats', 'OperatorController', 'getAttendanceStats');

// Log de actividad
$router->get('/operador/ajax/attendance-log', 'OperatorController', 'getAttendanceLogAjax');

// ================================
// EXPORTACIONES Y REPORTES
// ================================

// Exportación de asistencia
$router->get('/operador/export-attendance', 'OperatorController', 'exportAttendance');

// Lista de asistencia para impresión
$router->get('/operador/print-attendance', 'OperatorController', 'printAttendanceList');

// Rutas existentes adaptadas
$router->get('/operador/export-users', 'OperatorController', 'exportUsers');
$router->get('/operador/reportes/export', 'OperatorController', 'exportReport');

// ================================
// RUTAS ADICIONALES RECOMENDADAS
// ================================

// Exportación con parámetros específicos
$router->get('/operador/export-attendance-params', 'OperatorController', 'exportAttendanceWithParams');

// Reporte de participantes por asamblea
$router->get('/operador/reportes/participantes', 'OperatorController', 'reportesParticipantes');

// Estadísticas generales del operador
$router->get('/operador/estadisticas', 'OperatorController', 'estadisticasOperador');

// ================================
// RUTAS DE VOTANTE
// ================================
$router->get('/votante/dashboard', 'VoterController', 'dashboard');
$router->get('/votante', 'VoterController', 'dashboard'); // Ruta alternativa

// Gestión de perfil
$router->get('/votante/perfil', 'VoterController', 'profile');
$router->post('/votante/actualizar-perfil', 'VoterController', 'actualizarPerfil');
$router->post('/votante/cambiar-password', 'VoterController', 'cambiarPassword');

// Gestión de votaciones
$router->get('/votante/mis-votos', 'VoterController', 'myVotings');
$router->get('/votante/mis-votaciones', 'VoterController', 'myVotings'); // Alias
$router->get('/votante/votar/{id}', 'VoterController', 'votar');
$router->post('/votante/procesar-voto', 'VoterController', 'procesarVoto');

// Otras secciones
$router->get('/votante/asambleas', 'VoterController', 'asambleas');
$router->get('/votante/historial', 'VoterController', 'historial');
$router->get('/votante/documentos', 'VoterController', 'documentos');


$router->post('/votante/ajax/voting-details', 'VoterController', 'getVotingDetailsAjax');
$router->post('/votante/ajax/check-vote-status', 'VoterController', 'checkVoteStatus');



// ================================
// RUTAS COMUNES
// ================================
$router->get('/perfil', 'AuthController', 'perfil');
$router->get('/configuracion', 'AuthController', 'configuracion');

$router->dispatch();
?>
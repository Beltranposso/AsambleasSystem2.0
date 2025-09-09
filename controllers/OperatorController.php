<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class OperatorController {
    private $db;
    private $auth;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new AuthController();
        
        // Verificar que el usuario sea operador
        $this->auth->requireRole('operador');
    }
    
    // ================================
    // DASHBOARD
    // ================================
    
    public function dashboard() {
        $userRole = 'operador';
        $assignedAssemblies = $this->getAssignedAssemblies();
        $activeAssemblies = $this->getActiveAssignedAssemblies();
        $pendingValidations = $this->getPendingValidations();
        $todayStats = $this->getTodayOperatorStats();
        $alerts = $this->getOperatorAlerts();
        
        require_once __DIR__ . '/../views/operator/dashboard.php';
    }

    
    // ================================
    // GESTIÓN DE ASAMBLEAS
    // ================================
 public function asambleas() {
    $userRole = 'operador';
    
    // Obtener datos con debug
    $assemblies = $this->getAssignedAssemblies();
    $activeAssemblies = $this->getActiveAssignedAssemblies();
    
    // Debug: Log para verificar datos
    error_log("DEBUG - Operador ID: " . ($_SESSION['user_id'] ?? 'NO_SESSION'));
    error_log("DEBUG - Total assemblies encontradas: " . count($assemblies));
    error_log("DEBUG - Active assemblies encontradas: " . count($activeAssemblies));
    
    // Debug: Mostrar primera asamblea si existe
    if (!empty($assemblies)) {
        error_log("DEBUG - Primera asamblea: " . json_encode($assemblies[0]));
    }
    
    // Si está en modo debug, mostrar información adicional
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        echo "<h3>Debug Information</h3>";
        echo "<p>Operador ID: " . ($_SESSION['user_id'] ?? 'NO_SESSION') . "</p>";
        echo "<p>Total assemblies: " . count($assemblies) . "</p>";
        echo "<p>Active assemblies: " . count($activeAssemblies) . "</p>";
        echo "<pre>" . print_r($assemblies, true) . "</pre>";
        exit;
    }
    
    require_once __DIR__ . '/../views/operator/asambleas.php';
}
    
    // ================================
    // REGISTRO DE ASISTENCIA
    // ================================
    public function registroAsistencia() {
        $userRole = 'operador';
        $assemblyId = $_GET['asamblea'] ?? null;
        $assemblies = $this->getActiveAssignedAssemblies();
        $participants = [];
        $assembly = null;
        $attendanceLog = [];
        
        if ($assemblyId) {
            $assembly = $this->getAssemblyDetails($assemblyId);
            $participants = $this->getAssemblyParticipants($assemblyId);
            $attendanceLog = $this->getAttendanceLog($assemblyId);
        }
        
        require_once __DIR__ . '/../views/operator/registro_asistencia.php';
    }
    
    // ================================
    // VERIFICACIÓN DE USUARIOS
    // ================================
    public function verificarUsuarios() {
        $userRole = 'operador';
        $assemblyId = $_GET['asamblea'] ?? null;
        $searchTerm = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $users = $this->getUsersForVerification($searchTerm, $status);
        $pendingVerifications = $this->getPendingUserVerifications();
        
        require_once __DIR__ . '/../views/operator/verificar_usuarios.php';
    }
    
    // ================================
    // GESTIÓN DE COEFICIENTES
    // ================================
    public function coeficientes() {
        $userRole = 'operador';
        $assemblyId = $_GET['asamblea'] ?? null;
        $assemblies = $this->getAssignedAssemblies();
        $participants = [];
        $assembly = null;
        $coeficienteSummary = null;
        
        if ($assemblyId) {
            $assembly = $this->getAssemblyDetails($assemblyId);
            $participants = $this->getAssemblyParticipants($assemblyId);
            $coeficienteSummary = $this->getCoeficienteSummary($assemblyId);
        }
        
        require_once __DIR__ . '/../views/operator/coeficientes.php';
    }
    
    // ================================
    // ESTADO DE PAGOS
    // ================================
    public function estadoPagos() {
        $userRole = 'operador';
        $conjunto = $_GET['conjunto'] ?? null;
        $status = $_GET['status'] ?? '';
        $conjuntos = $this->getConjuntos();
        $voters = $this->getVotersPaymentStatus($conjunto, $status);
        $paymentStats = $this->getPaymentStats($conjunto);
        
        require_once __DIR__ . '/../views/operator/estado_pagos.php';
    }
    
    // ================================
    // REPORTES DE ASISTENCIA
    // ================================
    public function reportesAsistencia() {
        $userRole = 'operador';
        $assemblyId = $_GET['asamblea'] ?? null;
        $dateFrom = $_GET['fecha_desde'] ?? date('Y-m-01');
        $dateTo = $_GET['fecha_hasta'] ?? date('Y-m-t');
        
        $assemblies = $this->getAssignedAssemblies();
        $attendanceReport = $this->getAttendanceReport($dateFrom, $dateTo, $assemblyId);
        $attendanceStats = $this->getAttendanceReportStats($dateFrom, $dateTo, $assemblyId);
        
        require_once __DIR__ . '/../views/operator/reportes_asistencia.php';
    }
    
    // ================================
    // EXPORTACIÓN DE ASISTENCIA
    // ================================
    public function exportAttendance() {
        $assemblyId = $_GET['assembly_id'] ?? 0;
        
        try {
            $assembly = $this->getAssemblyDetails($assemblyId);
            $participants = $this->getAssemblyParticipants($assemblyId);
            
            if (!$assembly) {
                $_SESSION['error'] = 'Asamblea no encontrada';
                header('Location: /Asambleas/public/operador/asambleas');
                exit;
            }
            
            // Configurar headers para descarga de Excel
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="asistencia_' . $assemblyId . '_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            
            // Generar contenido Excel simple
            echo '<table border="1">';
            echo '<tr>';
            echo '<th>Nombre</th>';
            echo '<th>Apellido</th>';
            echo '<th>Cédula</th>';
            echo '<th>Apartamento</th>';
            echo '<th>Coeficiente</th>';
            echo '<th>Estado Pagos</th>';
            echo '<th>Asistencia</th>';
            echo '<th>Hora Entrada</th>';
            echo '<th>Hora Salida</th>';
            echo '</tr>';
            
            foreach ($participants as $participant) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($participant['nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($participant['apellido']) . '</td>';
                echo '<td>' . htmlspecialchars($participant['cedula']) . '</td>';
                echo '<td>' . htmlspecialchars($participant['apartamento'] ?? 'N/A') . '</td>';
                echo '<td>' . number_format($participant['coeficiente_asignado'], 4) . '</td>';
                echo '<td>' . htmlspecialchars($participant['estado_pagos'] ?? 'N/A') . '</td>';
                echo '<td>' . ($participant['asistencia'] ? 'Presente' : 'Ausente') . '</td>';
                echo '<td>' . ($participant['hora_ingreso'] ? date('H:i:s', strtotime($participant['hora_ingreso'])) : '') . '</td>';
                echo '<td>' . ($participant['hora_salida'] ? date('H:i:s', strtotime($participant['hora_salida'])) : '') . '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al exportar: ' . $e->getMessage();
            header('Location: /Asambleas/public/operador/asambleas');
            exit;
        }
    }
    
    // ================================
    // MÉTODOS PARA PROCESAR ACCIONES
    // ================================
    
    public function registrarEntrada() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/operador/registro-asistencia');
            exit;
        }
        
        try {
            $assemblyId = $_POST['asamblea_id'] ?? 0;
            $userId = $_POST['usuario_id'] ?? 0;
            $coeficiente = $_POST['coeficiente'] ?? 0;
            
            // Verificar que el usuario no esté ya registrado como presente
            $existing = $this->db->fetch("
                SELECT asistencia FROM participantes_asamblea 
                WHERE asamblea_id = ? AND usuario_id = ?
            ", [$assemblyId, $userId]);
            
            if ($existing && $existing['asistencia'] == 1) {
                $_SESSION['error'] = 'El participante ya está registrado como presente';
            } else {
                $this->recordAttendanceEntry($assemblyId, $userId, $coeficiente);
                $this->logActivity($assemblyId, $userId, 'entrada', 'Entrada registrada por operador');
                $_SESSION['success'] = 'Entrada registrada correctamente';
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al registrar entrada: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/operador/registro-asistencia?asamblea=' . ($assemblyId ?? ''));
        exit;
    }
    
    public function registrarSalida() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/operador/registro-asistencia');
            exit;
        }
        
        try {
            $assemblyId = $_POST['asamblea_id'] ?? 0;
            $userId = $_POST['usuario_id'] ?? 0;
            
            $this->recordAttendanceExit($assemblyId, $userId);
            $this->logActivity($assemblyId, $userId, 'salida', 'Salida registrada por operador');
            $_SESSION['success'] = 'Salida registrada correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al registrar salida: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/operador/registro-asistencia?asamblea=' . ($assemblyId ?? ''));
        exit;
    }
    
    public function verificarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/operador/verificar-usuarios');
            exit;
        }
        
        try {
            $userId = $_POST['usuario_id'] ?? 0;
            $verified = isset($_POST['verified']) ? 1 : 0;
            $notes = $_POST['notes'] ?? '';
            
            $this->updateUserVerification($userId, $verified, $notes);
            $_SESSION['success'] = 'Usuario verificado correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al verificar usuario: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/operador/verificar-usuarios');
        exit;
    }
    
    public function actualizarCoeficiente() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/operador/coeficientes');
            exit;
        }
        
        try {
            $assemblyId = $_POST['asamblea_id'] ?? 0;
            $userId = $_POST['usuario_id'] ?? 0;
            $newCoeficiente = $_POST['coeficiente'] ?? 0;
            $reason = $_POST['razon'] ?? '';
            
            $this->updateParticipantCoeficiente($assemblyId, $userId, $newCoeficiente, $reason);
            $_SESSION['success'] = 'Coeficiente actualizado correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar coeficiente: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/operador/coeficientes?asamblea=' . ($assemblyId ?? ''));
        exit;
    }
    
    public function actualizarEstadoPago() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/operador/estado-pagos');
            exit;
        }
        
        try {
            $voterId = $_POST['voter_id'] ?? 0;
            $newStatus = $_POST['estado_pagos'] ?? 'al_dia';
            $notes = $_POST['notas'] ?? '';
            
            $this->updatePaymentStatus($voterId, $newStatus, $notes);
            $_SESSION['success'] = 'Estado de pagos actualizado correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar estado de pagos: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/operador/estado-pagos');
        exit;
    }
    
    public function agregarNota() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/operador/verificar-usuarios');
            exit;
        }
        
        try {
            $userId = $_POST['usuario_id'] ?? 0;
            $noteType = $_POST['note_type'] ?? '';
            $noteContent = $_POST['note_content'] ?? '';
            $isImportant = isset($_POST['is_important']) ? 1 : 0;
            
            $this->addUserNote($userId, $noteType, $noteContent, $isImportant);
            $_SESSION['success'] = 'Nota agregada correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al agregar nota: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/operador/verificar-usuarios');
        exit;
    }
    
    // ================================
    // MÉTODOS AJAX
    // ================================
    
    public function getAssemblyDetailsAjax() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $assemblyId = $input['assembly_id'] ?? 0;
        
        try {
            $assembly = $this->getAssemblyDetails($assemblyId);
            $participants = $this->getAssemblyParticipants($assemblyId);
            $stats = $this->getAssemblyStats($assemblyId);
            
            if (!$assembly) {
                echo json_encode(['error' => 'Asamblea no encontrada']);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'assembly' => $assembly,
                'participants' => $participants,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al obtener detalles: ' . $e->getMessage()]);
        }
        exit;
    }
    
    public function buscarParticipante() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $searchTerm = $input['search'] ?? '';
        $assemblyId = $input['assembly_id'] ?? 0;
        
        if (strlen($searchTerm) < 2) {
            echo json_encode(['results' => []]);
            exit;
        }
        
        try {
            $participants = $this->db->fetchAll("
                SELECT pa.usuario_id, u.nombre, u.apellido, u.cedula, 
                       v.apartamento, pa.coeficiente_asignado, pa.asistencia
                FROM participantes_asamblea pa
                JOIN usuarios u ON pa.usuario_id = u.id
                LEFT JOIN votantes v ON u.id = v.usuario_id
                WHERE pa.asamblea_id = ? 
                AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.cedula LIKE ? OR v.apartamento LIKE ?)
                ORDER BY u.nombre, u.apellido
                LIMIT 10
            ", [
                $assemblyId,
                "%$searchTerm%", "%$searchTerm%", "%$searchTerm%", "%$searchTerm%"
            ]);
            
            echo json_encode(['results' => $participants]);
            
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error en búsqueda: ' . $e->getMessage()]);
        }
        exit;
    }
    
    public function validateParticipant() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $cedula = $input['cedula'] ?? '';
        $assemblyId = $input['assembly_id'] ?? 0;
        
        try {
            $participant = $this->db->fetch("
                SELECT pa.*, u.nombre, u.apellido, u.cedula, u.email, u.telefono,
                       v.apartamento, v.coeficiente, v.estado_pagos
                FROM participantes_asamblea pa
                JOIN usuarios u ON pa.usuario_id = u.id
                LEFT JOIN votantes v ON u.id = v.usuario_id
                WHERE pa.asamblea_id = ? AND u.cedula = ?
            ", [$assemblyId, $cedula]);
            
            if ($participant) {
                echo json_encode([
                    'found' => true,
                    'participant' => $participant,
                    'can_register' => $participant['asistencia'] == 0
                ]);
            } else {
                echo json_encode([
                    'found' => false,
                    'message' => 'Participante no encontrado en esta asamblea'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al validar: ' . $e->getMessage()]);
        }
        exit;
    }
    
    public function updateAttendanceStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $assemblyId = $input['assembly_id'] ?? 0;
        $userId = $input['user_id'] ?? 0;
        $action = $input['action'] ?? '';
        $coeficiente = $input['coeficiente'] ?? 0;
        
        try {
            if ($action === 'entrada') {
                $this->recordAttendanceEntry($assemblyId, $userId, $coeficiente);
                $this->logActivity($assemblyId, $userId, 'entrada', 'Entrada registrada');
                echo json_encode(['success' => true, 'message' => 'Entrada registrada']);
            } elseif ($action === 'salida') {
                $this->recordAttendanceExit($assemblyId, $userId);
                $this->logActivity($assemblyId, $userId, 'salida', 'Salida registrada');
                echo json_encode(['success' => true, 'message' => 'Salida registrada']);
            } else {
                echo json_encode(['error' => 'Acción no válida']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
        exit;
    }
    
    public function getAttendanceStats() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        $assemblyId = $_GET['assembly_id'] ?? 0;
        
        try {
            $stats = $this->getAssemblyStats($assemblyId);
            $recentEntries = $this->getRecentEntries($assemblyId, 5);
            
            echo json_encode([
                'stats' => $stats,
                'recent_entries' => $recentEntries
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al obtener estadísticas: ' . $e->getMessage()]);
        }
        exit;
    }
    
    public function getAttendanceLogAjax() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        $assemblyId = $_GET['assembly_id'] ?? 0;
        $limit = $_GET['limit'] ?? 20;
        
        try {
            $log = $this->db->fetchAll("
                SELECT al.*, u.nombre, u.apellido, u.cedula
                FROM activity_log al
                JOIN usuarios u ON al.usuario_id = u.id
                WHERE al.asamblea_id = ? 
                ORDER BY al.created_at DESC
                LIMIT ?
            ", [$assemblyId, $limit]);
            
            echo json_encode(['log' => $log]);
            
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al obtener log: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // ================================
    // IMPRESIÓN Y REPORTES
    // ================================
    
    public function printAttendanceList() {
        $assemblyId = $_GET['assembly_id'] ?? 0;
        
        try {
            $assembly = $this->getAssemblyDetails($assemblyId);
            $participants = $this->getAssemblyParticipants($assemblyId);
            $stats = $this->getAssemblyStats($assemblyId);
            
            if (!$assembly) {
                $_SESSION['error'] = 'Asamblea no encontrada';
                header('Location: /Asambleas/public/operador/registro-asistencia');
                exit;
            }
            
            // Generar HTML para impresión
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Lista de Asistencia - <?php echo htmlspecialchars($assembly['titulo']); ?></title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .info { margin-bottom: 15px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #000; padding: 5px; text-align: left; }
                    th { background-color: #f0f0f0; }
                    .stats { margin-top: 20px; }
                    @media print { 
                        .no-print { display: none; } 
                        body { margin: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2><?php echo htmlspecialchars($assembly['titulo']); ?></h2>
                    <p><?php echo htmlspecialchars($assembly['conjunto_nombre']); ?></p>
                    <p>Fecha: <?php echo date('d/m/Y H:i', strtotime($assembly['fecha_inicio'])); ?></p>
                </div>
                
                <div class="info">
                    <strong>Estadísticas:</strong>
                    Total Registrados: <?php echo $stats['total_registrados']; ?> | 
                    Presentes: <?php echo $stats['total_presentes']; ?> | 
                    Ausentes: <?php echo $stats['total_ausentes']; ?> |
                    % Asistencia: <?php echo $stats['total_registrados'] > 0 ? round(($stats['total_presentes'] / $stats['total_registrados']) * 100, 1) : 0; ?>%
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre Completo</th>
                            <th>Cédula</th>
                            <th>Apartamento</th>
                            <th>Coeficiente</th>
                            <th>Estado</th>
                            <th>Hora Entrada</th>
                            <th>Firma</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($participants as $participant): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($participant['nombre'] . ' ' . $participant['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($participant['cedula']); ?></td>
                                <td><?php echo htmlspecialchars($participant['apartamento'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format($participant['coeficiente_asignado'], 4); ?></td>
                                <td><?php echo $participant['asistencia'] ? 'Presente' : 'Ausente'; ?></td>
                                <td><?php echo $participant['hora_ingreso'] ? date('H:i', strtotime($participant['hora_ingreso'])) : ''; ?></td>
                                <td style="width: 100px;"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="stats">
                    <p><strong>Generado el:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                    <p><strong>Operador:</strong> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Sistema'); ?></p>
                </div>
                
                <div class="no-print">
                    <button onclick="window.print()">Imprimir</button>
                    <button onclick="window.close()">Cerrar</button>
                </div>
                
                <script>
                    window.onload = function() { window.print(); }
                </script>
            </body>
            </html>
            <?php
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al generar lista: ' . $e->getMessage();
            header('Location: /Asambleas/public/operador/registro-asistencia');
            exit;
        }
    }
    
    // ================================
    // MÉTODOS PRIVADOS PARA DATOS
    // ================================
    
  private function getAssignedAssemblies() {
    try {
        $operatorId = $_SESSION['user_id'] ?? 0;
        
        if ($operatorId === 0) {
            error_log("ERROR - No hay user_id en la sesión");
            return [];
        }
        
        // Consulta mejorada con más información de debug
        $query = "
            SELECT a.*, c.nombre as conjunto_nombre,
                   COUNT(pa.id) as total_participantes,
                   SUM(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) as total_asistentes,
                   ap.tipo_personal
            FROM asambleas a 
            JOIN conjuntos c ON a.conjunto_id = c.id 
            LEFT JOIN asignaciones_personal ap ON a.id = ap.asamblea_id
            LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
            WHERE ap.usuario_id = ? AND ap.tipo_personal = 'operador'
            GROUP BY a.id, ap.id
            ORDER BY a.fecha_inicio DESC
        ";
        
        $result = $this->db->fetchAll($query, [$operatorId]);
        
        error_log("DEBUG - Query ejecutada: " . $query);
        error_log("DEBUG - Parámetros: " . json_encode([$operatorId]));
        error_log("DEBUG - Resultado count: " . count($result));
        
        return $result;
        
    } catch (Exception $e) {
        error_log("ERROR getting assigned assemblies: " . $e->getMessage());
        error_log("ERROR Stack trace: " . $e->getTraceAsString());
        return [];
    }
}

    
    private function getActiveAssignedAssemblies() {
        try {
            $operatorId = $_SESSION['user_id'] ?? 0;
            return $this->db->fetchAll("
                SELECT a.*, c.nombre as conjunto_nombre 
                FROM asambleas a 
                JOIN conjuntos c ON a.conjunto_id = c.id 
                JOIN asignaciones_personal ap ON a.id = ap.asamblea_id
                WHERE ap.usuario_id = ? AND ap.tipo_personal = 'operador'
                AND a.estado = 'activa'
                ORDER BY a.fecha_inicio DESC
            ", [$operatorId]);
        } catch (Exception $e) {
            error_log("Error getting active assigned assemblies: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAssemblyDetails($id) {
        try {
            return $this->db->fetch("
                SELECT a.*, c.nombre as conjunto_nombre, c.direccion,
                       u.nombre as admin_nombre, u.apellido as admin_apellido
                FROM asambleas a 
                JOIN conjuntos c ON a.conjunto_id = c.id 
                LEFT JOIN usuarios u ON a.administrador_id = u.id
                WHERE a.id = ?
            ", [$id]);
        } catch (Exception $e) {
            error_log("Error getting assembly details: " . $e->getMessage());
            return null;
        }
    }
    
    private function getAssemblyParticipants($assemblyId) {
        try {
            return $this->db->fetchAll("
                SELECT pa.*, u.nombre, u.apellido, u.cedula, u.email, u.telefono,
                       v.apartamento, v.coeficiente as coef_propietario, v.estado_pagos
                FROM participantes_asamblea pa
                JOIN usuarios u ON pa.usuario_id = u.id
                LEFT JOIN votantes v ON u.id = v.usuario_id
                WHERE pa.asamblea_id = ?
                ORDER BY u.nombre, u.apellido
            ", [$assemblyId]);
        } catch (Exception $e) {
            error_log("Error getting assembly participants: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAttendanceLog($assemblyId) {
        try {
            return $this->db->fetchAll("
                SELECT al.*, u.nombre, u.apellido, u.cedula
                FROM activity_log al
                JOIN usuarios u ON al.usuario_id = u.id
                WHERE al.asamblea_id = ? 
                ORDER BY al.created_at DESC
                LIMIT 50
            ", [$assemblyId]);
        } catch (Exception $e) {
            error_log("Error getting attendance log: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAssemblyStats($assemblyId) {
        try {
            return $this->db->fetch("
                SELECT 
                    COUNT(*) as total_registrados,
                    SUM(CASE WHEN asistencia = 1 THEN 1 ELSE 0 END) as total_presentes,
                    SUM(CASE WHEN asistencia = 0 THEN 1 ELSE 0 END) as total_ausentes,
                    SUM(coeficiente_asignado) as coeficiente_total,
                    SUM(CASE WHEN asistencia = 1 THEN coeficiente_asignado ELSE 0 END) as coeficiente_presente,
                    AVG(coeficiente_asignado) as coeficiente_promedio
                FROM participantes_asamblea 
                WHERE asamblea_id = ?
            ", [$assemblyId]);
        } catch (Exception $e) {
            error_log("Error getting assembly stats: " . $e->getMessage());
            return [
                'total_registrados' => 0,
                'total_presentes' => 0,
                'total_ausentes' => 0,
                'coeficiente_total' => 0,
                'coeficiente_presente' => 0,
                'coeficiente_promedio' => 0
            ];
        }
    }
    
    private function getRecentEntries($assemblyId, $limit = 10) {
        try {
            return $this->db->fetchAll("
                SELECT pa.hora_ingreso, u.nombre, u.apellido, u.cedula, 
                       pa.coeficiente_asignado
                FROM participantes_asamblea pa
                JOIN usuarios u ON pa.usuario_id = u.id
                WHERE pa.asamblea_id = ? AND pa.asistencia = 1
                ORDER BY pa.hora_ingreso DESC
                LIMIT ?
            ", [$assemblyId, $limit]);
        } catch (Exception $e) {
            error_log("Error getting recent entries: " . $e->getMessage());
            return [];
        }
    }
    
    private function getUsersForVerification($searchTerm = '', $status = '') {
        try {
            $whereClause = "WHERE u.tipo_usuario = 'votante'";
            $params = [];
            
            if (!empty($searchTerm)) {
                $whereClause .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.cedula LIKE ?)";
                $searchParam = "%$searchTerm%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if (!empty($status)) {
                $whereClause .= " AND u.activo = ?";
                $params[] = $status === 'activo' ? 1 : 0;
            }
            
            return $this->db->fetchAll("
                SELECT u.*, v.apartamento, v.coeficiente, v.estado_pagos, c.nombre as conjunto_nombre
                FROM usuarios u
                LEFT JOIN votantes v ON u.id = v.usuario_id
                LEFT JOIN conjuntos c ON v.conjunto_id = c.id
                $whereClause
                ORDER BY u.nombre, u.apellido
            ", $params);
        } catch (Exception $e) {
            error_log("Error getting users for verification: " . $e->getMessage());
            return [];
        }
    }
    
    private function getPendingUserVerifications() {
        try {
            return $this->db->fetch("
                SELECT COUNT(*) as total
                FROM usuarios u
                WHERE u.tipo_usuario = 'votante' AND u.activo = 0
            ");
        } catch (Exception $e) {
            error_log("Error getting pending verifications: " . $e->getMessage());
            return ['total' => 0];
        }
    }
    
    private function getCoeficienteSummary($assemblyId) {
        try {
            return $this->db->fetch("
                SELECT 
                    COUNT(*) as total_participantes,
                    SUM(coeficiente_asignado) as coeficiente_total,
                    AVG(coeficiente_asignado) as coeficiente_promedio,
                    MIN(coeficiente_asignado) as coeficiente_minimo,
                    MAX(coeficiente_asignado) as coeficiente_maximo
                FROM participantes_asamblea
                WHERE asamblea_id = ?
            ", [$assemblyId]);
        } catch (Exception $e) {
            error_log("Error getting coeficiente summary: " . $e->getMessage());
            return null;
        }
    }
    
    private function getConjuntos() {
        try {
            return $this->db->fetchAll("SELECT * FROM conjuntos ORDER BY nombre");
        } catch (Exception $e) {
            error_log("Error getting conjuntos: " . $e->getMessage());
            return [];
        }
    }
    
    private function getVotersPaymentStatus($conjunto = null, $status = '') {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($conjunto) {
                $whereClause .= " AND v.conjunto_id = ?";
                $params[] = $conjunto;
            }
            
            if ($status) {
                $whereClause .= " AND v.estado_pagos = ?";
                $params[] = $status;
            }
            
            return $this->db->fetchAll("
                SELECT v.*, u.nombre, u.apellido, u.cedula, u.telefono, u.email,
                       c.nombre as conjunto_nombre
                FROM votantes v
                JOIN usuarios u ON v.usuario_id = u.id
                JOIN conjuntos c ON v.conjunto_id = c.id
                $whereClause
                ORDER BY c.nombre, u.nombre, u.apellido
            ", $params);
        } catch (Exception $e) {
            error_log("Error getting voters payment status: " . $e->getMessage());
            return [];
        }
    }
    
    private function getPaymentStats($conjunto = null) {
        try {
            $whereClause = $conjunto ? "WHERE v.conjunto_id = ?" : "";
            $params = $conjunto ? [$conjunto] : [];
            
            return $this->db->fetch("
                SELECT 
                    COUNT(*) as total_votantes,
                    SUM(CASE WHEN v.estado_pagos = 'al_dia' THEN 1 ELSE 0 END) as al_dia,
                    SUM(CASE WHEN v.estado_pagos = 'mora' THEN 1 ELSE 0 END) as en_mora,
                    SUM(CASE WHEN v.estado_pagos = 'suspendido' THEN 1 ELSE 0 END) as suspendidos
                FROM votantes v
                $whereClause
            ", $params);
        } catch (Exception $e) {
            error_log("Error getting payment stats: " . $e->getMessage());
            return [
                'total_votantes' => 0,
                'al_dia' => 0,
                'en_mora' => 0,
                'suspendidos' => 0
            ];
        }
    }
    
    private function getAttendanceReport($dateFrom, $dateTo, $assemblyId = null) {
        try {
            $whereClause = "WHERE DATE(a.fecha_inicio) BETWEEN ? AND ?";
            $params = [$dateFrom, $dateTo];
            
            if ($assemblyId) {
                $whereClause .= " AND a.id = ?";
                $params[] = $assemblyId;
            }
            
            // Solo mostrar asambleas asignadas al operador
            $operatorId = $_SESSION['user_id'] ?? 0;
            $whereClause .= " AND EXISTS (
                SELECT 1 FROM asignaciones_personal ap 
                WHERE ap.asamblea_id = a.id AND ap.usuario_id = ? AND ap.tipo_personal = 'operador'
            )";
            $params[] = $operatorId;
            
            return $this->db->fetchAll("
                SELECT a.titulo, a.fecha_inicio, c.nombre as conjunto_nombre,
                       COUNT(pa.id) as total_registrados,
                       SUM(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) as total_asistentes,
                       ROUND(SUM(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(pa.id), 2) as porcentaje_asistencia
                FROM asambleas a
                JOIN conjuntos c ON a.conjunto_id = c.id
                LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                $whereClause
                GROUP BY a.id
                ORDER BY a.fecha_inicio DESC
            ", $params);
        } catch (Exception $e) {
            error_log("Error getting attendance report: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAttendanceReportStats($dateFrom, $dateTo, $assemblyId = null) {
        try {
            $whereClause = "WHERE DATE(a.fecha_inicio) BETWEEN ? AND ?";
            $params = [$dateFrom, $dateTo];
            
            if ($assemblyId) {
                $whereClause .= " AND a.id = ?";
                $params[] = $assemblyId;
            }
            
            $operatorId = $_SESSION['user_id'] ?? 0;
            $whereClause .= " AND EXISTS (
                SELECT 1 FROM asignaciones_personal ap 
                WHERE ap.asamblea_id = a.id AND ap.usuario_id = ? AND ap.tipo_personal = 'operador'
            )";
            $params[] = $operatorId;
            
            return $this->db->fetch("
                SELECT 
                    COUNT(DISTINCT a.id) as total_asambleas,
                    COUNT(pa.id) as total_registros,
                    SUM(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) as total_asistencias,
                    ROUND(AVG(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) * 100, 2) as promedio_asistencia
                FROM asambleas a
                LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                $whereClause
            ", $params);
        } catch (Exception $e) {
            error_log("Error getting attendance report stats: " . $e->getMessage());
            return [
                'total_asambleas' => 0,
                'total_registros' => 0,
                'total_asistencias' => 0,
                'promedio_asistencia' => 0
            ];
        }
    }
    
    private function getPendingValidations() {
        try {
            $operatorId = $_SESSION['user_id'] ?? 0;
            return $this->db->fetch("
                SELECT COUNT(*) as pending 
                FROM participantes_asamblea pa
                JOIN asambleas a ON pa.asamblea_id = a.id
                JOIN asignaciones_personal ap ON a.id = ap.asamblea_id
                WHERE ap.usuario_id = ? AND ap.tipo_personal = 'operador'
                AND pa.asistencia = 0 AND a.estado = 'activa'
            ", [$operatorId]);
        } catch (Exception $e) {
            error_log("Error getting pending validations: " . $e->getMessage());
            return ['pending' => 0];
        }
    }
    
    private function getTodayOperatorStats() {
        try {
            $operatorId = $_SESSION['user_id'] ?? 0;
            return $this->db->fetch("
                SELECT 
                    COUNT(DISTINCT a.id) as asambleas_hoy,
                    COUNT(DISTINCT CASE WHEN a.estado = 'activa' THEN a.id END) as asambleas_activas,
                    COUNT(DISTINCT pa.usuario_id) as participantes_registrados
                FROM asambleas a
                JOIN asignaciones_personal ap ON a.id = ap.asamblea_id
                LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                WHERE ap.usuario_id = ? AND ap.tipo_personal = 'operador'
                AND DATE(a.fecha_inicio) = CURDATE()
            ", [$operatorId]);
        } catch (Exception $e) {
            error_log("Error getting today operator stats: " . $e->getMessage());
            return [
                'asambleas_hoy' => 0,
                'asambleas_activas' => 0,
                'participantes_registrados' => 0
            ];
        }
    }
    
    private function getOperatorAlerts() {
        try {
            $alerts = [];
            $operatorId = $_SESSION['user_id'] ?? 0;
            
            // Verificar asambleas activas sin registro de asistencia
            $activeWithoutAttendance = $this->db->fetch("
                SELECT COUNT(*) as total
                FROM asambleas a
                JOIN asignaciones_personal ap ON a.id = ap.asamblea_id
                WHERE ap.usuario_id = ? AND ap.tipo_personal = 'operador'
                AND a.estado = 'activa'
                AND NOT EXISTS (
                    SELECT 1 FROM participantes_asamblea pa 
                    WHERE pa.asamblea_id = a.id AND pa.asistencia = 1
                )
            ", [$operatorId]);
            
            if ($activeWithoutAttendance && $activeWithoutAttendance['total'] > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'message' => "Hay {$activeWithoutAttendance['total']} asamblea(s) activa(s) sin registro de asistencia"
                ];
            }
            
            return $alerts;
        } catch (Exception $e) {
            error_log("Error getting operator alerts: " . $e->getMessage());
            return [];
        }
    }
    
    // ================================
    // MÉTODOS PARA ACCIONES
    // ================================
    
    private function recordAttendanceEntry($assemblyId, $userId, $coeficiente) {
        return $this->db->execute("
            UPDATE participantes_asamblea 
            SET asistencia = 1, coeficiente_asignado = ?, hora_ingreso = NOW()
            WHERE asamblea_id = ? AND usuario_id = ?
        ", [$coeficiente, $assemblyId, $userId]);
    }
    
    private function recordAttendanceExit($assemblyId, $userId) {
        return $this->db->execute("
            UPDATE participantes_asamblea 
            SET asistencia = 0, 
                hora_salida = NOW()
            WHERE asamblea_id = ? AND usuario_id = ?
        ", [$assemblyId, $userId]);
    }
    
    private function updateUserVerification($userId, $verified, $notes) {
        return $this->db->execute("
            UPDATE usuarios 
            SET activo = ?, updated_at = NOW()
            WHERE id = ?
        ", [$verified, $userId]);
    }
    
    private function updateParticipantCoeficiente($assemblyId, $userId, $newCoeficiente, $reason) {
        return $this->db->execute("
            UPDATE participantes_asamblea 
            SET coeficiente_asignado = ?, 
                registro_coeficiente = CONCAT(COALESCE(registro_coeficiente, ''), 
                    DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), ': ', ?, '; ')
            WHERE asamblea_id = ? AND usuario_id = ?
        ", [$newCoeficiente, $reason, $assemblyId, $userId]);
    }
    
    private function updatePaymentStatus($voterId, $newStatus, $notes) {
        return $this->db->execute("
            UPDATE votantes 
            SET estado_pagos = ?
            WHERE id = ?
        ", [$newStatus, $voterId]);
    }
    
    private function addUserNote($userId, $noteType, $noteContent, $isImportant) {
        try {
            // Crear tabla de notas si no existe
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS user_notes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    usuario_id INT,
                    operador_id INT,
                    note_type VARCHAR(50),
                    note_content TEXT,
                    is_important BOOLEAN DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_usuario (usuario_id),
                    INDEX idx_operador (operador_id)
                )
            ");
            
            $operatorId = $_SESSION['user_id'] ?? 0;
            
            return $this->db->execute("
                INSERT INTO user_notes (usuario_id, operador_id, note_type, note_content, is_important)
                VALUES (?, ?, ?, ?, ?)
            ", [$userId, $operatorId, $noteType, $noteContent, $isImportant]);
            
        } catch (Exception $e) {
            error_log("Error adding user note: " . $e->getMessage());
            return false;
        }
    }
    
    private function logActivity($assemblyId, $userId, $action, $description) {
        try {
            $operatorId = $_SESSION['user_id'] ?? 0;
            
            // Crear tabla de log si no existe
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS activity_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    asamblea_id INT,
                    usuario_id INT,
                    operador_id INT,
                    action VARCHAR(50),
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_asamblea (asamblea_id),
                    INDEX idx_usuario (usuario_id),
                    INDEX idx_operador (operador_id)
                )
            ");
            
            return $this->db->execute("
                INSERT INTO activity_log (asamblea_id, usuario_id, operador_id, action, description)
                VALUES (?, ?, ?, ?, ?)
            ", [$assemblyId, $userId, $operatorId, $action, $description]);
            
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }
}

?>
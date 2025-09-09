<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class CoordinatorController {
    private $db;
    private $auth;
    public function debugRoutes() {
    echo "<h2>Debug de Rutas</h2>";
    echo "<p>REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "</p>";
    echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
    echo "<p>POST Data:</p>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    echo "<p>GET Data:</p>";
    echo "<pre>" . print_r($_GET, true) . "</pre>";
    exit;
}
    public function __construct() {
        $this->db = new Database();
        $this->auth = new AuthController();
        
        // Verificar que el usuario sea coordinador
        $this->auth->requireRole('coordinador');
    }
    
    // ================================
    // DASHBOARD
    // ================================
    public function dashboard() {
        $userRole = 'coordinador';
        $myAssemblies = $this->getMyAssemblies();
        $upcomingAssemblies = $this->getUpcomingAssemblies();
        $todayStats = $this->getTodayStats();
        $weeklyStats = $this->getWeeklyStats();
        $alerts = $this->getCoordinatorAlerts();
        
        require_once __DIR__ . '/../views/coordinator/dashboard.php';
    }
    /**
 * Vista de proyección para mostrar quórum y votaciones en tiempo real
 */
public function proyeccion() {
    $assemblyId = $_GET['asamblea'] ?? null;
    
    if (!$assemblyId) {
        $_SESSION['error'] = 'Debe especificar una asamblea para la vista de proyección';
        header('Location: /Asambleas/public/coordinador/asambleas');
        exit;
    }
    
    try {
        // Verificar permisos
        $this->validateCoordinatorPermissions($assemblyId);
        
        // Obtener datos de la asamblea
        $assembly = $this->getAssemblyDetails($assemblyId);
        if (!$assembly) {
            throw new Exception('Asamblea no encontrada');
        }
        
        // Obtener datos del quórum
        $quorumData = $this->getQuorumData($assemblyId);
        
        // Obtener votaciones de la asamblea
        $votaciones = $this->getAssemblyVotaciones($assemblyId);
        
        // Obtener votación activa (si existe)
        $votacionActiva = null;
        $resultadosVotacion = [];
        
        foreach ($votaciones as $votacion) {
            if ($votacion['estado'] === 'abierta') {
                $votacionActiva = $votacion;
                // Obtener resultados en tiempo real
                $resultadosVotacion = $this->getVotacionResultados($votacion['id']);
                break;
            }
        }
        
        // Incluir la vista de proyección (sin layouts)
        require_once __DIR__ . '/../views/coordinator/proyeccion.php';
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: /Asambleas/public/coordinador/asambleas');
        exit;
    }
}

/**
 * Obtener resultados de votación en tiempo real
 */

private function getVotacionResultados($votacionId) {
    try {
        return $this->db->fetchAll("
            SELECT 
                ov.id as opcion_id,
                ov.opcion as opcion_texto,
                ov.orden_display,
                COUNT(vo.id) as total_votos,
                COALESCE(SUM(vo.coeficiente_voto), 0) as coeficiente_total
            FROM opciones_votacion ov
            LEFT JOIN votos vo ON ov.id = vo.opcion_id
            WHERE ov.votacion_id = ?
            GROUP BY ov.id
            ORDER BY ov.orden_display
        ", [$votacionId]);
    } catch (Exception $e) {
        error_log("Error getting voting results: " . $e->getMessage());
        return [];
    }
}
    // ================================
    // GESTIÓN DE ASAMBLEAS
    // ================================
    public function asambleas() {
        $userRole = 'coordinador';
        $assemblies = $this->getMyAssemblies();
        $activeAssemblies = $this->getActiveAssemblies();
        
        require_once __DIR__ . '/../views/coordinator/asambleas.php';
    }
    
    // ================================
    // CONTROL DE ASISTENCIA
    // ================================
    public function asistencia() {
        $userRole = 'coordinador';
        $assemblyId = $_GET['asamblea'] ?? null;
        $assemblies = $this->getActiveAssemblies();
        $participants = [];
        $assembly = null;
        
        if ($assemblyId) {
            $assembly = $this->getAssemblyDetails($assemblyId);
            $participants = $this->getAssemblyParticipants($assemblyId);
        }
        
        require_once __DIR__ . '/../views/coordinator/asistencia.php';
    }

public function mostrarCrearVotacion() {
    $userRole = 'coordinador';
    $assemblyId = $_GET['asamblea'] ?? null;
    $assembly = null;
    
    if ($assemblyId) {
        try {
            // Verificar permisos
            $this->validateCoordinatorPermissions($assemblyId);
            $assembly = $this->getAssemblyDetails($assemblyId);
            
            if (!$assembly) {
                $_SESSION['error'] = 'Asamblea no encontrada';
                header('Location: /Asambleas/public/coordinador/votaciones');
                exit;
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Asambleas/public/coordinador/votaciones');
            exit;
        }
    } else {
        // Si no hay asamblea, mostrar error
        $_SESSION['error'] = 'Debe especificar una asamblea para crear votación';
        header('Location: /Asambleas/public/coordinador/votaciones');
        exit;
    }
    
    require_once __DIR__ . '/../views/coordinator/crear_votacion.php';
}

public function debugCrearVotacion() {
    header('Content-Type: application/json; charset=utf-8');
    
    $debug = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'session_user' => $_SESSION['user_id'] ?? 'NO_SESSION',
        'post_data' => $_POST,
        'get_data' => $_GET,
        'files_data' => $_FILES,
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'NOT_SET'
    ];
    
    // Si es POST, intentar procesar como si fuera crear votación
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $debug['validation'] = [
            'asamblea_id' => $_POST['asamblea_id'] ?? 'MISSING',
            'titulo' => $_POST['titulo'] ?? 'MISSING',
            'opciones' => $_POST['opciones'] ?? 'MISSING',
            'opciones_count' => is_array($_POST['opciones'] ?? null) ? count($_POST['opciones']) : 0
        ];
        
        // Test de base de datos
        try {
            $assemblyId = intval($_POST['asamblea_id'] ?? 0);
            if ($assemblyId > 0) {
                $assembly = $this->db->fetch("SELECT * FROM asambleas WHERE id = ?", [$assemblyId]);
                $debug['database'] = [
                    'assembly_found' => $assembly ? 'YES' : 'NO',
                    'assembly_data' => $assembly
                ];
            }
        } catch (Exception $e) {
            $debug['database_error'] = $e->getMessage();
        }
    }
    
    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}

// Método simple para test de conectividad
public function pingTest() {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'OK',
        'message' => 'Controlador funcionando correctamente',
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit;
}
   public function crearOperador() {
    // ===== DEBUG INICIAL =====
    file_put_contents(__DIR__ . '/../debug.log', 
        "\n=== CREAR OPERADOR DEBUG ===" . 
        "\nTiempo: " . date('Y-m-d H:i:s') . 
        "\nMétodo: " . $_SERVER['REQUEST_METHOD'] .
        "\nURI: " . $_SERVER['REQUEST_URI'] .
        "\nPOST: " . print_r($_POST, true) . 
        "\n========================\n", 
        FILE_APPEND
    );
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        file_put_contents(__DIR__ . '/../debug.log', "ERROR: Método no es POST\n", FILE_APPEND);
        header('Location: /Asambleas/public/coordinador/participantes');
        exit;
    }
    
    try {
        $assemblyId = $_POST['asamblea_id'] ?? 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $cedula = trim($_POST['cedula'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        
        file_put_contents(__DIR__ . '/../debug.log', 
            "Datos recibidos OPERADOR:\n" .
            "Assembly ID: $assemblyId\n" .
            "Nombre: $nombre\n" .
            "Apellido: $apellido\n" .
            "Cedula: $cedula\n" .
            "Email: $email\n" .
            "Password length: " . strlen($password) . "\n",
            FILE_APPEND
        );
        
        // Validaciones
        if (empty($nombre) || empty($apellido) || empty($cedula) || empty($email) || empty($password)) {
            throw new Exception('Todos los campos obligatorios deben ser completados');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del email no es válido');
        }
        
        // ===== DEBUG DETALLADO DE DUPLICADOS =====
       file_put_contents(__DIR__ . '/../debug.log', "=== TEST CONEXIÓN ===\n", FILE_APPEND);

// Test 1: Query directa simple
$connection = $this->db->getConnection();
$testResult = $connection->query("SELECT COUNT(*) as total FROM usuarios");
$testRow = $testResult->fetch_assoc();
file_put_contents(__DIR__ . '/../debug.log', "Total usuarios (directo): " . $testRow['total'] . "\n", FILE_APPEND);

// Test 2: Buscar específicamente este email
$emailTest = $connection->query("SELECT id, email FROM usuarios WHERE email = '$email'");
if ($emailTest && $emailTest->num_rows > 0) {
    $emailRow = $emailTest->fetch_assoc();
    file_put_contents(__DIR__ . '/../debug.log', "Email encontrado (directo): " . print_r($emailRow, true) . "\n", FILE_APPEND);
} else {
    file_put_contents(__DIR__ . '/../debug.log', "Email NO encontrado (directo)\n", FILE_APPEND);
}

// Test 3: Con prepared statement manual
$stmt = $connection->prepare("SELECT id, email FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    file_put_contents(__DIR__ . '/../debug.log', "Email encontrado (prepared): " . print_r($row, true) . "\n", FILE_APPEND);
} else {
    file_put_contents(__DIR__ . '/../debug.log', "Email NO encontrado (prepared)\n", FILE_APPEND);
}

// ===== DEBUG DETALLADO DE DUPLICADOS =====
file_put_contents(__DIR__ . '/../debug.log', "=== VERIFICANDO DUPLICADOS VOTANTE ===\n", FILE_APPEND);
        
        // Verificar email
        $emailQuery = "SELECT id, email FROM usuarios WHERE email = ?";
        file_put_contents(__DIR__ . '/../debug.log', "Query email: $emailQuery\nParam: [$email]\n", FILE_APPEND);
        $existingEmail = $this->db->fetch($emailQuery, [$email]);
        file_put_contents(__DIR__ . '/../debug.log', "Resultado email: " . print_r($existingEmail, true) . "\n", FILE_APPEND);
        
        if ($existingEmail) {
            throw new Exception('Ya existe un usuario con este email');
        }
        
        // Verificar cédula
        $cedulaQuery = "SELECT id, cedula FROM usuarios WHERE cedula = ?";
        file_put_contents(__DIR__ . '/../debug.log', "Query cédula: $cedulaQuery\nParam: [$cedula]\n", FILE_APPEND);
        $existingCedula = $this->db->fetch($cedulaQuery, [$cedula]);
        file_put_contents(__DIR__ . '/../debug.log', "Resultado cédula: " . print_r($existingCedula, true) . "\n", FILE_APPEND);
        
        if ($existingCedula) {
            throw new Exception('Ya existe un usuario con esta cédula');
        }
        
        // También verificar en tabla operadores_registro
        $existingOperador = $this->db->fetch("SELECT id, correo FROM operadores_registro WHERE correo = ?", [$email]);
        file_put_contents(__DIR__ . '/../debug.log', "Operador existente: " . print_r($existingOperador, true) . "\n", FILE_APPEND);
        
        if ($existingOperador) {
            throw new Exception('Ya existe un operador con este email');
        }
        
        file_put_contents(__DIR__ . '/../debug.log', "✅ NO hay duplicados - Continuando...\n", FILE_APPEND);
        
        // Iniciar transacción
        $this->db->beginTransaction();
        
        try {
            // Crear el usuario operador
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            file_put_contents(__DIR__ . '/../debug.log', "Creando usuario operador...\n", FILE_APPEND);
            
            $result = $this->db->execute("
                INSERT INTO usuarios (nombre, apellido, cedula, email, telefono, password, tipo_usuario, rol, activo, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'operador', 'operador', 1, NOW())
            ", [$nombre, $apellido, $cedula, $email, $telefono, $hashedPassword]);
            
            $userId = $this->db->getLastInsertId();
            
            file_put_contents(__DIR__ . '/../debug.log', 
                "Usuario OPERADOR creado con ID: $userId\n" .
                "Result: " . print_r($result, true) . "\n",
                FILE_APPEND
            );
            
            if (!$userId) {
                throw new Exception('Error al crear el usuario operador');
            }
            
            // Insertar en operadores_registro
            $this->db->execute("
                INSERT INTO operadores_registro (correo, contrasena_hash, usuario_id, created_at)
                VALUES (?, ?, ?, NOW())
            ", [$email, $hashedPassword, $userId]);
            
            file_put_contents(__DIR__ . '/../debug.log', "Registro en tabla operadores_registro creado\n", FILE_APPEND);
            
            // Asignar el operador a la asamblea
            $this->db->execute("
                INSERT INTO participantes_asamblea (asamblea_id, usuario_id, rol, coeficiente_asignado, created_at)
                VALUES (?, ?, 'operador', 0, NOW())
            ", [$assemblyId, $userId]);
            
            file_put_contents(__DIR__ . '/../debug.log', "Operador asignado a asamblea\n", FILE_APPEND);
            
            // Asignación de personal
            $this->db->execute("
                INSERT INTO asignaciones_personal (asamblea_id, usuario_id, tipo_personal, created_at)
                VALUES (?, ?, 'operador', NOW())
            ", [$assemblyId, $userId]);
            
            file_put_contents(__DIR__ . '/../debug.log', "Asignación de personal creada\n", FILE_APPEND);
            
            // Confirmar transacción
            $this->db->commit();
            
            $_SESSION['success'] = 'Operador de registro creado correctamente';
            file_put_contents(__DIR__ . '/../debug.log', "SUCCESS: Operador creado exitosamente - Transacción confirmada\n", FILE_APPEND);
            
        } catch (Exception $transactionError) {
            $this->db->rollback();
            
            // Manejar errores de MySQL directamente
            if (strpos($transactionError->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($transactionError->getMessage(), 'email') !== false) {
                    throw new Exception('Ya existe un usuario con este email');
                } elseif (strpos($transactionError->getMessage(), 'cedula') !== false) {
                    throw new Exception('Ya existe un usuario con esta cédula');
                } elseif (strpos($transactionError->getMessage(), 'correo') !== false) {
                    throw new Exception('Ya existe un operador con este email');
                }
            }
            
            file_put_contents(__DIR__ . '/../debug.log', "ERROR en transacción: " . $transactionError->getMessage() . "\n", FILE_APPEND);
            throw $transactionError;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al crear operador: ' . $e->getMessage();
        file_put_contents(__DIR__ . '/../debug.log', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    
    header('Location: /Asambleas/public/coordinador/participantes?asamblea=' . ($assemblyId ?? ''));
    exit;
}

public function crearVotacion() {
    // Debug inicial
    error_log("=== CREAR VOTACION INICIADO ===");
    error_log("METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST data: " . print_r($_POST, true));
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("ERROR: Método no es POST");
        header('Location: /Asambleas/public/coordinador/votaciones');
        exit;
    }
    
    $assemblyId = null;
    
    try {
        // Obtener datos básicos
        $assemblyId = intval($_POST['asamblea_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $tipoVotacion = $_POST['tipo_votacion'] ?? 'ordinaria';
        $quorumRequerido = floatval($_POST['quorum_requerido'] ?? 50);
        $mayoriaRequerida = floatval($_POST['mayoria_requerida'] ?? 50);
        $opciones = $_POST['opciones'] ?? [];
        
        error_log("Datos procesados:");
        error_log("  Assembly ID: $assemblyId");
        error_log("  Título: '$titulo'");
        error_log("  Opciones: " . print_r($opciones, true));
        
        // Validaciones
        if (empty($titulo)) {
            throw new Exception('El título es obligatorio');
        }
        
        if ($assemblyId <= 0) {
            throw new Exception('ID de asamblea inválido: ' . $assemblyId);
        }
        
        // Filtrar opciones vacías
        $opcionesValidas = array_filter($opciones, function($opcion) {
            return !empty(trim($opcion));
        });
        
        if (count($opcionesValidas) < 2) {
            throw new Exception('Mínimo 2 opciones requeridas. Recibidas: ' . count($opcionesValidas));
        }
        
        error_log("Validaciones OK - Verificando asamblea...");
        
        // Verificar asamblea existe y permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        $assembly = $this->db->fetch("
            SELECT * FROM asambleas 
            WHERE id = ? AND (coordinador_id = ? OR administrador_id = ?)
        ", [$assemblyId, $coordinatorId, $coordinatorId]);
        
        if (!$assembly) {
            throw new Exception('Asamblea no encontrada o sin permisos. ID: ' . $assemblyId);
        }
        
        error_log("Asamblea encontrada: " . $assembly['titulo']);
        error_log("Iniciando transacción...");
        
        // ===== CONFIGURAR VARIABLES PARA TRIGGERS =====
        try {
            $connection = $this->db->getConnection();
            $connection->query("SET @current_user_id = $coordinatorId");
            $connection->query("SET @current_user_ip = '" . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . "'");
            error_log("Variables de trigger configuradas");
        } catch (Exception $triggerError) {
            error_log("Warning: No se pudieron configurar variables de trigger: " . $triggerError->getMessage());
        }
        
        // Crear votación
        $this->db->beginTransaction();
        
        try {
            // Insertar votación
            $votacionResult = $this->db->execute("
                INSERT INTO votaciones (asamblea_id, titulo, descripcion, tipo_votacion, quorum_requerido, mayoria_requerida, estado, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'preparada', NOW())
            ", [$assemblyId, $titulo, $descripcion, $tipoVotacion, $quorumRequerido, $mayoriaRequerida]);
            
            $votacionId = $this->db->getLastInsertId();
            error_log("Votación creada con ID: $votacionId");
            
            if (!$votacionId || $votacionId <= 0) {
                throw new Exception('Error al crear votación - No se obtuvo ID válido');
            }
            
            // Crear opciones
            foreach ($opcionesValidas as $index => $opcion) {
                $opcionResult = $this->db->execute("
                    INSERT INTO opciones_votacion (votacion_id, opcion, orden_display, created_at)
                    VALUES (?, ?, ?, NOW())
                ", [$votacionId, trim($opcion), $index + 1]);
                
                if (!$opcionResult) {
                    throw new Exception("Error al crear opción: " . trim($opcion));
                }
                
                error_log("Opción " . ($index + 1) . " creada: " . trim($opcion));
            }
            
            $this->db->commit();
            error_log("✅ Transacción confirmada exitosamente");
            
            // Configurar mensaje de éxito
            $_SESSION['success'] = "Votación '$titulo' creada correctamente";
            error_log("SUCCESS: Mensaje de sesión configurado");
            
        } catch (Exception $transactionError) {
            $this->db->rollback();
            error_log("ERROR en transacción: " . $transactionError->getMessage());
            throw $transactionError;
        }
        
    } catch (Exception $e) {
        error_log("❌ ERROR GENERAL: " . $e->getMessage());
        $_SESSION['error'] = 'Error al crear votación: ' . $e->getMessage();
    }
    
    // REDIRECCIÓN FINAL - ASEGURAR QUE SE EJECUTE
    $redirectUrl = '/Asambleas/public/coordinador/votaciones';
    if ($assemblyId && $assemblyId > 0) {
        $redirectUrl .= '?asamblea=' . $assemblyId;
    }
    
    error_log("REDIRIGIENDO A: $redirectUrl");
    
    // MÚLTIPLES MÉTODOS DE REDIRECCIÓN PARA ASEGURAR
    
    // Método 1: Header Location (estándar)
    header('Location: ' . $redirectUrl);
    
    // Método 2: Limpiar cualquier output buffer
    ob_clean();
    
    // Método 3: Header Location otra vez por si acaso
    header('Location: ' . $redirectUrl, true, 302);
    
    // Método 4: JavaScript como backup
    echo "<script>window.location.href = '$redirectUrl';</script>";
    
    // Método 5: Meta refresh como segundo backup
    echo "<meta http-equiv='refresh' content='0; url=$redirectUrl'>";
    
    // Método 6: Mensaje para humanos si nada funciona
    echo "<p>Votación creada. <a href='$redirectUrl'>Haz clic aquí si no eres redirigido automáticamente</a></p>";
    
    // TERMINAR EJECUCIÓN INMEDIATAMENTE
    exit();
}

public function debugRoute() {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h2>🔍 DEBUG DE RUTAS Y SISTEMA</h2>";
    
    echo "<h3>📍 Información de Request</h3>";
    echo "<ul>";
    echo "<li><strong>REQUEST_METHOD:</strong> " . $_SERVER['REQUEST_METHOD'] . "</li>";
    echo "<li><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</li>";
    echo "</ul>";
    
    echo "<h3>📋 Datos POST</h3>";
    if (!empty($_POST)) {
        echo "<pre>" . print_r($_POST, true) . "</pre>";
    } else {
        echo "<p>No hay datos POST</p>";
    }
    
    echo "<h3>🧪 Test de Formulario</h3>";
    echo '<div style="border: 2px solid #007bff; padding: 20px; margin: 20px 0; border-radius: 5px;">';
    echo '<h4>Formulario de Prueba Directo</h4>';
    echo '<form method="POST" action="crear-votacion">';
    echo '<input type="hidden" name="asamblea_id" value="1">';
    echo '<p><label>Título: <input type="text" name="titulo" value="Votación de Prueba Debug" required style="width: 300px;"></label></p>';
    echo '<p><label>Opción 1: <input type="text" name="opciones[]" value="Sí" required></label></p>';
    echo '<p><label>Opción 2: <input type="text" name="opciones[]" value="No" required></label></p>';
    echo '<p><button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 3px;">🧪 Crear Votación de Prueba</button></p>';
    echo '</form>';
    echo '</div>';
    exit;
}

public function testSimple() {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'CoordinatorController funcionando correctamente',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}
/**
 * Abrir votación para recibir votos
 */
public function abrirVotacion($votacionId) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        // Verificar que la votación existe y está en estado preparada
        $votacion = $this->db->fetch("
            SELECT v.*, a.coordinador_id, a.administrador_id, a.estado as asamblea_estado
            FROM votaciones v
            JOIN asambleas a ON v.asamblea_id = a.id
            WHERE v.id = ?
        ", [$votacionId]);
        
        if (!$votacion) {
            throw new Exception('Votación no encontrada');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        if ($votacion['coordinador_id'] != $coordinatorId && $votacion['administrador_id'] != $coordinatorId) {
            throw new Exception('No tiene permisos para gestionar esta votación');
        }
        
        if ($votacion['estado'] !== 'preparada') {
            throw new Exception('Solo se pueden abrir votaciones en estado preparada');
        }
        
        if ($votacion['asamblea_estado'] !== 'activa') {
            throw new Exception('La asamblea debe estar activa para abrir votaciones');
        }
        
        // Verificar quórum si es requerido
        $quorumData = $this->getQuorumData($votacion['asamblea_id']);
        if ($quorumData && $quorumData['porcentaje_coeficiente'] < $votacion['quorum_requerido']) {
            throw new Exception('No se ha alcanzado el quórum mínimo requerido (' . $votacion['quorum_requerido'] . '%)');
        }
        
        // Abrir la votación
        $result = $this->db->execute("
            UPDATE votaciones 
            SET estado = 'abierta', fecha_inicio = NOW(), updated_at = NOW()
            WHERE id = ?
        ", [$votacionId]);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Votación abierta correctamente',
                'fecha_inicio' => date('Y-m-d H:i:s')
            ]);
        } else {
            throw new Exception('No se pudo abrir la votación');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Cerrar votación
 */
public function cerrarVotacion($votacionId) {
    // Debug inicial
    error_log("=== CERRAR VOTACION DEBUG ===");
    error_log("METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("URI: " . $_SERVER['REQUEST_URI']);
    error_log("Votacion ID: " . $votacionId);
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        // Leer input JSON si existe
        $input = file_get_contents('php://input');
        error_log("Input recibido: " . $input);
        
        if (!empty($input)) {
            $data = json_decode($input, true);
            error_log("JSON decodificado: " . print_r($data, true));
        }
        
        // Verificar que la votación existe y está abierta
        $votacion = $this->db->fetch("
            SELECT v.*, a.coordinador_id, a.administrador_id
            FROM votaciones v
            JOIN asambleas a ON v.asamblea_id = a.id
            WHERE v.id = ?
        ", [$votacionId]);
        
        error_log("Votación encontrada: " . print_r($votacion, true));
        
        if (!$votacion) {
            throw new Exception('Votación no encontrada');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        error_log("Coordinator ID: $coordinatorId");
        
        if ($votacion['coordinador_id'] != $coordinatorId && $votacion['administrador_id'] != $coordinatorId) {
            throw new Exception('No tiene permisos para gestionar esta votación');
        }
        
        if ($votacion['estado'] !== 'abierta') {
            throw new Exception('Solo se pueden cerrar votaciones abiertas. Estado actual: ' . $votacion['estado']);
        }
        
        error_log("Iniciando transacción para cerrar votación...");
        
        // Cerrar la votación y calcular resultados
        $this->db->beginTransaction();
        
        try {
            // Paso 1: Cerrar votación
            error_log("Paso 1: Cerrando votación...");
            $updateResult = $this->db->execute("
                UPDATE votaciones 
                SET estado = 'cerrada', fecha_cierre = NOW(), updated_at = NOW()
                WHERE id = ?
            ", [$votacionId]);
            
            error_log("Update result: " . print_r($updateResult, true));
            
            if (!$updateResult || $updateResult['affected_rows'] == 0) {
                throw new Exception('No se pudo actualizar el estado de la votación');
            }
            
            // Paso 2: Calcular resultados finales
            error_log("Paso 2: Calculando resultados...");
            try {
                $this->calcularResultadosVotacion($votacionId);
                error_log("Resultados calculados exitosamente");
            } catch (Exception $resultadosError) {
                error_log("ERROR en cálculo de resultados: " . $resultadosError->getMessage());
                // No fallar por el cálculo de resultados, la votación ya está cerrada
                error_log("Continuando sin calcular resultados...");
            }
            
            // Paso 3: Confirmar transacción
            $this->db->commit();
            error_log("Transacción confirmada");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Votación cerrada correctamente',
                'fecha_cierre' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $transactionError) {
            $this->db->rollback();
            error_log("Error en transacción: " . $transactionError->getMessage());
            throw $transactionError;
        }
        
    } catch (Exception $e) {
        error_log("ERROR GENERAL cerrarVotacion: " . $e->getMessage());
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Ver resultados de votación
 */
public function resultadosVotacion($votacionId) {
    try {
        // Verificar permisos
        $votacion = $this->db->fetch("
            SELECT v.*, a.coordinador_id, a.administrador_id, a.titulo as asamblea_titulo
            FROM votaciones v
            JOIN asambleas a ON v.asamblea_id = a.id
            WHERE v.id = ?
        ", [$votacionId]);
        
        if (!$votacion) {
            throw new Exception('Votación no encontrada');
        }
        
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        if ($votacion['coordinador_id'] != $coordinatorId && $votacion['administrador_id'] != $coordinatorId) {
            throw new Exception('No tiene permisos para ver esta votación');
        }
        
        // Obtener opciones y votos
        $opciones = $this->db->fetchAll("
            SELECT 
                ov.id, ov.opcion as opcion_texto, ov.orden_display,
                COUNT(vo.id) as total_votos,
                SUM(vo.coeficiente_voto) as coeficiente_total,
                COALESCE(SUM(vo.coeficiente_voto), 0) as coeficiente_obtenido
            FROM opciones_votacion ov
            LEFT JOIN votos vo ON ov.id = vo.opcion_id
            WHERE ov.votacion_id = ?
            GROUP BY ov.id
            ORDER BY ov.orden_display
        ", [$votacionId]);
        
        // Calcular totales
        $totalVotos = array_sum(array_column($opciones, 'total_votos'));
        $totalCoeficiente = array_sum(array_column($opciones, 'coeficiente_total'));
        
        // Obtener participantes elegibles
        $participantesElegibles = $this->db->fetch("
            SELECT 
                COUNT(*) as total_participantes,
                SUM(coeficiente_asignado) as coeficiente_total_asamblea
            FROM participantes_asamblea 
            WHERE asamblea_id = ? AND rol = 'votante' AND asistencia = 1
        ", [$votacion['asamblea_id']]);
        
        // Calcular participación
        $participacion = $participantesElegibles['coeficiente_total_asamblea'] > 0 
            ? ($totalCoeficiente / $participantesElegibles['coeficiente_total_asamblea']) * 100 
            : 0;
        
        // Determinar ganador (si la votación está cerrada)
        $ganador = null;
        if ($votacion['estado'] === 'cerrada' && !empty($opciones)) {
            $maxCoeficiente = max(array_column($opciones, 'coeficiente_obtenido'));
            foreach ($opciones as $opcion) {
                if ($opcion['coeficiente_obtenido'] == $maxCoeficiente) {
                    $ganador = $opcion;
                    break;
                }
            }
            
            // Verificar si cumple con la mayoría requerida
            if ($ganador && $totalCoeficiente > 0) {
                $porcentajeGanador = ($ganador['coeficiente_obtenido'] / $totalCoeficiente) * 100;
                $ganador['cumple_mayoria'] = $porcentajeGanador >= $votacion['mayoria_requerida'];
                $ganador['porcentaje'] = $porcentajeGanador;
            }
        }
        
        // Generar HTML para el modal
        ob_start();
        ?>
        <div class="row">
            <div class="col-md-8">
                <h6><?php echo htmlspecialchars($votacion['titulo']); ?></h6>
                <?php if (!empty($votacion['descripcion'])): ?>
                    <p class="text-muted"><?php echo htmlspecialchars($votacion['descripcion']); ?></p>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong>Resultados por Opción:</strong>
                </div>
                
                <?php foreach ($opciones as $opcion): ?>
                    <?php 
                    $porcentajeVotos = $totalVotos > 0 ? ($opcion['total_votos'] / $totalVotos) * 100 : 0;
                    $porcentajeCoeficiente = $totalCoeficiente > 0 ? ($opcion['coeficiente_obtenido'] / $totalCoeficiente) * 100 : 0;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><strong><?php echo htmlspecialchars($opcion['opcion_texto']); ?></strong></span>
                            <span><?php echo $opcion['total_votos']; ?> votos</span>
                        </div>
                        
                        <!-- Barra de votos -->
                        <div class="progress mb-1" style="height: 20px;">
                            <div class="progress-bar" style="width: <?php echo $porcentajeVotos; ?>%">
                                <?php echo number_format($porcentajeVotos, 1); ?>%
                            </div>
                        </div>
                        
                        <!-- Barra de coeficiente -->
                        <div class="progress" style="height: 15px;">
                            <div class="progress-bar bg-info" style="width: <?php echo $porcentajeCoeficiente; ?>%">
                                <?php echo number_format($porcentajeCoeficiente, 1); ?>%
                            </div>
                        </div>
                        
                        <small class="text-muted">
                            Coeficiente: <?php echo number_format($opcion['coeficiente_obtenido'], 4); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($ganador && $votacion['estado'] === 'cerrada'): ?>
                    <div class="alert <?php echo $ganador['cumple_mayoria'] ? 'alert-success' : 'alert-warning'; ?> mt-3">
                        <h6><i class="fas fa-trophy me-2"></i>Resultado Final</h6>
                        <p class="mb-1">
                            <strong>Opción ganadora:</strong> <?php echo htmlspecialchars($ganador['opcion_texto']); ?>
                        </p>
                        <p class="mb-1">
                            <strong>Porcentaje obtenido:</strong> <?php echo number_format($ganador['porcentaje'], 2); ?>%
                        </p>
                        <?php if (!$ganador['cumple_mayoria']): ?>
                            <p class="mb-0 text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                No alcanzó la mayoría requerida (<?php echo $votacion['mayoria_requerida']; ?>%)
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6>Estadísticas</h6>
                        
                        <div class="mb-2">
                            <small class="text-muted">Estado:</small>
                            <span class="badge bg-<?php echo $votacion['estado'] === 'abierta' ? 'success' : ($votacion['estado'] === 'preparada' ? 'warning' : 'secondary'); ?>">
                                <?php echo ucfirst($votacion['estado']); ?>
                            </span>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Total de votos:</small>
                            <strong><?php echo $totalVotos; ?></strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Participación:</small>
                            <strong><?php echo number_format($participacion, 1); ?>%</strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Coeficiente total:</small>
                            <strong><?php echo number_format($totalCoeficiente, 4); ?></strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Quórum requerido:</small>
                            <strong><?php echo $votacion['quorum_requerido']; ?>%</strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Mayoría requerida:</small>
                            <strong><?php echo $votacion['mayoria_requerida']; ?>%</strong>
                        </div>
                        
                        <?php if ($votacion['fecha_inicio']): ?>
                            <div class="mb-2">
                                <small class="text-muted">Iniciada:</small>
                                <strong><?php echo date('d/m/Y H:i', strtotime($votacion['fecha_inicio'])); ?></strong>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($votacion['fecha_cierre']): ?>
                            <div class="mb-2">
                                <small class="text-muted">Cerrada:</small>
                                <strong><?php echo date('d/m/Y H:i', strtotime($votacion['fecha_cierre'])); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        
        $content = ob_get_clean();
        echo $content;
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    exit;
}

/**
 * Duplicar votación
 */
public function duplicarVotacion($votacionId) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        // Obtener votación original
        $votacion = $this->db->fetch("
            SELECT v.*, a.coordinador_id, a.administrador_id
            FROM votaciones v
            JOIN asambleas a ON v.asamblea_id = a.id
            WHERE v.id = ?
        ", [$votacionId]);
        
        if (!$votacion) {
            throw new Exception('Votación no encontrada');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        if ($votacion['coordinador_id'] != $coordinatorId && $votacion['administrador_id'] != $coordinatorId) {
            throw new Exception('No tiene permisos para duplicar esta votación');
        }
        
        // Obtener opciones
        $opciones = $this->db->fetchAll("
            SELECT opcion as opcion_texto, orden_display
            FROM opciones_votacion
            WHERE votacion_id = ?
            ORDER BY orden_display
        ", [$votacionId]);
        
        $this->db->beginTransaction();
        
        try {
            // Crear nueva votación
            $nuevoTitulo = $votacion['titulo'] . ' (Copia)';
            
            $result = $this->db->execute("
                INSERT INTO votaciones (
                    asamblea_id, titulo, descripcion, tipo_votacion, 
                    quorum_requerido, mayoria_requerida, estado, 
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'preparada', NOW())
            ", [
                $votacion['asamblea_id'], 
                $nuevoTitulo, 
                $votacion['descripcion'], 
                $votacion['tipo_votacion'],
                $votacion['quorum_requerido'], 
                $votacion['mayoria_requerida']
            ]);
            
            $nuevaVotacionId = $this->db->getLastInsertId();
            
            // Duplicar opciones
            foreach ($opciones as $opcion) {
                $this->db->execute("
                    INSERT INTO opciones_votacion (
                        votacion_id, opcion, descripcion, orden_display
                    ) VALUES (?, ?, ?, ?)
                ", [$nuevaVotacionId, $opcion['opcion_texto'], '', $opcion['orden_display']]);
            }
            
            $this->db->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Votación duplicada correctamente',
                'nueva_votacion_id' => $nuevaVotacionId
            ]);
            
        } catch (Exception $transactionError) {
            $this->db->rollback();
            throw $transactionError;
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Eliminar votación
 */
public function eliminarVotacion($votacionId) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {  // ← CAMBIO AQUÍ
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        // Verificar que la votación existe
        $votacion = $this->db->fetch("
            SELECT v.*, a.coordinador_id, a.administrador_id
            FROM votaciones v
            JOIN asambleas a ON v.asamblea_id = a.id
            WHERE v.id = ?
        ", [$votacionId]);
        
        if (!$votacion) {
            throw new Exception('Votación no encontrada');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        if ($votacion['coordinador_id'] != $coordinatorId && $votacion['administrador_id'] != $coordinatorId) {
            throw new Exception('No tiene permisos para eliminar esta votación');
        }
        
        // Solo se pueden eliminar votaciones preparadas
        if ($votacion['estado'] !== 'preparada') {
            throw new Exception('Solo se pueden eliminar votaciones en estado preparada');
        }
        
        $this->db->beginTransaction();
        
        try {
            // Eliminar opciones
            $this->db->execute("DELETE FROM opciones_votacion WHERE votacion_id = ?", [$votacionId]);
            
            // Eliminar votación
            $this->db->execute("DELETE FROM votaciones WHERE id = ?", [$votacionId]);
            
            $this->db->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Votación eliminada correctamente'
            ]);
            
        } catch (Exception $transactionError) {
            $this->db->rollback();
            throw $transactionError;
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Calcular resultados de votación
 */

 public function testVotaciones() {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Las rutas de votaciones funcionan correctamente',
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $_SESSION['user_id'] ?? 'NO SESSION',
        'user_role' => $_SESSION['user_role'] ?? 'NO ROLE'
    ]);
    exit;
}

private function calcularResultadosVotacion($votacionId) {
    try {
        error_log("=== CALCULANDO RESULTADOS VOTACION $votacionId ===");
        
        // Verificar que la votación existe
        $votacion = $this->db->fetch("SELECT * FROM votaciones WHERE id = ?", [$votacionId]);
        if (!$votacion) {
            throw new Exception("Votación $votacionId no encontrada");
        }
        
        error_log("Votación encontrada: " . $votacion['titulo']);
        
        // Obtener resultados actuales
        $resultados = $this->db->fetchAll("
            SELECT 
                ov.id as opcion_id,
                ov.opcion as opcion_texto,
                COUNT(vo.id) as total_votos,
                COALESCE(SUM(vo.coeficiente_voto), 0) as coeficiente_total
            FROM opciones_votacion ov
            LEFT JOIN votos vo ON ov.id = vo.opcion_id
            WHERE ov.votacion_id = ?
            GROUP BY ov.id
            ORDER BY coeficiente_total DESC, total_votos DESC
        ", [$votacionId]);
        
        error_log("Resultados obtenidos: " . count($resultados) . " opciones");
        
        if (!empty($resultados)) {
            error_log("Guardando resultados en tabla resultados_votacion...");
            
            // Limpiar resultados anteriores para esta votación
            $this->db->execute("DELETE FROM resultados_votacion WHERE votacion_id = ?", [$votacionId]);
            
            // Guardar resultados en tabla de resultados
            foreach ($resultados as $index => $resultado) {
                error_log("Guardando resultado para opción: " . $resultado['opcion_texto']);
                
                $insertResult = $this->db->execute("
                    INSERT INTO resultados_votacion (
                        votacion_id, opcion_id, total_votos, 
                        coeficiente_total, posicion, created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())
                ", [
                    $votacionId, 
                    $resultado['opcion_id'], 
                    $resultado['total_votos'],
                    $resultado['coeficiente_total'], 
                    $index + 1
                ]);
                
                error_log("Resultado guardado: " . ($insertResult ? 'OK' : 'ERROR'));
            }
        }
        
        // Verificar si hay tabla historial_votaciones y si es necesaria
        $tablaHistorialExiste = $this->db->fetch("SHOW TABLES LIKE 'historial_votaciones'");
        
        if ($tablaHistorialExiste) {
            error_log("Tabla historial_votaciones existe, verificando estructura...");
            
            // Verificar estructura de la tabla
            $columnas = $this->db->fetchAll("SHOW COLUMNS FROM historial_votaciones");
            $columnNames = array_column($columnas, 'Field');
            
            error_log("Columnas en historial_votaciones: " . implode(', ', $columnNames));
            
            // Solo proceder si la tabla tiene las columnas correctas
            if (in_array('votacion_id', $columnNames) && 
                in_array('accion', $columnNames) && 
                in_array('created_at', $columnNames)) {
                
                // Registrar en historial (SIN usuario_id si causa problemas)
                if (in_array('usuario_id', $columnNames)) {
                    // Con usuario_id - verificar que el usuario existe
                    $usuarioId = $_SESSION['user_id'] ?? null;
                    if ($usuarioId) {
                        $usuarioExiste = $this->db->fetch("SELECT id FROM usuarios WHERE id = ?", [$usuarioId]);
                        if ($usuarioExiste) {
                            $this->db->execute("
                                INSERT INTO historial_votaciones (
                                    votacion_id, usuario_id, accion, detalles, created_at
                                ) VALUES (?, ?, 'cerrar_votacion', 'Votación cerrada y resultados calculados', NOW())
                            ", [$votacionId, $usuarioId]);
                            error_log("Historial guardado con usuario_id");
                        } else {
                            error_log("Usuario $usuarioId no existe, guardando sin usuario_id");
                            $this->db->execute("
                                INSERT INTO historial_votaciones (
                                    votacion_id, accion, detalles, created_at
                                ) VALUES (?, 'cerrar_votacion', 'Votación cerrada y resultados calculados', NOW())
                            ", [$votacionId]);
                        }
                    } else {
                        error_log("No hay usuario en sesión, guardando sin usuario_id");
                        $this->db->execute("
                            INSERT INTO historial_votaciones (
                                votacion_id, accion, detalles, created_at
                            ) VALUES (?, 'cerrar_votacion', 'Votación cerrada y resultados calculados', NOW())
                        ", [$votacionId]);
                    }
                } else {
                    // Sin usuario_id
                    $this->db->execute("
                        INSERT INTO historial_votaciones (
                            votacion_id, accion, detalles, created_at
                        ) VALUES (?, 'cerrar_votacion', 'Votación cerrada y resultados calculados', NOW())
                    ", [$votacionId]);
                    error_log("Historial guardado sin usuario_id");
                }
            } else {
                error_log("Tabla historial_votaciones no tiene estructura esperada, omitiendo historial");
            }
        } else {
            error_log("Tabla historial_votaciones no existe, omitiendo historial");
        }
        
        error_log("=== RESULTADOS CALCULADOS EXITOSAMENTE ===");
        
    } catch (Exception $e) {
        error_log("ERROR calculando resultados de votación {$votacionId}: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        throw new Exception("Error al calcular resultados: " . $e->getMessage());
    }
}
public function exportarResultados($votacionId) {
    try {
        // Verificar permisos
        $votacion = $this->db->fetch("
            SELECT v.*, a.coordinador_id, a.administrador_id, a.titulo as asamblea_titulo
            FROM votaciones v
            JOIN asambleas a ON v.asamblea_id = a.id
            WHERE v.id = ?
        ", [$votacionId]);
        
        if (!$votacion) {
            throw new Exception('Votación no encontrada');
        }
        
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        if ($votacion['coordinador_id'] != $coordinatorId && $votacion['administrador_id'] != $coordinatorId) {
            throw new Exception('No tiene permisos para exportar esta votación');
        }
        
        // Obtener datos para export
        $resultados = $this->db->fetchAll("
            SELECT 
                ov.opcion as opcion_texto,
                COUNT(vo.id) as total_votos,
                SUM(vo.coeficiente_voto) as coeficiente_total,
                ROUND((SUM(vo.coeficiente_voto) / (SELECT SUM(v2.coeficiente_voto) FROM votos v2 WHERE v2.votacion_id = ?)) * 100, 2) as porcentaje
            FROM opciones_votacion ov
            LEFT JOIN votos vo ON ov.id = vo.opcion_id
            WHERE ov.votacion_id = ?
            GROUP BY ov.id
            ORDER BY coeficiente_total DESC
        ", [$votacionId, $votacionId]);
        
        // Configurar headers para descarga
        $filename = 'resultados_votacion_' . $votacionId . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Escribir BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers del CSV
        fputcsv($output, [
            'Asamblea: ' . $votacion['asamblea_titulo'],
            'Votación: ' . $votacion['titulo'],
            'Fecha: ' . date('d/m/Y H:i:s'),
            'Estado: ' . ucfirst($votacion['estado'])
        ], ';');
        
        fputcsv($output, [], ';'); // Línea vacía
        
        fputcsv($output, [
            'Opción',
            'Total Votos',
            'Coeficiente',
            'Porcentaje'
        ], ';');
        
        // Datos
        foreach ($resultados as $resultado) {
            fputcsv($output, [
                $resultado['opcion_texto'],
                $resultado['total_votos'],
                number_format($resultado['coeficiente_total'], 6),
                $resultado['porcentaje'] . '%'
            ], ';');
        }
        
        fclose($output);
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al exportar: ' . $e->getMessage();
        header('Location: /Asambleas/public/coordinador/votaciones');
        exit;
    }
}
/**
 * Crear votante para asamblea
 */


public function crearVotante() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /Asambleas/public/coordinador/participantes');
        exit;
    }
    
    $assemblyId = null;
    
    try {
        $assemblyId = $_POST['asamblea_id'] ?? 0;
        
        // Validar asamblea
        if (empty($assemblyId) || $assemblyId <= 0) {
            throw new Exception('Debe especificar una asamblea válida');
        }
        
        // Obtener información de la asamblea
        $assembly = $this->db->fetch(
            "SELECT id, estado, titulo, conjunto_id FROM asambleas WHERE id = ?", 
            [$assemblyId]
        );
        
        if (!$assembly) {
            throw new Exception('La asamblea especificada no existe');
        }
        
        // Obtener datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $cedula = trim($_POST['cedula'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $apartamento = trim($_POST['apartamento'] ?? '');
        $coeficiente = floatval($_POST['coeficiente'] ?? 0);
        $estadoPagos = $_POST['estado_pagos'] ?? 'al_dia';
        $esRepresentado = isset($_POST['es_representado']) ? 1 : 0;
        
        // CORRECCIÓN: Manejo correcto del representante_id
        $representanteId = null;
        if ($esRepresentado == 1 && !empty($_POST['representante_id'])) {
            $representanteId = intval($_POST['representante_id']);
            
            // Validar que el representante existe
            $representante = $this->db->fetch(
                "SELECT id FROM usuarios WHERE id = ? AND tipo_usuario = 'votante'", 
                [$representanteId]
            );
            
            if (!$representante) {
                throw new Exception('El representante seleccionado no es válido');
            }
        }
        
        // Validaciones básicas
        if (empty($nombre) || empty($apellido) || empty($cedula) || empty($email)) {
            throw new Exception('Los campos nombre, apellido, cédula y email son obligatorios');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del email no es válido');
        }
        
        if ($coeficiente < 0 || $coeficiente > 1) {
            throw new Exception('El coeficiente debe estar entre 0 y 1');
        }
        
        // Verificar duplicados
        $existingUser = $this->db->fetch("SELECT id FROM usuarios WHERE cedula = ?", [$cedula]);
        if ($existingUser) {
            throw new Exception('Ya existe un usuario con esta cédula');
        }
        
        $existingEmail = $this->db->fetch("SELECT id FROM usuarios WHERE email = ?", [$email]);
        if ($existingEmail) {
            throw new Exception('Ya existe un usuario con este email');
        }
        
        // Iniciar transacción
        $this->db->beginTransaction();
        
        try {
            // Generar password temporal
            $tempPassword = $this->generateTempPassword();
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Crear usuario votante
            $result = $this->db->execute("
                INSERT INTO usuarios (nombre, apellido, cedula, email, telefono, password, tipo_usuario, rol, activo, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'votante', 'votante', 1, NOW())
            ", [$nombre, $apellido, $cedula, $email, $telefono, $hashedPassword]);
            
            $userId = $this->db->getLastInsertId();
            
            if (!$userId || $userId <= 0) {
                throw new Exception('Error al crear el usuario votante - No se obtuvo ID válido');
            }
            
            // Crear registro en tabla votantes
            $this->db->execute("
                INSERT INTO votantes (usuario_id, conjunto_id, apartamento, coeficiente, estado_pagos, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ", [$userId, $assembly['conjunto_id'], $apartamento, $coeficiente, $estadoPagos]);
            
            // CORRECCIÓN: Asignar el votante a la asamblea con representante_id correcto
            if ($representanteId !== null) {
                // Con representante
                $this->db->execute("
                    INSERT INTO participantes_asamblea (asamblea_id, usuario_id, rol, coeficiente_asignado, es_representado, representante_id, created_at)
                    VALUES (?, ?, 'votante', ?, ?, ?, NOW())
                ", [$assemblyId, $userId, $coeficiente, $esRepresentado, $representanteId]);
            } else {
                // Sin representante
                $this->db->execute("
                    INSERT INTO participantes_asamblea (asamblea_id, usuario_id, rol, coeficiente_asignado, es_representado, created_at)
                    VALUES (?, ?, 'votante', ?, ?, NOW())
                ", [$assemblyId, $userId, $coeficiente, $esRepresentado]);
            }
            
            // Confirmar transacción
            $this->db->commit();
            
            // Enviar email (opcional)
            try {
                $this->sendCredentialsEmail($email, $cedula, $tempPassword);
            } catch (Exception $emailError) {
                // No fallar por el email
                error_log("Error enviando email: " . $emailError->getMessage());
            }
            
            $_SESSION['success'] = "Votante '{$nombre} {$apellido}' creado correctamente. Password temporal: {$tempPassword}";
            
        } catch (Exception $transactionError) {
            $this->db->rollback();
            
            // Manejar errores de MySQL directamente
            if (strpos($transactionError->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($transactionError->getMessage(), 'email') !== false) {
                    throw new Exception('Ya existe un usuario con este email');
                } elseif (strpos($transactionError->getMessage(), 'cedula') !== false) {
                    throw new Exception('Ya existe un usuario con esta cédula');
                }
            }
            
            throw $transactionError;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al crear votante: ' . $e->getMessage();
    }
    
    // Redirect
    $redirectUrl = '/Asambleas/public/coordinador/participantes';
    if ($assemblyId && $assemblyId > 0) {
        $redirectUrl .= '?asamblea=' . $assemblyId;
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}
/**
 * Actualizar coeficiente de participante
 */
public function updateCoeficiente() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        $assemblyId = $_POST['asamblea_id'] ?? 0;
        $userId = $_POST['usuario_id'] ?? 0;
        $coeficiente = floatval($_POST['coeficiente'] ?? 0);
        
        if ($coeficiente < 0 || $coeficiente > 1) {
            throw new Exception('El coeficiente debe estar entre 0 y 1');
        }
        
        // Actualizar coeficiente en participantes_asamblea
        $result = $this->db->execute("
            UPDATE participantes_asamblea 
            SET coeficiente_asignado = ?, updated_at = NOW()
            WHERE asamblea_id = ? AND usuario_id = ?
        ", [$coeficiente, $assemblyId, $userId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Coeficiente actualizado correctamente']);
        } else {
            throw new Exception('No se pudo actualizar el coeficiente');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Remover participante de asamblea
 */
public function removeParticipant($userId) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $assemblyId = $input['asamblea_id'] ?? 0;
        
        // Verificar que el participante existe y es removible
        $participant = $this->db->fetch("
            SELECT pa.*, u.tipo_usuario 
            FROM participantes_asamblea pa
            JOIN usuarios u ON pa.usuario_id = u.id
            WHERE pa.asamblea_id = ? AND pa.usuario_id = ?
        ", [$assemblyId, $userId]);
        
        if (!$participant) {
            throw new Exception('Participante no encontrado');
        }
        
        // No permitir remover coordinadores o administradores
        if (in_array($participant['tipo_usuario'], ['coordinador', 'administrador'])) {
            throw new Exception('No se puede remover coordinadores o administradores');
        }
        
        // Remover de la asamblea
        $result = $this->db->execute("
            DELETE FROM participantes_asamblea 
            WHERE asamblea_id = ? AND usuario_id = ?
        ", [$assemblyId, $userId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Participante removido correctamente']);
        } else {
            throw new Exception('No se pudo remover el participante');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Obtener usuarios disponibles para representación
 */
private function getAvailableRepresentantes($assemblyId) {
    try {
        return $this->db->fetchAll("
            SELECT u.id, u.nombre, u.apellido, u.cedula
            FROM usuarios u
            JOIN participantes_asamblea pa ON u.id = pa.usuario_id
            WHERE pa.asamblea_id = ? 
            AND u.tipo_usuario = 'votante'
            AND pa.es_representado = 0
            ORDER BY u.nombre, u.apellido
        ", [$assemblyId]);
    } catch (Exception $e) {
        error_log("Error getting available representantes: " . $e->getMessage());
        return [];
    }
}

/**
 * Generar password temporal
 */
private function generateTempPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

/**
 * Enviar email con credenciales (implementación básica)
 */
private function sendCredentialsEmail($email, $cedula, $password) {
    // Implementar envío de email según tu configuración
    // Por ahora solo log para desarrollo
    error_log("Credenciales para $email - Usuario: $cedula, Password: $password");
    
    // Ejemplo básico con mail() - personalizar según necesidades
    /*
    $subject = "Credenciales de acceso - Sistema de Asambleas";
    $message = "
        Estimado/a usuario/a,
        
        Se han creado sus credenciales de acceso al sistema:
        
        Usuario: $cedula
        Contraseña temporal: $password
        
        Por favor, cambie su contraseña en el primer acceso.
        
        Saludos cordiales.
    ";
    
    $headers = "From: noreply@asambleas.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($email, $subject, $message, $headers);
    */
}

/**
 * Obtener estadísticas de la asamblea
 */
private function getAssemblyStatistics($assemblyId) {
    try {
        return $this->db->fetch("
            SELECT 
                COUNT(*) as total_participantes,
                COUNT(CASE WHEN pa.rol = 'votante' THEN 1 END) as total_votantes,
                COUNT(CASE WHEN pa.rol = 'operador' THEN 1 END) as total_operadores,
                COUNT(CASE WHEN pa.asistencia = 1 THEN 1 END) as total_asistentes,
                SUM(pa.coeficiente_asignado) as coeficiente_total,
                COUNT(CASE WHEN pa.es_representado = 1 THEN 1 END) as total_representados
            FROM participantes_asamblea pa
            WHERE pa.asamblea_id = ?
        ", [$assemblyId]);
    } catch (Exception $e) {
        error_log("Error getting assembly statistics: " . $e->getMessage());
        return [
            'total_participantes' => 0,
            'total_votantes' => 0,
            'total_operadores' => 0,
            'total_asistentes' => 0,
            'coeficiente_total' => 0,
            'total_representados' => 0
        ];
    }
}

/**
 * Validar permisos de coordinador para la asamblea
 */
private function validateCoordinatorPermissions($assemblyId) {
    $coordinatorId = $_SESSION['user_id'] ?? 0;
    
    $assembly = $this->db->fetch("
        SELECT id FROM asambleas 
        WHERE id = ? AND (coordinador_id = ? OR administrador_id = ?)
    ", [$assemblyId, $coordinatorId, $coordinatorId]);
    
    if (!$assembly) {
        throw new Exception('No tiene permisos para gestionar esta asamblea');
    }
    
    return true;
}
    
    // ================================
    // GESTIÓN DE PARTICIPANTES
    // ================================
public function participantes() {
    $userRole = 'coordinador';
    $assemblyId = $_GET['asamblea'] ?? null;
    $assemblies = $this->getActiveAssemblies();
    $participants = [];
    $assembly = null;
    $availableUsers = [];
    $availableRepresentantes = [];
    $assemblyStats = null;
    
    if ($assemblyId) {
        try {
            $this->validateCoordinatorPermissions($assemblyId);
            $assembly = $this->getAssemblyDetails($assemblyId);
            $participants = $this->getAssemblyParticipants($assemblyId);
            $availableUsers = $this->getAvailableUsers($assemblyId);
            $availableRepresentantes = $this->getAvailableRepresentantes($assemblyId);
            $assemblyStats = $this->getAssemblyStatistics($assemblyId);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
    
    require_once __DIR__ . '/../views/coordinator/participantes.php';
}
    
    // ================================
    // CONTROL DE QUÓRUM
    // ================================
    public function quorum() {
        $userRole = 'coordinador';
        $assemblyId = $_GET['asamblea'] ?? null;
        $assemblies = $this->getActiveAssemblies();
        $quorumData = [];
        $assembly = null;
        
        if ($assemblyId) {
            $assembly = $this->getAssemblyDetails($assemblyId);
            $quorumData = $this->getQuorumData($assemblyId);
        }
        
        require_once __DIR__ . '/../views/coordinator/quorum.php';
    }
    
    // ================================
    // VOTACIONES
    // ================================
    public function votaciones() {
        $userRole = 'coordinador';
        $assemblyId = $_GET['asamblea'] ?? null;
        $assemblies = $this->getActiveAssemblies();
        $votaciones = [];
        $assembly = null;
        
        if ($assemblyId) {
            $assembly = $this->getAssemblyDetails($assemblyId);
            $votaciones = $this->getAssemblyVotaciones($assemblyId);
        }
        
        require_once __DIR__ . '/../views/coordinator/votaciones.php';
    }
    
    // ================================
    // REPORTES
    // ================================
    public function reportesParticipacion() {
        $userRole = 'coordinador';
        $assemblyId = $_GET['asamblea'] ?? null;
        $dateFrom = $_GET['fecha_desde'] ?? date('Y-m-01');
        $dateTo = $_GET['fecha_hasta'] ?? date('Y-m-t');
        
        $assemblies = $this->getMyAssemblies();
        $participationReport = $this->getParticipationReport($assemblyId, $dateFrom, $dateTo);
        $attendanceStats = $this->getAttendanceStats($assemblyId, $dateFrom, $dateTo);
        
        require_once __DIR__ . '/../views/coordinator/reportes_participacion.php';
    }
    
    // ================================
    // MÉTODOS PARA PROCESAR ACCIONES
    // ================================
    
public function registrarAsistencia() {
    // Configurar headers para JSON
    header('Content-Type: application/json; charset=utf-8');
    
    // Debug inicial
    error_log("=== REGISTRAR ASISTENCIA DEBUG ===");
    error_log("METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
    error_log("POST data: " . print_r($_POST, true));
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("ERROR: Método no es POST");
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        // Obtener datos con validación
        $assemblyId = intval($_POST['asamblea_id'] ?? 0);
        $userId = intval($_POST['usuario_id'] ?? 0);
        $asistencia = intval($_POST['asistencia'] ?? 0);
        $coeficiente = floatval($_POST['coeficiente'] ?? 0);
        
        error_log("Datos procesados:");
        error_log("  Assembly ID: $assemblyId");
        error_log("  User ID: $userId");
        error_log("  Asistencia: $asistencia");
        error_log("  Coeficiente: $coeficiente");
        
        // Validaciones básicas
        if ($assemblyId <= 0) {
            throw new Exception('ID de asamblea inválido: ' . $assemblyId);
        }
        
        if ($userId <= 0) {
            throw new Exception('ID de usuario inválido: ' . $userId);
        }
        
        if ($coeficiente < 0 || $coeficiente > 1) {
            throw new Exception('El coeficiente debe estar entre 0 y 1. Recibido: ' . $coeficiente);
        }
        
        error_log("✅ Validaciones básicas OK");
        
        // Verificar sesión del coordinador
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        if ($coordinatorId <= 0) {
            throw new Exception('No hay sesión de coordinador válida');
        }
        
        error_log("Coordinator ID: $coordinatorId");
        
        // Verificar que la asamblea existe y el coordinador tiene permisos
        error_log("Verificando permisos de asamblea...");
        $assembly = $this->db->fetch("
            SELECT id, titulo, estado 
            FROM asambleas 
            WHERE id = ? AND (coordinador_id = ? OR administrador_id = ?)
        ", [$assemblyId, $coordinatorId, $coordinatorId]);
        
        error_log("Assembly query result: " . print_r($assembly, true));
        
        if (!$assembly) {
            throw new Exception('No tiene permisos para gestionar esta asamblea o la asamblea no existe');
        }
        
        error_log("✅ Permisos de asamblea OK");
        
        // Verificar que el participante existe en la asamblea
        error_log("Verificando participante en asamblea...");
        $participant = $this->db->fetch("
            SELECT pa.*, u.nombre, u.apellido 
            FROM participantes_asamblea pa
            JOIN usuarios u ON pa.usuario_id = u.id
            WHERE pa.asamblea_id = ? AND pa.usuario_id = ?
        ", [$assemblyId, $userId]);
        
        error_log("Participant query result: " . print_r($participant, true));
        
        if (!$participant) {
            throw new Exception('Participante no encontrado en esta asamblea');
        }
        
        error_log("✅ Participante encontrado: " . $participant['nombre'] . ' ' . $participant['apellido']);
        
        // Actualizar asistencia SIN updated_at
        error_log("Actualizando asistencia en base de datos...");
        
        $updateQuery = "
            UPDATE participantes_asamblea 
            SET asistencia = ?, 
                coeficiente_asignado = ?,
                hora_ingreso = CASE 
                    WHEN ? = 1 AND (hora_ingreso IS NULL OR hora_ingreso = '0000-00-00 00:00:00') THEN NOW() 
                    ELSE hora_ingreso 
                END,
                hora_salida = CASE 
                    WHEN ? = 0 THEN NOW() 
                    ELSE NULL 
                END
            WHERE asamblea_id = ? AND usuario_id = ?
        ";
        
        $updateParams = [$asistencia, $coeficiente, $asistencia, $asistencia, $assemblyId, $userId];
        
        error_log("Update query: $updateQuery");
        error_log("Update params: " . print_r($updateParams, true));
        
        $result = $this->db->execute($updateQuery, $updateParams);
        
        error_log("Update result: " . print_r($result, true));
        
        if ($result && isset($result['affected_rows']) && $result['affected_rows'] > 0) {
            error_log("✅ Asistencia actualizada correctamente");
            
            // Obtener datos actualizados para confirmar
            $updatedParticipant = $this->db->fetch("
                SELECT asistencia, coeficiente_asignado, hora_ingreso, hora_salida
                FROM participantes_asamblea 
                WHERE asamblea_id = ? AND usuario_id = ?
            ", [$assemblyId, $userId]);
            
            error_log("Datos actualizados: " . print_r($updatedParticipant, true));
            
            // Respuesta exitosa
            echo json_encode([
                'success' => true, 
                'message' => 'Asistencia actualizada correctamente',
                'data' => [
                    'usuario_id' => $userId,
                    'asistencia' => $asistencia,
                    'coeficiente' => $coeficiente,
                    'hora_ingreso' => $updatedParticipant['hora_ingreso'],
                    'hora_salida' => $updatedParticipant['hora_salida'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
            
        } else {
            throw new Exception('No se pudo actualizar la asistencia. Affected rows: ' . ($result['affected_rows'] ?? 'unknown'));
        }
        
    } catch (Exception $e) {
        error_log("❌ ERROR registrarAsistencia: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Respuesta de error
        http_response_code(400);
        echo json_encode([
            'error' => $e->getMessage(),
            'debug_info' => [
                'assembly_id' => $assemblyId ?? 'not_set',
                'user_id' => $userId ?? 'not_set',
                'coordinator_id' => $coordinatorId ?? 'not_set',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    }
    
    exit;
}
public function toggleAttendance() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $assemblyId = intval($input['asamblea_id'] ?? $_POST['asamblea_id'] ?? 0);
        $userId = intval($input['usuario_id'] ?? $_POST['usuario_id'] ?? 0);
        
        if ($assemblyId <= 0 || $userId <= 0) {
            throw new Exception('Parámetros inválidos');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        $assembly = $this->db->fetch("
            SELECT id FROM asambleas 
            WHERE id = ? AND (coordinador_id = ? OR administrador_id = ?)
        ", [$assemblyId, $coordinatorId, $coordinatorId]);
        
        if (!$assembly) {
            throw new Exception('No tiene permisos para gestionar esta asamblea');
        }
        
        // Obtener estado actual
        $participant = $this->db->fetch("
            SELECT asistencia, coeficiente_asignado FROM participantes_asamblea 
            WHERE asamblea_id = ? AND usuario_id = ?
        ", [$assemblyId, $userId]);
        
        if (!$participant) {
            throw new Exception('Participante no encontrado');
        }
        
        // Alternar estado
        $newAsistencia = $participant['asistencia'] == 1 ? 0 : 1;
        
        // Actualizar
        $result = $this->db->execute("
            UPDATE participantes_asamblea 
            SET asistencia = ?,
                hora_ingreso = CASE 
                    WHEN ? = 1 AND hora_ingreso IS NULL THEN NOW() 
                    ELSE hora_ingreso 
                END,
                hora_salida = CASE 
                    WHEN ? = 0 THEN NOW() 
                    ELSE NULL 
                END,
                updated_at = NOW()
            WHERE asamblea_id = ? AND usuario_id = ?
        ", [$newAsistencia, $newAsistencia, $newAsistencia, $assemblyId, $userId]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'new_status' => $newAsistencia,
                'message' => $newAsistencia == 1 ? 'Marcado como presente' : 'Marcado como ausente'
            ]);
        } else {
            throw new Exception('No se pudo actualizar el estado');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
public function bulkUpdateAttendance() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $assemblyId = intval($input['asamblea_id'] ?? 0);
        $userIds = $input['user_ids'] ?? [];
        $asistencia = intval($input['asistencia'] ?? 0);
        
        if ($assemblyId <= 0 || empty($userIds)) {
            throw new Exception('Parámetros inválidos');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        $assembly = $this->db->fetch("
            SELECT id FROM asambleas 
            WHERE id = ? AND (coordinador_id = ? OR administrador_id = ?)
        ", [$assemblyId, $coordinatorId, $coordinatorId]);
        
        if (!$assembly) {
            throw new Exception('No tiene permisos para gestionar esta asamblea');
        }
        
        $this->db->beginTransaction();
        
        try {
            $updated = 0;
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            $params = array_merge([$asistencia, $asistencia, $asistencia, $assemblyId], $userIds);
            
            $result = $this->db->execute("
                UPDATE participantes_asamblea 
                SET asistencia = ?,
                    hora_ingreso = CASE 
                        WHEN ? = 1 AND hora_ingreso IS NULL THEN NOW() 
                        ELSE hora_ingreso 
                    END,
                    hora_salida = CASE 
                        WHEN ? = 0 THEN NOW() 
                        ELSE NULL 
                    END,
                    updated_at = NOW()
                WHERE asamblea_id = ? AND usuario_id IN ($placeholders)
            ", $params);
            
            $updated = $result['affected_rows'] ?? 0;
            
            $this->db->commit();
            
            echo json_encode([
                'success' => true,
                'updated_count' => $updated,
                'message' => "Se actualizaron $updated participantes"
            ]);
            
        } catch (Exception $transactionError) {
            $this->db->rollback();
            throw $transactionError;
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
public function getQuorumDataAjax() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        $assemblyId = intval($_GET['asamblea'] ?? 0);
        
        if ($assemblyId <= 0) {
            throw new Exception('ID de asamblea inválido');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        $assembly = $this->db->fetch("
            SELECT id FROM asambleas 
            WHERE id = ? AND (coordinador_id = ? OR administrador_id = ?)
        ", [$assemblyId, $coordinatorId, $coordinatorId]);
        
        if (!$assembly) {
            throw new Exception('No tiene permisos para acceder a esta asamblea');
        }
        
        // Obtener datos de quórum actualizados
        $quorumData = $this->getQuorumData($assemblyId);
        
        if (!$quorumData) {
            throw new Exception('No se pudieron obtener los datos de quórum');
        }
        
        echo json_encode([
            'success' => true,
            'data' => $quorumData,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
public function getQuorumHistory() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        $assemblyId = intval($_GET['asamblea'] ?? 0);
        $limit = intval($_GET['limit'] ?? 10);
        
        if ($assemblyId <= 0) {
            throw new Exception('ID de asamblea inválido');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        $assembly = $this->db->fetch("
            SELECT id FROM asambleas 
            WHERE id = ? AND (coordinador_id = ? OR administrador_id = ?)
        ", [$assemblyId, $coordinatorId, $coordinatorId]);
        
        if (!$assembly) {
            throw new Exception('No tiene permisos para acceder a esta asamblea');
        }
        
        // Obtener historial de cambios de asistencia (como proxy del quórum)
        $historial = $this->db->fetchAll("
            SELECT 
                pa.usuario_id,
                u.nombre,
                u.apellido,
                pa.asistencia,
                pa.coeficiente_asignado,
                pa.hora_ingreso,
                pa.hora_salida,
                COALESCE(pa.updated_at, pa.created_at) as fecha_cambio
            FROM participantes_asamblea pa
            JOIN usuarios u ON pa.usuario_id = u.id
            WHERE pa.asamblea_id = ?
            AND (pa.hora_ingreso IS NOT NULL OR pa.hora_salida IS NOT NULL)
            ORDER BY COALESCE(pa.updated_at, pa.created_at) DESC
            LIMIT ?
        ", [$assemblyId, $limit]);
        
        echo json_encode([
            'success' => true,
            'historial' => $historial,
            'count' => count($historial),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
public function exportQuorumReport() {
    try {
        $assemblyId = intval($_GET['asamblea'] ?? 0);
        
        if ($assemblyId <= 0) {
            throw new Exception('ID de asamblea inválido');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        $assembly = $this->db->fetch("
            SELECT a.*, c.nombre as conjunto_nombre 
            FROM asambleas a
            JOIN conjuntos c ON a.conjunto_id = c.id
            WHERE a.id = ? AND (a.coordinador_id = ? OR a.administrador_id = ?)
        ", [$assemblyId, $coordinatorId, $coordinatorId]);
        
        if (!$assembly) {
            throw new Exception('No tiene permisos para exportar esta asamblea');
        }
        
        // Obtener datos de quórum
        $quorumData = $this->getQuorumData($assemblyId);
        
        if (!$quorumData) {
            throw new Exception('No se pudieron obtener los datos de quórum');
        }
        
        // Obtener detalles de participantes
        $participantes = $this->db->fetchAll("
            SELECT 
                u.nombre,
                u.apellido,
                u.cedula,
                v.apartamento,
                pa.asistencia,
                pa.coeficiente_asignado,
                pa.hora_ingreso,
                v.estado_pagos,
                pa.es_representado,
                rep.nombre as representante_nombre,
                rep.apellido as representante_apellido
            FROM participantes_asamblea pa
            JOIN usuarios u ON pa.usuario_id = u.id
            LEFT JOIN votantes v ON u.id = v.usuario_id
            LEFT JOIN usuarios rep ON pa.representante_id = rep.id
            WHERE pa.asamblea_id = ?
            ORDER BY pa.asistencia DESC, u.nombre, u.apellido
        ", [$assemblyId]);
        
        // Configurar headers para descarga
        $filename = 'quorum_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Escribir BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers del CSV
        fputcsv($output, [
            'REPORTE DE QUÓRUM',
            '',
            '',
            '',
            ''
        ], ';');
        
        fputcsv($output, [
            'Asamblea: ' . $assembly['titulo'],
            'Conjunto: ' . $assembly['conjunto_nombre'],
            'Fecha: ' . date('d/m/Y H:i:s', strtotime($assembly['fecha_inicio'])),
            'Estado: ' . ucfirst($assembly['estado']),
            'Generado: ' . date('d/m/Y H:i:s')
        ], ';');
        
        fputcsv($output, [], ';'); // Línea vacía
        
        // Resumen de quórum
        fputcsv($output, [
            'RESUMEN DE QUÓRUM',
            '',
            '',
            '',
            ''
        ], ';');
        
        fputcsv($output, [
            'Total Registrados:',
            $quorumData['total_registrados'],
            'Total Presentes:',
            $quorumData['total_asistentes'],
            '% Asistencia: ' . number_format($quorumData['porcentaje_asistencia'], 2) . '%'
        ], ';');
        
        fputcsv($output, [
            'Coeficiente Total:',
            number_format($quorumData['coeficiente_total'], 6),
            'Coeficiente Presente:',
            number_format($quorumData['coeficiente_presente'], 6),
            '% Coeficiente: ' . number_format($quorumData['porcentaje_coeficiente'], 2) . '%'
        ], ';');
        
        fputcsv($output, [
            'Quórum Mínimo:',
            $quorumData['quorum_minimo'] . '%',
            'Quórum Alcanzado:',
            $quorumData['quorum_alcanzado'] ? 'SÍ' : 'NO',
            ''
        ], ';');
        
        fputcsv($output, [], ';'); // Línea vacía
        
        // Headers de datos de participantes
        fputcsv($output, [
            'DETALLE DE PARTICIPANTES',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ], ';');
        
        fputcsv($output, [
            'Nombre',
            'Apellido',
            'Cédula',
            'Apartamento',
            'Asistencia',
            'Coeficiente',
            'Hora Ingreso',
            'Estado Pagos',
            'Representante'
        ], ';');
        
        // Datos de participantes
        foreach ($participantes as $participante) {
            $representante = '';
            if ($participante['es_representado'] && $participante['representante_nombre']) {
                $representante = $participante['representante_nombre'] . ' ' . $participante['representante_apellido'];
            }
            
            fputcsv($output, [
                $participante['nombre'],
                $participante['apellido'],
                $participante['cedula'],
                $participante['apartamento'] ?? 'N/A',
                $participante['asistencia'] == 1 ? 'Presente' : 'Ausente',
                number_format($participante['coeficiente_asignado'], 6),
                $participante['hora_ingreso'] ? date('H:i:s', strtotime($participante['hora_ingreso'])) : '',
                $participante['estado_pagos'] ?? 'N/A',
                $representante
            ], ';');
        }
        
        fclose($output);
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al exportar: ' . $e->getMessage();
        header('Location: /Asambleas/public/coordinador/quorum');
        exit;
    }
}
public function getQuorumTimeStats() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        $assemblyId = intval($_GET['asamblea'] ?? 0);
        $interval = $_GET['interval'] ?? '1 HOUR'; // 1 HOUR, 30 MINUTE, etc.
        
        if ($assemblyId <= 0) {
            throw new Exception('ID de asamblea inválido');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        $assembly = $this->db->fetch("
            SELECT id, fecha_inicio FROM asambleas 
            WHERE id = ? AND (coordinador_id = ? OR administrador_id = ?)
        ", [$assemblyId, $coordinatorId, $coordinatorId]);
        
        if (!$assembly) {
            throw new Exception('No tiene permisos para acceder a esta asamblea');
        }
        
        // Obtener cambios de asistencia por tiempo
        $stats = $this->db->fetchAll("
            SELECT 
                DATE_FORMAT(COALESCE(hora_ingreso, created_at), '%H:%i') as hora,
                COUNT(*) as cambios,
                SUM(CASE WHEN asistencia = 1 THEN 1 ELSE 0 END) as ingresos,
                SUM(CASE WHEN asistencia = 0 THEN 1 ELSE 0 END) as salidas,
                SUM(CASE WHEN asistencia = 1 THEN coeficiente_asignado ELSE 0 END) as coeficiente_agregado
            FROM participantes_asamblea 
            WHERE asamblea_id = ?
            AND (hora_ingreso IS NOT NULL OR hora_salida IS NOT NULL)
            GROUP BY DATE_FORMAT(COALESCE(hora_ingreso, created_at), '%H:%i')
            ORDER BY hora
        ", [$assemblyId]);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'assembly_start' => $assembly['fecha_inicio'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Activar/Finalizar asamblea con verificación de quórum
 */
public function toggleAssemblyStatus() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    try {
        $assemblyId = intval($_POST['asamblea_id'] ?? 0);
        $action = $_POST['action'] ?? ''; // 'activate' o 'finalize'
        
        if ($assemblyId <= 0) {
            throw new Exception('ID de asamblea inválido');
        }
        
        if (!in_array($action, ['activate', 'finalize'])) {
            throw new Exception('Acción inválida');
        }
        
        // Verificar permisos
        $coordinatorId = $_SESSION['user_id'] ?? 0;
        $assembly = $this->db->fetch("
            SELECT * FROM asambleas 
            WHERE id = ? AND (coordinador_id = ? OR administrador_id = ?)
        ", [$assemblyId, $coordinatorId, $coordinatorId]);
        
        if (!$assembly) {
            throw new Exception('No tiene permisos para gestionar esta asamblea');
        }
        
        if ($action === 'activate') {
            // Verificar que se pueda activar
            if ($assembly['estado'] === 'activa') {
                throw new Exception('La asamblea ya está activa');
            }
            
            // Verificar quórum antes de activar
            $quorumData = $this->getQuorumData($assemblyId);
            if (!$quorumData || !$quorumData['quorum_alcanzado']) {
                throw new Exception('No se puede activar la asamblea sin quórum suficiente');
            }
            
            // Activar asamblea
            $result = $this->db->execute("
                UPDATE asambleas 
                SET estado = 'activa', fecha_inicio = NOW() 
                WHERE id = ?
            ", [$assemblyId]);
            
            $message = 'Asamblea activada correctamente';
            
        } else { // finalize
            // Verificar que se pueda finalizar
            if ($assembly['estado'] === 'finalizada') {
                throw new Exception('La asamblea ya está finalizada');
            }
            
            // Finalizar asamblea
            $result = $this->db->execute("
                UPDATE asambleas 
                SET estado = 'finalizada', fecha_fin = NOW() 
                WHERE id = ?
            ", [$assemblyId]);
            
            $message = 'Asamblea finalizada correctamente';
        }
        
        if ($result && $result['affected_rows'] > 0) {
            echo json_encode([
                'success' => true,
                'message' => $message,
                'new_status' => $action === 'activate' ? 'activa' : 'finalizada'
            ]);
        } else {
            throw new Exception('No se pudo actualizar el estado de la asamblea');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
    public function agregarParticipante() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/coordinador/participantes');
            exit;
        }
        
        try {
            $assemblyId = $_POST['asamblea_id'] ?? 0;
            $userId = $_POST['usuario_id'] ?? 0;
            $coeficiente = $_POST['coeficiente'] ?? 0;
            
            $this->addParticipant($assemblyId, $userId, $coeficiente);
            $_SESSION['success'] = 'Participante agregado correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al agregar participante: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/coordinador/participantes?asamblea=' . ($assemblyId ?? ''));
        exit;
    }
    
    // ================================
    // MÉTODOS PRIVADOS PARA DATOS
    // ================================
    
    private function getMyAssemblies() {
        try {
            $coordinatorId = $_SESSION['user_id'] ?? 0;
            return $this->db->fetchAll("
                SELECT a.*, c.nombre as conjunto_nombre,
                       COUNT(pa.id) as total_participantes,
                       SUM(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) as total_asistentes
                FROM asambleas a 
                JOIN conjuntos c ON a.conjunto_id = c.id 
                LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                WHERE a.coordinador_id = ? OR a.administrador_id = ?
                GROUP BY a.id
                ORDER BY a.fecha_inicio DESC
            ", [$coordinatorId, $coordinatorId]);
        } catch (Exception $e) {
            error_log("Error getting my assemblies: " . $e->getMessage());
            return [];
        }
    }
    
    private function getUpcomingAssemblies() {
        try {
            $coordinatorId = $_SESSION['user_id'] ?? 0;
            return $this->db->fetchAll("
                SELECT a.*, c.nombre as conjunto_nombre 
                FROM asambleas a 
                JOIN conjuntos c ON a.conjunto_id = c.id 
                WHERE (a.coordinador_id = ? OR a.administrador_id = ?)
                AND a.fecha_inicio > NOW() 
                AND a.estado IN ('programada', 'activa')
                ORDER BY a.fecha_inicio ASC 
                LIMIT 5
            ", [$coordinatorId, $coordinatorId]);
        } catch (Exception $e) {
            error_log("Error getting upcoming assemblies: " . $e->getMessage());
            return [];
        }
    }
    
    private function getActiveAssemblies() {
        try {
            $coordinatorId = $_SESSION['user_id'] ?? 0;
            return $this->db->fetchAll("
                SELECT a.*, c.nombre as conjunto_nombre 
                FROM asambleas a 
                JOIN conjuntos c ON a.conjunto_id = c.id 
                WHERE (a.coordinador_id = ? OR a.administrador_id = ?)
                AND a.estado = 'activa'
                ORDER BY a.fecha_inicio DESC
            ", [$coordinatorId, $coordinatorId]);
        } catch (Exception $e) {
            error_log("Error getting active assemblies: " . $e->getMessage());
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
                SELECT pa.*, u.nombre, u.apellido, u.cedula, u.email,
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
    
    private function getAvailableUsers($assemblyId) {
        try {
            return $this->db->fetchAll("
                SELECT u.id, u.nombre, u.apellido, u.cedula, v.apartamento, v.coeficiente
                FROM usuarios u
                LEFT JOIN votantes v ON u.id = v.usuario_id
                WHERE u.tipo_usuario = 'votante' 
                AND u.activo = 1
                AND u.id NOT IN (
                    SELECT usuario_id FROM participantes_asamblea WHERE asamblea_id = ?
                )
                ORDER BY u.nombre, u.apellido
            ", [$assemblyId]);
        } catch (Exception $e) {
            error_log("Error getting available users: " . $e->getMessage());
            return [];
        }
    }
    
    private function getQuorumData($assemblyId) {
        try {
            $data = $this->db->fetch("
                SELECT 
                    a.quorum_minimo,
                    COUNT(pa.id) as total_registrados,
                    SUM(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) as total_asistentes,
                    SUM(CASE WHEN pa.asistencia = 1 THEN pa.coeficiente_asignado ELSE 0 END) as coeficiente_presente,
                    SUM(pa.coeficiente_asignado) as coeficiente_total
                FROM asambleas a
                LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                WHERE a.id = ?
                GROUP BY a.id
            ", [$assemblyId]);
            
            if ($data) {
                $data['porcentaje_asistencia'] = $data['total_registrados'] > 0 
                    ? round(($data['total_asistentes'] / $data['total_registrados']) * 100, 2) 
                    : 0;
                $data['porcentaje_coeficiente'] = $data['coeficiente_total'] > 0 
                    ? round(($data['coeficiente_presente'] / $data['coeficiente_total']) * 100, 2) 
                    : 0;
                $data['quorum_alcanzado'] = $data['porcentaje_coeficiente'] >= $data['quorum_minimo'];
            }
            
            return $data;
        } catch (Exception $e) {
            error_log("Error getting quorum data: " . $e->getMessage());
            return null;
        }
    }
    
    private function getAssemblyVotaciones($assemblyId) {
        try {
            return $this->db->fetchAll("
                SELECT v.*, 
                       COUNT(vo.id) as total_votos,
                       SUM(vo.coeficiente_voto) as coeficiente_votado
                FROM votaciones v
                LEFT JOIN votos vo ON v.id = vo.votacion_id
                WHERE v.asamblea_id = ?
                GROUP BY v.id
                ORDER BY v.created_at DESC
            ", [$assemblyId]);
        } catch (Exception $e) {
            error_log("Error getting assembly votaciones: " . $e->getMessage());
            return [];
        }
    }
    
    private function getTodayStats() {
        try {
            $coordinatorId = $_SESSION['user_id'] ?? 0;
            return $this->db->fetch("
                SELECT 
                    COUNT(CASE WHEN DATE(a.fecha_inicio) = CURDATE() THEN 1 END) as asambleas_hoy,
                    COUNT(CASE WHEN a.estado = 'activa' THEN 1 END) as asambleas_activas,
                    COUNT(CASE WHEN DATE(a.fecha_inicio) = CURDATE() AND a.estado = 'activa' THEN 1 END) as activas_hoy
                FROM asambleas a
                WHERE a.coordinador_id = ? OR a.administrador_id = ?
            ", [$coordinatorId, $coordinatorId]);
        } catch (Exception $e) {
            error_log("Error getting today stats: " . $e->getMessage());
            return ['asambleas_hoy' => 0, 'asambleas_activas' => 0, 'activas_hoy' => 0];
        }
    }
    
    private function getWeeklyStats() {
        try {
            $coordinatorId = $_SESSION['user_id'] ?? 0;
            return $this->db->fetchAll("
                SELECT 
                    DATE(a.fecha_inicio) as fecha,
                    COUNT(*) as total_asambleas,
                    AVG(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) * 100 as promedio_asistencia
                FROM asambleas a
                LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                WHERE (a.coordinador_id = ? OR a.administrador_id = ?)
                AND a.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(a.fecha_inicio)
                ORDER BY fecha DESC
            ", [$coordinatorId, $coordinatorId]);
        } catch (Exception $e) {
            error_log("Error getting weekly stats: " . $e->getMessage());
            return [];
        }
    }
    
    private function getCoordinatorAlerts() {
        try {
            $alerts = [];
            $coordinatorId = $_SESSION['user_id'] ?? 0;
            
            // Asambleas sin quórum
            $lowQuorum = $this->db->fetchAll("
                SELECT a.titulo, a.id
                FROM asambleas a
                LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                WHERE (a.coordinador_id = ? OR a.administrador_id = ?)
                AND a.estado = 'activa'
                GROUP BY a.id
                HAVING (SUM(CASE WHEN pa.asistencia = 1 THEN pa.coeficiente_asignado ELSE 0 END) / 
                        SUM(pa.coeficiente_asignado)) * 100 < a.quorum_minimo
            ", [$coordinatorId, $coordinatorId]);
            
            foreach ($lowQuorum as $assembly) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'message' => "La asamblea '{$assembly['titulo']}' no tiene quórum suficiente"
                ];
            }
            
            // Asambleas próximas
            $upcoming = $this->db->fetch("
                SELECT COUNT(*) as total
                FROM asambleas a
                WHERE (a.coordinador_id = ? OR a.administrador_id = ?)
                AND DATE(a.fecha_inicio) = CURDATE()
                AND a.estado = 'programada'
            ", [$coordinatorId, $coordinatorId]);
            
            if ($upcoming && $upcoming['total'] > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'calendar',
                    'message' => "Tienes {$upcoming['total']} asamblea(s) programada(s) para hoy"
                ];
            }
            
            return $alerts;
        } catch (Exception $e) {
            error_log("Error getting coordinator alerts: " . $e->getMessage());
            return [];
        }
    }
    
    private function getParticipationReport($assemblyId, $dateFrom, $dateTo) {
        try {
            $whereClause = "1=1";
            $params = [];
            
            if ($assemblyId) {
                $whereClause .= " AND a.id = ?";
                $params[] = $assemblyId;
            }
            
            $whereClause .= " AND DATE(a.fecha_inicio) BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
            
            return $this->db->fetchAll("
                SELECT a.titulo, a.fecha_inicio,
                       COUNT(pa.id) as total_registrados,
                       SUM(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) as total_asistentes,
                       ROUND(SUM(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(pa.id), 2) as porcentaje_asistencia
                FROM asambleas a
                LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                WHERE $whereClause
                GROUP BY a.id
                ORDER BY a.fecha_inicio DESC
            ", $params);
        } catch (Exception $e) {
            error_log("Error getting participation report: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAttendanceStats($assemblyId, $dateFrom, $dateTo) {
        try {
            $whereClause = "1=1";
            $params = [];
            
            if ($assemblyId) {
                $whereClause .= " AND a.id = ?";
                $params[] = $assemblyId;
            }
            
            $whereClause .= " AND DATE(a.fecha_inicio) BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
            
            return $this->db->fetch("
                SELECT 
                    COUNT(DISTINCT a.id) as total_asambleas,
                    COUNT(pa.id) as total_registros,
                    SUM(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) as total_asistencias,
                    ROUND(AVG(CASE WHEN pa.asistencia = 1 THEN 1 ELSE 0 END) * 100, 2) as promedio_asistencia
                FROM asambleas a
                LEFT JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                WHERE $whereClause
            ", $params);
        } catch (Exception $e) {
            error_log("Error getting attendance stats: " . $e->getMessage());
            return [
                'total_asambleas' => 0,
                'total_registros' => 0,
                'total_asistencias' => 0,
                'promedio_asistencia' => 0
            ];
        }
    }
    
    private function updateAttendance($assemblyId, $userId, $asistencia, $coeficiente) {
        return $this->db->execute("
            UPDATE participantes_asamblea 
            SET asistencia = ?, coeficiente_asignado = ?, 
                hora_ingreso = CASE WHEN ? = 1 AND hora_ingreso IS NULL THEN NOW() ELSE hora_ingreso END,
                hora_salida = CASE WHEN ? = 0 THEN NOW() ELSE NULL END
            WHERE asamblea_id = ? AND usuario_id = ?
        ", [$asistencia, $coeficiente, $asistencia, $asistencia, $assemblyId, $userId]);
    }
    
    private function addParticipant($assemblyId, $userId, $coeficiente) {
        return $this->db->execute("
            INSERT INTO participantes_asamblea (asamblea_id, usuario_id, rol, coeficiente_asignado)
            SELECT ?, ?, u.tipo_usuario, ?
            FROM usuarios u 
            WHERE u.id = ?
        ", [$assemblyId, $userId, $coeficiente, $userId]);
    }
}


?>
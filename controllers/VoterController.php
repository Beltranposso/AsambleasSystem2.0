<?php
require_once '../core/Database.php';

class VoterController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // ========================================
    // MÉTODO AUXILIAR PARA OBTENER ID DEL VOTO
    // ========================================
    
    private function getLastInsertedVoteId($votingId, $userId) {
        try {
            // Buscar el voto recién insertado
            $vote = $this->db->fetch(
                "SELECT id FROM votos 
                 WHERE votacion_id = ? AND usuario_id = ? 
                 ORDER BY timestamp_voto DESC, id DESC 
                 LIMIT 1", 
                [$votingId, $userId]
            );
            
            return $vote ? $vote['id'] : null;
            
        } catch (Exception $e) {
            error_log("Error obteniendo ID del voto: " . $e->getMessage());
            return null;
        }
    }
    
    public function dashboard() {
        $voterInfo = $this->getVoterInfo();
        $myAssemblies = $this->getMyAssemblies();
        $availableVotings = $this->getAvailableVotings();
        
        require_once '../views/voter/dashboard.php';
    }
    
    // ========================================
    // MÉTODOS CORREGIDOS PRINCIPALES
    // ========================================
    
private function getVoterInfo() {
    try {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) return null;
        
        // CONSULTA MEJORADA: Obtener coeficiente real
        $sql = "SELECT 
                    u.*,
                    u.nombre as nombres,
                    u.apellido as apellidos,
                    COALESCE(
                        (SELECT SUM(pa.coeficiente_asignado) 
                         FROM participantes_asamblea pa 
                         WHERE pa.usuario_id = u.id AND pa.rol = 'votante'), 
                        COALESCE(
                            (SELECT SUM(v.coeficiente) 
                             FROM votantes v 
                             WHERE v.usuario_id = u.id), 
                            1.0
                        )
                    ) as coeficiente_participacion
                FROM usuarios u
                WHERE u.id = ?";
        
        $result = $this->db->fetch($sql, [$userId]);
        
        if ($result) {
            error_log("Información del votante ID $userId:");
            error_log("  Nombre: {$result['nombres']} {$result['apellidos']}");
            error_log("  Coeficiente: {$result['coeficiente_participacion']}");
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error en getVoterInfo: " . $e->getMessage());
        return null;
    }
}
private function getVotingStatistics($votingId) {
    try {
        $sql = "SELECT 
                    COUNT(DISTINCT v.usuario_id) as total_votantes,
                    COUNT(v.id) as total_votos,
                    SUM(v.coeficiente_voto) as total_coeficiente_usado,
                    AVG(v.coeficiente_voto) as promedio_coeficiente,
                    MAX(v.coeficiente_voto) as max_coeficiente,
                    MIN(v.coeficiente_voto) as min_coeficiente
                FROM votos v
                WHERE v.votacion_id = ?";
        
        $stats = $this->db->fetch($sql, [$votingId]);
        
        // Obtener total de participantes elegibles
        $eligibleSql = "SELECT COUNT(DISTINCT pa.usuario_id) as total_elegibles
                        FROM votaciones vo
                        JOIN asambleas a ON vo.asamblea_id = a.id
                        JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                        WHERE vo.id = ? AND pa.rol = 'votante'";
        
        $eligible = $this->db->fetch($eligibleSql, [$votingId]);
        
        if ($stats && $eligible) {
            $stats['total_elegibles'] = $eligible['total_elegibles'];
            $stats['porcentaje_participacion'] = $eligible['total_elegibles'] > 0 
                ? round(($stats['total_votantes'] / $eligible['total_elegibles']) * 100, 2) 
                : 0;
        }
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error en getVotingStatistics: " . $e->getMessage());
        return null;
    }
}

    
    private function getAvailableVotings() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) return [];
            
            // FIX 2: Consulta más robusta con verificación de participación
            $sql = "SELECT DISTINCT 
                        v.id,
                        v.titulo,
                        v.descripcion,
                        v.fecha_cierre,
                        v.estado,
                        v.created_at,
                        a.titulo as asamblea_titulo,
                        a.id as asamblea_id,
                        CASE 
                            WHEN EXISTS(
                                SELECT 1 FROM votos vo 
                                WHERE vo.votacion_id = v.id AND vo.usuario_id = ?
                            ) THEN 1 
                            ELSE 0 
                        END as ya_voto,
                        CASE 
                            WHEN v.estado = 'abierta' 
                            AND (v.fecha_cierre IS NULL OR v.fecha_cierre > NOW())
                            AND NOT EXISTS(
                                SELECT 1 FROM votos vo 
                                WHERE vo.votacion_id = v.id AND vo.usuario_id = ?
                            )
                            THEN 1 
                            ELSE 0 
                        END as puede_votar
                    FROM votaciones v 
                    JOIN asambleas a ON v.asamblea_id = a.id
                    JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                    WHERE pa.usuario_id = ?
                    AND pa.rol = 'votante'
                    AND v.estado IN ('abierta', 'cerrada')
                    ORDER BY v.created_at DESC";
            
            return $this->db->fetchAll($sql, [$userId, $userId, $userId]);
            
        } catch (Exception $e) {
            error_log("Error en getAvailableVotings: " . $e->getMessage());
            return [];
        }
    }
    
    // FIX 3: Método canVote completamente reescrito y más robusto
    private function canVote($votingId) {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            error_log("=== VERIFICANDO PERMISO DE VOTO ===");
            error_log("User ID: $userId, Voting ID: $votingId");
            
            if (!$userId || !$votingId) {
                error_log("ERROR: Faltan parámetros básicos");
                return false;
            }
            
            // Paso 1: Verificar que la votación existe y está abierta
            $voting = $this->db->fetch(
                "SELECT id, estado, fecha_cierre, asamblea_id FROM votaciones WHERE id = ?", 
                [$votingId]
            );
            
            if (!$voting) {
                error_log("ERROR: Votación no existe");
                return false;
            }
            
            error_log("Votación encontrada - Estado: " . $voting['estado']);
            
            if ($voting['estado'] !== 'abierta') {
                error_log("ERROR: Votación no está abierta");
                return false;
            }
            
            // Paso 2: Verificar fecha de cierre
            if (!empty($voting['fecha_cierre'])) {
                $now = new DateTime();
                $closeDate = new DateTime($voting['fecha_cierre']);
                if ($now > $closeDate) {
                    error_log("ERROR: Votación ha expirado");
                    return false;
                }
            }
            
            // Paso 3: Verificar participación en la asamblea
            $participation = $this->db->fetch(
                "SELECT pa.id, pa.rol, pa.asistencia, pa.coeficiente_asignado
                 FROM participantes_asamblea pa
                 WHERE pa.asamblea_id = ? AND pa.usuario_id = ? AND pa.rol = 'votante'", 
                [$voting['asamblea_id'], $userId]
            );
            
            if (!$participation) {
                error_log("ERROR: Usuario no participa en la asamblea");
                return false;
            }
            
            error_log("Participación encontrada - Rol: " . $participation['rol']);
            
            // Paso 4: Verificar que no haya votado ya
            $existingVote = $this->db->fetch(
                "SELECT id FROM votos WHERE votacion_id = ? AND usuario_id = ?", 
                [$votingId, $userId]
            );
            
            if ($existingVote) {
                error_log("ERROR: Usuario ya votó - Voto ID: " . $existingVote['id']);
                return false;
            }
            
            error_log("✅ USUARIO PUEDE VOTAR");
            return true;
            
        } catch (Exception $e) {
            error_log("ERROR en canVote: " . $e->getMessage());
            return false;
        }
    }
    
    // FIX 4: Coeficiente más robusto
    private function getVoterCoefficient($votingId, $userId) {
        try {
            // Opción 1: Coeficiente asignado en participantes_asamblea
            $sql = "SELECT pa.coeficiente_asignado, a.id as asamblea_id
                    FROM votaciones v
                    JOIN asambleas a ON v.asamblea_id = a.id
                    JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                    WHERE v.id = ? AND pa.usuario_id = ? AND pa.rol = 'votante'";
            
            $result = $this->db->fetch($sql, [$votingId, $userId]);
            
            if ($result && !empty($result['coeficiente_asignado']) && $result['coeficiente_asignado'] > 0) {
                error_log("Coeficiente desde participantes_asamblea: " . $result['coeficiente_asignado']);
                return floatval($result['coeficiente_asignado']);
            }
            
            // Opción 2: Coeficiente desde tabla votantes
            $fallbackSql = "SELECT v.coeficiente 
                           FROM votantes v 
                           JOIN asambleas a ON a.conjunto_id = v.conjunto_id
                           JOIN votaciones vo ON vo.asamblea_id = a.id
                           WHERE vo.id = ? AND v.usuario_id = ?";
            
            $fallback = $this->db->fetch($fallbackSql, [$votingId, $userId]);
            
            if ($fallback && $fallback['coeficiente'] > 0) {
                error_log("Coeficiente desde votantes: " . $fallback['coeficiente']);
                return floatval($fallback['coeficiente']);
            }
            
            // Opción 3: Valor por defecto
            error_log("Usando coeficiente por defecto: 1.0");
            return 1.0;
            
        } catch (Exception $e) {
            error_log("Error obteniendo coeficiente: " . $e->getMessage());
            return 1.0;
        }
    }
    
    // ========================================
    // MÉTODOS AJAX CORREGIDOS
    // ========================================
    
public function getVotingDetailsAjax() {
    // Limpieza completa de output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    error_log("=== AJAX VOTING DETAILS START ===");
    
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    try {
        $votingId = $_POST['voting_id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        
        error_log("Voting ID: $votingId, User ID: $userId");
        
        if (!$votingId || !$userId) {
            echo json_encode(['success' => false, 'error' => 'Parámetros faltantes']);
            exit;
        }
        
        // Verificar acceso básico
        if (!$this->canAccessVoting($votingId, $userId)) {
            echo json_encode(['success' => false, 'error' => 'No tienes acceso a esta votación']);
            exit;
        }
        
        // Obtener detalles
        $voting = $this->getVotingDetails($votingId);
        if (!$voting) {
            echo json_encode(['success' => false, 'error' => 'Votación no encontrada']);
            exit;
        }
        
        // Obtener opciones con conteos
        $options = $this->getVotingOptions($votingId);
        if (empty($options)) {
            echo json_encode(['success' => false, 'error' => 'No hay opciones disponibles para esta votación']);
            exit;
        }
        
        // Obtener estadísticas
        $statistics = $this->getVotingStatistics($votingId);
        
        // Formatear opciones para el frontend
        $formattedOptions = [];
        foreach ($options as $option) {
            $formattedOptions[] = [
                'id' => $option['id'],
                'opcion' => $option['opcion'],
                'descripcion' => $option['descripcion'] ?? '',
                'orden_display' => $option['orden_display'] ?? 0,
                'votos' => (int)$option['total_votos'],
                'coeficiente_total' => (float)$option['total_coeficiente'],
                'poder_voto' => (float)$option['poder_voto']
            ];
        }
        
        $response = [
            'success' => true,
            'voting' => $voting,
            'options' => $formattedOptions,
            'statistics' => $statistics,
            'debug_info' => [
                'user_id' => $userId,
                'voting_id' => $votingId,
                'options_count' => count($formattedOptions),
                'total_votes' => $statistics['total_votos'] ?? 0,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
        
        error_log("SUCCESS: Enviando respuesta con " . count($formattedOptions) . " opciones y " . ($statistics['total_votos'] ?? 0) . " votos");
        echo json_encode($response);
        
    } catch (Exception $e) {
        error_log("ERROR en getVotingDetailsAjax: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'error' => 'Error interno del servidor',
            'debug_info' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]);
    }
    
    exit;
}
    public function checkVoteStatus() {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $votingId = $_POST['voting_id'] ?? null;
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$votingId || !$userId) {
                echo json_encode(['success' => false, 'error' => 'Parámetros faltantes']);
                exit;
            }
            
            // Verificar si ya votó
            $existingVote = $this->db->fetch(
                "SELECT vo.id, vo.timestamp_voto, ov.opcion 
                 FROM votos vo 
                 JOIN opciones_votacion ov ON vo.opcion_id = ov.id 
                 WHERE vo.votacion_id = ? AND vo.usuario_id = ?", 
                [$votingId, $userId]
            );
            
            $canVote = $this->canVote($votingId);
            
            echo json_encode([
                'success' => true,
                'hasVoted' => !empty($existingVote),
                'canVote' => $canVote,
                'vote' => $existingVote
            ]);
            
        } catch (Exception $e) {
            error_log("Error en checkVoteStatus: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error interno']);
        }
        exit;
    }
    
    // ========================================
    // MÉTODO PROCESAR VOTO - ULTRA CORREGIDO
    // ========================================
    
    public function procesarVoto() {
        error_log("========================================");
        error_log("=== PROCESANDO VOTO - VERSIÓN CORREGIDA ===");
        error_log("========================================");
        
        // FIX 6: Verificar método HTTP antes que nada
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("ERROR: Método incorrecto - " . $_SERVER['REQUEST_METHOD']);
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Método no permitido']);
                exit;
            }
            
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: /Asambleas/public/dashboard/votante');
            exit;
        }
        
        // Extraer datos
        $votingId = $_POST['voting_id'] ?? null;
        $optionId = $_POST['option_id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        
        error_log("Datos recibidos:");
        error_log("  Voting ID: " . ($votingId ?: 'NULL'));
        error_log("  Option ID: " . ($optionId ?: 'NULL'));
        error_log("  User ID: " . ($userId ?: 'NULL'));
        error_log("  Ajax request: " . (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) ? 'SÍ' : 'NO'));
        
        // Validar datos básicos
        if (!$votingId || !$optionId || !$userId) {
            error_log("ERROR: Datos incompletos");
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                while (ob_get_level()) ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
                exit;
            }
            
            $_SESSION['error'] = 'Datos de votación incompletos.';
            header('Location: /Asambleas/public/dashboard/votante');
            exit;
        }
        
        try {
            // FIX 7: Verificaciones en orden lógico
            
            // 1. Verificar que puede votar (incluye todas las validaciones)
            if (!$this->canVote($votingId)) {
                throw new Exception('No puedes votar en esta votación o ya has votado');
            }
            
            // 2. Verificar opción válida
            $optionExists = $this->db->fetch(
                "SELECT id, opcion FROM opciones_votacion WHERE id = ? AND votacion_id = ?", 
                [$optionId, $votingId]
            );
            
            if (!$optionExists) {
                throw new Exception('Opción de voto inválida');
            }
            
            error_log("✅ Opción válida: " . $optionExists['opcion']);
            
            // 3. Obtener coeficiente
            $coefficient = $this->getVoterCoefficient($votingId, $userId);
            error_log("✅ Coeficiente: $coefficient");
            
            // 4. Transacción para insertar voto
            $this->db->beginTransaction();
            
            try {
                // Verificación final de duplicado dentro de la transacción
                $finalCheck = $this->db->fetch(
                    "SELECT id FROM votos WHERE votacion_id = ? AND usuario_id = ? FOR UPDATE", 
                    [$votingId, $userId]
                );
                
                if ($finalCheck) {
                    throw new Exception('Ya has votado en esta votación');
                }
                
                // Insertar voto
                $insertSql = "INSERT INTO votos (votacion_id, usuario_id, opcion_id, coeficiente_voto, timestamp_voto, ip_address) 
                              VALUES (?, ?, ?, ?, NOW(), ?)";
                
                $result = $this->db->execute($insertSql, [
                    $votingId, 
                    $userId, 
                    $optionId, 
                    $coefficient, 
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]);
                
                if (!$result) {
                    throw new Exception('Error al insertar el voto');
                }
                
                // Verificar inserción - Obtener el ID del voto insertado
                $voteId = $this->getLastInsertedVoteId($votingId, $userId);
                error_log("✅ Voto insertado con ID: $voteId");
                
                $this->db->commit();
                
                // Log de auditoría
                $this->logVotingActivity($userId, $votingId, 'VOTO_EMITIDO', [
                    'opcion_id' => $optionId,
                    'coeficiente' => $coefficient,
                    'voto_id' => $voteId
                ]);
                
                error_log("✅ VOTO PROCESADO EXITOSAMENTE");
                
                // Respuesta
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    while (ob_get_level()) ob_end_clean();
                    header('Content-Type: application/json');
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Voto registrado exitosamente',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'vote_id' => $voteId
                    ]);
                    exit;
                } else {
                    $_SESSION['success'] = 'Tu voto ha sido registrado exitosamente.';
                    header('Location: /Asambleas/public/dashboard/votante');
                    exit;
                }
                
            } catch (Exception $transactionError) {
                $this->db->rollback();
                throw $transactionError;
            }
            
        } catch (Exception $e) {
            error_log("ERROR: " . $e->getMessage());
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                while (ob_get_level()) ob_end_clean();
                header('Content-Type: application/json');
                
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
                exit;
            } else {
                $_SESSION['error'] = 'Error al registrar el voto: ' . $e->getMessage();
                header('Location: /Asambleas/public/dashboard/votante');
                exit;
            }
        }
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    private function canAccessVoting($votingId, $userId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM votaciones v
                    JOIN asambleas a ON v.asamblea_id = a.id
                    JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                    WHERE v.id = ? AND pa.usuario_id = ? AND pa.rol = 'votante'";
            
            $result = $this->db->fetch($sql, [$votingId, $userId]);
            return $result && $result['count'] > 0;
            
        } catch (Exception $e) {
            error_log("Error en canAccessVoting: " . $e->getMessage());
            return false;
        }
    }
    
    private function getVotingDetails($votingId) {
        try {
            $sql = "SELECT v.*, a.titulo as asamblea_titulo 
                    FROM votaciones v 
                    JOIN asambleas a ON v.asamblea_id = a.id 
                    WHERE v.id = ?";
            
            return $this->db->fetch($sql, [$votingId]);
            
        } catch (Exception $e) {
            error_log("Error en getVotingDetails: " . $e->getMessage());
            return null;
        }
    }
    
 private function getVotingOptions($votingId) {
    try {
        // NUEVA CONSULTA: Incluye conteo de votos y coeficientes
        $sql = "SELECT 
                    ov.*,
                    COALESCE(COUNT(v.id), 0) as total_votos,
                    COALESCE(SUM(v.coeficiente_voto), 0) as total_coeficiente,
                    COALESCE(SUM(CASE WHEN v.coeficiente_voto > 0 THEN v.coeficiente_voto ELSE 1 END), 0) as poder_voto
                FROM opciones_votacion ov
                LEFT JOIN votos v ON ov.id = v.opcion_id
                WHERE ov.votacion_id = ?
                GROUP BY ov.id, ov.opcion, ov.descripcion, ov.orden_display
                ORDER BY ov.orden_display ASC, ov.id ASC";
        
        $options = $this->db->fetchAll($sql, [$votingId]);
        
        // Debug para verificar los datos
        error_log("Opciones con votos para votación $votingId:");
        foreach ($options as $option) {
            error_log("  Opción {$option['id']}: {$option['opcion']} - Votos: {$option['total_votos']}, Coeficiente: {$option['total_coeficiente']}");
        }
        
        return $options;
        
    } catch (Exception $e) {
        error_log("Error en getVotingOptions: " . $e->getMessage());
        return [];
    }
}
    
    private function logVotingActivity($userId, $votingId, $action, $details = []) {
        try {
            // Verificar si la tabla existe
            $tableExists = $this->db->fetch("SHOW TABLES LIKE 'voting_logs'");
            
            if ($tableExists) {
                $sql = "INSERT INTO voting_logs (usuario_id, votacion_id, accion, detalles, ip_address, user_agent, timestamp) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
                
                $this->db->execute($sql, [
                    $userId,
                    $votingId,
                    $action,
                    json_encode($details),
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Error en log (no crítico): " . $e->getMessage());
        }
    }
    
    // ========================================
    // MÉTODOS SIN CAMBIOS IMPORTANTES
    // ========================================
    
    private function getMyAssemblies() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) return [];
            
            $sql = "SELECT DISTINCT
                        a.id,
                        a.titulo,
                        a.descripcion,
                        a.fecha_inicio,
                        a.estado,
                        c.nombre as conjunto_nombre,
                        v.apartamento,
                        v.coeficiente,
                        v.es_propietario,
                        v.estado_pagos,
                        CASE 
                            WHEN v.apartamento IS NOT NULL THEN CONCAT('Apto ', v.apartamento)
                            ELSE 'Propiedad'
                        END as tipo_propiedad,
                        CASE 
                            WHEN v.estado_pagos = 'al_dia' THEN 1
                            ELSE 0
                        END as al_dia
                    FROM participantes_asamblea pa
                    JOIN asambleas a ON pa.asamblea_id = a.id
                    JOIN conjuntos c ON a.conjunto_id = c.id
                    LEFT JOIN votantes v ON pa.usuario_id = v.usuario_id AND a.conjunto_id = v.conjunto_id
                    WHERE pa.usuario_id = ? AND pa.rol = 'votante'
                    ORDER BY a.fecha_inicio DESC";
            
            return $this->db->fetchAll($sql, [$userId]);
            
        } catch (Exception $e) {
            error_log("Error en getMyAssemblies: " . $e->getMessage());
            return [];
        }
    }
    
    public function profile() {
        $voterInfo = $this->getVoterInfo();
        $myAssemblies = $this->getMyAssemblies();
        require_once '../views/voter/profile.php';
    }
    
    public function myVotings() {
        $voterInfo = $this->getVoterInfo();
        $myAssemblies = $this->getMyAssemblies();
        $votings = $this->getMyVotings();
        require_once '../views/voter/my_votings.php';
    }
    
    private function getMyVotings() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) return [];
            
            $sql = "SELECT 
                        v.*,
                        vo.timestamp_voto,
                        ov.opcion,
                        a.titulo as asamblea_titulo
                    FROM votaciones v
                    JOIN asambleas a ON v.asamblea_id = a.id
                    JOIN participantes_asamblea pa ON a.id = pa.asamblea_id
                    LEFT JOIN votos vo ON v.id = vo.votacion_id AND vo.usuario_id = ?
                    LEFT JOIN opciones_votacion ov ON vo.opcion_id = ov.id
                    WHERE pa.usuario_id = ? AND pa.rol = 'votante'
                    ORDER BY v.created_at DESC";
            
            return $this->db->fetchAll($sql, [$userId, $userId]);
            
        } catch (Exception $e) {
            error_log("Error en getMyVotings: " . $e->getMessage());
            return [];
        }
    }
    
    public function asambleas() {
        $voterInfo = $this->getVoterInfo();
        $myAssemblies = $this->getMyAssemblies();
        require_once '../views/voter/assemblies.php';
    }
    
    public function historial() {
        $voterInfo = $this->getVoterInfo();
        $votingHistory = $this->getVotingHistory();
        require_once '../views/voter/history.php';
    }
    
    private function getVotingHistory() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) return [];
            
            $sql = "SELECT 
                        v.*,
                        vo.timestamp_voto,
                        ov.opcion,
                        a.titulo as asamblea_titulo
                    FROM votaciones v
                    JOIN asambleas a ON v.asamblea_id = a.id
                    JOIN votos vo ON v.id = vo.votacion_id
                    JOIN opciones_votacion ov ON vo.opcion_id = ov.id
                    WHERE vo.usuario_id = ?
                    ORDER BY vo.timestamp_voto DESC";
            
            return $this->db->fetchAll($sql, [$userId]);
            
        } catch (Exception $e) {
            error_log("Error en getVotingHistory: " . $e->getMessage());
            return [];
        }
    }
    
    public function documentos() {
        $voterInfo = $this->getVoterInfo();
        require_once '../views/voter/documents.php';
    }
}
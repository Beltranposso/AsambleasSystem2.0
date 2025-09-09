<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class AdminController {
    private $db;
    private $auth;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new AuthController();
        
        // Verificar que el usuario sea administrador
        $this->auth->requireRole('administrador');
    }
    
    // ================================
    // DASHBOARD PRINCIPAL
    // ================================
    public function dashboard() {
        $userRole = 'administrador';
        $totalAssemblies = $this->getTotalAssemblies();
        $activeAssemblies = $this->getActiveAssemblies();
        $totalVoters = $this->getTotalVoters();
        $totalCoordinators = $this->getTotalCoordinators();
        $metrics = $this->getSystemMetrics();
        $recentAssemblies = $this->getRecentAssemblies();
        $alerts = $this->getSystemAlerts();
        
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    // ================================
    // GESTIÓN DE ASAMBLEAS
    // ================================
    public function asambleas() {
        $assemblies = $this->getAssemblies();
        require_once __DIR__ . '/../views/admin/asambleas.php';
    }
    
  public function crearAsamblea() {
    try {
        $conjuntos = $this->getConjuntos();
        $coordinadores = $this->getCoordinadores();
        
        // Debug: verificar que los datos se están obteniendo
        error_log("Conjuntos obtenidos: " . count($conjuntos));
        error_log("Coordinadores obtenidos: " . count($coordinadores));
        
        require_once __DIR__ . '/../views/admin/cerate_assembly.php';
    } catch (Exception $e) {
        error_log("Error en crearAsamblea: " . $e->getMessage());
        $_SESSION['error'] = 'Error al cargar la página: ' . $e->getMessage();
        header('Location: /Asambleas/public/admin/asambleas');
        exit;
    }
}

    
    public function editarAsamblea($id) {
        $asamblea = $this->getAssemblyById($id);
        $conjuntos = $this->getConjuntos();
        $coordinadores = $this->getCoordinadores();
        
        if (!$asamblea) {
            $_SESSION['error'] = 'Asamblea no encontrada';
            header('Location: /Asambleas/public/admin/asambleas');
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/edit_assembly.php';
    }
    
    public function detalleAsamblea($id) {
        $assembly = $this->getAssemblyById($id);
        $participants = $this->getAssemblyParticipants($id);
        $statistics = $this->getAssemblyStatistics($id);
        
        if (!$assembly) {
            $_SESSION['error'] = 'Asamblea no encontrada';
            header('Location: /Asambleas/public/admin/asambleas');
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/detalle_asamblea.php';
    }
    public function crearUsuarioConjunto() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }
    
    try {
        // Validar datos requeridos
        $errores = [];
        
        if (empty($_POST['nombre'])) {
            $errores[] = 'El nombre es requerido';
        }
        
        if (empty($_POST['apellido'])) {
            $errores[] = 'El apellido es requerido';
        }
        
        if (empty($_POST['cedula'])) {
            $errores[] = 'La cédula es requerida';
        }
        
        if (empty($_POST['email'])) {
            $errores[] = 'El email es requerido';
        }
        
        if (empty($_POST['conjunto_id'])) {
            $errores[] = 'El conjunto es requerido';
        }
        
        if (empty($_POST['apartamento'])) {
            $errores[] = 'El apartamento/unidad es requerido';
        }
        
        if (empty($_POST['password'])) {
            $errores[] = 'La contraseña es requerida';
        }
        
        // Validar formato de email
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del email no es válido';
        }
        
        // Verificar si el email ya existe
        if (!empty($_POST['email'])) {
            $existingUser = $this->db->fetch("SELECT id FROM usuarios WHERE email = ?", [$_POST['email']]);
            if ($existingUser) {
                $errores[] = 'El email ya está registrado en el sistema';
            }
        }
        
        // Verificar si la cédula ya existe
        if (!empty($_POST['cedula'])) {
            $existingCedula = $this->db->fetch("SELECT id FROM usuarios WHERE cedula = ?", [$_POST['cedula']]);
            if ($existingCedula) {
                $errores[] = 'La cédula ya está registrada en el sistema';
            }
        }
        
        // Verificar si el apartamento ya tiene residente en el conjunto
        if (!empty($_POST['apartamento']) && !empty($_POST['conjunto_id'])) {
            $existingApart = $this->db->fetch("
                SELECT u.id FROM usuarios u 
                JOIN votantes v ON u.id = v.usuario_id 
                WHERE v.conjunto_id = ? AND v.apartamento = ?
            ", [$_POST['conjunto_id'], $_POST['apartamento']]);
            
            if ($existingApart) {
                $errores[] = 'Ya existe un residente registrado en este apartamento';
            }
        }
        
        if (!empty($errores)) {
            echo json_encode(['success' => false, 'message' => implode('<br>', $errores)]);
            exit;
        }
        
        // Crear usuario
        $userData = [
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'email' => trim($_POST['email']),
            'cedula' => trim($_POST['cedula']),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'rol' => 'votante',
            'tipo_usuario' => 'votante',
            'activo' => 1
        ];
        
        $result = $this->createUser($userData);
        
        if ($result['success']) {
            $userId = $result['insert_id'];
            
            // Crear entrada en tabla votantes (relación con conjunto)
            $votanteData = [
                'usuario_id' => $userId,
                'conjunto_id' => (int)$_POST['conjunto_id'],
                'apartamento' => trim($_POST['apartamento']),
                'coeficiente' => floatval($_POST['coeficiente'] ?? 0.0250),
                'es_propietario' => (int)($_POST['es_propietario'] ?? 1),
                'estado_pagos' => 'al_dia'
            ];
            
            $this->insertVotante($votanteData);
            
            echo json_encode(['success' => true, 'message' => 'Residente creado correctamente']);
        } else {
            throw new Exception('Error al crear el usuario');
        }
        
    } catch (Exception $e) {
        error_log("Error creating conjunto user: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al crear el residente: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * Obtener usuarios de un conjunto específico
 */
public function getConjuntoUsers($conjuntoId) {
    try {
        $users = $this->db->fetchAll("
            SELECT u.id, u.nombre, u.apellido, u.email, u.cedula, u.telefono, u.activo,
                   v.apartamento, v.coeficiente, v.es_propietario, v.estado_pagos,
                   DATE_FORMAT(u.created_at, '%d/%m/%Y') as fecha_registro
            FROM usuarios u
            JOIN votantes v ON u.id = v.usuario_id
            WHERE v.conjunto_id = ?
            ORDER BY v.apartamento, u.apellido, u.nombre
        ", [$conjuntoId]);
        
        // Generar HTML para la tabla
        $html = '<div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Apartamento</th>
                                <th>Residente</th>
                                <th>Cédula</th>
                                <th>Email</th>
                                <th>Coeficiente</th>
                                <th>Tipo</th>
                                <th>Estado Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        if (empty($users)) {
            $html .= '<tr><td colspan="8" class="text-center">No hay residentes registrados</td></tr>';
        } else {
            foreach ($users as $user) {
                $tipoBadge = $user['es_propietario'] ? 
                    '<span class="badge bg-success">Propietario</span>' : 
                    '<span class="badge bg-info">Arrendatario</span>';
                
                $estadoBadge = match($user['estado_pagos']) {
                    'al_dia' => '<span class="badge bg-success">Al día</span>',
                    'mora' => '<span class="badge bg-warning">Mora</span>',
                    'suspendido' => '<span class="badge bg-danger">Suspendido</span>',
                    default => '<span class="badge bg-secondary">N/A</span>'
                };
                
                $statusBadge = $user['activo'] ? 
                    '<span class="badge bg-success">Activo</span>' : 
                    '<span class="badge bg-danger">Inactivo</span>';
                
                $html .= "<tr>
                            <td><strong>{$user['apartamento']}</strong></td>
                            <td>
                                <div>
                                    <strong>{$user['nombre']} {$user['apellido']}</strong>
                                    <br><small class='text-muted'>{$statusBadge}</small>
                                </div>
                            </td>
                            <td>{$user['cedula']}</td>
                            <td>
                                <small>{$user['email']}</small>
                                <br><small class='text-muted'>{$user['telefono']}</small>
                            </td>
                            <td>" . number_format($user['coeficiente'] * 100, 4) . "%</td>
                            <td>{$tipoBadge}</td>
                            <td>{$estadoBadge}</td>
                            <td>
                                <div class='btn-group btn-group-sm'>
                                    <button class='btn btn-outline-primary btn-sm' onclick='editUser({$user['id']})' title='Editar'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                    <button class='btn btn-outline-danger btn-sm' onclick='deleteUser({$user['id']}, \"{$user['nombre']} {$user['apellido']}\")' title='Eliminar'>
                                        <i class='fas fa-trash'></i>
                                    </button>
                                </div>
                            </td>
                          </tr>";
            }
        }
        
        $html .= '</tbody></table></div>';
        echo $html;
        
    } catch (Exception $e) {
        error_log("Error getting conjunto users: " . $e->getMessage());
        echo '<div class="alert alert-danger">Error al cargar los usuarios del conjunto</div>';
    }
    exit;
}

/**
 * Actualizar estadísticas de un conjunto
 */
public function updateConjuntoStats($conjuntoId) {
    try {
        $stats = $this->getConjuntoStats($conjuntoId);
        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch (Exception $e) {
        error_log("Error updating conjunto stats: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al actualizar estadísticas']);
    }
    exit;
}

/**
 * Obtener estadísticas detalladas de un conjunto
 */
public function getConjuntoStats($conjuntoId) {
    try {
        $conjunto = $this->db->fetch("SELECT * FROM conjuntos WHERE id = ?", [$conjuntoId]);
        
        if (!$conjunto) {
            echo json_encode(['success' => false, 'message' => 'Conjunto no encontrado']);
            exit;
        }
        
        // Estadísticas básicas
        $totalResidentes = $this->db->fetch("
            SELECT COUNT(*) as total FROM votantes WHERE conjunto_id = ?
        ", [$conjuntoId]);
        
        $totalAsambleas = $this->db->fetch("
            SELECT COUNT(*) as total FROM asambleas WHERE conjunto_id = ?
        ", [$conjuntoId]);
        
        // Distribución por tipo de residente
        $propietarios = $this->db->fetch("
            SELECT COUNT(*) as total FROM votantes 
            WHERE conjunto_id = ? AND es_propietario = 1
        ", [$conjuntoId]);
        
        $arrendatarios = $this->db->fetch("
            SELECT COUNT(*) as total FROM votantes 
            WHERE conjunto_id = ? AND es_propietario = 0
        ", [$conjuntoId]);
        
        // Estado de pagos
        $alDia = $this->db->fetch("
            SELECT COUNT(*) as total FROM votantes 
            WHERE conjunto_id = ? AND estado_pagos = 'al_dia'
        ", [$conjuntoId]);
        
        $mora = $this->db->fetch("
            SELECT COUNT(*) as total FROM votantes 
            WHERE conjunto_id = ? AND estado_pagos = 'mora'
        ", [$conjuntoId]);
        
        $suspendido = $this->db->fetch("
            SELECT COUNT(*) as total FROM votantes 
            WHERE conjunto_id = ? AND estado_pagos = 'suspendido'
        ", [$conjuntoId]);
        
        $totalResidentesCount = $totalResidentes['total'];
        $ocupacion = $conjunto['total_unidades'] > 0 ? 
            round(($totalResidentesCount / $conjunto['total_unidades']) * 100, 2) : 0;
        
        $stats = [
            'id' => $conjuntoId,
            'nombre' => $conjunto['nombre'],
            'total_unidades' => $conjunto['total_unidades'],
            'total_residentes' => $totalResidentesCount,
            'total_asambleas' => $totalAsambleas['total'],
            'ocupacion' => $ocupacion,
            'propietarios' => $propietarios['total'],
            'arrendatarios' => $arrendatarios['total'],
            'propietarios_pct' => $totalResidentesCount > 0 ? round(($propietarios['total'] / $totalResidentesCount) * 100, 1) : 0,
            'arrendatarios_pct' => $totalResidentesCount > 0 ? round(($arrendatarios['total'] / $totalResidentesCount) * 100, 1) : 0,
            'al_dia' => $alDia['total'],
            'mora' => $mora['total'],
            'suspendido' => $suspendido['total'],
            'al_dia_pct' => $totalResidentesCount > 0 ? round(($alDia['total'] / $totalResidentesCount) * 100, 1) : 0,
            'mora_pct' => $totalResidentesCount > 0 ? round(($mora['total'] / $totalResidentesCount) * 100, 1) : 0,
            'suspendido_pct' => $totalResidentesCount > 0 ? round(($suspendido['total'] / $totalResidentesCount) * 100, 1) : 0
        ];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (Exception $e) {
        error_log("Error getting conjunto stats: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener estadísticas']);
    }
    exit;
}

/**
 * Obtener estadísticas globales del sistema
 */
public function getGlobalStats() {
    try {
        // Estadísticas básicas
        $totalConjuntos = $this->db->fetch("SELECT COUNT(*) as total FROM conjuntos");
        $totalUnidades = $this->db->fetch("SELECT SUM(total_unidades) as total FROM conjuntos");
        $totalResidentes = $this->db->fetch("SELECT COUNT(*) as total FROM votantes");
        $totalAsambleas = $this->db->fetch("SELECT COUNT(*) as total FROM asambleas");
        
        // Top conjuntos por residentes
        $topConjuntos = $this->db->fetchAll("
            SELECT c.nombre, COUNT(v.id) as residentes, c.total_unidades as unidades
            FROM conjuntos c
            LEFT JOIN votantes v ON c.id = v.conjunto_id
            GROUP BY c.id, c.nombre, c.total_unidades
            ORDER BY residentes DESC
            LIMIT 5
        ");
        
        // Distribución por ciudades
        $porCiudad = $this->db->fetchAll("
            SELECT ciudad, COUNT(*) as total,
                   ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM conjuntos)), 1) as porcentaje
            FROM conjuntos 
            WHERE ciudad IS NOT NULL AND ciudad != ''
            GROUP BY ciudad
            ORDER BY total DESC
        ");
        
        $stats = [
            'total_conjuntos' => $totalConjuntos['total'],
            'total_unidades' => $totalUnidades['total'] ?? 0,
            'total_residentes' => $totalResidentes['total'],
            'total_asambleas' => $totalAsambleas['total'],
            'top_conjuntos' => $topConjuntos,
            'por_ciudad' => $porCiudad
        ];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (Exception $e) {
        error_log("Error getting global stats: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener estadísticas globales']);
    }
    exit;
}

/**
 * Exportar lista de conjuntos
 */
public function exportConjuntos() {
    try {
        $conjuntos = $this->getConjuntos();
        
        // Crear archivo CSV
        $filename = 'conjuntos_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeceras
        fputcsv($output, [
            'ID', 'Nombre', 'Dirección', 'Ciudad', 'Teléfono', 
            'Email', 'NIT', 'Total Unidades', 'Residentes Registrados', 
            'Asambleas Realizadas', 'Fecha Registro'
        ]);
        
        // Datos
        foreach ($conjuntos as $conjunto) {
            // Obtener estadísticas adicionales
            $residentes = $this->db->fetch("
                SELECT COUNT(*) as total FROM votantes WHERE conjunto_id = ?
            ", [$conjunto['id']]);
            
            $asambleas = $this->db->fetch("
                SELECT COUNT(*) as total FROM asambleas WHERE conjunto_id = ?
            ", [$conjunto['id']]);
            
            fputcsv($output, [
                $conjunto['id'],
                $conjunto['nombre'],
                $conjunto['direccion'],
                $conjunto['ciudad'],
                $conjunto['telefono'],
                $conjunto['email'],
                $conjunto['nit'],
                $conjunto['total_unidades'],
                $residentes['total'] ?? 0,
                $asambleas['total'] ?? 0,
                date('d/m/Y', strtotime($conjunto['created_at'] ?? 'now'))
            ]);
        }
        
        fclose($output);
        
    } catch (Exception $e) {
        error_log("Error exporting conjuntos: " . $e->getMessage());
        $_SESSION['error'] = 'Error al exportar la lista de conjuntos';
        header('Location: /Asambleas/public/admin/conjuntos');
    }
    exit;
}

/**
 * Generar reporte de conjunto específico
 */
public function generateConjuntoReport($conjuntoId) {
    try {
        // Obtener datos del conjunto
        $conjunto = $this->db->fetch("SELECT * FROM conjuntos WHERE id = ?", [$conjuntoId]);
        
        if (!$conjunto) {
            $_SESSION['error'] = 'Conjunto no encontrado';
            header('Location: /Asambleas/public/admin/conjuntos');
            exit;
        }
        
        // Aquí implementarías la generación del PDF
        // Por ahora, simulamos con un mensaje
        $_SESSION['success'] = 'Reporte generado correctamente para: ' . $conjunto['nombre'];
        header('Location: /Asambleas/public/admin/conjuntos');
        
    } catch (Exception $e) {
        error_log("Error generating conjunto report: " . $e->getMessage());
        $_SESSION['error'] = 'Error al generar el reporte';
        header('Location: /Asambleas/public/admin/conjuntos');
    }
    exit;
}

/**
 * Generar reporte global del sistema
 */
public function generateGlobalReport() {
    try {
        // Aquí implementarías la generación del reporte global en PDF
        // Por ahora, simulamos con un mensaje
        $_SESSION['success'] = 'Reporte global generado correctamente';
        header('Location: /Asambleas/public/admin/conjuntos');
        
    } catch (Exception $e) {
        error_log("Error generating global report: " . $e->getMessage());
        $_SESSION['error'] = 'Error al generar el reporte global';
        header('Location: /Asambleas/public/admin/conjuntos');
    }
    exit;
}

/**
 * Eliminar conjunto
 */
public function eliminarConjunto($id) {
    try {
        // Verificar si tiene asambleas
        $asambleas = $this->db->fetch("
            SELECT COUNT(*) as total FROM asambleas WHERE conjunto_id = ?
        ", [$id]);
        
        if ($asambleas && $asambleas['total'] > 0) {
            throw new Exception('No se puede eliminar un conjunto con asambleas registradas');
        }
        
        // Verificar si tiene residentes
        $residentes = $this->db->fetch("
            SELECT COUNT(*) as total FROM votantes WHERE conjunto_id = ?
        ", [$id]);
        
        if ($residentes && $residentes['total'] > 0) {
            throw new Exception('No se puede eliminar un conjunto con residentes registrados');
        }
        
        // Eliminar conjunto
        $result = $this->db->execute("DELETE FROM conjuntos WHERE id = ?", [$id]);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Conjunto eliminado correctamente';
        } else {
            throw new Exception('Error al eliminar el conjunto');
        }
        
    } catch (Exception $e) {
        error_log("Error deleting conjunto: " . $e->getMessage());
        $_SESSION['error'] = 'Error al eliminar conjunto: ' . $e->getMessage();
    }
    
    header('Location: /Asambleas/public/admin/conjuntos');
    exit;
}

/**
 * Editar conjunto
 */
public function editarConjunto($id) {
    $conjunto = $this->db->fetch("SELECT * FROM conjuntos WHERE id = ?", [$id]);
    
    if (!$conjunto) {
        $_SESSION['error'] = 'Conjunto no encontrado';
        header('Location: /Asambleas/public/admin/conjuntos');
        exit;
    }
    
    require_once __DIR__ . '/../views/admin/edit_conjunto.php';
}
private function createAssembly($data) {
    try {
        // Log para debug
        error_log("createAssembly - Datos recibidos: " . json_encode($data));
        
        $query = "
            INSERT INTO asambleas (
                titulo, descripcion, conjunto_id, coordinador_id, 
                fecha_inicio, fecha_expiracion, quorum_minimo, estado, 
                tipo_asamblea, link_reunion, created_at, updated_at
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";
        
        $params = [
            $data['titulo'],
            $data['descripcion'],
            $data['conjunto_id'],
            $data['coordinador_id'],
            $data['fecha_inicio'],
            $data['fecha_expiracion'], // CORREGIDO: usar fecha_expiracion
            $data['quorum_minimo'],
            $data['estado'],
            $data['tipo_asamblea'],
            $data['link_reunion']
        ];
        
        // Log de la query y parámetros
        error_log("createAssembly - Query: " . $query);
        error_log("createAssembly - Params: " . json_encode($params));
        
        // Ejecutar la query
        $result = $this->db->execute($query, $params);
        
        // Log del resultado
        error_log("createAssembly - Resultado: " . json_encode($result));
        
        if ($result['success'] && $result['affected_rows'] > 0) {
            error_log("createAssembly - Éxito, ID insertado: " . $result['insert_id']);
            return $result['insert_id'];
        } else {
            throw new Exception('No se pudo crear la asamblea - Sin filas afectadas. Resultado: ' . json_encode($result));
        }
        
    } catch (Exception $e) {
        error_log("Error en createAssembly: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        throw new Exception('Error al crear la asamblea: ' . $e->getMessage());
    }
}


public function guardarAsamblea() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /Asambleas/public/admin/asambleas');
        exit;
    }
    
    try {
        // Log de datos recibidos
        error_log("guardarAsamblea - POST data: " . json_encode($_POST));
        
        // Validar campos requeridos
        $errores = [];
        
        if (empty($_POST['titulo'])) {
            $errores[] = 'El título es requerido';
        }
        
        if (empty($_POST['conjunto_id'])) {
            $errores[] = 'Debe seleccionar un conjunto';
        }
        
        if (empty($_POST['coordinador_id'])) {
            $errores[] = 'Debe seleccionar un coordinador';
        }
        
        if (empty($_POST['fecha_inicio'])) {
            $errores[] = 'La fecha de inicio es requerida';
        }
        
        if (empty($_POST['fecha_fin'])) {
            $errores[] = 'La fecha de finalización es requerida';
        }
        
        // Validar que la fecha de fin sea posterior a la de inicio
        if (!empty($_POST['fecha_inicio']) && !empty($_POST['fecha_fin'])) {
            if (strtotime($_POST['fecha_fin']) <= strtotime($_POST['fecha_inicio'])) {
                $errores[] = 'La fecha de finalización debe ser posterior a la fecha de inicio';
            }
        }
        
        // VALIDACIÓN MEJORADA DE FECHAS EN EL PASADO
        if (!empty($_POST['fecha_inicio'])) {
            $fechaInicio = new DateTime($_POST['fecha_inicio']);
            $ahora = new DateTime();
            // Permitir fechas hasta 5 minutos en el pasado para evitar problemas de timezone
            $ahora->sub(new DateInterval('PT5M')); 
            
            if ($fechaInicio < $ahora) {
                $errores[] = 'La fecha de inicio no puede ser en el pasado';
            }
        }
        
        if (!empty($errores)) {
            $_SESSION['error'] = implode('<br>', $errores);
            // Preservar los datos del formulario
            $_SESSION['form_data'] = $_POST;
            header('Location: /Asambleas/public/admin/asambleas/crear');
            exit;
        }
        
        // Preparar datos para guardar - MAPEO CORREGIDO
        $data = [
            'titulo' => trim($_POST['titulo']),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'conjunto_id' => (int)$_POST['conjunto_id'],
            'coordinador_id' => (int)$_POST['coordinador_id'],
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_expiracion' => $_POST['fecha_fin'], // CORREGIDO: mapear a fecha_expiracion
            'quorum_minimo' => floatval($_POST['quorum_minimo'] ?? 50),
            'estado' => $_POST['estado'] ?? 'programada',
            'tipo_asamblea' => $_POST['tipo_asamblea'],
            'link_reunion' => trim($_POST['link_reunion'] ?? '')
        ];
        
        // Log de datos preparados
        error_log("guardarAsamblea - Datos preparados: " . json_encode($data));
        
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Actualizar asamblea existente
            $this->updateAssembly($_POST['id'], $data);
            $_SESSION['success'] = 'Asamblea actualizada correctamente';
        } else {
            // Crear nueva asamblea
            $assemblyId = $this->createAssembly($data);
            $_SESSION['success'] = 'Asamblea creada correctamente con ID: ' . $assemblyId;
        }
        
        // Limpiar datos del formulario
        unset($_SESSION['form_data']);
        
        header('Location: /Asambleas/public/admin/asambleas');
        exit;
        
    } catch (Exception $e) {
        error_log("Error al guardar asamblea: " . $e->getMessage());
        $_SESSION['error'] = 'Error al guardar la asamblea: ' . $e->getMessage();
        $_SESSION['form_data'] = $_POST;
        
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            header('Location: /Asambleas/public/admin/asambleas/editar/' . $_POST['id']);
        } else {
            header('Location: /Asambleas/public/admin/asambleas/crear');
        }
        exit;
    }
}
    public function eliminarAsamblea($id) {
        try {
            $this->deleteAssembly($id);
            $_SESSION['success'] = 'Asamblea eliminada correctamente';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al eliminar la asamblea: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/admin/asambleas');
        exit;
    }
    
    // ================================
    // GESTIÓN DE USUARIOS
    // ================================
    public function usuarios() {
        $usuarios = $this->getUsuarios();
        require_once __DIR__ . '/../views/admin/usuarios.php';
    }
    
    public function crearUsuario() {
        require_once __DIR__ . '/../views/admin/create_user.php';
    }
    
    public function editarUsuario($id) {
        $usuario = $this->getUserById($id);
        
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header('Location: /Asambleas/public/admin/usuarios');
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/edit_user.php';
    }
    
    public function guardarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/admin/usuarios');
            exit;
        }
        
        try {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'apellido' => $_POST['apellido'] ?? '',
                'email' => $_POST['email'] ?? '',
                'cedula' => $_POST['cedula'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'rol' => $_POST['rol'] ?? 'votante',
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];
            
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $this->updateUser($_POST['id'], $data);
                $_SESSION['success'] = 'Usuario actualizado correctamente';
            } else {
                if (empty($_POST['password'])) {
                    throw new Exception('La contraseña es requerida para nuevos usuarios');
                }
                $this->createUser($data);
                $_SESSION['success'] = 'Usuario creado correctamente';
            }
            
            header('Location: /Asambleas/public/admin/usuarios');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al guardar el usuario: ' . $e->getMessage();
            header('Location: /Asambleas/public/admin/usuarios');
            exit;
        }
    }
  private function insertVotante($data) {
    try {
        $query = "
            INSERT INTO votantes (usuario_id, conjunto_id, apartamento, coeficiente, 
                                es_propietario, estado_pagos, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $result = $this->db->execute($query, [
            $data['usuario_id'],
            $data['conjunto_id'],
            $data['apartamento'],
            $data['coeficiente'],
            $data['es_propietario'],
            $data['estado_pagos']
        ]);
        
        return $result;
    } catch (Exception $e) {
        error_log("Error insertando votante: " . $e->getMessage());
        throw new Exception("Error al crear el votante: " . $e->getMessage());
    }
}  
    public function eliminarUsuario($id) {
        try {
            $this->deleteUser($id);
            $_SESSION['success'] = 'Usuario eliminado correctamente';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al eliminar el usuario: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/admin/usuarios');
        exit;
    }
    
    public function checkEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        
        if (empty($email)) {
            echo json_encode(['exists' => false]);
            exit;
        }
        
        try {
            $user = $this->db->fetch("SELECT id FROM usuarios WHERE email = ?", [$email]);
            echo json_encode(['exists' => $user !== null]);
        } catch (Exception $e) {
            echo json_encode(['exists' => false, 'error' => 'Error verificando email']);
        }
        exit;
    }
    
    public function checkCedula() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $cedula = $input['cedula'] ?? '';
        
        if (empty($cedula)) {
            echo json_encode(['exists' => false]);
            exit;
        }
        
        try {
            $user = $this->db->fetch("SELECT id FROM usuarios WHERE cedula = ?", [$cedula]);
            echo json_encode(['exists' => $user !== null]);
        } catch (Exception $e) {
            echo json_encode(['exists' => false, 'error' => 'Error verificando cédula']);
        }
        exit;
    }
    
    // ================================
    // GESTIÓN DE COORDINADORES
    // ================================
    public function coordinadores() {
        $coordinators = $this->getCoordinators();
        require_once __DIR__ . '/../views/admin/coordinators.php';
    }
    
    public function crearCoordinador() {
        require_once __DIR__ . '/../views/admin/create_coordinator.php';
    }
    
    public function editarCoordinador($id) {
        $coordinador = $this->getCoordinatorById($id);
        
        if (!$coordinador) {
            $_SESSION['error'] = 'Coordinador no encontrado';
            header('Location: /Asambleas/public/admin/coordinadores');
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/edit_coordinator.php';
    }
    
    public function guardarCoordinador() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/admin/coordinadores');
            exit;
        }
        
        try {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'apellido' => $_POST['apellido'] ?? '',
                'email' => $_POST['email'] ?? '',
                'cedula' => $_POST['cedula'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'password' => $_POST['password'] ?? ''
            ];
            
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $this->updateCoordinator($_POST['id'], $data);
                $_SESSION['success'] = 'Coordinador actualizado correctamente';
            } else {
                if (empty($data['password'])) {
                    throw new Exception('La contraseña es requerida para nuevos coordinadores');
                }
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                $data['rol'] = 'coordinador';
                $data['activo'] = 1;
                
                $this->createUser($data);
                $_SESSION['success'] = 'Coordinador creado correctamente';
            }
            
            header('Location: /Asambleas/public/admin/coordinadores');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al guardar coordinador: ' . $e->getMessage();
            header('Location: /Asambleas/public/admin/coordinadores');
            exit;
        }
    }
    
    // ================================
    // GESTIÓN DE CONJUNTOS
    // ================================
    public function conjuntos() {
        $conjuntos = $this->getConjuntos();
        require_once __DIR__ . '/../views/admin/conjuntos.php';
    }
    
    public function crearConjunto() {
        require_once __DIR__ . '/../views/admin/crear_conjunto.php';
    }
    
    public function guardarConjunto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/admin/conjuntos');
            exit;
        }
        
        try {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'ciudad' => $_POST['ciudad'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'email' => $_POST['email'] ?? '',
                'nit' => $_POST['nit'] ?? '',
                'total_unidades' => $_POST['total_unidades'] ?? 0
            ];
            
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $this->updateConjunto($_POST['id'], $data);
                $_SESSION['success'] = 'Conjunto actualizado correctamente';
            } else {
                $this->createConjunto($data);
                $_SESSION['success'] = 'Conjunto creado correctamente';
            }
            
            header('Location: /Asambleas/public/admin/conjuntos');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al guardar conjunto: ' . $e->getMessage();
            header('Location: /Asambleas/public/admin/conjuntos');
            exit;
        }
    }
    
    // ================================
    // OTRAS SECCIONES
    // ================================
    public function votaciones() {
        $votaciones = $this->getVotaciones();
        require_once __DIR__ . '/../views/admin/votaciones.php';
    }
    
    public function reportes() {
        $metrics = $this->getSystemMetrics();
        $monthlyStats = $this->getMonthlyStats();
        $topConjuntos = $this->getTopConjuntos();
        require_once __DIR__ . '/../views/admin/reportes.php';
    }
    
    public function configuracion() {
        $config = $this->getSystemConfig();
        require_once __DIR__ . '/../views/admin/configuracion.php';
    }
    
    public function guardarConfiguracion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/admin/configuracion');
            exit;
        }
        
        try {
            $config = [
                'sistema_nombre' => $_POST['sistema_nombre'] ?? '',
                'email_notificaciones' => $_POST['email_notificaciones'] ?? '',
                'quorum_default' => $_POST['quorum_default'] ?? 50,
                'timezone' => $_POST['timezone'] ?? 'America/Bogota'
            ];
            
            $this->updateSystemConfig($config);
            $_SESSION['success'] = 'Configuración actualizada correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar configuración: ' . $e->getMessage();
        }
        
        header('Location: /Asambleas/public/admin/configuracion');
        exit;
    }
    
    // ================================
    // MÉTODOS PRIVADOS PARA BASE DE DATOS - MÉTRICAS BÁSICAS
    // ================================
    private function getTotalAssemblies() {
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as total FROM asambleas");
            return $result ? $result['total'] : 0;
        } catch (Exception $e) {
            error_log("Error getting total assemblies: " . $e->getMessage());
            return 0;
        }
    }
    
    private function getActiveAssemblies() {
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as total FROM asambleas WHERE estado = 'activa'");
            return $result ? $result['total'] : 0;
        } catch (Exception $e) {
            error_log("Error getting active assemblies: " . $e->getMessage());
            return 0;
        }
    }
    
    private function getTotalVoters() {
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'votante'");
            return $result ? $result['total'] : 0;
        } catch (Exception $e) {
            error_log("Error getting total voters: " . $e->getMessage());
            return 0;
        }
    }
    
    private function getTotalCoordinators() {
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'coordinador'");
            return $result ? $result['total'] : 0;
        } catch (Exception $e) {
            error_log("Error getting total coordinators: " . $e->getMessage());
            return 0;
        }
    }
    
    
    // ================================
    // MÉTODOS PARA OBTENER LISTAS
    // ================================
    private function getAssemblies() {
        try {
            return $this->db->fetchAll("
                SELECT a.*, c.nombre as conjunto_nombre, u.nombre as coordinador_nombre 
                FROM asambleas a 
                LEFT JOIN conjuntos c ON a.conjunto_id = c.id 
                LEFT JOIN usuarios u ON a.coordinador_id = u.id
                ORDER BY a.fecha_inicio DESC
            ");
        } catch (Exception $e) {
            error_log("Error getting assemblies: " . $e->getMessage());
            return [];
        }
    }
    
    private function getRecentAssemblies() {
        try {
            return $this->db->fetchAll("
                SELECT a.*, c.nombre as conjunto_nombre 
                FROM asambleas a 
                LEFT JOIN conjuntos c ON a.conjunto_id = c.id 
                ORDER BY a.created_at DESC 
                LIMIT 5
            ");
        } catch (Exception $e) {
            error_log("Error getting recent assemblies: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAssemblyById($id) {
        try {
            return $this->db->fetch("
                SELECT a.*, c.nombre as conjunto_nombre, u.nombre as coordinador_nombre 
                FROM asambleas a 
                LEFT JOIN conjuntos c ON a.conjunto_id = c.id 
                LEFT JOIN usuarios u ON a.coordinador_id = u.id
                WHERE a.id = ?
            ", [$id]);
        } catch (Exception $e) {
            error_log("Error getting assembly by ID: " . $e->getMessage());
            return null;
        }
    }
    
private function getCoordinators() {
    try {
        // Consulta más robusta que maneja múltiples escenarios
        $coordinators = $this->db->fetchAll("
            SELECT u.id, 
                   COALESCE(u.nombre, '') as nombre, 
                   COALESCE(u.apellido, '') as apellido, 
                   COALESCE(u.email, '') as email,
                   COALESCE(u.cedula, '') as cedula, 
                   COALESCE(u.telefono, '') as telefono, 
                   COALESCE(u.activo, 1) as activo,
                   COALESCE(DATE_FORMAT(u.created_at, '%d/%m/%Y'), 'N/A') as fecha_registro
            FROM usuarios u
            WHERE (u.tipo_usuario = 'coordinador' OR u.rol = 'coordinador')
            ORDER BY u.nombre, u.apellido
        ");
        
        return $coordinators;
    } catch (Exception $e) {
        error_log("Error getting coordinators: " . $e->getMessage());
        return [];
    }
}
    
    private function getCoordinadores() {
        return $this->getCoordinators();
    }
    
    private function getCoordinatorById($id) {
        try {
            return $this->db->fetch("
                SELECT * FROM usuarios 
                WHERE id = ? AND rol = 'coordinador'
            ", [$id]);
        } catch (Exception $e) {
            error_log("Error getting coordinator by ID: " . $e->getMessage());
            return null;
        }
    }
    
private function getConjuntos() {
    try {
        $conjuntos = $this->db->fetchAll("
            SELECT id, nombre, direccion, ciudad, total_unidades 
            FROM conjuntos 
            ORDER BY nombre ASC
        ");
        
        return $conjuntos ?: [];
    } catch (Exception $e) {
        error_log("Error getting conjuntos: " . $e->getMessage());
        return [];
    }
}
    public function eliminarCoordinador($id) {
    try {
        // Verificar que el coordinador existe y es realmente un coordinador
        $coordinator = $this->db->fetch("
            SELECT id FROM usuarios 
            WHERE id = ? AND (tipo_usuario = 'coordinador' OR rol = 'coordinador')
        ", [$id]);
        
        if (!$coordinator) {
            throw new Exception('Coordinador no encontrado');
        }
        
        // Verificar si tiene asambleas asignadas
        $hasAssemblies = $this->db->fetch("
            SELECT COUNT(*) as total 
            FROM asambleas 
            WHERE coordinador_id = ?
        ", [$id]);
        
        if ($hasAssemblies && $hasAssemblies['total'] > 0) {
            throw new Exception('No se puede eliminar un coordinador con asambleas asignadas');
        }
        
        // Desactivar en lugar de eliminar
        $this->db->execute("UPDATE usuarios SET activo = 0, updated_at = NOW() WHERE id = ?", [$id]);
        $_SESSION['success'] = 'Coordinador eliminado correctamente';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al eliminar coordinador: ' . $e->getMessage();
    }
    
    header('Location: /Asambleas/public/admin/coordinadores');
    exit;
}

    
private function getUsuarios() {
    try {
        return $this->db->fetchAll("
            SELECT id, nombre, apellido, 
                   COALESCE(email, '') as email, 
                   COALESCE(cedula, '') as cedula, 
                   COALESCE(telefono, '') as telefono, 
                   COALESCE(rol, tipo_usuario) as rol,
                   COALESCE(activo, 1) as activo, 
                   DATE_FORMAT(created_at, '%d/%m/%Y') as fecha_registro
            FROM usuarios 
            ORDER BY nombre, apellido
        ");
    } catch (Exception $e) {
        error_log("Error getting users: " . $e->getMessage());
        return [];
    }
}
    
    private function getUserById($id) {
        try {
            return $this->db->fetch("SELECT * FROM usuarios WHERE id = ?", [$id]);
        } catch (Exception $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return null;
        }
    }
    
    private function getVotaciones() {
        try {
            return $this->db->fetchAll("
                SELECT v.*, a.titulo as asamblea_titulo 
                FROM votaciones v 
                LEFT JOIN asambleas a ON v.asamblea_id = a.id 
                ORDER BY v.created_at DESC
            ");
        } catch (Exception $e) {
            error_log("Error getting votaciones: " . $e->getMessage());
            return [];
        }
    }
    
    // ================================
    // MÉTODOS PARA CREAR/ACTUALIZAR/ELIMINAR
    // ================================

    
private function updateAssembly($id, $data) {
    try {
        $query = "
            UPDATE asambleas 
            SET titulo = ?, descripcion = ?, conjunto_id = ?, coordinador_id = ?,
                fecha_inicio = ?, fecha_expiracion = ?, quorum_minimo = ?, estado = ?,
                tipo_asamblea = ?, link_reunion = ?, updated_at = NOW()
            WHERE id = ?
        ";
        
        $params = [
            $data['titulo'],
            $data['descripcion'],
            $data['conjunto_id'],
            $data['coordinador_id'],
            $data['fecha_inicio'],
            $data['fecha_expiracion'], // CORREGIDO: usar fecha_expiracion
            $data['quorum_minimo'],
            $data['estado'],
            $data['tipo_asamblea'],
            $data['link_reunion'],
            $id
        ];
        
        // Log para debug
        error_log("updateAssembly - Query: " . $query);
        error_log("updateAssembly - Params: " . json_encode($params));
        
        $result = $this->db->execute($query, $params);
        
        if ($result['success']) {
            return true;
        } else {
            throw new Exception('No se pudo actualizar la asamblea');
        }
        
    } catch (Exception $e) {
        error_log("Error en updateAssembly: " . $e->getMessage());
        throw new Exception('Error al actualizar la asamblea: ' . $e->getMessage());
    }
}
public function debugAssembly() {
    try {
        echo "<h2>Debug - Información de la Base de Datos</h2>";
        
        // Verificar conjuntos
        $conjuntos = $this->getConjuntos();
        echo "<h3>Conjuntos (" . count($conjuntos) . "):</h3>";
        echo "<pre>" . print_r($conjuntos, true) . "</pre>";
        
        // Verificar coordinadores
        $coordinadores = $this->getCoordinadores();
        echo "<h3>Coordinadores (" . count($coordinadores) . "):</h3>";
        echo "<pre>" . print_r($coordinadores, true) . "</pre>";
        
        // Verificar estructura de tabla asambleas
        $columns = $this->db->fetchAll("DESCRIBE asambleas");
        echo "<h3>Estructura tabla asambleas:</h3>";
        echo "<pre>" . print_r($columns, true) . "</pre>";
        
        // Probar insert simple
        echo "<h3>Prueba de INSERT:</h3>";
        $testData = [
            'titulo' => 'Test Asamblea',
            'descripcion' => 'Prueba',
            'conjunto_id' => $conjuntos[0]['id'] ?? 1,
            'coordinador_id' => $coordinadores[0]['id'] ?? 2,
            'fecha_inicio' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'fecha_expiracion' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'quorum_minimo' => 50.00,
            'estado' => 'programada',
            'tipo_asamblea' => 'ordinaria',
            'link_reunion' => ''
        ];
        
        echo "Datos para insertar:<br>";
        echo "<pre>" . print_r($testData, true) . "</pre>";
        
        try {
            $result = $this->createAssembly($testData);
            echo "Resultado: Asamblea creada con ID: " . $result;
        } catch (Exception $e) {
            echo "Error al crear: " . $e->getMessage();
        }
        
    } catch (Exception $e) {
        echo "Error en debug: " . $e->getMessage();
    }
    exit;
}

    
    private function deleteAssembly($id) {
        // Verificar si la asamblea tiene participantes o votaciones
        $participants = $this->db->fetch("SELECT COUNT(*) as total FROM participantes_asamblea WHERE asamblea_id = ?", [$id]);
        if ($participants && $participants['total'] > 0) {
            throw new Exception('No se puede eliminar una asamblea con participantes registrados');
        }
        
        return $this->db->execute("DELETE FROM asambleas WHERE id = ?", [$id]);
    }
    
  private function createUser($data) {
    try {
        // Si no se especifica rol, usar tipo_usuario
        if (empty($data['rol']) && !empty($data['tipo_usuario'])) {
            $data['rol'] = $data['tipo_usuario'];
        }
        
        $query = "
            INSERT INTO usuarios (nombre, apellido, email, cedula, telefono, password, 
                                tipo_usuario, rol, activo, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $result = $this->db->execute($query, [
            $data['nombre'], 
            $data['apellido'], 
            $data['email'], 
            $data['cedula'],
            $data['telefono'] ?? null, 
            $data['password'], 
            $data['tipo_usuario'] ?? $data['rol'], 
            $data['rol'],
            $data['activo'] ?? 1
        ]);
        
        return $result;
    } catch (Exception $e) {
        error_log("Error creating user: " . $e->getMessage());
        throw new Exception("Error al crear el usuario: " . $e->getMessage());
    }
}

    
    private function updateUser($id, $data) {
        try {
            if (isset($data['password'])) {
                $query = "
                    UPDATE usuarios 
                    SET nombre = ?, apellido = ?, email = ?, cedula = ?, telefono = ?, 
                        password = ?, rol = ?, activo = ?, updated_at = NOW()
                    WHERE id = ?
                ";
                $params = [
                    $data['nombre'], $data['apellido'], $data['email'], $data['cedula'],
                    $data['telefono'], $data['password'], $data['rol'], $data['activo'], $id
                ];
            } else {
                $query = "
                    UPDATE usuarios 
                    SET nombre = ?, apellido = ?, email = ?, cedula = ?, telefono = ?, 
                        rol = ?, activo = ?, updated_at = NOW()
                    WHERE id = ?
                ";
                $params = [
                    $data['nombre'], $data['apellido'], $data['email'], $data['cedula'],
                    $data['telefono'], $data['rol'], $data['activo'], $id
                ];
            }
            
            $result = $this->db->execute($query, $params);
            return $result;
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            throw new Exception("Error al actualizar el usuario: " . $e->getMessage());
        }
    }
    
    private function deleteUser($id) {
        try {
            // No eliminar físicamente, solo desactivar
            $result = $this->db->execute("UPDATE usuarios SET activo = 0, updated_at = NOW() WHERE id = ?", [$id]);
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            throw new Exception("Error al eliminar el usuario: " . $e->getMessage());
        }
    }
    
    private function updateCoordinator($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $query = "
                UPDATE usuarios 
                SET nombre = ?, apellido = ?, email = ?, cedula = ?, telefono = ?, 
                    password = ?, updated_at = NOW()
                WHERE id = ? AND rol = 'coordinador'
            ";
            $params = [
                $data['nombre'], $data['apellido'], $data['email'], $data['cedula'],
                $data['telefono'], $data['password'], $id
            ];
        } else {
            $query = "
                UPDATE usuarios 
                SET nombre = ?, apellido = ?, email = ?, cedula = ?, telefono = ?, 
                    updated_at = NOW()
                WHERE id = ? AND rol = 'coordinador'
            ";
            $params = [
                $data['nombre'], $data['apellido'], $data['email'], $data['cedula'],
                $data['telefono'], $id
            ];
        }
        
        return $this->db->execute($query, $params);
    }
    
    private function createConjunto($data) {
        $query = "
            INSERT INTO conjuntos (nombre, direccion, ciudad, telefono, email, nit, total_unidades, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        return $this->db->execute($query, [
            $data['nombre'], $data['direccion'], $data['ciudad'], $data['telefono'],
            $data['email'], $data['nit'], $data['total_unidades']
        ]);
    }
    
    private function updateConjunto($id, $data) {
        $query = "
            UPDATE conjuntos 
            SET nombre = ?, direccion = ?, ciudad = ?, telefono = ?, email = ?, 
                nit = ?, total_unidades = ?, updated_at = NOW()
            WHERE id = ?
        ";
        
        return $this->db->execute($query, [
            $data['nombre'], $data['direccion'], $data['ciudad'], $data['telefono'],
            $data['email'], $data['nit'], $data['total_unidades'], $id
        ]);
    }
    
    // ================================
    // MÉTODOS PARA ESTADÍSTICAS Y REPORTES
    // ================================
    private function getSystemMetrics() {
        return [
            'total_assemblies' => $this->getTotalAssemblies(),
            'active_assemblies' => $this->getActiveAssemblies(),
            'total_voters' => $this->getTotalVoters(),
            'total_coordinators' => $this->getTotalCoordinators()
        ];
    }
    
    private function getSystemAlerts() {
        try {
            $alerts = [];
            
            // Verificar asambleas sin quórum
            $lowQuorum = $this->db->fetch("
                SELECT COUNT(*) as total 
                FROM asambleas a 
                WHERE a.estado = 'activa'
            ");
            
            if ($lowQuorum && $lowQuorum['total'] > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'info-circle',
                    'message' => "Hay {$lowQuorum['total']} asamblea(s) activa(s) en el sistema"
                ];
            }
            
            // Verificar asambleas programadas para hoy
            $todayAssemblies = $this->db->fetch("
                SELECT COUNT(*) as total 
                FROM asambleas 
                WHERE DATE(fecha_inicio) = CURDATE() 
                AND estado = 'programada'
            ");
            
            if ($todayAssemblies && $todayAssemblies['total'] > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'calendar',
                    'message' => "Tienes {$todayAssemblies['total']} asamblea(s) programada(s) para hoy"
                ];
            }
            
            // Verificar usuarios inactivos
            $inactiveUsers = $this->db->fetch("
                SELECT COUNT(*) as total 
                FROM usuarios 
                WHERE activo = 0
            ");
            
            if ($inactiveUsers && $inactiveUsers['total'] > 0) {
                $alerts[] = [
                    'type' => 'secondary',
                    'icon' => 'user-slash',
                    'message' => "Hay {$inactiveUsers['total']} usuario(s) inactivo(s) en el sistema"
                ];
            }
            
            return $alerts;
        } catch (Exception $e) {
            error_log("Error getting system alerts: " . $e->getMessage());
            return [];
        }
    }
    
    private function getMonthlyStats() {
        try {
            return $this->db->fetchAll("
                SELECT 
                    DATE_FORMAT(fecha_inicio, '%Y-%m') as mes,
                    COUNT(*) as total_asambleas,
                    SUM(CASE WHEN estado = 'finalizada' THEN 1 ELSE 0 END) as finalizadas
                FROM asambleas 
                WHERE fecha_inicio >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(fecha_inicio, '%Y-%m')
                ORDER BY mes DESC
            ");
        } catch (Exception $e) {
            error_log("Error getting monthly stats: " . $e->getMessage());
            return [];
        }
    }
    
    private function getTopConjuntos() {
        try {
            return $this->db->fetchAll("
                SELECT c.nombre, COUNT(a.id) as total_asambleas
                FROM conjuntos c
                LEFT JOIN asambleas a ON c.id = a.conjunto_id
                GROUP BY c.id, c.nombre
                ORDER BY total_asambleas DESC
                LIMIT 5
            ");
        } catch (Exception $e) {
            error_log("Error getting top conjuntos: " . $e->getMessage());
            return [];
        }
    }
    
    private function getSystemConfig() {
        try {
            // En una implementación real, esto vendría de una tabla de configuración
            return [
                'sistema_nombre' => 'Sistema de Asambleas',
                'email_notificaciones' => 'admin@sistema.com',
                'quorum_default' => 50,
                'timezone' => 'America/Bogota'
            ];
        } catch (Exception $e) {
            error_log("Error getting system config: " . $e->getMessage());
            return [];
        }
    }
    
    private function updateSystemConfig($config) {
        // En una implementación real, esto actualizaría una tabla de configuración
        // Por ahora, simplemente simularemos que se guarda
        return true;
    }
    
    private function getAssemblyParticipants($assemblyId) {
        try {
            return $this->db->fetchAll("
                SELECT pa.*, u.nombre, u.apellido, u.email, u.cedula
                FROM participantes_asamblea pa
                JOIN usuarios u ON pa.usuario_id = u.id
                WHERE pa.asamblea_id = ?
                ORDER BY u.nombre, u.apellido
            ", [$assemblyId]);
        } catch (Exception $e) {
            error_log("Error getting assembly participants: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAssemblyStatistics($assemblyId) {
        try {
            return $this->db->fetch("
                SELECT 
                    COUNT(*) as total_participantes,
                    SUM(CASE WHEN asistencia = 1 THEN 1 ELSE 0 END) as total_asistentes,
                    SUM(CASE WHEN asistencia = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as porcentaje_asistencia
                FROM participantes_asamblea 
                WHERE asamblea_id = ?
            ", [$assemblyId]);
        } catch (Exception $e) {
            error_log("Error getting assembly statistics: " . $e->getMessage());
            return [
                'total_participantes' => 0,
                'total_asistentes' => 0,
                'porcentaje_asistencia' => 0
            ];
        }
    }
}

?>
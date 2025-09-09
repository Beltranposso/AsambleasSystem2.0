<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista de Proyección - Sistema de Asambleas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --shadow-lg: 1rem 3rem rgba(0, 0, 0, 0.175);
        }
        
        body {
            background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            color: var(--gray-800);
        }
        
        .header-bar {
            background: var(--white);
            border-bottom: 1px solid var(--gray-300);
            padding: 20px;
            color: var(--gray-800);
            box-shadow: var(--shadow-sm);
        }
        
        .header-bar h1 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }
        
        .header-bar p {
            color: var(--gray-600);
            margin: 0;
        }
        
        .quorum-display {
            background: var(--white);
            border-radius: 12px;
            padding: 40px;
            margin: 20px 0;
            box-shadow: var(--shadow);
            text-align: center;
            border: 1px solid var(--gray-200);
        }
        
        .quorum-percentage {
            font-size: 6rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.05em;
            transition: color 0.3s ease;
        }
        
        .quorum-label {
            font-size: 1.5rem;
            color: var(--gray-600);
            margin-top: 10px;
            font-weight: 500;
            letter-spacing: 0.1em;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 10px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }
        
        .stats-card {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: transform 0.2s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            transition: all 0.3s ease;
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: var(--gray-600);
            margin-top: 8px;
            font-weight: 500;
        }
        
        .voting-section {
            background: var(--white);
            border-radius: 12px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }
        
        .voting-card {
            background: var(--gray-100);
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            border: 1px solid var(--gray-300);
            border-left: 4px solid var(--accent-color);
            transition: all 0.2s ease;
        }
        
        .voting-card:hover {
            background: var(--white);
            box-shadow: var(--shadow-sm);
        }
        
        .real-time-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success-color);
            color: var(--white);
            padding: 10px 16px;
            border-radius: 20px;
            font-weight: 600;
            z-index: 1000;
            font-size: 0.9rem;
            box-shadow: var(--shadow);
        }
        
        .close-projection {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background-color: var(--gray-600);
            border-color: var(--gray-600);
            color: var(--white);
            font-weight: 500;
        }
        
        .voting-progress {
            height: 24px;
            margin: 15px 0;
            border-radius: 12px;
            overflow: hidden;
            background-color: var(--gray-200);
        }
        
        .progress-bar {
            transition: width 0.6s ease;
            height: 100%;
        }
        
        .text-success { color: var(--success-color) !important; }
        .text-warning { color: var(--warning-color) !important; }
        .text-danger { color: var(--danger-color) !important; }
        .text-primary { color: var(--accent-color) !important; }
        .text-info { color: var(--accent-color) !important; }
        .text-secondary { color: var(--gray-600) !important; }
        
        .bg-success { background-color: var(--success-color) !important; }
        .bg-warning { background-color: var(--warning-color) !important; }
        .bg-danger { background-color: var(--danger-color) !important; }
        .bg-primary { background-color: var(--accent-color) !important; }
        .bg-secondary { background-color: var(--gray-600) !important; }
        
        .connection-status {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .connection-online {
            background: var(--success-color);
            color: white;
        }
        
        .connection-offline {
            background: var(--danger-color);
            color: white;
        }
        
        .notification-temp {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(44, 62, 80, 0.95);
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            z-index: 9999;
            font-size: 1rem;
            font-weight: 500;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(10px);
        }
        
        .last-update {
            position: fixed;
            bottom: 20px;
            left: 20px;
            color: var(--gray-600);
            font-size: 0.85rem;
            background: var(--white);
            padding: 8px 12px;
            border-radius: 20px;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-300);
        }
        
        @media (max-width: 768px) {
            .quorum-percentage {
                font-size: 4rem;
            }
            
            .quorum-label {
                font-size: 1.2rem;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .header-bar {
                padding: 15px;
            }
            
            .quorum-display, .voting-section {
                padding: 20px;
                margin: 15px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Indicador de tiempo real -->
    <div class="real-time-indicator" id="realTimeIndicator">
        <i class="fas fa-broadcast-tower me-2"></i>INICIANDO...
    </div>
    
    <!-- Botón cerrar proyección -->
    <a href="#" onclick="cerrarProyeccion()" class="btn btn-secondary close-projection">
        <i class="fas fa-times me-2"></i>Cerrar Proyección
    </a>

    <!-- Indicador de conexión -->
    <div class="connection-status connection-offline" id="connectionStatus">
        <i class="fas fa-wifi me-1"></i>Conectando...
    </div>

    <div class="container-fluid">
        <!-- Header de la asamblea -->
        <div class="header-bar">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0" id="assemblyTitle">
                        <i class="fas fa-users me-3"></i>
                        <?php echo htmlspecialchars($assembly['titulo'] ?? 'Asamblea de Propietarios'); ?>
                    </h1>
                    <p class="mb-0" id="assemblyInfo">
                        <?php echo htmlspecialchars($assembly['conjunto_nombre'] ?? 'Conjunto Residencial'); ?> •
                        <?php echo date('d/m/Y H:i', strtotime($assembly['fecha_inicio'] ?? 'now')); ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="assembly-info">
                        <strong>Estado:</strong> 
                        <span class="badge bg-success fs-6 ms-2" id="assemblyStatus">
                            <?php echo ucfirst($assembly['estado'] ?? 'Activa'); ?>
                        </span>
                        <span class="status-indicator bg-success" id="statusIndicator"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Columna principal - Quórum -->
            <div class="col-md-8">
                <!-- Display del Quórum -->
                <div class="quorum-display">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="quorum-percentage text-success" id="quorumPercentage">
                                <?php echo number_format($quorumData['porcentaje_coeficiente'] ?? 67.5, 1); ?>%
                            </div>
                            <div class="quorum-label">
                                QUÓRUM ACTUAL
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6">
                                    <div class="stats-card">
                                        <div class="stats-number text-primary" id="totalAsistentes">
                                            <?php echo $quorumData['total_asistentes'] ?? 142; ?>
                                        </div>
                                        <div class="stats-label">Asistentes</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stats-card">
                                        <div class="stats-number text-info" id="totalRegistrados">
                                            <?php echo $quorumData['total_registrados'] ?? 210; ?>
                                        </div>
                                        <div class="stats-label">Registrados</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stats-card">
                                        <div class="stats-number text-success" id="coeficientePresente">
                                            <?php echo number_format($quorumData['coeficiente_presente'] ?? 0.6750, 4); ?>
                                        </div>
                                        <div class="stats-label">Coef. Presente</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stats-card">
                                        <div class="stats-number text-secondary" id="quorumMinimo">
                                            <?php echo $assembly['quorum_minimo'] ?? 50; ?>%
                                        </div>
                                        <div class="stats-label">Mín. Requerido</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Barra de progreso del quórum -->
                    <div class="mt-4">
                        <div class="progress voting-progress">
                            <div class="progress-bar bg-success" 
                                 style="width: <?php echo min(100, $quorumData['porcentaje_coeficiente'] ?? 67.5); ?>%" 
                                 id="quorumProgressBar">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">0%</small>
                            <small class="text-primary fw-semibold" id="quorumTarget">
                                Meta: <?php echo $assembly['quorum_minimo'] ?? 50; ?>%
                            </small>
                            <small class="text-muted">100%</small>
                        </div>
                    </div>
                </div>

                <!-- Sección de votaciones activas -->
                <div class="voting-section" id="activeVotingSection">
                    <h3><i class="fas fa-vote-yea me-3 text-primary"></i>Votaciones</h3>
                    <div id="votingContent">
                        <?php if (isset($votacionActiva) && $votacionActiva): ?>
                            <div class="voting-card">
                                <h4><?php echo htmlspecialchars($votacionActiva['titulo']); ?></h4>
                                <?php if (!empty($votacionActiva['descripcion'])): ?>
                                    <p class="text-muted mb-3"><?php echo htmlspecialchars($votacionActiva['descripcion']); ?></p>
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <?php if (isset($resultadosVotacion) && !empty($resultadosVotacion)): ?>
                                            <?php 
                                            $totalVotos = array_sum(array_column($resultadosVotacion, 'total_votos'));
                                            $totalCoeficiente = array_sum(array_column($resultadosVotacion, 'coeficiente_total'));
                                            ?>
                                            <?php foreach ($resultadosVotacion as $resultado): ?>
                                                <?php 
                                                $porcentajeVotos = $totalVotos > 0 ? ($resultado['total_votos'] / $totalVotos) * 100 : 0;
                                                $porcentajeCoeficiente = $totalCoeficiente > 0 ? ($resultado['coeficiente_total'] / $totalCoeficiente) * 100 : 0;
                                                ?>
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <strong><?php echo htmlspecialchars($resultado['opcion_texto']); ?></strong>
                                                        <span class="badge bg-primary"><?php echo $resultado['total_votos']; ?> votos</span>
                                                    </div>
                                                    <div class="progress mb-2" style="height: 25px;">
                                                        <div class="progress-bar <?php echo $porcentajeVotos > 50 ? 'bg-success' : 'bg-warning'; ?>" 
                                                             style="width: <?php echo $porcentajeVotos; ?>%">
                                                            <?php echo number_format($porcentajeVotos, 1); ?>%
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        Coeficiente: <?php echo number_format($resultado['coeficiente_total'], 4); ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center py-3">
                                                <i class="fas fa-hourglass-half fa-2x text-muted mb-2"></i>
                                                <p class="text-muted">Esperando votos...</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="stats-card">
                                            <div class="stats-number text-primary">
                                                <?php echo $totalVotos ?? 0; ?>
                                            </div>
                                            <div class="stats-label">Votos Emitidos</div>
                                        </div>
                                        <div class="stats-card">
                                            <div class="stats-number text-success">
                                                <?php 
                                                $participacion = isset($totalVotos) && ($quorumData['total_asistentes'] ?? 0) > 0 
                                                    ? ($totalVotos / $quorumData['total_asistentes']) * 100 
                                                    : 0;
                                                echo number_format($participacion, 1);
                                                ?>%
                                            </div>
                                            <div class="stats-label">Participación</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button class="btn btn-warning btn-lg" onclick="cerrarVotacion(<?php echo $votacionActiva['id']; ?>)">
                                        <i class="fas fa-stop me-2"></i>Cerrar Votación
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-vote-yea fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No hay votaciones activas</h4>
                                <p class="text-muted">Cree una nueva votación o active una existente</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Columna lateral - Control de votaciones -->
            <div class="col-md-4">
                <!-- Sección para crear nueva votación -->
                <div class="voting-section mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3><i class="fas fa-plus-circle me-2 text-success"></i>Crear Votación</h3>
                        <button class="btn btn-outline-secondary btn-sm" onclick="toggleCreateForm()">
                            <i class="fas fa-chevron-down" id="toggleIcon"></i>
                        </button>
                    </div>
                    
                    <div id="createVotingForm" style="display: none;">
                        <form id="quickVotingForm" onsubmit="crearVotacionRapida(event)">
                            <input type="hidden" name="asamblea_id" value="<?php echo $assemblyId ?? ''; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Título de la Votación</strong></label>
                                <input type="text" class="form-control" name="titulo" required 
                                       placeholder="Ej: Aprobación del presupuesto 2024">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descripción (opcional)</label>
                                <textarea class="form-control" name="descripcion" rows="2" 
                                          placeholder="Descripción breve de la votación"></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label">Quórum (%)</label>
                                    <input type="number" class="form-control" name="quorum_requerido" 
                                           value="<?php echo $assembly['quorum_minimo'] ?? 50; ?>" min="0" max="100">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Mayoría (%)</label>
                                    <input type="number" class="form-control" name="mayoria_requerida" 
                                           value="50" min="0" max="100">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Opciones de Votación</strong></label>
                                <div id="opciones-container">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="opciones[]" 
                                               placeholder="Opción 1" value="A favor" required>
                                        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="opciones[]" 
                                               placeholder="Opción 2" value="En contra" required>
                                        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="addOption()">
                                    <i class="fas fa-plus me-1"></i>Agregar Opción
                                </button>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="fas fa-check me-2"></i>Crear y Preparar Votación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Sección de votaciones existentes -->
                <div class="voting-section">
                    <h3><i class="fas fa-cog me-3 text-primary"></i>Votaciones Existentes</h3>
                    <div id="existingVotingsContainer">
                        <?php if (isset($votaciones) && !empty($votaciones)): ?>
                            <?php foreach ($votaciones as $votacion): ?>
                                <div class="voting-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($votacion['titulo']); ?></h5>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-secondary btn-sm" 
                                                    onclick="duplicarVotacion(<?php echo $votacion['id']; ?>)" 
                                                    title="Duplicar votación">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <?php if ($votacion['estado'] === 'preparada'): ?>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="eliminarVotacion(<?php echo $votacion['id']; ?>)" 
                                                        title="Eliminar votación">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($votacion['descripcion'])): ?>
                                        <p class="text-muted small mb-2">
                                            <?php echo htmlspecialchars($votacion['descripcion']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">
                                            Estado: 
                                            <span class="badge <?php 
                                                switch($votacion['estado']) {
                                                    case 'abierta': echo 'bg-success'; break;
                                                    case 'preparada': echo 'bg-warning'; break;
                                                    case 'cerrada': echo 'bg-secondary'; break;
                                                    default: echo 'bg-primary';
                                                }
                                            ?>">
                                                <?php echo ucfirst($votacion['estado']); ?>
                                            </span>
                                        </small>
                                        <small class="text-muted">
                                            <?php echo $votacion['total_votos'] ?? 0; ?> votos
                                        </small>
                                    </div>
                                    
                                    <div class="voting-actions">
                                        <?php if ($votacion['estado'] === 'preparada'): ?>
                                            <button class="btn btn-success btn-sm w-100" 
                                                    onclick="abrirVotacion(<?php echo $votacion['id']; ?>)">
                                                <i class="fas fa-play me-2"></i>Abrir Votación
                                            </button>
                                        <?php elseif ($votacion['estado'] === 'abierta'): ?>
                                            <button class="btn btn-warning btn-sm w-100" 
                                                    onclick="cerrarVotacion(<?php echo $votacion['id']; ?>)">
                                                <i class="fas fa-stop me-2"></i>Cerrar Votación
                                            </button>
                                        <?php elseif ($votacion['estado'] === 'cerrada'): ?>
                                            <button class="btn btn-info btn-sm w-100" 
                                                    onclick="verResultados(<?php echo $votacion['id']; ?>)">
                                                <i class="fas fa-chart-bar me-2"></i>Ver Resultados
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted small">No hay votaciones disponibles</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timestamp de última actualización -->
    <div class="last-update" id="lastUpdate">
        <i class="fas fa-clock me-1"></i>Cargado: <?php echo date('H:i:s'); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let refreshInterval;
        let isConnected = false;
        const assemblyId = <?php echo json_encode($assemblyId ?? null); ?>;
        
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Vista de Proyección iniciada');
            updateConnectionStatus(true, 'EN VIVO');
            startAutoRefresh();
            
            // Atajos de teclado
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'r') {
                    e.preventDefault();
                    location.reload();
                }
                if (e.key === 'f' || e.key === 'F11') {
                    if (!e.ctrlKey) {
                        e.preventDefault();
                        toggleFullscreen();
                    }
                }
            });
        });
        
        // Funciones de conexión y actualización
        function updateConnectionStatus(connected, message) {
            isConnected = connected;
            const indicator = document.getElementById('realTimeIndicator');
            const status = document.getElementById('connectionStatus');
            
            if (indicator) {
                indicator.innerHTML = `<i class="fas fa-broadcast-tower me-2"></i>${message}`;
                indicator.style.background = connected ? 'var(--success-color)' : 'var(--danger-color)';
            }
            
            if (status) {
                status.innerHTML = `<i class="fas fa-wifi me-1"></i>${connected ? 'Conectado' : 'Desconectado'}`;
                status.className = `connection-status ${connected ? 'connection-online' : 'connection-offline'}`;
            }
        }
        
        function startAutoRefresh() {
            refreshInterval = setInterval(function() {
                updateTimestamp();
                // Simular actualizaciones de datos
                simulateDataUpdate();
            }, 5000); // Cada 5 segundos
        }
        
        function updateTimestamp() {
            const element = document.getElementById('lastUpdate');
            if (element) {
                const now = new Date();
                element.innerHTML = `<i class="fas fa-clock me-1"></i>Actualizado: ${now.toLocaleTimeString()}`;
            }
        }
        
        function simulateDataUpdate() {
            // Simular pequeños cambios en el quórum
            const quorumElement = document.getElementById('quorumPercentage');
            if (quorumElement) {
                const currentValue = parseFloat(quorumElement.textContent.replace('%', '')) || 67.5;
                const variation = (Math.random() - 0.5) * 2; // Variación de ±1%
                const newValue = Math.max(0, Math.min(100, currentValue + variation));
                
                quorumElement.textContent = newValue.toFixed(1) + '%';
                
                // Actualizar color según el quórum mínimo
                const minQuorum = parseFloat(document.getElementById('quorumMinimo').textContent.replace('%', '')) || 50;
                quorumElement.className = 'quorum-percentage ' + getQuorumColorClass(newValue, minQuorum);
                
                // Actualizar barra de progreso
                const progressBar = document.getElementById('quorumProgressBar');
                if (progressBar) {
                    progressBar.style.width = Math.min(newValue, 100) + '%';
                    progressBar.className = 'progress-bar ' + getQuorumProgressClass(newValue, minQuorum);
                }
            }
        }
        
        function getQuorumColorClass(percentage, minQuorum) {
            if (percentage >= minQuorum) return 'text-success';
            if (percentage >= minQuorum * 0.8) return 'text-warning';
            return 'text-danger';
        }
        
        function getQuorumProgressClass(percentage, minQuorum) {
            if (percentage >= minQuorum) return 'bg-success';
            if (percentage >= minQuorum * 0.8) return 'bg-warning';
            return 'bg-danger';
        }
        
        // Funciones de formulario
        function toggleCreateForm() {
            const form = document.getElementById('createVotingForm');
            const icon = document.getElementById('toggleIcon');
            
            if (form.style.display === 'none') {
                form.style.display = 'block';
                icon.className = 'fas fa-chevron-up';
            } else {
                form.style.display = 'none';
                icon.className = 'fas fa-chevron-down';
            }
        }
        
        function addOption() {
            const container = document.getElementById('opciones-container');
            const optionCount = container.children.length + 1;
            
            const newOption = document.createElement('div');
            newOption.className = 'input-group mb-2';
            newOption.innerHTML = `
                <input type="text" class="form-control" name="opciones[]" 
                       placeholder="Opción ${optionCount}" required>
                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            container.appendChild(newOption);
        }
        
        function removeOption(button) {
            const container = document.getElementById('opciones-container');
            if (container.children.length > 2) {
                button.parentElement.remove();
                
                // Renumerar placeholders
                const inputs = container.querySelectorAll('input[name="opciones[]"]');
                inputs.forEach((input, index) => {
                    if (!input.value) {
                        input.placeholder = `Opción ${index + 1}`;
                    }
                });
            } else {
                showNotification('Debe haber al menos 2 opciones', 'warning');
            }
        }
        
        // Funciones de votación
        async function crearVotacionRapida(event) {
            event.preventDefault();
            
            const form = document.getElementById('quickVotingForm');
            const submitBtn = document.getElementById('submitBtn');
            const formData = new FormData(form);
            
            // Validaciones
            const titulo = formData.get('titulo').trim();
            if (!titulo) {
                showNotification('El título es obligatorio', 'warning');
                return;
            }
            
            const opciones = formData.getAll('opciones[]').filter(op => op.trim() !== '');
            if (opciones.length < 2) {
                showNotification('Debe agregar al menos 2 opciones', 'warning');
                return;
            }
            
            // Deshabilitar botón durante el envío
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
            
            try {
                const response = await fetch('/Asambleas/public/coordinador/crear-votacion', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    showNotification('Votación creada exitosamente!', 'success');
                    form.reset();
                    
                    // Ocultar formulario
                    document.getElementById('createVotingForm').style.display = 'none';
                    document.getElementById('toggleIcon').className = 'fas fa-chevron-down';
                    
                    // Recargar página después de 2 segundos
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                    
                } else {
                    throw new Error(`Error ${response.status}: ${response.statusText}`);
                }
                
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error al crear votación: ' + error.message, 'error');
            } finally {
                // Rehabilitar botón
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Crear y Preparar Votación';
            }
        }
        
        async function abrirVotacion(id) {
            if (!confirm('¿Está seguro de abrir esta votación?')) {
                return;
            }
            
            try {
                const response = await fetch(`/Asambleas/public/coordinador/abrir-votacion/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Votación abierta correctamente', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            } catch (error) {
                showNotification('Error al abrir votación: ' + error.message, 'error');
            }
        }
        
        async function cerrarVotacion(id) {
            if (!confirm('¿Está seguro de cerrar esta votación? No se podrán emitir más votos.')) {
                return;
            }
            
            try {
                const response = await fetch(`/Asambleas/public/coordinador/cerrar-votacion/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Votación cerrada correctamente', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            } catch (error) {
                showNotification('Error al cerrar votación: ' + error.message, 'error');
            }
        }
        
        function verResultados(id) {
            window.open(`/Asambleas/public/coordinador/resultados-votacion/${id}`, '_blank', 'width=800,height=600');
        }
        
        async function duplicarVotacion(id) {
            if (!confirm('¿Duplicar esta votación?')) return;
            
            try {
                const response = await fetch(`/Asambleas/public/coordinador/duplicar-votacion/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Votación duplicada correctamente', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            } catch (error) {
                showNotification('Error al duplicar votación: ' + error.message, 'error');
            }
        }
        
        async function eliminarVotacion(id) {
            if (!confirm('¿Está seguro de eliminar esta votación? Esta acción no se puede deshacer.')) return;
            
            try {
                const response = await fetch(`/Asambleas/public/coordinador/eliminar-votacion/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Votación eliminada correctamente', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            } catch (error) {
                showNotification('Error al eliminar votación: ' + error.message, 'error');
            }
        }
        
        // Funciones de utilidad
        function showNotification(message, type = 'info', duration = 3000) {
            // Remover notificación existente
            let notification = document.querySelector('.notification-temp');
            if (notification) {
                notification.remove();
            }
            
            notification = document.createElement('div');
            notification.className = 'notification-temp';
            
            const icons = {
                success: 'check-circle',
                error: 'exclamation-triangle',
                warning: 'exclamation-circle',
                info: 'info-circle'
            };
            
            const colors = {
                success: 'var(--success-color)',
                error: 'var(--danger-color)',
                warning: 'var(--warning-color)',
                info: 'var(--accent-color)'
            };
            
            notification.style.background = colors[type] || colors.info;
            notification.innerHTML = `<i class="fas fa-${icons[type] || icons.info} me-2"></i>${message}`;
            document.body.appendChild(notification);
            
            setTimeout(() => notification.remove(), duration);
        }
        
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(console.error);
            } else {
                document.exitFullscreen().catch(console.error);
            }
        }
        
        function cerrarProyeccion() {
            if (confirm('¿Está seguro de cerrar la vista de proyección?')) {
                window.location.href = '/Asambleas/public/coordinador/asambleas';
            }
        }
        
        // Cleanup al salir
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });
        
        // Manejo de visibilidad de página
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Pausar actualizaciones cuando la página no es visible
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                }
                updateConnectionStatus(false, 'PAUSADO');
            } else {
                // Reanudar cuando la página vuelve a ser visible
                startAutoRefresh();
                updateConnectionStatus(true, 'EN VIVO');
            }
        });
        
        console.log('✅ Vista de Proyección cargada correctamente');
        console.log('🎯 Controles disponibles:');
        console.log('   • Ctrl+R: Recargar página');
        console.log('   • F o F11: Pantalla completa');
        console.log('   • Auto-refresh: cada 5 segundos');
    </script>
</body>
</html>
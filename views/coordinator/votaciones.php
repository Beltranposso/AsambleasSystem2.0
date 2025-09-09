<?php $title = 'Gestión de Votaciones'; $userRole = 'coordinador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <!-- Container para alertas dinámicas -->
        <div id="alertsContainer"></div>
        
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-vote-yea me-2"></i>Gestión de Votaciones</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <?php if (isset($assembly)): ?>
                    <!-- CAMBIO: Enlace directo a página de crear votación -->
                    <a href="crear-votacion?asamblea=<?php echo $assembly['id']; ?>" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Nueva Votación
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Selector de Asamblea -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label for="assemblySelect" class="form-label">Seleccionar Asamblea:</label>
                        <select class="form-select" id="assemblySelect" onchange="changeAssembly()">
                            <option value="">Seleccione una asamblea...</option>
                            <?php if (isset($assemblies)): ?>
                                <?php foreach ($assemblies as $asm): ?>
                                    <option value="<?php echo $asm['id']; ?>" 
                                            <?php echo (isset($assembly) && $assembly['id'] == $asm['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($asm['titulo']); ?> - 
                                        <?php echo htmlspecialchars($asm['conjunto_nombre']); ?>
                                        (<?php echo ucfirst($asm['estado']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <?php if (isset($assembly)): ?>
                        <div class="col-md-6">
                            <div class="text-end">
                                <h6 class="mb-1"><?php echo htmlspecialchars($assembly['titulo']); ?></h6>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($assembly['conjunto_nombre']); ?>
                                    <br>
                                    <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y H:i', strtotime($assembly['fecha_inicio'])); ?>
                                    <span class="badge bg-<?php echo $assembly['estado'] === 'activa' ? 'success' : 'warning'; ?> ms-2">
                                        <?php echo ucfirst($assembly['estado']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (isset($assembly)): ?>
            <!-- Estadísticas de Votaciones -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo count($votaciones ?? []); ?></h4>
                            <small class="text-muted">Total Votaciones</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h4 class="text-success">
                                <?php 
                                $activas = 0;
                                if (isset($votaciones)) {
                                    foreach ($votaciones as $v) {
                                        if ($v['estado'] === 'abierta') $activas++;
                                    }
                                }
                                echo $activas;
                                ?>
                            </h4>
                            <small class="text-muted">Activas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h4 class="text-warning">
                                <?php 
                                $preparadas = 0;
                                if (isset($votaciones)) {
                                    foreach ($votaciones as $v) {
                                        if ($v['estado'] === 'preparada') $preparadas++;
                                    }
                                }
                                echo $preparadas;
                                ?>
                            </h4>
                            <small class="text-muted">Preparadas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h4 class="text-info">
                                <?php 
                                $cerradas = 0;
                                if (isset($votaciones)) {
                                    foreach ($votaciones as $v) {
                                        if ($v['estado'] === 'cerrada') $cerradas++;
                                    }
                                }
                                echo $cerradas;
                                ?>
                            </h4>
                            <small class="text-muted">Finalizadas</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Votaciones -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-list me-2"></i>Votaciones de la Asamblea</h6>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" onclick="filterVotaciones('all')">Todas</button>
                            <button class="btn btn-outline-success" onclick="filterVotaciones('abierta')">Activas</button>
                            <button class="btn btn-outline-warning" onclick="filterVotaciones('preparada')">Preparadas</button>
                            <button class="btn btn-outline-info" onclick="filterVotaciones('cerrada')">Cerradas</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="votacionesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Votación</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Votos</th>
                                    <th>Participación</th>
                                    <th>Tiempo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($votaciones) && !empty($votaciones)): ?>
                                    <?php foreach ($votaciones as $votacion): ?>
                                        <tr data-status="<?php echo $votacion['estado']; ?>" data-voting-id="<?php echo $votacion['id']; ?>">
                                            <td>
                                                <strong><?php echo htmlspecialchars($votacion['titulo']); ?></strong>
                                                <?php if (!empty($votacion['descripcion'])): ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($votacion['descripcion'], 0, 100)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $tipoClass = '';
                                                switch($votacion['tipo_votacion']) {
                                                    case 'ordinaria':
                                                        $tipoClass = 'bg-primary';
                                                        break;
                                                    case 'extraordinaria':
                                                        $tipoClass = 'bg-warning';
                                                        break;
                                                    case 'unanimidad':
                                                        $tipoClass = 'bg-danger';
                                                        break;
                                                    default:
                                                        $tipoClass = 'bg-secondary';
                                                }
                                                ?>
                                                <span class="badge <?php echo $tipoClass; ?>">
                                                    <?php echo ucfirst($votacion['tipo_votacion']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $estadoClass = '';
                                                $estadoIcon = '';
                                                switch($votacion['estado']) {
                                                    case 'abierta':
                                                        $estadoClass = 'bg-success';
                                                        $estadoIcon = 'play-circle';
                                                        break;
                                                    case 'preparada':
                                                        $estadoClass = 'bg-warning';
                                                        $estadoIcon = 'clock';
                                                        break;
                                                    case 'cerrada':
                                                        $estadoClass = 'bg-secondary';
                                                        $estadoIcon = 'check-circle';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $estadoClass; ?>">
                                                    <i class="fas fa-<?php echo $estadoIcon; ?> me-1"></i>
                                                    <?php echo ucfirst($votacion['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary votos-count"><?php echo $votacion['total_votos'] ?? 0; ?></span>
                                                <small class="text-muted">votos</small>
                                            </td>
                                            <td>
                                                <?php 
                                                $coeficienteVotado = $votacion['coeficiente_votado'] ?? 0;
                                                $coeficienteTotal = 1; // Simplificado para el ejemplo
                                                $participacion = $coeficienteTotal > 0 ? ($coeficienteVotado / $coeficienteTotal) * 100 : 0;
                                                $participacionClass = $participacion >= 75 ? 'bg-success' : ($participacion >= 50 ? 'bg-warning' : 'bg-danger');
                                                ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar participacion-bar <?php echo $participacionClass; ?>" 
                                                         style="width: <?php echo min($participacion, 100); ?>%">
                                                        <?php echo number_format($participacion, 1); ?>%
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    Coef: <?php echo number_format($coeficienteVotado, 4); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($votacion['estado'] === 'abierta'): ?>
                                                    <?php if ($votacion['fecha_inicio']): ?>
                                                        <small class="text-success">
                                                            <i class="fas fa-play me-1"></i>
                                                            Desde: <?php echo date('H:i', strtotime($votacion['fecha_inicio'])); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                <?php elseif ($votacion['estado'] === 'cerrada'): ?>
                                                    <?php if ($votacion['fecha_cierre']): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-stop me-1"></i>
                                                            Cerró: <?php echo date('H:i', strtotime($votacion['fecha_cierre'])); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <small class="text-warning">
                                                        <i class="fas fa-clock me-1"></i>Sin iniciar
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($votacion['estado'] === 'preparada'): ?>
                                                        <button class="btn btn-success btn-sm" 
                                                                onclick="openVoting(<?php echo $votacion['id']; ?>)"
                                                                title="Abrir votación">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    <?php elseif ($votacion['estado'] === 'abierta'): ?>
                                                        <button class="btn btn-warning btn-sm" 
                                                                onclick="closeVoting(<?php echo $votacion['id']; ?>)"
                                                                title="Cerrar votación">
                                                            <i class="fas fa-stop"></i>
                                                        </button>
                                                        <button class="btn btn-info btn-sm" 
                                                                onclick="viewResults(<?php echo $votacion['id']; ?>)"
                                                                title="Ver resultados en tiempo real">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-info btn-sm" 
                                                                onclick="viewResults(<?php echo $votacion['id']; ?>)"
                                                                title="Ver resultados finales">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                                data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="editVoting(<?php echo $votacion['id']; ?>)">
                                                                <i class="fas fa-edit me-2"></i>Editar
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="duplicateVoting(<?php echo $votacion['id']; ?>)">
                                                                <i class="fas fa-copy me-2"></i>Duplicar
                                                            </a></li>
                                                            <?php if ($votacion['estado'] === 'preparada'): ?>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteVoting(<?php echo $votacion['id']; ?>)">
                                                                    <i class="fas fa-trash me-2"></i>Eliminar
                                                                </a></li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-vote-yea fa-3x mb-3"></i>
                                                <p>No hay votaciones en esta asamblea</p>
                                                <!-- CAMBIO: Enlace directo en lugar de modal -->
                                                <a href="crear-votacion?asamblea=<?php echo $assembly['id']; ?>" class="btn btn-success">
                                                    <i class="fas fa-plus me-2"></i>Crear Primera Votación
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Votaciones Activas (resto del código igual) -->
            <?php 
            $votacionesActivas = [];
            if (isset($votaciones)) {
                foreach ($votaciones as $v) {
                    if ($v['estado'] === 'abierta') {
                        $votacionesActivas[] = $v;
                    }
                }
            }
            ?>
            <?php if (!empty($votacionesActivas)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-broadcast-tower me-2"></i>Votaciones Activas - Monitoreo en Tiempo Real
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($votacionesActivas as $activa): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-success" data-voting-id="<?php echo $activa['id']; ?>">
                                                <div class="card-body">
                                                    <h6><?php echo htmlspecialchars($activa['titulo']); ?></h6>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <small class="text-muted">Votos recibidos:</small>
                                                        <strong class="votos-count"><?php echo $activa['total_votos'] ?? 0; ?></strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <small class="text-muted">Participación:</small>
                                                        <strong><?php echo number_format(($activa['coeficiente_votado'] ?? 0) * 100, 1); ?>%</strong>
                                                    </div>
                                                    <div class="btn-group btn-group-sm w-100">
                                                        <button class="btn btn-info" onclick="viewResults(<?php echo $activa['id']; ?>)">
                                                            <i class="fas fa-chart-bar me-1"></i>Resultados
                                                        </button>
                                                        <button class="btn btn-warning" onclick="closeVoting(<?php echo $activa['id']; ?>)">
                                                            <i class="fas fa-stop me-1"></i>Cerrar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h5>Selecciona una Asamblea</h5>
                    <p class="text-muted">Selecciona una asamblea para gestionar sus votaciones.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- ELIMINAMOS COMPLETAMENTE EL MODAL -->

<!-- Modal Ver Resultados (este sí lo mantenemos) -->
<div class="modal fade" id="resultsModal" tabindex="-1" aria-labelledby="resultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultsModalLabel">Resultados de Votación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="resultsContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="exportResults()">
                    <i class="fas fa-download me-2"></i>Exportar Resultados
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript SIMPLIFICADO - SIN configuración de formularios
let votacionManager = null;

function changeAssembly() {
    const select = document.getElementById('assemblySelect');
    const assemblyId = select.value;
    
    if (assemblyId) {
        window.location.href = `votaciones?asamblea=${assemblyId}`;
    } else {
        window.location.href = 'votaciones';
    }
}

function filterVotaciones(status) {
    const rows = document.querySelectorAll('#votacionesTable tbody tr[data-status]');
    
    rows.forEach(row => {
        if (status === 'all' || row.getAttribute('data-status') === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Actualizar botones activos
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    if (window.event && window.event.target) {
        window.event.target.classList.add('active');
    }
}

// Funciones AJAX para las acciones de votaciones (estas funcionan bien)
async function openVoting(votingId) {
    if (!confirm('¿Está seguro de abrir esta votación? Los participantes podrán empezar a votar.')) {
        return;
    }

    try {
        showLoading('Abriendo votación...');
        
        const response = await fetch(`/Asambleas/public/coordinador/abrir-votacion/${votingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ votacion_id: votingId })
        });

        const responseText = await response.text();

        if (response.ok) {
            try {
                const result = JSON.parse(responseText);
                if (result.success) {
                    showAlert('Votación abierta correctamente', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(result.error || 'Error al abrir la votación');
                }
            } catch (jsonError) {
                showAlert('Votación procesada', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            throw new Error(`Error HTTP ${response.status}`);
        }
    } catch (error) {
        console.error('Error abriendo votación:', error);
        showAlert(`Error abriendo votación: ${error.message}`, 'error');
    } finally {
        hideLoading();
    }
}

async function closeVoting(votingId) {
    if (!confirm('¿Está seguro de cerrar esta votación? No se podrán recibir más votos.')) {
        return;
    }

    try {
        showLoading('Cerrando votación...');
        
        const response = await fetch(`/Asambleas/public/coordinador/cerrar-votacion/${votingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ votacion_id: votingId })
        });

        const responseText = await response.text();

        if (response.ok) {
            try {
                const result = JSON.parse(responseText);
                if (result.success) {
                    showAlert('Votación cerrada correctamente', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(result.error || 'Error al cerrar la votación');
                }
            } catch (jsonError) {
                showAlert('Votación procesada', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            throw new Error(`Error HTTP ${response.status}`);
        }
    } catch (error) {
        console.error('Error cerrando votación:', error);
        showAlert(`Error cerrando votación: ${error.message}`, 'error');
    } finally {
        hideLoading();
    }
}

async function viewResults(votingId) {
    try {
        const resultsContent = document.getElementById('resultsContent');
        if (resultsContent) {
            resultsContent.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p>Cargando resultados...</p>
                </div>
            `;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('resultsModal'));
        modal.show();
        
        const response = await fetch(`/Asambleas/public/coordinador/resultados-votacion/${votingId}`);
        
        if (response.ok) {
            const html = await response.text();
            if (resultsContent) {
                resultsContent.innerHTML = html;
            }
        } else {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
    } catch (error) {
        console.error('Error cargando resultados:', error);
        const resultsContent = document.getElementById('resultsContent');
        if (resultsContent) {
            resultsContent.innerHTML = `
                <div class="alert alert-danger">
                    <h6>Error al cargar resultados</h6>
                    <p>${error.message}</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewResults(${votingId})">
                        <i class="fas fa-refresh me-1"></i>Reintentar
                    </button>
                </div>
            `;
        }
    }
}

function editVoting(votingId) {
    showAlert('Función de edición en desarrollo', 'info');
}

async function duplicateVoting(votingId) {
    if (!confirm('¿Crear una copia de esta votación?')) {
        return;
    }

    try {
        showLoading('Duplicando votación...');
        
        const response = await fetch(`/Asambleas/public/coordinador/duplicar-votacion/${votingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                showAlert('Votación duplicada correctamente', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(result.error || 'Error al duplicar la votación');
            }
        } else {
            throw new Error(`Error HTTP: ${response.status}`);
        }
    } catch (error) {
        console.error('Error duplicando votación:', error);
        showAlert(error.message, 'error');
    } finally {
        hideLoading();
    }
}

async function deleteVoting(votingId) {
    if (!confirm('¿Está seguro de eliminar esta votación? Esta acción no se puede deshacer.')) {
        return;
    }

    try {
        showLoading('Eliminando votación...');
        
        const response = await fetch(`/Asambleas/public/coordinador/eliminar-votacion/${votingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                showAlert('Votación eliminada correctamente', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(result.error || 'Error al eliminar la votación');
            }
        } else {
            throw new Error(`Error HTTP: ${response.status}`);
        }
    } catch (error) {
        console.error('Error eliminando votación:', error);
        showAlert(error.message, 'error');
    } finally {
        hideLoading();
    }
}

function exportResults() {
    showAlert('Función de exportación en desarrollo', 'info');
}

// Funciones de utilidad
function showLoading(message = 'Procesando...') {
    let loadingOverlay = document.getElementById('loadingOverlay');
    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'loadingOverlay';
        loadingOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        loadingOverlay.innerHTML = `
            <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; min-width: 200px;">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <div id="loadingMessage">${message}</div>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    } else {
        const messageEl = document.getElementById('loadingMessage');
        if (messageEl) messageEl.textContent = message;
        loadingOverlay.style.display = 'flex';
    }
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}

function showAlert(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const icon = type === 'success' ? 'check-circle' : 
                type === 'error' ? 'exclamation-circle' : 
                type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const main = document.querySelector('main');
    if (main) {
        const firstElement = main.firstElementChild;
        if (firstElement) {
            firstElement.insertAdjacentHTML('beforebegin', alertHtml);
            
            setTimeout(() => {
                const alert = main.querySelector('.alert');
                if (alert) {
                    try {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    } catch (e) {
                        alert.remove();
                    }
                }
            }, 5000);
        }
    }
}

// Auto-refresh para votaciones activas
function setupAutoRefresh() {
    const votacionesActivas = document.querySelectorAll('[data-status="abierta"]');
    if (votacionesActivas.length > 0) {
        console.log(`Auto-refresh configurado para ${votacionesActivas.length} votaciones activas`);
        
        setInterval(function() {
            if (!document.querySelector('.modal.show')) {
                location.reload();
            }
        }, 15000);
    }
}

// Inicialización simplificada
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Página de votaciones cargada (SIN MODAL)');
    
    // Solo configurar auto-refresh
    setupAutoRefresh();
    
    console.log('✅ Configuración completada');
});
</script>

<?php include '../views/layouts/footer.php'; ?>
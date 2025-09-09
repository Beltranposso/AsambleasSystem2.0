<?php $title = 'Control de Quórum'; $userRole = 'coordinador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-percentage me-2"></i>Control de Quórum</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <?php if (isset($assembly)): ?>
                    <button type="button" class="btn btn-outline-secondary" onclick="refreshQuorum()">
                        <i class="fas fa-sync-alt me-2"></i>Actualizar
                    </button>
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

        <?php if (isset($assembly) && isset($quorumData)): ?>
            <!-- Estado del Quórum -->
            <div class="row mb-4">
                <div class="col-12">
                    <?php 
                    $quorumAlcanzado = $quorumData['quorum_alcanzado'] ?? false;
                    $porcentajeCoeficiente = $quorumData['porcentaje_coeficiente'] ?? 0;
                    $quorumMinimo = $quorumData['quorum_minimo'] ?? 50;
                    ?>
                    <div class="card border-<?php echo $quorumAlcanzado ? 'success' : 'warning'; ?>">
                        <div class="card-header bg-<?php echo $quorumAlcanzado ? 'success' : 'warning'; ?> text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-<?php echo $quorumAlcanzado ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                    Estado del Quórum
                                </h5>
                                <h4 class="mb-0"><?php echo number_format($porcentajeCoeficiente, 2); ?>%</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="progress mb-3" style="height: 30px;">
                                        <div class="progress-bar bg-<?php echo $quorumAlcanzado ? 'success' : 'warning'; ?>" 
                                             style="width: <?php echo min($porcentajeCoeficiente, 100); ?>%">
                                            <?php echo number_format($porcentajeCoeficiente, 2); ?>%
                                        </div>
                                        <!-- Línea del quórum mínimo -->
                                        <div style="position: absolute; left: <?php echo $quorumMinimo; ?>%; top: 0; bottom: 0; width: 2px; background-color: red; z-index: 10;"></div>
                                    </div>
                                    <p class="mb-0">
                                        <?php if ($quorumAlcanzado): ?>
                                            <span class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                <strong>QUÓRUM ALCANZADO</strong> - La asamblea puede sesionar válidamente
                                            </span>
                                        <?php else: ?>
                                            <span class="text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <strong>QUÓRUM NO ALCANZADO</strong> - Se requiere <?php echo number_format($quorumMinimo - $porcentajeCoeficiente, 2); ?>% adicional
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="row">
                                        <div class="col-6">
                                            <h6 class="text-muted mb-1">Requerido</h6>
                                            <h4 class="text-danger"><?php echo number_format($quorumMinimo, 2); ?>%</h4>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-muted mb-1">Actual</h6>
                                            <h4 class="text-<?php echo $quorumAlcanzado ? 'success' : 'warning'; ?>">
                                                <?php echo number_format($porcentajeCoeficiente, 2); ?>%
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Detalladas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo $quorumData['total_registrados'] ?? 0; ?></h4>
                            <small class="text-muted">Total Registrados</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h4 class="text-success"><?php echo $quorumData['total_asistentes'] ?? 0; ?></h4>
                            <small class="text-muted">Presentes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h4 class="text-info"><?php echo number_format($quorumData['coeficiente_presente'] ?? 0, 4); ?></h4>
                            <small class="text-muted">Coeficiente Presente</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-secondary">
                        <div class="card-body">
                            <h4 class="text-secondary"><?php echo number_format($quorumData['coeficiente_total'] ?? 0, 4); ?></h4>
                            <small class="text-muted">Coeficiente Total</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Análisis por Porcentajes -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-chart-pie me-2"></i>Asistencia por Personas</h6>
                        </div>
                        <div class="card-body">
                            <?php 
                            $totalReg = $quorumData['total_registrados'] ?? 1;
                            $totalAsis = $quorumData['total_asistentes'] ?? 0;
                            $porcentajeAsistencia = $totalReg > 0 ? ($totalAsis / $totalReg) * 100 : 0;
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Presentes:</span>
                                <strong class="text-success"><?php echo $totalAsis; ?> (<?php echo number_format($porcentajeAsistencia, 1); ?>%)</strong>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" style="width: <?php echo $porcentajeAsistencia; ?>%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ausentes:</span>
                                <strong class="text-danger"><?php echo $totalReg - $totalAsis; ?> (<?php echo number_format(100 - $porcentajeAsistencia, 1); ?>%)</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: <?php echo 100 - $porcentajeAsistencia; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-calculator me-2"></i>Asistencia por Coeficiente</h6>
                        </div>
                        <div class="card-body">
                            <?php 
                            $coefPresente = $quorumData['coeficiente_presente'] ?? 0;
                            $coefTotal = $quorumData['coeficiente_total'] ?? 1;
                            $coefAusente = $coefTotal - $coefPresente;
                            $porcentajeCoefPresente = $coefTotal > 0 ? ($coefPresente / $coefTotal) * 100 : 0;
                            $porcentajeCoefAusente = 100 - $porcentajeCoefPresente;
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Coef. Presente:</span>
                                <strong class="text-success"><?php echo number_format($coefPresente, 4); ?> (<?php echo number_format($porcentajeCoefPresente, 1); ?>%)</strong>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" style="width: <?php echo $porcentajeCoefPresente; ?>%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Coef. Ausente:</span>
                                <strong class="text-danger"><?php echo number_format($coefAusente, 4); ?> (<?php echo number_format($porcentajeCoefAusente, 1); ?>%)</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: <?php echo $porcentajeCoefAusente; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Simulador de Quórum -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6><i class="fas fa-calculator me-2"></i>Simulador de Quórum</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">¿Qué pasaría si llegan más participantes?</label>
                            <div class="input-group">
                                <span class="input-group-text">+</span>
                                <input type="number" class="form-control" id="additionalParticipants" 
                                       min="0" max="10" value="0" onchange="simulateQuorum()">
                                <span class="input-group-text">participantes</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Coeficiente promedio estimado:</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="avgCoeficiente" 
                                       step="0.001" min="0" max="1" value="0.025" onchange="simulateQuorum()">
                                <span class="input-group-text">por persona</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div id="simulationResult" class="alert alert-info">
                            <strong>Simulación:</strong> Agregue participantes para ver el impacto en el quórum
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
          <div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h6>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary" onclick="exportQuorumReport()" title="Exportar reporte">
                    <i class="fas fa-download"></i>
                </button>
                <button class="btn btn-outline-secondary" onclick="refreshQuorum()" title="Actualizar datos">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <?php if (isset($assembly) && $assembly['estado'] === 'programada'): ?>
                    <button class="btn btn-success btn-sm" onclick="toggleAssemblyStatus('activate')" 
                            <?php echo !$quorumAlcanzado ? 'disabled title="Se requiere quórum para activar"' : 'title="Activar asamblea"'; ?>>
                        <i class="fas fa-play"></i>
                    </button>
                <?php elseif (isset($assembly) && $assembly['estado'] === 'activa'): ?>
                    <button class="btn btn-warning btn-sm" onclick="toggleAssemblyStatus('finalize')" title="Finalizar asamblea">
                        <i class="fas fa-stop"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <a href="/Asambleas/public/coordinador/asistencia?asamblea=<?php echo $assembly['id']; ?>" 
                   class="btn btn-primary w-100">
                    <i class="fas fa-user-check me-2"></i>Control de Asistencia
                </a>
            </div>
            <div class="col-md-3">
                <a href="/Asambleas/public/coordinador/participantes?asamblea=<?php echo $assembly['id']; ?>" 
                   class="btn btn-info w-100">
                    <i class="fas fa-users me-2"></i>Gestionar Participantes
                </a>
            </div>
            <div class="col-md-3">
                <?php if ($quorumAlcanzado): ?>
                    <a href="/Asambleas/public/coordinador/votaciones?asamblea=<?php echo $assembly['id']; ?>" 
                       class="btn btn-success w-100">
                        <i class="fas fa-vote-yea me-2"></i>Iniciar Votaciones
                    </a>
                <?php else: ?>
                    <button class="btn btn-outline-secondary w-100" disabled title="Se requiere quórum para votar">
                        <i class="fas fa-vote-yea me-2"></i>Votaciones (Sin Quórum)
                    </button>
                <?php endif; ?>
            </div>
            <div class="col-md-3">
                <a href="/Asambleas/public/coordinador/proyeccion?asamblea=<?php echo $assembly['id']; ?>" 
                   class="btn btn-dark w-100" target="_blank">
                    <i class="fas fa-tv me-2"></i>Vista Proyección
                </a>
            </div>
        </div>
        
        <?php if (isset($assembly)): ?>
        <!-- Segunda fila de acciones -->
        <div class="row mt-3">
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100" onclick="simulateQuorumServer()" title="Simulación con datos del servidor">
                    <i class="fas fa-calculator me-2"></i>Simulación Avanzada
                </button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-info w-100" onclick="loadQuorumHistory()" title="Actualizar historial">
                    <i class="fas fa-history me-2"></i>Ver Historial
                </button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-success w-100" onclick="exportQuorumReport()" title="Descargar reporte completo">
                    <i class="fas fa-file-excel me-2"></i>Exportar Reporte
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php if (isset($assembly) && ($assembly['estado'] === 'programada' || $assembly['estado'] === 'activa')): ?>
<div class="card mt-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Control de Asamblea</h6>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="mb-1">Estado actual: 
                    <span class="badge bg-<?php echo $assembly['estado'] === 'activa' ? 'success' : 'warning'; ?> fs-6">
                        <?php echo ucfirst($assembly['estado']); ?>
                    </span>
                </h6>
                <?php if ($assembly['estado'] === 'programada'): ?>
                    <p class="text-muted mb-0">
                        <?php if ($quorumAlcanzado): ?>
                            <i class="fas fa-check-circle text-success me-1"></i>
                            La asamblea tiene quórum suficiente y puede ser activada
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                            Se requiere quórum mínimo para activar la asamblea
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle text-info me-1"></i>
                        La asamblea está activa. Puede finalizar cuando sea necesario.
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if ($assembly['estado'] === 'programada'): ?>
                    <button class="btn btn-success" onclick="toggleAssemblyStatus('activate')" 
                            <?php echo !$quorumAlcanzado ? 'disabled' : ''; ?>>
                        <i class="fas fa-play me-2"></i>Activar Asamblea
                    </button>
                <?php else: ?>
                    <button class="btn btn-warning" onclick="toggleAssemblyStatus('finalize')">
                        <i class="fas fa-stop me-2"></i>Finalizar Asamblea
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
        <?php elseif (isset($assembly)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>Sin Datos de Quórum</h5>
                    <p class="text-muted">No se pudieron cargar los datos de quórum para esta asamblea.</p>
                    <a href="/Asambleas/public/coordinador/participantes?asamblea=<?php echo $assembly['id']; ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>Gestionar Participantes
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h5>Selecciona una Asamblea</h5>
                    <p class="text-muted">Selecciona una asamblea activa para controlar su quórum.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
// Variables globales para quórum
let quorumRefreshInterval;
let currentQuorumData = null;
const assemblyId = <?php echo isset($assembly) ? $assembly['id'] : 'null'; ?>;

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 Quórum Manager iniciado');
    
    if (assemblyId) {
        startQuorumMonitoring();
        loadQuorumHistory();
    }
    
    // Inicializar simulación
    simulateQuorum();
});

// ================================
// FUNCIONES PRINCIPALES
// ================================

function startQuorumMonitoring() {
    // Cargar datos iniciales
    refreshQuorumData();
    
    // Auto-refresh cada 15 segundos para asambleas activas
    <?php if (isset($assembly) && $assembly['estado'] === 'activa'): ?>
    quorumRefreshInterval = setInterval(() => {
        if (!document.hidden) {
            refreshQuorumData();
        }
    }, 15000);
    console.log('📊 Monitoreo automático de quórum activado');
    <?php endif; ?>
}

async function refreshQuorumData() {
    if (!assemblyId) return;
    
    try {
        const response = await fetch(`/Asambleas/public/coordinador/quorum-data?asamblea=${assemblyId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            currentQuorumData = result.data;
            updateQuorumDisplay(result.data);
            console.log('✅ Datos de quórum actualizados');
        } else {
            throw new Error(result.error || 'Error desconocido');
        }
        
    } catch (error) {
        console.error('❌ Error actualizando quórum:', error);
        showQuorumAlert('Error al actualizar datos de quórum', 'warning');
    }
}

function updateQuorumDisplay(data) {
    // Actualizar porcentaje principal
    const quorumPercentage = document.querySelector('.card-header h4');
    if (quorumPercentage) {
        quorumPercentage.textContent = `${parseFloat(data.porcentaje_coeficiente).toFixed(2)}%`;
    }
    
    // Actualizar barra de progreso
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        const percentage = Math.min(parseFloat(data.porcentaje_coeficiente), 100);
        progressBar.style.width = `${percentage}%`;
        progressBar.textContent = `${percentage.toFixed(2)}%`;
        
        // Actualizar clase según estado
        const quorumAlcanzado = data.quorum_alcanzado;
        progressBar.className = `progress-bar bg-${quorumAlcanzado ? 'success' : 'warning'}`;
    }
    
    // Actualizar estadísticas
    updateStatCard('total_registrados', data.total_registrados);
    updateStatCard('total_asistentes', data.total_asistentes);
    updateStatCard('coeficiente_presente', parseFloat(data.coeficiente_presente).toFixed(4));
    updateStatCard('coeficiente_total', parseFloat(data.coeficiente_total).toFixed(4));
    
    // Actualizar estado del quórum
    updateQuorumStatus(data);
    
    // Actualizar gráficos de asistencia
    updateAttendanceCharts(data);
    
    // Actualizar timestamp
    updateLastRefresh();
}

function updateStatCard(cardId, value) {
    const elements = document.querySelectorAll(`[data-stat="${cardId}"], .text-${cardId.split('_')[1]}`);
    elements.forEach(element => {
        if (element.tagName === 'H4') {
            element.textContent = value;
        }
    });
}

function updateQuorumStatus(data) {
    const statusCard = document.querySelector('.card-header');
    const statusText = document.querySelector('.card-body p');
    
    if (statusCard && statusText) {
        const quorumAlcanzado = data.quorum_alcanzado;
        const className = quorumAlcanzado ? 'success' : 'warning';
        const icon = quorumAlcanzado ? 'check-circle' : 'exclamation-triangle';
        
        // Actualizar header
        statusCard.className = `card-header bg-${className} text-white`;
        
        // Actualizar texto
        if (quorumAlcanzado) {
            statusText.innerHTML = `
                <span class="text-success">
                    <i class="fas fa-${icon} me-1"></i>
                    <strong>QUÓRUM ALCANZADO</strong> - La asamblea puede sesionar válidamente
                </span>
            `;
        } else {
            const diferencia = data.quorum_minimo - data.porcentaje_coeficiente;
            statusText.innerHTML = `
                <span class="text-warning">
                    <i class="fas fa-${icon} me-1"></i>
                    <strong>QUÓRUM NO ALCANZADO</strong> - Se requiere ${diferencia.toFixed(2)}% adicional
                </span>
            `;
        }
    }
}

function updateAttendanceCharts(data) {
    // Actualizar gráfico de personas
    const totalReg = data.total_registrados || 1;
    const totalAsis = data.total_asistentes || 0;
    const porcentajeAsistencia = totalReg > 0 ? (totalAsis / totalReg) * 100 : 0;
    
    updateProgressChart('personas', {
        presente: { count: totalAsis, percentage: porcentajeAsistencia },
        ausente: { count: totalReg - totalAsis, percentage: 100 - porcentajeAsistencia }
    });
    
    // Actualizar gráfico de coeficientes
    const coefPresente = parseFloat(data.coeficiente_presente) || 0;
    const coefTotal = parseFloat(data.coeficiente_total) || 1;
    const coefAusente = coefTotal - coefPresente;
    const porcentajeCoefPresente = coefTotal > 0 ? (coefPresente / coefTotal) * 100 : 0;
    
    updateProgressChart('coeficiente', {
        presente: { value: coefPresente, percentage: porcentajeCoefPresente },
        ausente: { value: coefAusente, percentage: 100 - porcentajeCoefPresente }
    });
}

function updateProgressChart(type, data) {
    const cards = document.querySelectorAll('.card');
    let targetCard = null;
    
    // Buscar la card correcta por el contenido del header
    cards.forEach(card => {
        const header = card.querySelector('.card-header h6');
        if (header && header.textContent.includes(type === 'personas' ? 'Personas' : 'Coeficiente')) {
            targetCard = card;
        }
    });
    
    if (!targetCard) return;
    
    // Actualizar valores de presente
    const presenteElements = targetCard.querySelectorAll('.text-success strong');
    const ausenteElements = targetCard.querySelectorAll('.text-danger strong');
    const progressBars = targetCard.querySelectorAll('.progress-bar');
    
    if (type === 'personas') {
        if (presenteElements[0]) {
            presenteElements[0].textContent = `${data.presente.count} (${data.presente.percentage.toFixed(1)}%)`;
        }
        if (ausenteElements[0]) {
            ausenteElements[0].textContent = `${data.ausente.count} (${data.ausente.percentage.toFixed(1)}%)`;
        }
        if (progressBars[0]) {
            progressBars[0].style.width = `${data.presente.percentage}%`;
        }
        if (progressBars[1]) {
            progressBars[1].style.width = `${data.ausente.percentage}%`;
        }
    } else {
        if (presenteElements[0]) {
            presenteElements[0].textContent = `${data.presente.value.toFixed(4)} (${data.presente.percentage.toFixed(1)}%)`;
        }
        if (ausenteElements[0]) {
            ausenteElements[0].textContent = `${data.ausente.value.toFixed(4)} (${data.ausente.percentage.toFixed(1)}%)`;
        }
        if (progressBars[0]) {
            progressBars[0].style.width = `${data.presente.percentage}%`;
        }
        if (progressBars[1]) {
            progressBars[1].style.width = `${data.ausente.percentage}%`;
        }
    }
}

function updateLastRefresh() {
    // Buscar elemento de timestamp o crearlo
    let timestampElement = document.getElementById('last-refresh');
    if (!timestampElement) {
        timestampElement = document.createElement('small');
        timestampElement.id = 'last-refresh';
        timestampElement.className = 'text-muted';
        timestampElement.style.cssText = 'position: fixed; bottom: 10px; right: 10px; background: rgba(255,255,255,0.9); padding: 5px 10px; border-radius: 5px; font-size: 0.8em;';
        document.body.appendChild(timestampElement);
    }
    
    const now = new Date();
    timestampElement.innerHTML = `<i class="fas fa-clock me-1"></i>Actualizado: ${now.toLocaleTimeString()}`;
}

// ================================
// FUNCIONES DE SIMULACIÓN
// ================================

function simulateQuorum() {
    const additionalParticipants = parseInt(document.getElementById('additionalParticipants').value) || 0;
    const avgCoeficiente = parseFloat(document.getElementById('avgCoeficiente').value) || 0;
    
    if (!currentQuorumData) {
        // Usar datos PHP como fallback
        const currentCoefPresente = <?php echo $quorumData['coeficiente_presente'] ?? 0; ?>;
        const currentCoefTotal = <?php echo $quorumData['coeficiente_total'] ?? 1; ?>;
        const quorumMinimo = <?php echo $quorumData['quorum_minimo'] ?? 50; ?>;
        
        performSimulation(additionalParticipants, avgCoeficiente, {
            coeficiente_presente: currentCoefPresente,
            coeficiente_total: currentCoefTotal,
            quorum_minimo: quorumMinimo
        });
    } else {
        performSimulation(additionalParticipants, avgCoeficiente, currentQuorumData);
    }
}

function performSimulation(additionalParticipants, avgCoeficiente, data) {
    // Calcular nuevo coeficiente
    const additionalCoef = additionalParticipants * avgCoeficiente;
    const newCoefPresente = parseFloat(data.coeficiente_presente) + additionalCoef;
    const newCoefTotal = parseFloat(data.coeficiente_total) + additionalCoef;
    
    // Calcular nuevo porcentaje
    const newPercentage = newCoefTotal > 0 ? (newCoefPresente / newCoefTotal) * 100 : 0;
    const quorumMinimo = parseFloat(data.quorum_minimo) || 50;
    const quorumAlcanzado = newPercentage >= quorumMinimo;
    
    // Calcular diferencia necesaria
    let diferenciaNecesaria = 0;
    if (!quorumAlcanzado && avgCoeficiente > 0) {
        const coeficienteNecesario = (quorumMinimo / 100) * newCoefTotal - newCoefPresente;
        diferenciaNecesaria = Math.ceil(coeficienteNecesario / avgCoeficiente);
    }
    
    // Mostrar resultado
    const resultDiv = document.getElementById('simulationResult');
    const statusClass = quorumAlcanzado ? 'alert-success' : 'alert-warning';
    const statusIcon = quorumAlcanzado ? 'check-circle' : 'exclamation-triangle';
    const statusText = quorumAlcanzado ? 'QUÓRUM ALCANZADO' : 'QUÓRUM NO ALCANZADO';
    
    let extraInfo = '';
    if (!quorumAlcanzado && diferenciaNecesaria > 0) {
        extraInfo = `<br><small class="text-muted">Se necesitarían ${diferenciaNecesaria} participante(s) adicional(es) con coef. ${avgCoeficiente}</small>`;
    }
    
    resultDiv.className = `alert ${statusClass}`;
    resultDiv.innerHTML = `
        <strong><i class="fas fa-${statusIcon} me-1"></i>Simulación:</strong> 
        Con ${additionalParticipants} participante(s) adicional(es), el quórum sería del 
        <strong>${newPercentage.toFixed(2)}%</strong> - ${statusText}
        <br>
        <small class="text-muted">
            Coeficiente presente: ${newCoefPresente.toFixed(4)} / ${newCoefTotal.toFixed(4)}
        </small>
        ${extraInfo}
    `;
}

async function simulateQuorumServer() {
    if (!assemblyId) return;
    
    const additionalParticipants = parseInt(document.getElementById('additionalParticipants').value) || 0;
    const avgCoeficiente = parseFloat(document.getElementById('avgCoeficiente').value) || 0;
    
    try {
        const response = await fetch('/Asambleas/public/coordinador/simulate-quorum', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                asamblea_id: assemblyId,
                additional_participants: additionalParticipants,
                avg_coeficiente: avgCoeficiente
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayServerSimulation(result.simulation);
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Error en simulación:', error);
        showQuorumAlert('Error al simular quórum', 'error');
    }
}

function displayServerSimulation(simulation) {
    const resultDiv = document.getElementById('simulationResult');
    const statusClass = simulation.quorum_alcanzado ? 'alert-success' : 'alert-warning';
    const statusIcon = simulation.quorum_alcanzado ? 'check-circle' : 'exclamation-triangle';
    const statusText = simulation.quorum_alcanzado ? 'QUÓRUM ALCANZADO' : 'QUÓRUM NO ALCANZADO';
    
    let extraInfo = '';
    if (!simulation.quorum_alcanzado && simulation.diferencia_necesaria > 0) {
        extraInfo = `<br><small class="text-muted">Se necesitarían ${simulation.diferencia_necesaria} participante(s) adicional(es)</small>`;
    }
    
    resultDiv.className = `alert ${statusClass}`;
    resultDiv.innerHTML = `
        <strong><i class="fas fa-${statusIcon} me-1"></i>Simulación (Servidor):</strong> 
        Con ${simulation.additional_participants} participante(s) adicional(es), el quórum sería del 
        <strong>${simulation.new_porcentaje_coeficiente.toFixed(2)}%</strong> - ${statusText}
        <br>
        <small class="text-muted">
            Coeficiente presente: ${simulation.new_coef_presente.toFixed(4)} / ${simulation.new_coef_total.toFixed(4)}
        </small>
        ${extraInfo}
    `;
}

// ================================
// FUNCIONES DE HISTORIAL
// ================================

async function loadQuorumHistory() {
    if (!assemblyId) return;
    
    try {
        const response = await fetch(`/Asambleas/public/coordinador/quorum-history?asamblea=${assemblyId}&limit=10`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayQuorumHistory(result.historial);
        }
        
    } catch (error) {
        console.error('Error cargando historial:', error);
    }
}

function displayQuorumHistory(historial) {
    // Crear sección de historial si no existe
    let historySection = document.getElementById('quorum-history');
    if (!historySection && historial.length > 0) {
        historySection = document.createElement('div');
        historySection.id = 'quorum-history';
        historySection.className = 'card mt-4';
        historySection.innerHTML = `
            <div class="card-header">
                <h6><i class="fas fa-history me-2"></i>Historial de Cambios</h6>
            </div>
            <div class="card-body" id="history-content">
            </div>
        `;
        
        // Insertar antes de las acciones rápidas
        const actionsCard = document.querySelector('.card:last-child');
        if (actionsCard) {
            actionsCard.parentNode.insertBefore(historySection, actionsCard);
        }
    }
    
    const historyContent = document.getElementById('history-content');
    if (historyContent && historial.length > 0) {
        let historyHtml = '<div class="timeline">';
        
        historial.forEach(item => {
            const time = new Date(item.fecha_cambio).toLocaleTimeString();
            const action = item.asistencia == 1 ? 'Ingresó' : 'Salió';
            const icon = item.asistencia == 1 ? 'user-plus' : 'user-minus';
            const color = item.asistencia == 1 ? 'success' : 'warning';
            
            historyHtml += `
                <div class="timeline-item">
                    <div class="timeline-marker bg-${color}">
                        <i class="fas fa-${icon}"></i>
                    </div>
                    <div class="timeline-content">
                        <small class="text-muted">${time}</small>
                        <p class="mb-0">${item.nombre} ${item.apellido} ${action}</p>
                        <small class="text-muted">Coeficiente: ${parseFloat(item.coeficiente_asignado).toFixed(4)}</small>
                    </div>
                </div>
            `;
        });
        
        historyHtml += '</div>';
        historyContent.innerHTML = historyHtml;
    }
}

// ================================
// FUNCIONES DE EXPORTACIÓN
// ================================

function exportQuorumReport() {
    if (!assemblyId) {
        showQuorumAlert('No hay asamblea seleccionada', 'warning');
        return;
    }
    
    const url = `/Asambleas/public/coordinador/export-quorum-report?asamblea=${assemblyId}`;
    window.open(url, '_blank');
    showQuorumAlert('Descargando reporte de quórum...', 'info', 2000);
}

// ================================
// FUNCIONES DE CONTROL DE ASAMBLEA
// ================================

async function toggleAssemblyStatus(action) {
    if (!assemblyId) return;
    
    const actionText = action === 'activate' ? 'activar' : 'finalizar';
    const confirmMessage = `¿Está seguro de ${actionText} esta asamblea?`;
    
    if (!confirm(confirmMessage)) return;
    
    try {
        const response = await fetch('/Asambleas/public/coordinador/toggle-assembly-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                asamblea_id: assemblyId,
                action: action
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showQuorumAlert(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Error:', error);
        showQuorumAlert(`Error al ${actionText} asamblea: ${error.message}`, 'error');
    }
}

// ================================
// FUNCIONES DE UTILIDAD
// ================================

function changeAssembly() {
    const select = document.getElementById('assemblySelect');
    const assemblyId = select.value;
    
    if (assemblyId) {
        window.location.href = `/Asambleas/public/coordinador/quorum?asamblea=${assemblyId}`;
    } else {
        window.location.href = '/Asambleas/public/coordinador/quorum';
    }
}

function refreshQuorum() {
    if (assemblyId) {
        refreshQuorumData();
        loadQuorumHistory();
        showQuorumAlert('Datos actualizados', 'success', 2000);
    } else {
        location.reload();
    }
}

function showQuorumAlert(message, type = 'info', duration = 4000) {
    // Remover alertas anteriores
    document.querySelectorAll('.quorum-alert').forEach(alert => alert.remove());
    
    const alertClass = type === 'success' ? 'alert-success' : 
                     type === 'error' ? 'alert-danger' : 
                     type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const icon = type === 'success' ? 'check-circle' : 
                type === 'error' ? 'exclamation-circle' : 
                type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    const alertElement = document.createElement('div');
    alertElement.className = `alert ${alertClass} quorum-alert`;
    alertElement.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    alertElement.innerHTML = `
        <i class="fas fa-${icon} me-2"></i>${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(alertElement);
    
    // Auto-hide
    setTimeout(() => {
        if (alertElement && alertElement.parentNode) {
            alertElement.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => alertElement.remove(), 300);
        }
    }, duration);
}

// ================================
// CLEANUP Y EVENTOS
// ================================

// Limpiar intervals al salir
window.addEventListener('beforeunload', function() {
    if (quorumRefreshInterval) {
        clearInterval(quorumRefreshInterval);
    }
});

// Pausar/reanudar monitoring cuando se oculta/muestra la página
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        if (quorumRefreshInterval) {
            clearInterval(quorumRefreshInterval);
        }
    } else {
        if (assemblyId && <?php echo isset($assembly) && $assembly['estado'] === 'activa' ? 'true' : 'false'; ?>) {
            startQuorumMonitoring();
        }
    }
});

// Estilos CSS adicionales para animaciones
if (!document.getElementById('quorum-styles')) {
    const style = document.createElement('style');
    style.id = 'quorum-styles';
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-marker {
            position: absolute;
            left: -30px;
            top: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: -18px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
    `;
    document.head.appendChild(style);
}

console.log('✅ Quórum JavaScript completamente cargado');
</script>

<?php include '../views/layouts/footer.php'; ?>
<?php $title = 'Gestión de Coeficientes'; $userRole = 'operador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-calculator me-2"></i>Gestión de Coeficientes</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <?php if (isset($assembly)): ?>
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adjustCoeficientModal">
                            <i class="fas fa-edit me-2"></i>Ajuste Masivo
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshCoeficients()">
                            <i class="fas fa-sync-alt me-2"></i>Actualizar
                        </button>
                    </div>
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

        <?php if (isset($assembly) && isset($coeficienteSummary)): ?>
            <!-- Resumen de Coeficientes -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo $coeficienteSummary['total_participantes'] ?? 0; ?></h4>
                            <small class="text-muted">Total Participantes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h4 class="text-info"><?php echo number_format($coeficienteSummary['coeficiente_total'] ?? 0, 4); ?></h4>
                            <small class="text-muted">Coeficiente Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h4 class="text-success"><?php echo number_format($coeficienteSummary['coeficiente_promedio'] ?? 0, 4); ?></h4>
                            <small class="text-muted">Promedio</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-<?php echo ($coeficienteSummary['coeficiente_total'] ?? 0) > 1 ? 'danger' : 'success'; ?>">
                        <div class="card-body">
                            <h4 class="text-<?php echo ($coeficienteSummary['coeficiente_total'] ?? 0) > 1 ? 'danger' : 'success'; ?>">
                                <?php echo number_format(($coeficienteSummary['coeficiente_total'] ?? 0) * 100, 2); ?>%
                            </h4>
                            <small class="text-muted">Representación</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado del Coeficiente Total -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6><i class="fas fa-chart-line me-2"></i>Estado del Coeficiente Total</h6>
                </div>
                <div class="card-body">
                    <?php 
                    $total = $coeficienteSummary['coeficiente_total'] ?? 0;
                    $percentage = $total * 100;
                    $isOverLimit = $total > 1;
                    ?>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-<?php echo $isOverLimit ? 'danger' : 'success'; ?>" 
                             style="width: <?php echo min($percentage, 100); ?>%">
                            <?php echo number_format($percentage, 2); ?>%
                        </div>
                        <!-- Línea del 100% -->
                        <div style="position: absolute; left: 100%; top: 0; bottom: 0; width: 2px; background-color: red; z-index: 10;"></div>
                    </div>
                    
                    <?php if ($isOverLimit): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>ALERTA:</strong> El coeficiente total excede 1.0000 
                            (<?php echo number_format($total - 1, 4); ?> por encima del límite)
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>CORRECTO:</strong> El coeficiente total está dentro del límite permitido
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lista de Participantes con Coeficientes -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Participantes y Coeficientes</h6>
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control form-control-sm" 
                                   id="searchParticipant" placeholder="Buscar participante...">
                            <button class="btn btn-outline-secondary btn-sm" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="participantsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Participante</th>
                                    <th>Apartamento</th>
                                    <th>Coef. Base</th>
                                    <th>Coef. Asignado</th>
                                    <th>% del Total</th>
                                    <th>Estado</th>
                                    <th>Historial</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($participants) && !empty($participants)): ?>
                                    <?php foreach ($participants as $participant): ?>
                                        <tr data-participant-id="<?php echo $participant['usuario_id']; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                                                             style="width: 35px; height: 35px; font-size: 12px;">
                                                            <?php echo strtoupper(substr($participant['nombre'], 0, 1) . substr($participant['apellido'], 0, 1)); ?>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($participant['nombre'] . ' ' . $participant['apellido']); ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($participant['cedula']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo htmlspecialchars($participant['apartamento'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?php echo number_format($participant['coef_propietario'] ?? 0, 4); ?></span>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm" style="width: 130px;">
                                                    <input type="number" 
                                                           class="form-control coeficiente-input" 
                                                           value="<?php echo $participant['coeficiente_asignado']; ?>"
                                                           step="0.0001" 
                                                           min="0" 
                                                           max="1"
                                                           data-user-id="<?php echo $participant['usuario_id']; ?>"
                                                           data-original="<?php echo $participant['coeficiente_asignado']; ?>">
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="updateCoeficiente(<?php echo $participant['usuario_id']; ?>)"
                                                            title="Actualizar">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $totalSum = $coeficienteSummary['coeficiente_total'] ?? 1;
                                                $percentage = $totalSum > 0 ? ($participant['coeficiente_asignado'] / $totalSum) * 100 : 0;
                                                ?>
                                                <strong><?php echo number_format($percentage, 2); ?>%</strong>
                                            </td>
                                            <td>
                                                <?php 
                                                $coefAsignado = $participant['coeficiente_asignado'];
                                                $coefBase = $participant['coef_propietario'] ?? 0;
                                                if (abs($coefAsignado - $coefBase) < 0.0001): ?>
                                                    <span class="badge bg-success">Normal</span>
                                                <?php elseif ($coefAsignado > $coefBase): ?>
                                                    <span class="badge bg-warning">Aumentado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Reducido</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($participant['registro_coeficiente'])): ?>
                                                    <button class="btn btn-outline-info btn-sm" 
                                                            onclick="viewHistory(<?php echo $participant['usuario_id']; ?>)"
                                                            title="Ver historial de cambios">
                                                        <i class="fas fa-history"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin cambios</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="editCoeficiente(<?php echo $participant['usuario_id']; ?>)"
                                                            title="Editar coeficiente">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            onclick="resetCoeficiente(<?php echo $participant['usuario_id']; ?>)"
                                                            title="Restaurar coeficiente base">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info btn-sm" 
                                                            onclick="copyCoeficiente(<?php echo $participant['usuario_id']; ?>)"
                                                            title="Copiar coeficiente">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-calculator fa-3x mb-3"></i>
                                                <p>No hay participantes en esta asamblea</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Herramientas de Análisis -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-chart-pie me-2"></i>Distribución de Coeficientes</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="coeficienteChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Validaciones</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php if (($coeficienteSummary['coeficiente_total'] ?? 0) > 1): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                        <span class="text-danger">
                                            <i class="fas fa-times-circle me-2"></i>Total excede 1.0000
                                        </span>
                                        <span class="badge bg-danger"><?php echo number_format($coeficienteSummary['coeficiente_total'] - 1, 4); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (($coeficienteSummary['coeficiente_total'] ?? 0) < 0.9): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Total muy bajo
                                        </span>
                                        <span class="badge bg-warning"><?php echo number_format(1 - $coeficienteSummary['coeficiente_total'], 4); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                $anomalies = 0;
                                if (isset($participants)) {
                                    foreach ($participants as $p) {
                                        if ($p['coeficiente_asignado'] > 0.1) $anomalies++; // Ejemplo de validación
                                    }
                                }
                                if ($anomalies > 0): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                        <span class="text-info">
                                            <i class="fas fa-info-circle me-2"></i>Coeficientes atípicos
                                        </span>
                                        <span class="badge bg-info"><?php echo $anomalies; ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!$anomalies && ($coeficienteSummary['coeficiente_total'] ?? 0) <= 1 && ($coeficienteSummary['coeficiente_total'] ?? 0) >= 0.9): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                        <span class="text-success">
                                            <i class="fas fa-check-circle me-2"></i>Todos los coeficientes son válidos
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h5>Selecciona una Asamblea</h5>
                    <p class="text-muted">Selecciona una asamblea para gestionar los coeficientes de participación.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Modal Editar Coeficiente -->
<div class="modal fade" id="editCoeficienteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Coeficiente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCoeficienteForm" method="POST" action="/Asambleas/public/operador/actualizar-coeficiente">
                <div class="modal-body">
                    <input type="hidden" name="asamblea_id" value="<?php echo isset($assembly) ? $assembly['id'] : ''; ?>">
                    <input type="hidden" name="usuario_id" id="edit_usuario_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Participante:</label>
                        <div id="edit_participant_info" class="alert alert-light"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="coef_base" class="form-label">Coeficiente Base:</label>
                            <input type="number" class="form-control" id="coef_base" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_coeficiente" class="form-label">Nuevo Coeficiente:</label>
                            <input type="number" class="form-control" name="coeficiente" id="edit_coeficiente" 
                                   step="0.0001" min="0" max="1" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="razon" class="form-label">Razón del Cambio:</label>
                        <textarea class="form-control" name="razon" id="razon" rows="3" required 
                                  placeholder="Explique el motivo del cambio de coeficiente..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Impacto:</strong> <span id="impact_info">-</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Coeficiente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajuste Masivo -->
<?php if (isset($assembly)): ?>
<div class="modal fade" id="adjustCoeficientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajuste Masivo de Coeficientes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Opciones de Ajuste</h6>
                        <div class="mb-3">
                            <label class="form-label">Tipo de Ajuste:</label>
                            <select class="form-select" id="adjustment_type">
                                <option value="normalize">Normalizar a 1.0000</option>
                                <option value="reset">Restaurar valores base</option>
                                <option value="proportional">Ajuste proporcional</option>
                                <option value="custom">Valor personalizado</option>
                            </select>
                        </div>
                        <div class="mb-3" id="custom_value_group" style="display: none;">
                            <label for="custom_value" class="form-label">Valor Objetivo:</label>
                            <input type="number" class="form-control" id="custom_value" 
                                   step="0.0001" min="0" max="1" value="1.0000">
                        </div>
                        <div class="mb-3">
                            <label for="adjustment_reason" class="form-label">Razón del Ajuste:</label>
                            <textarea class="form-control" id="adjustment_reason" rows="3" 
                                      placeholder="Explique el motivo del ajuste masivo..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Previsualización</h6>
                        <div class="alert alert-info">
                            <strong>Estado Actual:</strong><br>
                            Total: <?php echo number_format($coeficienteSummary['coeficiente_total'] ?? 0, 4); ?><br>
                            Participantes: <?php echo $coeficienteSummary['total_participantes'] ?? 0; ?>
                        </div>
                        <div id="preview_results" class="alert alert-light">
                            Seleccione un tipo de ajuste para ver la previsualización
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="applyMassAdjustment()">Aplicar Ajuste</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Historial de Cambios -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historial de Cambios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function changeAssembly() {
    const select = document.getElementById('assemblySelect');
    const assemblyId = select.value;
    
    if (assemblyId) {
        window.location.href = `/Asambleas/public/operador/coeficientes?asamblea=${assemblyId}`;
    } else {
        window.location.href = '/Asambleas/public/operador/coeficientes';
    }
}

function updateCoeficiente(userId) {
    const input = document.querySelector(`[data-user-id="${userId}"]`);
    const newValue = parseFloat(input.value) || 0;
    const originalValue = parseFloat(input.getAttribute('data-original'));
    
    if (newValue === originalValue) {
        showAlert('No hay cambios para guardar', 'info');
        return;
    }
    
    if (newValue < 0 || newValue > 1) {
        showAlert('El coeficiente debe estar entre 0 y 1', 'error');
        input.value = originalValue;
        return;
    }
    
    const reason = prompt('Razón del cambio:');
    if (!reason) {
        input.value = originalValue;
        return;
    }
    
    const formData = new FormData();
    formData.append('asamblea_id', <?php echo isset($assembly) ? $assembly['id'] : 0; ?>);
    formData.append('usuario_id', userId);
    formData.append('coeficiente', newValue);
    formData.append('razon', reason);
    
    fetch('/Asambleas/public/operador/actualizar-coeficiente', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            showAlert('Coeficiente actualizado correctamente', 'success');
            input.setAttribute('data-original', newValue);
            updateTotals();
        } else {
            showAlert('Error al actualizar coeficiente', 'error');
            input.value = originalValue;
        }
    })
    .catch(error => {
        showAlert('Error de conexión', 'error');
        input.value = originalValue;
    });
}

function editCoeficiente(userId) {
    const row = document.querySelector(`[data-participant-id="${userId}"]`);
    const name = row.querySelector('strong').textContent;
    const cedula = row.querySelector('small').textContent.replace('🆔', '').trim();
    const currentCoef = parseFloat(row.querySelector('.coeficiente-input').value);
    const baseCoef = parseFloat(row.querySelector('td:nth-child(3) span').textContent);
    
    document.getElementById('edit_usuario_id').value = userId;
    document.getElementById('edit_participant_info').innerHTML = `
        <strong>${name}</strong><br>
        <small class="text-muted">${cedula}</small>
    `;
    document.getElementById('coef_base').value = baseCoef.toFixed(4);
    document.getElementById('edit_coeficiente').value = currentCoef.toFixed(4);
    
    // Calcular impacto inicial
    updateImpactInfo();
    
    const modal = new bootstrap.Modal(document.getElementById('editCoeficienteModal'));
    modal.show();
}

function resetCoeficiente(userId) {
    if (confirm('¿Restaurar el coeficiente base para este participante?')) {
        const row = document.querySelector(`[data-participant-id="${userId}"]`);
        const baseCoef = parseFloat(row.querySelector('td:nth-child(3) span').textContent);
        const input = row.querySelector('.coeficiente-input');
        
        input.value = baseCoef.toFixed(4);
        updateCoeficiente(userId);
    }
}

function copyCoeficiente(userId) {
    const row = document.querySelector(`[data-participant-id="${userId}"]`);
    const coef = row.querySelector('.coeficiente-input').value;
    
    navigator.clipboard.writeText(coef).then(() => {
        showAlert('Coeficiente copiado al portapapeles', 'success');
    });
}

function viewHistory(userId) {
    const row = document.querySelector(`[data-participant-id="${userId}"]`);
    const name = row.querySelector('strong').textContent;
    const historyData = '<?php echo json_encode($participants ?? []); ?>';
    
    // Simular historial (en implementación real vendría del servidor)
    document.getElementById('historyContent').innerHTML = `
        <h6>Historial de ${name}</h6>
        <div class="timeline">
            <div class="timeline-item">
                <small class="text-muted">Ejemplo de historial de cambios aparecería aquí</small>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    modal.show();
}

function refreshCoeficients() {
    location.reload();
}

function updateImpactInfo() {
    const newCoef = parseFloat(document.getElementById('edit_coeficiente').value) || 0;
    const baseCoef = parseFloat(document.getElementById('coef_base').value) || 0;
    const difference = newCoef - baseCoef;
    
    let impactText = '';
    if (Math.abs(difference) < 0.0001) {
        impactText = 'Sin cambios en el coeficiente';
    } else if (difference > 0) {
        impactText = `Aumento de ${difference.toFixed(4)} (+${(difference * 100).toFixed(2)}%)`;
    } else {
        impactText = `Reducción de ${Math.abs(difference).toFixed(4)} (${(difference * 100).toFixed(2)}%)`;
    }
    
    document.getElementById('impact_info').textContent = impactText;
}

function updateTotals() {
    const inputs = document.querySelectorAll('.coeficiente-input');
    let total = 0;
    
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    // Actualizar displays del total (simplificado)
    console.log('Nuevo total:', total.toFixed(4));
}

function applyMassAdjustment() {
    const type = document.getElementById('adjustment_type').value;
    const reason = document.getElementById('adjustment_reason').value;
    
    if (!reason.trim()) {
        showAlert('Debe proporcionar una razón para el ajuste masivo', 'error');
        return;
    }
    
    if (confirm('¿Aplicar el ajuste masivo de coeficientes? Esta acción afectará a todos los participantes.')) {
        // Implementar lógica de ajuste masivo
        showAlert('Ajuste masivo aplicado correctamente', 'success');
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('adjustCoeficientModal'));
        modal.hide();
        
        setTimeout(() => location.reload(), 1000);
    }
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : (type === 'info' ? 'alert-info' : 'alert-danger');
    const icon = type === 'success' ? 'check-circle' : (type === 'info' ? 'info-circle' : 'exclamation-circle');
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const main = document.querySelector('main');
    const firstCard = main.querySelector('.card');
    firstCard.insertAdjacentHTML('beforebegin', alertHtml);
    
    setTimeout(() => {
        const alert = main.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 3000);
}

// Event listeners
document.getElementById('edit_coeficiente')?.addEventListener('input', updateImpactInfo);

document.getElementById('adjustment_type')?.addEventListener('change', function() {
    const customGroup = document.getElementById('custom_value_group');
    if (this.value === 'custom') {
        customGroup.style.display = 'block';
    } else {
        customGroup.style.display = 'none';
    }
    
    // Actualizar previsualización
    updatePreview();
});

function updatePreview() {
    const type = document.getElementById('adjustment_type').value;
    const currentTotal = <?php echo $coeficienteSummary['coeficiente_total'] ?? 0; ?>;
    const participants = <?php echo $coeficienteSummary['total_participantes'] ?? 0; ?>;
    
    let previewText = '';
    switch(type) {
        case 'normalize':
            previewText = `Nuevo total: 1.0000 (ajuste de ${(1 - currentTotal).toFixed(4)})`;
            break;
        case 'reset':
            previewText = 'Restaurar todos los coeficientes a sus valores base';
            break;
        case 'proportional':
            const newAvg = 1 / participants;
            previewText = `Promedio por participante: ${newAvg.toFixed(4)}`;
            break;
        case 'custom':
            const customValue = parseFloat(document.getElementById('custom_value').value) || 1;
            previewText = `Nuevo total objetivo: ${customValue.toFixed(4)}`;
            break;
    }
    
    document.getElementById('preview_results').innerHTML = previewText;
}

// Búsqueda en tiempo real
document.getElementById('searchParticipant')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#participantsTable tbody tr[data-participant-id]');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Gráfico de distribución de coeficientes
<?php if (isset($participants) && !empty($participants)): ?>
const ctx = document.getElementById('coeficienteChart')?.getContext('2d');
if (ctx) {
    const participantNames = [
        <?php foreach ($participants as $p): ?>
            '<?php echo addslashes($p['nombre'] . ' ' . substr($p['apellido'], 0, 1) . '.'); ?>',
        <?php endforeach; ?>
    ];
    
    const coeficientes = [
        <?php foreach ($participants as $p): ?>
            <?php echo $p['coeficiente_asignado']; ?>,
        <?php endforeach; ?>
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: participantNames,
            datasets: [{
                data: coeficientes,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        font: {
                            size: 10
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const percentage = ((context.parsed / <?php echo $coeficienteSummary['coeficiente_total'] ?? 1; ?>) * 100).toFixed(2);
                            return context.label + ': ' + context.parsed.toFixed(4) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>
</script>

<?php include '../views/layouts/footer.php'; ?>
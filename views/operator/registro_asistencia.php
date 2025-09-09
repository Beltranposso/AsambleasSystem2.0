<?php $title = 'Registro de Asistencia'; $userRole = 'operador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-clipboard-check me-2"></i>Registro de Asistencia</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <?php if (isset($assembly)): ?>
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickRegistrationModal">
                            <i class="fas fa-user-plus me-2"></i>Registro Rápido
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshAttendance()">
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

        <?php if (isset($assembly)): ?>
            <!-- Estadísticas de Registro -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo count($participants ?? []); ?></h4>
                            <small class="text-muted">Total Registrados</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h4 class="text-success">
                                <?php 
                                $presentes = 0;
                                if (isset($participants)) {
                                    foreach ($participants as $p) {
                                        if ($p['asistencia'] == 1) $presentes++;
                                    }
                                }
                                echo $presentes;
                                ?>
                            </h4>
                            <small class="text-muted">Presentes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h4 class="text-info">
                                <?php 
                                $total = count($participants ?? []);
                                echo $total > 0 ? round(($presentes / $total) * 100, 1) . '%' : '0%';
                                ?>
                            </h4>
                            <small class="text-muted">% Asistencia</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registro Rápido por Búsqueda -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6><i class="fas fa-search me-2"></i>Búsqueda Rápida de Participantes</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchParticipant" 
                                       placeholder="Buscar por nombre, cédula o apartamento...">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="filterStatus">
                                <option value="">Todos los estados</option>
                                <option value="presente">Solo presentes</option>
                                <option value="ausente">Solo ausentes</option>
                                <option value="al_dia">Al día en pagos</option>
                                <option value="mora">En mora</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Participantes -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Lista de Participantes</h6>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="exportList()">
                                <i class="fas fa-download me-1"></i>Exportar
                            </button>
                            <button class="btn btn-outline-secondary" onclick="printList()">
                                <i class="fas fa-print me-1"></i>Imprimir
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
                                    <th>Coeficiente</th>
                                    <th>Estado Pagos</th>
                                    <th>Estado Asistencia</th>
                                    <th>Hora Entrada</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($participants) && !empty($participants)): ?>
                                    <?php foreach ($participants as $participant): ?>
                                        <tr data-participant-id="<?php echo $participant['usuario_id']; ?>" 
                                            data-search="<?php echo strtolower($participant['nombre'] . ' ' . $participant['apellido'] . ' ' . $participant['cedula'] . ' ' . ($participant['apartamento'] ?? '')); ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <?php if ($participant['asistencia'] == 1): ?>
                                                            <div class="bg-success rounded-circle" style="width: 12px; height: 12px;"></div>
                                                        <?php else: ?>
                                                            <div class="bg-secondary rounded-circle" style="width: 12px; height: 12px;"></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($participant['nombre'] . ' ' . $participant['apellido']); ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($participant['cedula']); ?>
                                                            <?php if ($participant['telefono']): ?>
                                                                <br><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($participant['telefono']); ?>
                                                            <?php endif; ?>
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
                                                <strong><?php echo number_format($participant['coeficiente_asignado'], 4); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Base: <?php echo number_format($participant['coef_propietario'] ?? 0, 4); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php
                                                $pagoClass = '';
                                                switch($participant['estado_pagos'] ?? 'al_dia') {
                                                    case 'al_dia':
                                                        $pagoClass = 'bg-success';
                                                        $pagoText = 'Al día';
                                                        break;
                                                    case 'mora':
                                                        $pagoClass = 'bg-warning';
                                                        $pagoText = 'En mora';
                                                        break;
                                                    case 'suspendido':
                                                        $pagoClass = 'bg-danger';
                                                        $pagoText = 'Suspendido';
                                                        break;
                                                    default:
                                                        $pagoClass = 'bg-secondary';
                                                        $pagoText = 'N/A';
                                                }
                                                ?>
                                                <span class="badge <?php echo $pagoClass; ?>"><?php echo $pagoText; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($participant['asistencia'] == 1): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Presente
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-times me-1"></i>Ausente
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($participant['hora_ingreso']): ?>
                                                    <strong><?php echo date('H:i', strtotime($participant['hora_ingreso'])); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo date('d/m', strtotime($participant['hora_ingreso'])); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($participant['asistencia'] == 0): ?>
                                                        <button class="btn btn-success btn-sm" 
                                                                onclick="registerEntry(<?php echo $participant['usuario_id']; ?>)"
                                                                title="Registrar entrada">
                                                            <i class="fas fa-sign-in-alt"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-warning btn-sm" 
                                                                onclick="registerExit(<?php echo $participant['usuario_id']; ?>)"
                                                                title="Registrar salida">
                                                            <i class="fas fa-sign-out-alt"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="editParticipant(<?php echo $participant['usuario_id']; ?>)"
                                                            title="Editar datos">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users-slash fa-3x mb-3"></i>
                                                <p>No hay participantes registrados en esta asamblea</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Log de Asistencia en Tiempo Real -->
            <?php if (isset($attendanceLog) && !empty($attendanceLog)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h6><i class="fas fa-history me-2"></i>Registro de Entradas (Tiempo Real)</h6>
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <div class="timeline">
                            <?php foreach (array_slice($attendanceLog, 0, 10) as $log): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($log['nombre'] . ' ' . $log['apellido']); ?></h6>
                                        <p class="mb-1">
                                            <small class="text-muted">
                                                <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($log['cedula']); ?>
                                                <i class="fas fa-calculator ms-2 me-1"></i>Coef: <?php echo number_format($log['coeficiente_asignado'], 4); ?>
                                            </small>
                                        </p>
                                        <small class="text-success">
                                            <i class="fas fa-clock me-1"></i>
                                            Entrada: <?php echo date('H:i:s', strtotime($log['hora_ingreso'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h5>Selecciona una Asamblea</h5>
                    <p class="text-muted">Selecciona una asamblea activa para registrar la asistencia de los participantes.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Modal Registro Rápido -->
<?php if (isset($assembly)): ?>
<div class="modal fade" id="quickRegistrationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registro Rápido de Entrada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickRegistrationForm" method="POST" action="/Asambleas/public/operador/registrar-entrada">
                <div class="modal-body">
                    <input type="hidden" name="asamblea_id" value="<?php echo $assembly['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="search_participant" class="form-label">Buscar Participante:</label>
                        <input type="text" class="form-control" id="search_participant" 
                               placeholder="Escriba nombre, cédula o apartamento...">
                        <div id="search_results" class="list-group mt-2" style="display: none;"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="usuario_id" class="form-label">Participante Seleccionado:</label>
                        <input type="hidden" name="usuario_id" id="usuario_id">
                        <div id="selected_participant" class="alert alert-info" style="display: none;"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="coeficiente" class="form-label">Coeficiente:</label>
                        <input type="number" class="form-control" name="coeficiente" id="coeficiente" 
                               step="0.0001" min="0" max="1" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="registerButton" disabled>
                        <i class="fas fa-sign-in-alt me-2"></i>Registrar Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Editar Participante -->
<div class="modal fade" id="editParticipantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Datos del Participante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editParticipantForm" method="POST" action="/Asambleas/public/operador/actualizar-coeficiente">
                <div class="modal-body">
                    <input type="hidden" name="asamblea_id" value="<?php echo isset($assembly) ? $assembly['id'] : ''; ?>">
                    <input type="hidden" name="usuario_id" id="edit_usuario_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Participante:</label>
                        <div id="edit_participant_info" class="alert alert-light"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_coeficiente" class="form-label">Nuevo Coeficiente:</label>
                        <input type="number" class="form-control" name="coeficiente" id="edit_coeficiente" 
                               step="0.0001" min="0" max="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="razon" class="form-label">Razón del Cambio:</label>
                        <textarea class="form-control" name="razon" id="razon" rows="3" required 
                                  placeholder="Explique el motivo del cambio de coeficiente..."></textarea>
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

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.timeline-item:last-child {
    border-bottom: none;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
}

.timeline-content p {
    margin-bottom: 5px;
    font-size: 12px;
}
</style>

<script>
function changeAssembly() {
    const select = document.getElementById('assemblySelect');
    const assemblyId = select.value;
    
    if (assemblyId) {
        window.location.href = `/Asambleas/public/operador/registro-asistencia?asamblea=${assemblyId}`;
    } else {
        window.location.href = '/Asambleas/public/operador/registro-asistencia';
    }
}

function registerEntry(userId) {
    if (confirm('¿Confirmar entrada de este participante?')) {
        const formData = new FormData();
        formData.append('asamblea_id', <?php echo isset($assembly) ? $assembly['id'] : 0; ?>);
        formData.append('usuario_id', userId);
        
        // Obtener coeficiente de la tabla
        const row = document.querySelector(`[data-participant-id="${userId}"]`);
        const coeficiente = row.querySelector('td:nth-child(3) strong').textContent;
        formData.append('coeficiente', coeficiente);
        
        fetch('/Asambleas/public/operador/registrar-entrada', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                showAlert('Error al registrar entrada', 'error');
            }
        })
        .catch(error => {
            showAlert('Error de conexión', 'error');
        });
    }
}

function registerExit(userId) {
    if (confirm('¿Confirmar salida de este participante?')) {
        const formData = new FormData();
        formData.append('asamblea_id', <?php echo isset($assembly) ? $assembly['id'] : 0; ?>);
        formData.append('usuario_id', userId);
        formData.append('action', 'exit');
        
        fetch('/Asambleas/public/operador/registrar-salida', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                showAlert('Error al registrar salida', 'error');
            }
        })
        .catch(error => {
            showAlert('Error de conexión', 'error');
        });
    }
}

function editParticipant(userId) {
    // Obtener datos del participante
    const row = document.querySelector(`[data-participant-id="${userId}"]`);
    const name = row.querySelector('strong').textContent;
    const cedula = row.querySelector('small').textContent.replace('🆔', '').trim();
    const currentCoef = row.querySelector('td:nth-child(3) strong').textContent;
    
    // Llenar modal
    document.getElementById('edit_usuario_id').value = userId;
    document.getElementById('edit_participant_info').innerHTML = `
        <strong>${name}</strong><br>
        <small class="text-muted">${cedula}</small>
    `;
    document.getElementById('edit_coeficiente').value = currentCoef;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('editParticipantModal'));
    modal.show();
}

function refreshAttendance() {
    location.reload();
}

function clearSearch() {
    document.getElementById('searchParticipant').value = '';
    filterParticipants();
}

function exportList() {
    const assemblyId = <?php echo isset($assembly) ? $assembly['id'] : 0; ?>;
    window.open(`/Asambleas/public/operador/export-attendance/${assemblyId}`, '_blank');
}

function printList() {
    window.print();
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
    
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

// Filtros en tiempo real
document.getElementById('searchParticipant')?.addEventListener('input', filterParticipants);
document.getElementById('filterStatus')?.addEventListener('change', filterParticipants);

function filterParticipants() {
    const searchTerm = document.getElementById('searchParticipant').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('#participantsTable tbody tr[data-participant-id]');
    
    rows.forEach(row => {
        const searchData = row.getAttribute('data-search');
        const statusBadges = row.querySelectorAll('.badge');
        
        let matchesSearch = searchTerm === '' || searchData.includes(searchTerm);
        let matchesStatus = statusFilter === '';
        
        if (statusFilter && !matchesStatus) {
            statusBadges.forEach(badge => {
                const badgeText = badge.textContent.toLowerCase();
                if (
                    (statusFilter === 'presente' && badgeText.includes('presente')) ||
                    (statusFilter === 'ausente' && badgeText.includes('ausente')) ||
                    (statusFilter === 'al_dia' && badgeText.includes('al día')) ||
                    (statusFilter === 'mora' && badgeText.includes('mora'))
                ) {
                    matchesStatus = true;
                }
            });
        }
        
        if (matchesSearch && (statusFilter === '' || matchesStatus)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Búsqueda rápida en modal
document.getElementById('search_participant')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const resultsDiv = document.getElementById('search_results');
    
    if (searchTerm.length < 2) {
        resultsDiv.style.display = 'none';
        return;
    }
    
    // Simular búsqueda (en implementación real sería AJAX)
    const participants = <?php echo json_encode($participants ?? []); ?>;
    const results = participants.filter(p => 
        p.nombre.toLowerCase().includes(searchTerm) ||
        p.apellido.toLowerCase().includes(searchTerm) ||
        p.cedula.includes(searchTerm) ||
        (p.apartamento && p.apartamento.includes(searchTerm))
    ).slice(0, 5);
    
    if (results.length > 0) {
        resultsDiv.innerHTML = results.map(p => `
            <button type="button" class="list-group-item list-group-item-action" 
                    onclick="selectParticipant('${p.usuario_id}', '${p.nombre} ${p.apellido}', '${p.cedula}', '${p.coeficiente_asignado}')">
                <strong>${p.nombre} ${p.apellido}</strong><br>
                <small class="text-muted">${p.cedula} - Apt: ${p.apartamento || 'N/A'}</small>
            </button>
        `).join('');
        resultsDiv.style.display = 'block';
    } else {
        resultsDiv.style.display = 'none';
    }
});

function selectParticipant(userId, name, cedula, coeficiente) {
    document.getElementById('usuario_id').value = userId;
    document.getElementById('coeficiente').value = coeficiente;
    document.getElementById('selected_participant').innerHTML = `
        <strong>${name}</strong><br>
        <small class="text-muted">${cedula}</small>
    `;
    document.getElementById('selected_participant').style.display = 'block';
    document.getElementById('registerButton').disabled = false;
    document.getElementById('search_results').style.display = 'none';
}

// Auto-refresh cada 30 segundos para asambleas activas
<?php if (isset($assembly) && $assembly['estado'] === 'activa'): ?>
setInterval(function() {
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
<?php endif; ?>
</script>

<?php include '../views/layouts/footer.php'; ?>
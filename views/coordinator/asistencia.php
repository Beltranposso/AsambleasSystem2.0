<?php $title = 'Control de Asistencia'; $userRole = 'coordinador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-user-check me-2"></i>Control de Asistencia</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <?php if (isset($assembly)): ?>
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-success" onclick="markAllPresent()">
                            <i class="fas fa-check-double me-2"></i>Marcar Todos Presentes
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshAttendance()">
                            <i class="fas fa-sync-alt me-2"></i>Actualizar
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mensajes de estado -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

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
            <!-- Estadísticas de Asistencia -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h4 class="text-primary" id="total-registrados"><?php echo count($participants ?? []); ?></h4>
                            <small class="text-muted">Total Registrados</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h4 class="text-success" id="total-presentes">
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
                <div class="col-md-3">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h4 class="text-warning" id="total-ausentes"><?php echo count($participants ?? []) - $presentes; ?></h4>
                            <small class="text-muted">Ausentes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h4 class="text-info" id="porcentaje-asistencia">
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

            <!-- Control de Asistencia -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-list-check me-2"></i>Lista de Participantes</h6>
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
                                    <th>Coeficiente</th>
                                    <th>Estado Pagos</th>
                                    <th>Asistencia</th>
                                    <th>Hora Ingreso</th>
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
                                                        <div class="status-indicator <?php echo $participant['asistencia'] == 1 ? 'bg-success' : 'bg-secondary'; ?> rounded-circle" 
                                                             style="width: 12px; height: 12px;"></div>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($participant['nombre'] . ' ' . $participant['apellido']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($participant['cedula']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo htmlspecialchars($participant['apartamento'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm coeficiente-input" 
                                                       value="<?php echo $participant['coeficiente_asignado']; ?>"
                                                       step="0.0001" 
                                                       min="0" 
                                                       max="1"
                                                       data-user-id="<?php echo $participant['usuario_id']; ?>"
                                                       style="width: 100px;">
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
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input attendance-switch" 
                                                           type="checkbox" 
                                                           <?php echo $participant['asistencia'] == 1 ? 'checked' : ''; ?>
                                                           data-user-id="<?php echo $participant['usuario_id']; ?>"
                                                           id="attendance_<?php echo $participant['usuario_id']; ?>">
                                                    <label class="form-check-label attendance-label" for="attendance_<?php echo $participant['usuario_id']; ?>">
                                                        <?php echo $participant['asistencia'] == 1 ? 'Presente' : 'Ausente'; ?>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted hora-ingreso">
                                                    <?php 
                                                    if ($participant['hora_ingreso']) {
                                                        echo date('H:i', strtotime($participant['hora_ingreso']));
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary btn-sm save-btn" 
                                                            onclick="saveAttendance(<?php echo $participant['usuario_id']; ?>)"
                                                            title="Guardar cambios">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                    <button class="btn btn-outline-<?php echo $participant['asistencia'] == 1 ? 'warning' : 'success'; ?> btn-sm toggle-btn" 
                                                            onclick="toggleAttendance(<?php echo $participant['usuario_id']; ?>)"
                                                            title="<?php echo $participant['asistencia'] == 1 ? 'Marcar como ausente' : 'Marcar como presente'; ?>">
                                                        <i class="fas fa-<?php echo $participant['asistencia'] == 1 ? 'user-times' : 'user-check'; ?>"></i>
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

            <!-- Acciones rápidas -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <button class="btn btn-success w-100" onclick="markAllPresent()">
                                <i class="fas fa-check-double me-2"></i>Marcar Todos Presentes
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-warning w-100" onclick="markAllAbsent()">
                                <i class="fas fa-user-times me-2"></i>Marcar Todos Ausentes
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100" onclick="saveAllChanges()">
                                <i class="fas fa-save me-2"></i>Guardar Todos los Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h5>Selecciona una Asamblea</h5>
                    <p class="text-muted">Selecciona una asamblea activa para gestionar la asistencia de los participantes.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
// Variables globales
const assemblyId = <?php echo isset($assembly) ? $assembly['id'] : 0; ?>;

function changeAssembly() {
    const select = document.getElementById('assemblySelect');
    const assemblyId = select.value;
    
    if (assemblyId) {
        window.location.href = `/Asambleas/public/coordinador/asistencia?asamblea=${assemblyId}`;
    } else {
        window.location.href = '/Asambleas/public/coordinador/asistencia';
    }
}

// Función principal para guardar asistencia
async function saveAttendance(userId) {
    console.log('🔄 Iniciando saveAttendance para usuario:', userId);
    
    const row = document.querySelector(`[data-participant-id="${userId}"]`);
    if (!row) {
        console.error('❌ No se encontró la fila del participante:', userId);
        showAlert('Error: No se encontró el participante', 'error');
        return;
    }
    
    const attendanceSwitch = row.querySelector('.attendance-switch');
    const coeficienteInput = row.querySelector('.coeficiente-input');
    const saveBtn = row.querySelector('.save-btn');
    
    if (!attendanceSwitch || !coeficienteInput || !saveBtn) {
        console.error('❌ No se encontraron elementos necesarios en la fila');
        showAlert('Error: Elementos de la interfaz no encontrados', 'error');
        return;
    }
    
    // Verificar que tenemos assemblyId
    if (!assemblyId || assemblyId <= 0) {
        console.error('❌ assemblyId no válido:', assemblyId);
        showAlert('Error: ID de asamblea no válido', 'error');
        return;
    }
    
    // Mostrar loading en el botón
    const originalContent = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    saveBtn.disabled = true;
    
    const data = {
        asamblea_id: assemblyId,
        usuario_id: userId,
        asistencia: attendanceSwitch.checked ? 1 : 0,
        coeficiente: parseFloat(coeficienteInput.value) || 0
    };
    
    console.log('📤 Enviando datos:', data);
    
    try {
        const response = await fetch('/Asambleas/public/coordinador/registrar-asistencia', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(data)
        });
        
        console.log('📥 Respuesta recibida. Status:', response.status);
        
        // Obtener el texto de la respuesta primero
        const responseText = await response.text();
        console.log('📄 Texto de respuesta:', responseText);
        
        // Intentar parsear como JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('❌ Error parseando JSON:', jsonError);
            console.error('❌ Respuesta del servidor:', responseText);
            throw new Error('El servidor devolvió una respuesta inválida');
        }
        
        console.log('📋 Resultado parseado:', result);
        
        if (response.ok && result.success) {
            console.log('✅ Asistencia guardada exitosamente');
            showAlert('Asistencia actualizada correctamente', 'success');
            updateRowDisplay(userId);
            updateStats();
            
            // Si es presente, actualizar hora de ingreso
            if (data.asistencia === 1) {
                const horaElement = row.querySelector('.hora-ingreso');
                if (horaElement) {
                    horaElement.textContent = new Date().toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        } else {
            // Error del servidor
            const errorMessage = result.error || 'Error desconocido del servidor';
            console.error('❌ Error del servidor:', errorMessage);
            
            if (result.debug_info) {
                console.log('🔍 Info de debug:', result.debug_info);
            }
            
            throw new Error(errorMessage);
        }
    } catch (error) {
        console.error('❌ Error en saveAttendance:', error);
        
        // Mostrar mensaje de error más específico
        let errorMessage = 'Error al actualizar asistencia';
        
        if (error.message.includes('JSON') || error.message.includes('inválida')) {
            errorMessage += ': El servidor devolvió una respuesta inválida';
        } else if (error.message.includes('network') || error.message.includes('fetch')) {
            errorMessage += ': Error de conexión';
        } else {
            errorMessage += ': ' + error.message;
        }
        
        showAlert(errorMessage, 'error');
        
        // Revertir el estado del switch si falló
        attendanceSwitch.checked = !attendanceSwitch.checked;
        updateRowDisplay(userId);
        
    } finally {
        // Restaurar botón siempre
        saveBtn.innerHTML = originalContent;
        saveBtn.disabled = false;
    }
}


// Función para alternar asistencia rápidamente
async function toggleAttendance(userId) {
    const row = document.querySelector(`[data-participant-id="${userId}"]`);
    const attendanceSwitch = row.querySelector('.attendance-switch');
    
    // Alternar el estado
    attendanceSwitch.checked = !attendanceSwitch.checked;
    
    // Actualizar display inmediatamente
    updateRowDisplay(userId);
    
    // Guardar automáticamente
    await saveAttendance(userId);
}

// Actualizar la visualización de una fila
function updateRowDisplay(userId) {
    const row = document.querySelector(`[data-participant-id="${userId}"]`);
    const attendanceSwitch = row.querySelector('.attendance-switch');
    const label = row.querySelector('.attendance-label');
    const indicator = row.querySelector('.status-indicator');
    const toggleBtn = row.querySelector('.toggle-btn');
    
    if (attendanceSwitch.checked) {
        // Presente
        label.textContent = 'Presente';
        indicator.className = 'status-indicator bg-success rounded-circle';
        toggleBtn.className = 'btn btn-outline-warning btn-sm toggle-btn';
        toggleBtn.title = 'Marcar como ausente';
        toggleBtn.innerHTML = '<i class="fas fa-user-times"></i>';
    } else {
        // Ausente
        label.textContent = 'Ausente';
        indicator.className = 'status-indicator bg-secondary rounded-circle';
        toggleBtn.className = 'btn btn-outline-success btn-sm toggle-btn';
        toggleBtn.title = 'Marcar como presente';
        toggleBtn.innerHTML = '<i class="fas fa-user-check"></i>';
    }
}

// Marcar presente
async function markPresent(userId) {
    const row = document.querySelector(`[data-participant-id="${userId}"]`);
    const attendanceSwitch = row.querySelector('.attendance-switch');
    
    attendanceSwitch.checked = true;
    updateRowDisplay(userId);
    await saveAttendance(userId);
}

// Marcar ausente
async function markAbsent(userId) {
    const row = document.querySelector(`[data-participant-id="${userId}"]`);
    const attendanceSwitch = row.querySelector('.attendance-switch');
    
    attendanceSwitch.checked = false;
    updateRowDisplay(userId);
    await saveAttendance(userId);
}

// Marcar todos como presentes
async function markAllPresent() {
    if (!confirm('¿Marcar a todos los participantes como presentes?')) {
        return;
    }
    
    const switches = document.querySelectorAll('.attendance-switch');
    const promises = [];
    
    switches.forEach(switchEl => {
        const userId = switchEl.getAttribute('data-user-id');
        switchEl.checked = true;
        updateRowDisplay(userId);
        promises.push(saveAttendance(userId));
    });
    
    try {
        await Promise.all(promises);
        showAlert('Todos los participantes marcados como presentes', 'success');
    } catch (error) {
        showAlert('Error al actualizar algunas asistencias', 'error');
    }
}

// Marcar todos como ausentes
async function markAllAbsent() {
    if (!confirm('¿Marcar a todos los participantes como ausentes?')) {
        return;
    }
    
    const switches = document.querySelectorAll('.attendance-switch');
    const promises = [];
    
    switches.forEach(switchEl => {
        const userId = switchEl.getAttribute('data-user-id');
        switchEl.checked = false;
        updateRowDisplay(userId);
        promises.push(saveAttendance(userId));
    });
    
    try {
        await Promise.all(promises);
        showAlert('Todos los participantes marcados como ausentes', 'success');
    } catch (error) {
        showAlert('Error al actualizar algunas asistencias', 'error');
    }
}

// Guardar todos los cambios
async function saveAllChanges() {
    const rows = document.querySelectorAll('[data-participant-id]');
    const promises = [];
    
    rows.forEach(row => {
        const userId = row.getAttribute('data-participant-id');
        promises.push(saveAttendance(userId));
    });
    
    try {
        await Promise.all(promises);
        showAlert(`Se guardaron ${rows.length} registros de asistencia`, 'success');
    } catch (error) {
        showAlert('Error al guardar algunos registros', 'error');
    }
}

// Actualizar estadísticas
function updateStats() {
    const switches = document.querySelectorAll('.attendance-switch');
    let presentes = 0;
    
    switches.forEach(switchEl => {
        if (switchEl.checked) presentes++;
    });
    
    const total = switches.length;
    const ausentes = total - presentes;
    const percentage = total > 0 ? ((presentes / total) * 100).toFixed(1) : 0;
    
    // Actualizar las cards de estadísticas
    document.getElementById('total-presentes').textContent = presentes;
    document.getElementById('total-ausentes').textContent = ausentes;
    document.getElementById('porcentaje-asistencia').textContent = percentage + '%';
}

// Refrescar página
function refreshAttendance() {
    location.reload();
}

// Mostrar alertas
function showAlert(message, type = 'info', duration = 5000) {
    console.log(`🔔 Mostrando alerta: [${type}] ${message}`);
    
    // Crear contenedor de alertas si no existe
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(alertContainer);
    }
    
    const alertClass = type === 'success' ? 'alert-success' : 
                     type === 'error' ? 'alert-danger' : 
                     type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const icon = type === 'success' ? 'check-circle' : 
                type === 'error' ? 'exclamation-circle' : 
                type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    // Crear elemento de alerta
    const alertElement = document.createElement('div');
    alertElement.className = `alert ${alertClass} shadow`;
    alertElement.style.cssText = `
        margin-bottom: 10px;
        border-radius: 8px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        animation: slideInRight 0.3s ease-out;
    `;
    
    alertElement.innerHTML = `
        <div>
            <i class="fas fa-${icon} me-2"></i>${message}
        </div>
        <button type="button" class="btn-close" onclick="this.parentElement.remove()" style="
            background: none;
            border: none;
            font-size: 1.2em;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
        ">&times;</button>
    `;
    
    // Agregar alerta al contenedor
    alertContainer.appendChild(alertElement);
    
    // Auto-hide después del tiempo especificado
    setTimeout(() => {
        if (alertElement && alertElement.parentNode) {
            alertElement.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                if (alertElement && alertElement.parentNode) {
                    alertElement.remove();
                }
            }, 300);
        }
    }, duration);
}

// Agregar estilos CSS para las animaciones
if (!document.getElementById('alert-animations')) {
    const style = document.createElement('style');
    style.id = 'alert-animations';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}


// Búsqueda en tiempo real
document.getElementById('searchParticipant')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#participantsTable tbody tr[data-participant-id]');
    
    rows.forEach(row => {
        const name = row.querySelector('strong').textContent.toLowerCase();
        const cedula = row.querySelector('small').textContent.toLowerCase();
        const apartamento = row.querySelector('.badge').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || cedula.includes(searchTerm) || apartamento.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Event listeners para switches de asistencia
document.querySelectorAll('.attendance-switch').forEach(switchEl => {
    switchEl.addEventListener('change', function() {
        const userId = this.getAttribute('data-user-id');
        updateRowDisplay(userId);
        
        // Auto-save con delay
        clearTimeout(this.saveTimeout);
        this.saveTimeout = setTimeout(() => {
            saveAttendance(userId);
        }, 800);
    });
});

// Event listeners para coeficientes
document.querySelectorAll('.coeficiente-input').forEach(input => {
    input.addEventListener('blur', function() {
        const userId = this.getAttribute('data-user-id');
        saveAttendance(userId);
    });
    
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const userId = this.getAttribute('data-user-id');
            saveAttendance(userId);
        }
    });
});

// Auto-refresh cada 60 segundos si hay asamblea activa
<?php if (isset($assembly) && $assembly['estado'] === 'activa'): ?>
setInterval(() => {
    updateStats();
}, 60000);
<?php endif; ?>

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('Control de Asistencia inicializado');
    console.log('Assembly ID:', assemblyId);
    
    // Actualizar estadísticas al cargar
    updateStats();
});
</script>

<style>
.status-indicator {
    width: 12px;
    height: 12px;
    transition: background-color 0.3s ease;
}

.attendance-switch {
    transform: scale(1.1);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.form-control-sm {
    font-size: 0.875rem;
}

.fixed-alert {
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.card-header {
    background-color: rgba(0, 123, 255, 0.05);
    border-bottom: 1px solid rgba(0, 123, 255, 0.1);
}

.badge {
    font-size: 0.75em;
}
</style>

<?php include '../views/layouts/footer.php'; ?>
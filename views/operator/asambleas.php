<?php $title = 'Mis Asambleas Asignadas'; $userRole = 'operador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-calendar-alt me-2"></i>Mis Asambleas Asignadas</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-2"></i>Filtrar
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('all')">Todas</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('activa')">Activas</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('programada')">Programadas</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('finalizada')">Finalizadas</a></li>
                    </ul>
                </div>
                <button type="button" class="btn btn-primary" onclick="refreshAssemblies()">
                    <i class="fas fa-sync-alt me-2"></i>Actualizar
                </button>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-primary"><?php echo count($assignedAssemblies ?? []); ?></h4>
                        <small class="text-muted">Total Asignadas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-success">
                            <?php 
                            $active = 0;
                            if (isset($assignedAssemblies)) {
                                foreach ($assignedAssemblies as $a) {
                                    if ($a['estado'] === 'activa') $active++;
                                }
                            }
                            echo $active;
                            ?>
                        </h4>
                        <small class="text-muted">Activas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-warning">
                            <?php 
                            $programmed = 0;
                            if (isset($assignedAssemblies)) {
                                foreach ($assignedAssemblies as $a) {
                                    if ($a['estado'] === 'programada') $programmed++;
                                }
                            }
                            echo $programmed;
                            ?>
                        </h4>
                        <small class="text-muted">Programadas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-info">
                            <?php 
                            $totalParticipants = 0;
                            if (isset($assignedAssemblies)) {
                                foreach ($assignedAssemblies as $a) {
                                    $totalParticipants += $a['total_participantes'] ?? 0;
                                }
                            }
                            echo $totalParticipants;
                            ?>
                        </h4>
                        <small class="text-muted">Total Participantes</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de asambleas -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Asambleas Asignadas</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="assembliesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Asamblea</th>
                                <th>Conjunto</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Participantes</th>
                                <th>Asistencia</th>
                                <th>Mi Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($assignedAssemblies) && !empty($assignedAssemblies)): ?>
                                <?php foreach ($assignedAssemblies as $assembly): ?>
                                    <tr data-status="<?php echo $assembly['estado']; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($assembly['titulo']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($assembly['tipo_asamblea'] ?? 'General'); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($assembly['conjunto_nombre'] ?? ''); ?></td>
                                        <td>
                                            <?php 
                                            $fecha = date('d/m/Y H:i', strtotime($assembly['fecha_inicio']));
                                            echo $fecha;
                                            ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php 
                                                $now = time();
                                                $start = strtotime($assembly['fecha_inicio']);
                                                if ($start > $now) {
                                                    echo '<i class="fas fa-clock"></i> Próxima';
                                                } elseif ($assembly['estado'] === 'activa') {
                                                    echo '<i class="fas fa-play-circle text-success"></i> En curso';
                                                } else {
                                                    echo '<i class="fas fa-check-circle"></i> Finalizada';
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            $statusText = ucfirst($assembly['estado']);
                                            switch($assembly['estado']) {
                                                case 'activa':
                                                    $statusClass = 'bg-success';
                                                    break;
                                                case 'programada':
                                                    $statusClass = 'bg-warning';
                                                    break;
                                                case 'finalizada':
                                                    $statusClass = 'bg-secondary';
                                                    break;
                                                case 'cancelada':
                                                    $statusClass = 'bg-danger';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-primary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $assembly['total_participantes'] ?? 0; ?></span>
                                            <small class="text-muted">registrados</small>
                                        </td>
                                        <td>
                                            <?php 
                                            $total = $assembly['total_participantes'] ?? 0;
                                            $asistentes = $assembly['total_asistentes'] ?? 0;
                                            $percentage = $total > 0 ? round(($asistentes / $total) * 100, 1) : 0;
                                            $progressClass = $percentage >= 75 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                                            ?>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar <?php echo $progressClass; ?>" 
                                                     style="width: <?php echo $percentage; ?>%">
                                                    <?php echo $asistentes . '/' . $total . ' (' . $percentage . '%)'; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="fas fa-clipboard-check me-1"></i>Operador
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($assembly['estado'] === 'activa'): ?>
                                                    <a href="/operator/attendance?assembly_id=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-primary btn-sm" title="Registro de Asistencia">
                                                        <i class="fas fa-clipboard-check"></i>
                                                    </a>
                                                    <a href="/operator/verify-users?assembly_id=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-warning btn-sm" title="Verificar Usuarios">
                                                        <i class="fas fa-user-shield"></i>
                                                    </a>
                                                    <a href="/operator/coefficients?assembly_id=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-info btn-sm" title="Gestión de Coeficientes">
                                                        <i class="fas fa-calculator"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                            data-bs-toggle="dropdown" title="Más opciones">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="/operator/reports/attendance?assembly_id=<?php echo $assembly['id']; ?>">
                                                            <i class="fas fa-chart-bar me-2"></i>Ver Reporte
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="viewAssemblyDetails(<?php echo $assembly['id']; ?>)">
                                                            <i class="fas fa-info-circle me-2"></i>Ver Detalles
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="/operator/export-attendance?assembly_id=<?php echo $assembly['id']; ?>">
                                                            <i class="fas fa-download me-2"></i>Exportar Lista
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="/operator/print-attendance?assembly_id=<?php echo $assembly['id']; ?>" target="_blank">
                                                            <i class="fas fa-print me-2"></i>Imprimir Lista
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                            <p>No tienes asambleas asignadas</p>
                                            <small>Las asambleas asignadas aparecerán aquí cuando un coordinador te las asigne</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Asambleas activas destacadas -->
        <?php if (isset($activeAssemblies) && !empty($activeAssemblies)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-play-circle me-2"></i>Asambleas Activas - Acción Requerida</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($activeAssemblies as $active): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-left-success">
                                            <div class="card-body">
                                                <h6><?php echo htmlspecialchars($active['titulo']); ?></h6>
                                                <p class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($active['conjunto_nombre']); ?>
                                                        <br>
                                                        <i class="fas fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($active['fecha_inicio'])); ?>
                                                    </small>
                                                </p>
                                                <div class="btn-group btn-group-sm w-100">
                                                    <a href="/operator/attendance?assembly_id=<?php echo $active['id']; ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-clipboard-check me-1"></i>Asistencia
                                                    </a>
                                                    <a href="/operator/verify-users?assembly_id=<?php echo $active['id']; ?>" 
                                                       class="btn btn-warning btn-sm">
                                                        <i class="fas fa-user-shield me-1"></i>Verificar
                                                    </a>
                                                    <a href="/operator/coefficients?assembly_id=<?php echo $active['id']; ?>" 
                                                       class="btn btn-info btn-sm">
                                                        <i class="fas fa-calculator me-1"></i>Coeficientes
                                                    </a>
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

        <!-- Información del rol de operador -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle me-2"></i>Responsabilidades del Operador</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-success me-2"></i>Registrar asistencia de participantes</li>
                            <li><i class="fas fa-check text-success me-2"></i>Verificar identidad de usuarios</li>
                            <li><i class="fas fa-check text-success me-2"></i>Validar coeficientes de participación</li>
                            <li><i class="fas fa-check text-success me-2"></i>Controlar estado de pagos</li>
                            <li><i class="fas fa-check text-success me-2"></i>Generar reportes de asistencia</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-pie me-2"></i>Mi Desempeño</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary"><?php echo count($assignedAssemblies ?? []); ?></h4>
                                <small class="text-muted">Asambleas Total</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success"><?php echo $active ?? 0; ?></h4>
                                <small class="text-muted">Actualmente Activas</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-info"><?php echo $totalParticipants ?? 0; ?></h4>
                                <small class="text-muted">Participantes Gestionados</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-warning">
                                    <?php 
                                    $avgAttendance = 0;
                                    if (isset($assignedAssemblies) && !empty($assignedAssemblies)) {
                                        $total = 0;
                                        $count = 0;
                                        foreach ($assignedAssemblies as $a) {
                                            if (($a['total_participantes'] ?? 0) > 0) {
                                                $total += (($a['total_asistentes'] ?? 0) / $a['total_participantes']) * 100;
                                                $count++;
                                            }
                                        }
                                        $avgAttendance = $count > 0 ? round($total / $count, 1) : 0;
                                    }
                                    echo $avgAttendance . '%';
                                    ?>
                                </h4>
                                <small class="text-muted">Asistencia Promedio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal para detalles de asamblea -->
<div class="modal fade" id="assemblyDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Asamblea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="assemblyDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterByStatus(status) {
    const rows = document.querySelectorAll('#assembliesTable tbody tr[data-status]');
    
    rows.forEach(row => {
        if (status === 'all' || row.getAttribute('data-status') === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function refreshAssemblies() {
    location.reload();
}

function viewAssemblyDetails(assemblyId) {
    const modal = new bootstrap.Modal(document.getElementById('assemblyDetailsModal'));
    const content = document.getElementById('assemblyDetailsContent');
    
    // Mostrar spinner
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Hacer petición AJAX
    fetch('/operator/ajax/assembly-details', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            assembly_id: assemblyId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const assembly = data.assembly;
            const stats = data.stats;
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información General</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Título:</strong></td><td>${assembly.titulo}</td></tr>
                            <tr><td><strong>Conjunto:</strong></td><td>${assembly.conjunto_nombre}</td></tr>
                            <tr><td><strong>Fecha:</strong></td><td>${new Date(assembly.fecha_inicio).toLocaleDateString('es-ES')}</td></tr>
                            <tr><td><strong>Estado:</strong></td><td><span class="badge bg-${assembly.estado === 'activa' ? 'success' : 'secondary'}">${
                            assembly.estado}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Estadísticas</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Total Registrados:</strong></td><td>${stats.total_registrados}</td></tr>
                            <tr><td><strong>Presentes:</strong></td><td>${stats.total_presentes}</td></tr>
                            <tr><td><strong>Ausentes:</strong></td><td>${stats.total_ausentes}</td></tr>
                            <tr><td><strong>% Asistencia:</strong></td><td>${stats.total_registrados > 0 ? Math.round((stats.total_presentes / stats.total_registrados) * 100) : 0}%</td></tr>
                        </table>
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar los detalles: ${data.error}
                </div>
            `;
        }
    })
    .catch(error => {
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error de conexión: ${error.message}
            </div>
        `;
    });
}

function exportAttendanceList(assemblyId) {
    // Exportación directa
    window.open(`/operator/export-attendance?assembly_id=${assemblyId}`, '_blank');
}

// Auto-refresh cada 30 segundos para asambleas activas
if (document.querySelector('[data-status="activa"]')) {
    setInterval(function() {
        if (!document.querySelector('.modal.show')) {
            location.reload();
        }
    }, 30000);
}
</script>

<?php include '../views/layouts/footer.php'; ?>
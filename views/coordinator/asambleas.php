<?php $title = 'Mis Asambleas'; $userRole = 'coordinador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-calendar-alt me-2"></i>Mis Asambleas</h1>
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
                        <h4 class="text-primary"><?php echo count($assemblies ?? []); ?></h4>
                        <small class="text-muted">Total Asambleas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-success">
                            <?php 
                            $active = 0;
                            if (isset($assemblies)) {
                                foreach ($assemblies as $a) {
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
                            if (isset($assemblies)) {
                                foreach ($assemblies as $a) {
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
                            $avgAttendance = 0;
                            if (isset($assemblies) && !empty($assemblies)) {
                                $total = 0;
                                $count = 0;
                                foreach ($assemblies as $a) {
                                    if ($a['total_participantes'] > 0) {
                                        $total += ($a['total_asistentes'] / $a['total_participantes']) * 100;
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

        <!-- Lista de asambleas -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Asambleas</h6>
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
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($assemblies) && !empty($assemblies)): ?>
                                <?php foreach ($assemblies as $assembly): ?>
                                    <tr data-status="<?php echo $assembly['estado']; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($assembly['titulo']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($assembly['tipo_asamblea'] ?? ''); ?></small>
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
                                            <div class="btn-group btn-group-sm">
                                                <!-- Botón de Vista de Proyección - DESTACADO para asambleas activas -->
                                                <?php if ($assembly['estado'] === 'activa'): ?>
                                                    <a href="/Asambleas/public/coordinador/proyeccion?asamblea=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       title="Vista de Proyección" 
                                                       target="_blank">
                                                        <i class="fas fa-tv"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($assembly['estado'] === 'activa'): ?>
                                                    <a href="/Asambleas/public/coordinador/asistencia?asamblea=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-primary btn-sm" title="Control Asistencia">
                                                        <i class="fas fa-user-check"></i>
                                                    </a>
                                                    <a href="/Asambleas/public/coordinador/quorum?asamblea=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-info btn-sm" title="Ver Quórum">
                                                        <i class="fas fa-percentage"></i>
                                                    </a>
                                                    <a href="/Asambleas/public/coordinador/votaciones?asamblea=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-success btn-sm" title="Votaciones">
                                                        <i class="fas fa-vote-yea"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <!-- Para asambleas no activas, mostrar Vista de Proyección en el menú desplegable -->
                                                    <a href="/Asambleas/public/coordinador/participantes?asamblea=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-outline-primary btn-sm" title="Ver Participantes">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                            data-bs-toggle="dropdown" title="Más opciones">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <!-- Vista de Proyección en el menú para todas las asambleas -->
                                                        <li><a class="dropdown-item" 
                                                               href="/Asambleas/public/coordinador/proyeccion?asamblea=<?php echo $assembly['id']; ?>" 
                                                               target="_blank">
                                                            <i class="fas fa-tv me-2 text-danger"></i>Vista de Proyección
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        
                                                        <li><a class="dropdown-item" href="/Asambleas/public/coordinador/participantes?asamblea=<?php echo $assembly['id']; ?>">
                                                            <i class="fas fa-users me-2"></i>Gestionar Participantes
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="/Asambleas/public/coordinador/votaciones?asamblea=<?php echo $assembly['id']; ?>">
                                                            <i class="fas fa-vote-yea me-2"></i>Gestionar Votaciones
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="/Asambleas/public/coordinador/reportes/participacion?asamblea=<?php echo $assembly['id']; ?>">
                                                            <i class="fas fa-chart-bar me-2"></i>Ver Reporte
                                                        </a></li>
                                                        
                                                        <?php if ($assembly['estado'] === 'programada'): ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-success" href="#" onclick="activateAssembly(<?php echo $assembly['id']; ?>)">
                                                                <i class="fas fa-play me-2"></i>Activar Asamblea
                                                            </a></li>
                                                        <?php endif; ?>
                                                        <?php if ($assembly['estado'] === 'activa'): ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-warning" href="#" onclick="finalizeAssembly(<?php echo $assembly['id']; ?>)">
                                                                <i class="fas fa-stop me-2"></i>Finalizar Asamblea
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
                                            <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                            <p>No tienes asambleas asignadas</p>
                                            <small>Las asambleas aparecerán aquí cuando un administrador te las asigne</small>
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
                                                <div class="btn-group btn-group-sm mb-2">
                                                    <!-- Botón de Vista de Proyección destacado -->
                                                    <a href="/Asambleas/public/coordinador/proyeccion?asamblea=<?php echo $active['id']; ?>" 
                                                       class="btn btn-danger btn-sm" target="_blank">
                                                        <i class="fas fa-tv me-1"></i>Vista Proyección
                                                    </a>
                                                    <a href="/Asambleas/public/coordinador/asistencia?asamblea=<?php echo $active['id']; ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-user-check me-1"></i>Asistencia
                                                    </a>
                                                    <a href="/Asambleas/public/coordinador/quorum?asamblea=<?php echo $active['id']; ?>" 
                                                       class="btn btn-info btn-sm">
                                                        <i class="fas fa-percentage me-1"></i>Quórum
                                                    </a>
                                                    <a href="/Asambleas/public/coordinador/votaciones?asamblea=<?php echo $active['id']; ?>" 
                                                       class="btn btn-success btn-sm">
                                                        <i class="fas fa-vote-yea me-1"></i>Votaciones
                                                    </a>
                                                </div>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Use la Vista de Proyección para mostrar el quórum y votaciones en pantalla grande
                                                    </small>
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
    </main>
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

function activateAssembly(assemblyId) {
    if (confirm('¿Está seguro de activar esta asamblea? Esto permitirá el registro de asistencia y votaciones.')) {
        // Aquí iría la llamada AJAX para activar la asamblea
        fetch(`/Asambleas/public/coordinador/activar-asamblea/${assemblyId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

function finalizeAssembly(assemblyId) {
    if (confirm('¿Está seguro de finalizar esta asamblea? No se podrán realizar más cambios después.')) {
        // Aquí iría la llamada AJAX para finalizar la asamblea
        fetch(`/Asambleas/public/coordinador/finalizar-asamblea/${assemblyId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

// Auto-refresh cada 30 segundos para asambleas activas
if (document.querySelector('[data-status="activa"]')) {
    setInterval(function() {
        location.reload();
    }, 30000);
}

// Resaltar botones de Vista de Proyección
document.addEventListener('DOMContentLoaded', function() {
    // Agregar tooltip explicativo a los botones de proyección
    const projectionBtns = document.querySelectorAll('a[href*="proyeccion"]');
    projectionBtns.forEach(btn => {
        btn.setAttribute('data-bs-toggle', 'tooltip');
        btn.setAttribute('data-bs-placement', 'top');
        btn.setAttribute('title', 'Abrir vista de proyección en pantalla completa para mostrar quórum y votaciones');
    });
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>
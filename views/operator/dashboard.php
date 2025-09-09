<?php $title = 'Dashboard - Operador'; $userRole = 'operador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard - Operador</h1>
        </div>
        
        <!-- Métricas de trabajo -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Asambleas Asignadas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">2</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes Validación</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">5</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Validados Hoy</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Votantes</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">25</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-vote-yea fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tareas principales -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tasks me-2"></i>Asambleas Asignadas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($assignedAssemblies) && !empty($assignedAssemblies)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Asamblea</th>
                                            <th>Conjunto</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignedAssemblies as $assembly): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assembly['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($assembly['conjunto_nombre']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($assembly['fecha_inicio'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $assembly['estado'] === 'activa' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($assembly['estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="/operator/attendance?assembly_id=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-check me-1"></i>Validar
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                <p>No tienes asambleas asignadas</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-tools me-2"></i>Herramientas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/operator/attendance" class="btn btn-primary">
                                <i class="fas fa-check-circle me-2"></i>Validar Asistencia
                            </a>
                            <a href="/operator/voters" class="btn btn-success">
                                <i class="fas fa-users me-2"></i>Gestionar Votantes
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-bell me-2"></i>Recordatorios</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning" role="alert">
                            <small>
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Tienes 5 asistencias pendientes de validar
                            </small>
                        </div>
                        <div class="alert alert-info" role="alert">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Recuerda verificar los documentos de identidad
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../views/layouts/footer.php'; ?>
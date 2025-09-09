<?php $title = 'Mis Votaciones'; $userRole = 'votante'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-vote-yea me-2"></i>Mis Votaciones</h1>
        </div>
        
        <!-- Votaciones activas -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-poll me-2"></i>Votaciones Activas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">Aprobación del Presupuesto 2024</h6>
                                        <p class="card-text text-muted">Votación para aprobar el presupuesto anual del conjunto</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Asamblea: Ordinaria Marzo</small>
                                            <span class="badge bg-success">Abierta</span>
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-vote-yea me-2"></i>Votar Ahora
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h6 class="card-title">Mejoras Zonas Comunes</h6>
                                        <p class="card-text text-muted">Votación sobre inversión en mejoras de zonas comunes</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Asamblea: Torre del Sol</small>
                                            <span class="badge bg-warning">Programada</span>
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn btn-warning btn-sm w-100" disabled>
                                                <i class="fas fa-clock me-2"></i>Próximamente
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historial de votaciones -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-history me-2"></i>Historial de Votaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Votación</th>
                                        <th>Asamblea</th>
                                        <th>Mi Voto</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($votings) && !empty($votings)): ?>
                                        <?php foreach ($votings as $voting): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($voting['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($voting['asamblea_titulo']); ?></td>
                                                <td>
                                                    <?php if (isset($voting['opcion'])): ?>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($voting['opcion']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">No votó</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($voting['timestamp_voto'])): ?>
                                                        <?php echo date('d/m/Y H:i', strtotime($voting['timestamp_voto'])); ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $voting['estado'] === 'cerrada' ? 'secondary' : 'success'; ?>">
                                                        <?php echo ucfirst($voting['estado']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay historial de votaciones</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../views/layouts/footer.php'; ?>
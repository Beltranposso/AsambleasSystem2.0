<?php $title = 'Gestión de Asambleas'; $userRole = 'administrador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-users me-2"></i>Gestión de Asambleas</h1>
            <a href="/admin/create-assembly" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nueva Asamblea
            </a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Conjunto</th>
                                <th>Fecha Inicio</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($assemblies) && !empty($assemblies)): ?>
                                <?php foreach ($assemblies as $assembly): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assembly['titulo']); ?></td>
                                        <td><?php echo htmlspecialchars($assembly['conjunto_nombre']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($assembly['fecha_inicio'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $assembly['estado'] === 'activa' ? 'success' : ($assembly['estado'] === 'programada' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($assembly['estado']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo ucfirst($assembly['tipo_asamblea']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay asambleas registradas</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../views/layouts/footer.php'; ?>

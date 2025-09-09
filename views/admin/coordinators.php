<?php $title = 'Gestión de Coordinadores'; $userRole = 'administrador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-user-tie me-2"></i>Gestión de Coordinadores</h1>
            <a href="/admin/create-coordinator" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Nuevo Coordinador
            </a>
        </div>
        
        <!-- Filtros y búsqueda -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form class="row g-3">
                            <div class="col-md-4">
                                <label for="search_coordinator" class="form-label">Buscar Coordinador</label>
                                <input type="text" class="form-control" id="search_coordinator" placeholder="Nombre, correo o cédula">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_status" class="form-label">Estado</label>
                                <select class="form-select" id="filter_status">
                                    <option value="">Todos</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="suspendido">Suspendido</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sort_by" class="form-label">Ordenar por</label>
                                <select class="form-select" id="sort_by">
                                    <option value="nombre">Nombre</option>
                                    <option value="fecha">Fecha de registro</option>
                                    <option value="estado">Estado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de coordinadores -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list me-2"></i>Lista de Coordinadores</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Coordinador</th>
                                        <th>Correo</th>
                                        <th>Teléfono</th>
                                        <th>Asambleas Asignadas</th>
                                        <th>Estado</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($coordinators) && !empty($coordinators)): ?>
                                        <?php foreach ($coordinators as $coordinator): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle bg-primary text-white me-3">
                                                            <?php echo strtoupper(substr($coordinator['nombre'], 0, 1) . substr($coordinator['apellido'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($coordinator['nombre'] . ' ' . $coordinator['apellido']); ?></strong>
                                                            <br><small class="text-muted">Cédula: <?php echo htmlspecialchars($coordinator['cedula']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($coordinator['email']); ?></td>
                                                <td><?php echo htmlspecialchars($coordinator['telefono'] ?? 'No registrado'); ?></td>
                                                <td>
                                                    <span class="badge bg-info">3 Asambleas</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $coordinator['activo'] === 1 ? 'success' : ($coordinator['activo'] === 0 ? 'danger' : 'secondary'); ?>">
                                                        <?php echo ucfirst($coordinator['activo']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($coordinator['fecha_registro'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-info" title="Asignar asambleas">
                                                            <i class="fas fa-tasks"></i>
                                                        </button>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#"><i class="fas fa-key me-2"></i>Cambiar contraseña</a></li>
                                                                <li><a class="dropdown-item" href="#"><i class="fas fa-ban me-2"></i>Suspender</a></li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i>Eliminar</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-user-tie fa-3x mb-3"></i>
                                                <p>No hay coordinadores registrados</p>
                                                <a href="/admin/create-coordinator" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Crear primer coordinador
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Estadísticas rápidas -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4>1</h4>
                                        <small>Total Coordinadores</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4>1</h4>
                                        <small>Activos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4>0</h4>
                                        <small>Suspendidos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4>3</h4>
                                        <small>Asambleas Asignadas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>

<?php include '../views/layouts/footer.php'; ?>
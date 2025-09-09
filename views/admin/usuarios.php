<?php 
$title = 'Gestión de Usuarios - Administrador'; 
$userRole = 'administrador'; 
require_once __DIR__ . '/../../core/helpers.php';
?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">
                <i class="fas fa-user-friends me-2"></i>Gestión de Usuarios
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <a href="<?= url('/admin/usuarios/crear') ?>" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Nuevo Usuario
                    </a>
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-download me-1"></i>Exportar Excel</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-1"></i>Exportar PDF</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-upload me-1"></i>Importar Usuarios</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Filtros de búsqueda -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="searchUsers" class="form-label">Buscar Usuarios</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchUsers" 
                                   placeholder="Buscar por nombre, email, cédula...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="filterRole" class="form-label">Rol</label>
                        <select class="form-select" id="filterRole">
                            <option value="">Todos los roles</option>
                            <option value="administrador">Administrador</option>
                            <option value="coordinador">Coordinador</option>
                            <option value="operador">Operador</option>
                            <option value="votante">Votante</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Estado</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button class="btn btn-outline-primary" type="button">
                                <i class="fas fa-filter me-1"></i>Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>Lista de Usuarios
                    <span class="badge bg-secondary ms-2"><?= count($usuarios ?? []) ?> registros</span>
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($usuarios)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Cédula</th>
                                    <th>Teléfono</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr class="user-row">
                                        <td>
                                            <span class="badge bg-light text-dark">#<?= $usuario['id'] ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-primary rounded-circle">
                                                        <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-envelope me-1 text-muted"></i>
                                            <?= htmlspecialchars($usuario['email']) ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-id-card me-1 text-muted"></i>
                                            <?= htmlspecialchars($usuario['cedula']) ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-phone me-1 text-muted"></i>
                                            <?= htmlspecialchars($usuario['telefono']) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $roleColors = [
                                                'administrador' => 'danger',
                                                'coordinador' => 'success',
                                                'operador' => 'warning',
                                                'votante' => 'info'
                                            ];
                                            $colorClass = $roleColors[$usuario['rol']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $colorClass ?>">
                                                <?= ucfirst($usuario['rol']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($usuario['activo']): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Activo
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Inactivo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= $usuario['fecha_registro'] ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= url('/admin/usuarios/editar/' . $usuario['id']) ?>" 
                                                   class="btn btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-warning" 
                                                        title="<?= $usuario['activo'] ? 'Desactivar' : 'Activar' ?>"
                                                        onclick="toggleUserStatus(<?= $usuario['id'] ?>, <?= $usuario['activo'] ? 'false' : 'true' ?>)">
                                                    <i class="fas fa-<?= $usuario['activo'] ? 'user-slash' : 'user-check' ?>"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-delete" 
                                                        title="Eliminar"
                                                        onclick="deleteUser(<?= $usuario['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <nav aria-label="Paginación de usuarios">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Anterior</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                <?php else: ?>
                    <!-- Estado vacío -->
                    <div class="text-center py-5">
                        <i class="fas fa-user-friends fa-4x text-gray-300 mb-4"></i>
                        <h4 class="text-gray-600">No hay usuarios registrados</h4>
                        <p class="text-muted mb-4">Comienza creando el primer usuario</p>
                        <a href="<?= url('/admin/usuarios/crear') ?>" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>Crear Primer Usuario
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Usuarios</h6>
                                <h3><?= count($usuarios ?? []) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Activos</h6>
                                <h3><?= count(array_filter($usuarios ?? [], function($u) { return $u['activo']; })) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Votantes</h6>
                                <h3><?= count(array_filter($usuarios ?? [], function($u) { return $u['rol'] === 'votante'; })) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-vote-yea fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Coordinadores</h6>
                                <h3><?= count(array_filter($usuarios ?? [], function($u) { return $u['rol'] === 'coordinador'; })) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-tie fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php 
$pageScripts = '
<script>
function deleteUser(id) {
    if (confirm("¿Estás seguro de que quieres eliminar este usuario?")) {
        // Crear formulario para envío POST
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "' . url('/admin/usuarios/eliminar/') . '" + id;
        
        // Agregar token CSRF
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf_token";
        csrfInput.value = "' . csrf_token() . '";
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleUserStatus(id, activate) {
    const action = activate ? "activar" : "desactivar";
    if (confirm(`¿Estás seguro de que quieres ${action} este usuario?`)) {
        // Enviar petición AJAX o formulario
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "' . url('/admin/usuarios/toggle/') . '" + id;
        
        const actionInput = document.createElement("input");
        actionInput.type = "hidden";
        actionInput.name = "action";
        actionInput.value = activate ? "activate" : "deactivate";
        form.appendChild(actionInput);
        
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf_token";
        csrfInput.value = "' . csrf_token() . '";
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Filtros en tiempo real
document.getElementById("searchUsers").addEventListener("input", function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll(".user-row");
    
    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? "" : "none";
    });
});

// Filtro por rol
document.getElementById("filterRole").addEventListener("change", function() {
    const selectedRole = this.value.toLowerCase();
    const rows = document.querySelectorAll(".user-row");
    
    rows.forEach(function(row) {
        if (selectedRole === "") {
            row.style.display = "";
        } else {
            const roleBadge = row.querySelector(".badge");
            const role = roleBadge ? roleBadge.textContent.toLowerCase() : "";
            row.style.display = role.includes(selectedRole) ? "" : "none";
        }
    });
});

// Filtro por estado
document.getElementById("filterStatus").addEventListener("change", function() {
    const selectedStatus = this.value;
    const rows = document.querySelectorAll(".user-row");
    
    rows.forEach(function(row) {
        if (selectedStatus === "") {
            row.style.display = "";
        } else {
            const statusBadge = row.querySelector(".badge.bg-success, .badge.bg-danger");
            const isActive = statusBadge && statusBadge.classList.contains("bg-success");
            const shouldShow = (selectedStatus === "1" && isActive) || (selectedStatus === "0" && !isActive);
            row.style.display = shouldShow ? "" : "none";
        }
    });
});
</script>
';
?>

<?php include '../views/layouts/footer.php'; ?>

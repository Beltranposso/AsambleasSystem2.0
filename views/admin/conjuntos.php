<?php 
$title = 'Gestión de Conjuntos - Administrador'; 
$userRole = 'administrador'; 
require_once __DIR__ . '/../../core/helpers.php';
?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">
                <i class="fas fa-building me-2"></i>Gestión de Conjuntos Residenciales
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <a href="<?= url('/admin/conjuntos/crear') ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Nuevo Conjunto
                    </a>
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportConjuntos()"><i class="fas fa-download me-1"></i>Exportar Lista</a></li>
                        <li><a class="dropdown-item" href="#" onclick="generateGlobalReport()"><i class="fas fa-file-pdf me-1"></i>Reporte PDF</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="showStatistics()"><i class="fas fa-chart-bar me-1"></i>Estadísticas</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Alertas y mensajes -->
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

        <!-- Filtros de búsqueda -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="searchConjuntos" class="form-label">Buscar Conjuntos</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchConjuntos" 
                                   placeholder="Buscar por nombre, dirección, NIT...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="filterCiudad" class="form-label">Ciudad</label>
                        <select class="form-select" id="filterCiudad">
                            <option value="">Todas las ciudades</option>
                            <?php 
                            $ciudades = array_unique(array_column($conjuntos ?? [], 'ciudad'));
                            foreach($ciudades as $ciudad): 
                                if(!empty($ciudad)):
                            ?>
                                <option value="<?= strtolower($ciudad) ?>"><?= htmlspecialchars($ciudad) ?></option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button class="btn btn-outline-primary" type="button" onclick="applyFilters()">
                                <i class="fas fa-filter me-1"></i>Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de conjuntos -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>Conjuntos Registrados
                    <span class="badge bg-primary ms-2" id="conjuntos-count"><?= count($conjuntos ?? []) ?> conjuntos</span>
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($conjuntos)): ?>
                    <div class="row" id="conjuntos-container">
                        <?php foreach ($conjuntos as $conjunto): ?>
                            <div class="col-md-6 col-lg-4 mb-4 conjunto-card" data-conjunto-id="<?= $conjunto['id'] ?>">
                                <div class="card h-100 shadow-sm border-left-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title fw-bold text-primary mb-1">
                                                    <?= htmlspecialchars($conjunto['nombre']) ?>
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?= htmlspecialchars($conjunto['ciudad'] ?? 'Ciudad no especificada') ?>
                                                </p>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" 
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="<?= url('/admin/conjuntos/editar/' . $conjunto['id']) ?>">
                                                            <i class="fas fa-edit me-2"></i>Editar Información
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= url('/admin/asambleas?conjunto=' . $conjunto['id']) ?>">
                                                            <i class="fas fa-users me-2"></i>Ver Asambleas
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" onclick="showConjuntoUsers(<?= $conjunto['id'] ?>, '<?= htmlspecialchars($conjunto['nombre']) ?>')">
                                                            <i class="fas fa-user-friends me-2"></i>Gestionar Residentes
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" onclick="showConjuntoStats(<?= $conjunto['id'] ?>)">
                                                            <i class="fas fa-chart-pie me-2"></i>Ver Estadísticas
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item text-danger" 
                                                                onclick="deleteConjunto(<?= $conjunto['id'] ?>)">
                                                            <i class="fas fa-trash me-2"></i>Eliminar Conjunto
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                            <small><?= htmlspecialchars($conjunto['direccion']) ?></small>
                                        </div>
                                        
                                        <?php if (!empty($conjunto['telefono'])): ?>
                                        <div class="mb-2">
                                            <i class="fas fa-phone text-muted me-2"></i>
                                            <small><?= htmlspecialchars($conjunto['telefono']) ?></small>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($conjunto['email'])): ?>
                                        <div class="mb-2">
                                            <i class="fas fa-envelope text-muted me-2"></i>
                                            <small><?= htmlspecialchars($conjunto['email']) ?></small>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($conjunto['nit'])): ?>
                                        <div class="mb-3">
                                            <i class="fas fa-id-card text-muted me-2"></i>
                                            <small>NIT: <?= htmlspecialchars($conjunto['nit']) ?></small>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Estadísticas del conjunto -->
                                        <div class="row text-center border-top pt-3">
                                            <div class="col-4">
                                                <div class="text-primary fw-bold conjunto-unidades" data-conjunto="<?= $conjunto['id'] ?>">
                                                    <?= $conjunto['total_unidades'] ?? 0 ?>
                                                </div>
                                                <small class="text-muted">Unidades</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-success fw-bold conjunto-asambleas" data-conjunto="<?= $conjunto['id'] ?>">
                                                    <?= $conjunto['total_asambleas'] ?? 0 ?>
                                                </div>
                                                <small class="text-muted">Asambleas</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-info fw-bold conjunto-residentes" data-conjunto="<?= $conjunto['id'] ?>">
                                                    <?= $conjunto['total_residentes'] ?? 0 ?>
                                                </div>
                                                <small class="text-muted">Residentes</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Activo
                                            </span>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-success" 
                                                        title="Crear Residente"
                                                        onclick="showCreateUserModal(<?= $conjunto['id'] ?>, '<?= htmlspecialchars($conjunto['nombre']) ?>')">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info" 
                                                        title="Ver Residentes"
                                                        onclick="showConjuntoUsers(<?= $conjunto['id'] ?>, '<?= htmlspecialchars($conjunto['nombre']) ?>')">
                                                    <i class="fas fa-users"></i>
                                                </button>
                                                <a href="<?= url('/admin/conjuntos/editar/' . $conjunto['id']) ?>" 
                                                   class="btn btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-warning" 
                                                        title="Generar Reporte"
                                                        onclick="generateConjuntoReport(<?= $conjunto['id'] ?>)">
                                                    <i class="fas fa-file-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Estado vacío -->
                    <div class="text-center py-5" id="empty-state">
                        <i class="fas fa-building fa-4x text-gray-300 mb-4"></i>
                        <h4 class="text-gray-600">No hay conjuntos registrados</h4>
                        <p class="text-muted mb-4">Los conjuntos residenciales son la base para organizar las asambleas</p>
                        <a href="<?= url('/admin/conjuntos/crear') ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Registrar Primer Conjunto
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estadísticas generales -->
        <?php if (!empty($conjuntos)): ?>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Conjuntos</h6>
                                <h3><?= count($conjuntos) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-building fa-2x"></i>
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
                                <h6 class="card-title">Total Unidades</h6>
                                <h3><?= array_sum(array_column($conjuntos, 'total_unidades')) ?: 0 ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-home fa-2x"></i>
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
                                <h6 class="card-title">Ciudades</h6>
                                <h3><?= count(array_unique(array_filter(array_column($conjuntos, 'ciudad')))) ?: 1 ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-map-marker-alt fa-2x"></i>
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
                                <h6 class="card-title">Total Residentes</h6>
                                <h3><?= array_sum(array_column($conjuntos, 'total_residentes')) ?: 0 ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<!-- Modal para crear usuario -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>
                    Crear Residente en <span id="modal-conjunto-name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createUserForm">
                <div class="modal-body">
                    <input type="hidden" id="user-conjunto-id" name="conjunto_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user-nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="user-nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="user-apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="user-apellido" name="apellido" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user-cedula" class="form-label">Cédula *</label>
                            <input type="text" class="form-control" id="user-cedula" name="cedula" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="user-email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="user-email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user-telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="user-telefono" name="telefono">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="user-apartamento" class="form-label">Apartamento/Unidad *</label>
                            <input type="text" class="form-control" id="user-apartamento" name="apartamento" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user-coeficiente" class="form-label">Coeficiente de Participación</label>
                            <input type="number" class="form-control" id="user-coeficiente" name="coeficiente" 
                                   min="0" max="1" step="0.0001" value="0.0250">
                            <small class="text-muted">Valor entre 0 y 1 (ejemplo: 0.0250 = 2.5%)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="user-tipo-propietario" class="form-label">Tipo de Residente</label>
                            <select class="form-select" id="user-tipo-propietario" name="es_propietario">
                                <option value="1">Propietario</option>
                                <option value="0">Arrendatario</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="user-password" class="form-label">Contraseña Temporal *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="user-password" name="password" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                <i class="fas fa-random"></i> Generar
                            </button>
                        </div>
                        <small class="text-muted">El usuario podrá cambiarla en su primer acceso</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submitCreateUserForm()">
                        <i class="fas fa-save me-1"></i>Crear Residente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para gestionar usuarios del conjunto -->
<div class="modal fade" id="usersModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users me-2"></i>
                    Residentes de <span id="users-modal-conjunto-name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <input type="text" class="form-control" id="search-users" placeholder="Buscar residentes...">
                    </div>
                    <button type="button" class="btn btn-success btn-sm" 
                            onclick="openCreateUserFromModal()">
                        <i class="fas fa-user-plus me-1"></i>Nuevo Residente
                    </button>
                </div>
                <div id="users-table-container">
                    <!-- Contenido cargado dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentConjuntoId = null;
let currentConjuntoName = null;

// Funciones de filtrado
function applyFilters() {
    const searchTerm = document.getElementById('searchConjuntos').value.toLowerCase();
    const selectedCiudad = document.getElementById('filterCiudad').value.toLowerCase();
    const cards = document.querySelectorAll('.conjunto-card');
    let visibleCount = 0;
    
    cards.forEach(function(card) {
        const text = card.textContent.toLowerCase();
        const matchesSearch = searchTerm === '' || text.includes(searchTerm);
        const matchesCiudad = selectedCiudad === '' || text.includes(selectedCiudad);
        
        if (matchesSearch && matchesCiudad) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    document.getElementById('conjuntos-count').textContent = visibleCount + ' conjuntos';
    
    // Mostrar/ocultar estado vacío
    const emptyState = document.getElementById('empty-state');
    if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

// Filtro en tiempo real
document.getElementById('searchConjuntos').addEventListener('input', applyFilters);
document.getElementById('filterCiudad').addEventListener('change', applyFilters);

// Función para eliminar conjunto
function deleteConjunto(id) {
    if (confirm('¿Está seguro de que quiere eliminar este conjunto?\n\nEsta acción eliminará también todas las asambleas y usuarios asociados.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url('/admin/conjuntos/eliminar/') ?>' + id;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= csrf_token() ?>';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Función para mostrar modal de crear usuario
function showCreateUserModal(conjuntoId, conjuntoName) {
    currentConjuntoId = conjuntoId;
    currentConjuntoName = conjuntoName;
    
    document.getElementById('user-conjunto-id').value = conjuntoId;
    document.getElementById('modal-conjunto-name').textContent = conjuntoName;
    
    // Limpiar formulario
    document.getElementById('createUserForm').reset();
    document.getElementById('user-conjunto-id').value = conjuntoId;
    
    const modal = new bootstrap.Modal(document.getElementById('createUserModal'));
    modal.show();
}

// Función para generar contraseña aleatoria
function generatePassword() {
    const length = 8;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let password = '';
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    document.getElementById('user-password').value = password;
}

// Función para mostrar usuarios del conjunto
function showConjuntoUsers(conjuntoId, conjuntoName) {
    currentConjuntoId = conjuntoId;
    currentConjuntoName = conjuntoName;
    
    document.getElementById('users-modal-conjunto-name').textContent = conjuntoName;
    
    // Cargar usuarios
    loadConjuntoUsers(conjuntoId);
    
    const modal = new bootstrap.Modal(document.getElementById('usersModal'));
    modal.show();
}

// Función para cargar usuarios del conjunto
function loadConjuntoUsers(conjuntoId) {
    const container = document.getElementById('users-table-container');
    container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
    
    fetch(`<?= url('/admin/conjuntos/usuarios/') ?>${conjuntoId}`)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="alert alert-danger">Error al cargar usuarios</div>';
        });
}

// Función para abrir crear usuario desde el modal de usuarios
function openCreateUserFromModal() {
    // Cerrar modal de usuarios
    bootstrap.Modal.getInstance(document.getElementById('usersModal')).hide();
    
    // Abrir modal de crear usuario
    setTimeout(() => {
        showCreateUserModal(currentConjuntoId, currentConjuntoName);
    }, 300);
}

// Envío del formulario de crear usuario
/* document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Deshabilitar botón
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creando...';
    
    fetch('<?= url('/admin/conjuntos/crear-usuario') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
            
            // Mostrar mensaje de éxito
            showAlert('success', 'Usuario creado correctamente');
            
            // Actualizar estadísticas si es necesario
            updateConjuntoStats(currentConjuntoId);
            
            // Si el modal de usuarios está abierto, recargar la lista
            const usersModal = document.getElementById('usersModal');
            if (usersModal.classList.contains('show')) {
                loadConjuntoUsers(currentConjuntoId);
            }
        } else {
            showAlert('error', data.message || 'Error al crear el usuario');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error al crear el usuario');
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Crear Residente';
    });
}); */

function submitCreateUserForm() {
    console.log('🚀 ===== INICIO CREAR USUARIO =====');
    console.log('Timestamp:', new Date().toISOString());
    
    // STEP 1: Obtener elementos
    const form = document.getElementById('createUserForm');
    const submitBtn = event.target;
    
    console.log('📋 STEP 1: Verificando elementos DOM');
    console.log('  Formulario encontrado:', !!form);
    console.log('  Botón encontrado:', !!submitBtn);
    console.log('  ID del formulario:', form?.id);
    console.log('  Texto del botón:', submitBtn?.textContent?.trim());
    
    if (!form) {
        console.error('❌ ERROR: Formulario no encontrado');
        showAlert('error', 'Error: Formulario no encontrado');
        return false;
    }
    
    if (!submitBtn) {
        console.error('❌ ERROR: Botón no encontrado');
        showAlert('error', 'Error: Botón no encontrado');
        return false;
    }
    
    // STEP 2: Extraer datos del formulario
    console.log('📊 STEP 2: Extrayendo datos del formulario');
    const formData = new FormData(form);
    
    // Debug completo de FormData
    console.log('  Datos del formulario:');
    const formDataObj = {};
    for (let [key, value] of formData.entries()) {
        formDataObj[key] = value;
        console.log(`    ${key}: "${value}"`);
    }
    
    // Validar datos críticos
    const criticalFields = {
        conjunto_id: formData.get('conjunto_id'),
        nombre: formData.get('nombre'),
        apellido: formData.get('apellido'),
        cedula: formData.get('cedula'),
        email: formData.get('email'),
        apartamento: formData.get('apartamento'),
        password: formData.get('password')
    };
    
    console.log('🔍 Verificación de campos críticos:');
    Object.entries(criticalFields).forEach(([key, value]) => {
        const isValid = value && value.trim() !== '';
        console.log(`  ${key}: "${value}" (${isValid ? 'VÁLIDO' : 'INVÁLIDO'})`);
    });
    
    // STEP 3: Validaciones del formulario
    console.log('✅ STEP 3: Iniciando validaciones');
    const requiredFields = ['nombre', 'apellido', 'cedula', 'email', 'apartamento', 'password'];
    let isValid = true;
    let validationErrors = [];
    
    // Limpiar clases de error previas
    const prevErrors = form.querySelectorAll('.is-invalid');
    console.log(`  Limpiando ${prevErrors.length} errores previos`);
    prevErrors.forEach(el => el.classList.remove('is-invalid'));
    
    // Validar campos requeridos
    console.log('  Validando campos requeridos:');
    requiredFields.forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        const fieldValue = field?.value?.trim();
        const fieldValid = fieldValue && fieldValue !== '';
        
        console.log(`    ${fieldName}: "${fieldValue}" (${fieldValid ? 'OK' : 'ERROR'})`);
        
        if (!fieldValid) {
            field?.classList.add('is-invalid');
            validationErrors.push(`${fieldName} es requerido`);
            isValid = false;
        }
    });
    
    // VALIDACIONES ADICIONALES ESPECÍFICAS según AdminController
    console.log('  Validaciones adicionales específicas:');
    
    // Validar conjunto_id específicamente
    const conjuntoId = form.querySelector('[name="conjunto_id"]');
    if (!conjuntoId || !conjuntoId.value || conjuntoId.value === '0') {
        console.log('    conjunto_id: INVÁLIDO (debe ser > 0)');
        conjuntoId?.classList.add('is-invalid');
        validationErrors.push('Debe seleccionar un conjunto válido');
        isValid = false;
    } else {
        console.log(`    conjunto_id: ${conjuntoId.value} (OK)`);
    }
    
    // Validar que apartamento no esté vacío
    const apartamento = form.querySelector('[name="apartamento"]');
    if (!apartamento?.value?.trim()) {
        console.log('    apartamento: INVÁLIDO (vacío)');
        apartamento?.classList.add('is-invalid');
        validationErrors.push('El apartamento/unidad es requerido');
        isValid = false;
    } else {
        console.log(`    apartamento: "${apartamento.value}" (OK)`);
    }
    
    // Validar es_propietario (debe ser 0 o 1)
    const esPropietario = form.querySelector('[name="es_propietario"]');
    if (esPropietario && !['0', '1'].includes(esPropietario.value)) {
        console.log(`    es_propietario: "${esPropietario.value}" (INVÁLIDO, debe ser 0 o 1)`);
        validationErrors.push('Tipo de residente inválido');
        isValid = false;
    } else {
        console.log(`    es_propietario: ${esPropietario?.value || 'N/A'} (OK)`);
    }
    
    // Validar email
    console.log('  Validando formato de email:');
    const email = form.querySelector('[name="email"]');
    const emailValue = email?.value?.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const emailValid = !emailValue || emailPattern.test(emailValue);
    
    console.log(`    Email: "${emailValue}"`);
    console.log(`    Formato válido: ${emailValid}`);
    
    if (emailValue && !emailValid) {
        email.classList.add('is-invalid');
        if (email.nextElementSibling) {
            email.nextElementSibling.textContent = 'Formato de email inválido';
        }
        validationErrors.push('Email con formato inválido');
        isValid = false;
    }
    
    // Validar coeficiente
    console.log('  Validando coeficiente:');
    const coeficiente = form.querySelector('[name="coeficiente"]');
    const coeficienteValue = parseFloat(coeficiente?.value);
    const coeficienteValid = !isNaN(coeficienteValue) && coeficienteValue >= 0 && coeficienteValue <= 1;
    
    console.log(`    Coeficiente: ${coeficienteValue}`);
    console.log(`    Rango válido (0-1): ${coeficienteValid}`);
    
    if (!coeficienteValid) {
        coeficiente?.classList.add('is-invalid');
        validationErrors.push('Coeficiente debe estar entre 0 y 1');
        isValid = false;
    }
    
    // Resumen de validación
    console.log('📋 RESUMEN DE VALIDACIÓN:');
    console.log(`  Formulario válido: ${isValid}`);
    console.log(`  Errores encontrados: ${validationErrors.length}`);
    if (validationErrors.length > 0) {
        console.log('  Lista de errores:');
        validationErrors.forEach((error, index) => {
            console.log(`    ${index + 1}. ${error}`);
        });
    }
    
    if (!isValid) {
        console.log('❌ VALIDACIÓN FALLIDA - Deteniendo ejecución');
        showAlert('error', 'Por favor corrija los errores en el formulario');
        return false;
    }
    
    // STEP 4: Preparar envío
    console.log('🚀 STEP 4: Preparando envío de datos');
    const url = '<?= url('/admin/conjuntos/crear-usuario') ?>';
    console.log(`  URL destino: ${url}`);
    console.log(`  Método: POST`);
    console.log(`  Tipo de datos: FormData`);
    console.log(`  CurrentConjuntoId: ${currentConjuntoId}`);
    console.log(`  CurrentConjuntoName: ${currentConjuntoName}`);
    
    // Cambiar estado del botón
    console.log('  Cambiando estado del botón...');
    const originalButtonText = submitBtn.innerHTML;
    console.log(`    Texto original: ${originalButtonText}`);
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creando...';
    console.log('    Botón deshabilitado y con spinner');
    
    // STEP 5: Enviar petición
    console.log('🌐 STEP 5: Enviando petición AJAX');
    const startTime = performance.now();
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const responseTime = performance.now() - startTime;
        console.log('📡 RESPUESTA RECIBIDA:');
        console.log(`  Tiempo de respuesta: ${Math.round(responseTime)}ms`);
        console.log(`  Status: ${response.status} ${response.statusText}`);
        console.log(`  OK: ${response.ok}`);
        console.log(`  Headers:`, [...response.headers.entries()]);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('📊 DATOS DE RESPUESTA:');
        console.log('  Respuesta parseada:', data);
        console.log(`  Success: ${data.success}`);
        console.log(`  Message: ${data.message || 'N/A'}`);
        
        if (data.success) {
            console.log('✅ USUARIO CREADO EXITOSAMENTE');
            
            // Cerrar modal
            console.log('  Cerrando modal...');
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('createUserModal'));
            if (modalInstance) {
                modalInstance.hide();
                console.log('    Modal cerrado correctamente');
            } else {
                console.warn('    Warning: No se pudo obtener instancia del modal');
            }
            
            // Mostrar mensaje de éxito
            console.log('  Mostrando mensaje de éxito...');
            showAlert('success', 'Usuario creado correctamente');
            
            // Actualizar estadísticas
            console.log('  Actualizando estadísticas...');
            if (currentConjuntoId) {
                updateConjuntoStats(currentConjuntoId);
                console.log(`    Estadísticas actualizadas para conjunto ${currentConjuntoId}`);
            } else {
                console.warn('    Warning: currentConjuntoId no está definido');
            }
            
            // Recargar lista de usuarios si está visible
            const usersModal = document.getElementById('usersModal');
            if (usersModal && usersModal.classList.contains('show')) {
                console.log('  Modal de usuarios visible, recargando lista...');
                loadConjuntoUsers(currentConjuntoId);
            } else {
                console.log('  Modal de usuarios no visible, no se recarga lista');
            }
            
            // Limpiar formulario
            console.log('  Limpiando formulario...');
            form.reset();
            console.log('    Formulario limpiado');
            
        } else {
            console.error('❌ ERROR EN LA CREACIÓN DEL USUARIO');
            console.error(`  Mensaje de error: ${data.message}`);
            console.error(`  Datos adicionales:`, data);
            showAlert('error', data.message || 'Error al crear el usuario');
        }
    })
    .catch(error => {
        console.error('💥 ERROR EN LA PETICIÓN:');
        console.error('  Tipo de error:', error.constructor.name);
        console.error('  Mensaje:', error.message);
        console.error('  Stack trace:', error.stack);
        
        // Análisis del tipo de error
        let userMessage = 'Error al crear el usuario';
        if (error.message.includes('Failed to fetch')) {
            userMessage = 'Error de conexión. Verifica tu conexión a internet.';
            console.error('  Tipo: Error de conexión/red');
        } else if (error.message.includes('HTTP 404')) {
            userMessage = 'Endpoint no encontrado. Contacta al administrador.';
            console.error('  Tipo: Endpoint no encontrado');
        } else if (error.message.includes('HTTP 500')) {
            userMessage = 'Error interno del servidor. Inténtalo de nuevo.';
            console.error('  Tipo: Error del servidor');
        } else {
            console.error('  Tipo: Error desconocido');
        }
        
        showAlert('error', userMessage);
    })
    .finally(() => {
        console.log('🧹 CLEANUP FINAL:');
        
        // Restaurar botón SIEMPRE
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalButtonText;
            console.log('  Botón restaurado correctamente');
        } else {
            console.warn('  Warning: No se pudo restaurar el botón');
        }
        
        const finalTime = performance.now() - startTime;
        console.log(`  Tiempo total de operación: ${Math.round(finalTime)}ms`);
        console.log('🏁 ===== FIN CREAR USUARIO =====');
    });
}

// Función para mostrar alertas
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas ${iconClass} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insertar al inicio de la página
    const container = document.querySelector('main');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Función para actualizar estadísticas de un conjunto
function updateConjuntoStats(conjuntoId) {
    fetch(`<?= url('/admin/conjuntos/stats/') ?>${conjuntoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.stats;
                document.querySelector(`.conjunto-residentes[data-conjunto="${conjuntoId}"]`).textContent = stats.residentes;
                document.querySelector(`.conjunto-asambleas[data-conjunto="${conjuntoId}"]`).textContent = stats.asambleas;
                document.querySelector(`.conjunto-unidades[data-conjunto="${conjuntoId}"]`).textContent = stats.unidades;
            }
        })
        .catch(error => console.error('Error updating stats:', error));
}

// Función para mostrar estadísticas del conjunto
function showConjuntoStats(conjuntoId) {
    fetch(`<?= url('/admin/conjuntos/estadisticas/') ?>${conjuntoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Crear modal con estadísticas detalladas
                const modal = createStatsModal(data.stats);
                document.body.appendChild(modal);
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                
                // Eliminar modal cuando se cierre
                modal.addEventListener('hidden.bs.modal', () => {
                    modal.remove();
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error al cargar estadísticas');
        });
}

// Función para crear modal de estadísticas
function createStatsModal(stats) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-pie me-2"></i>Estadísticas de ${stats.nombre}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body text-center">
                                    <h3>${stats.total_unidades}</h3>
                                    <p>Unidades Totales</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body text-center">
                                    <h3>${stats.total_residentes}</h3>
                                    <p>Residentes Registrados</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body text-center">
                                    <h3>${stats.total_asambleas}</h3>
                                    <p>Asambleas Realizadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-warning text-white mb-3">
                                <div class="card-body text-center">
                                    <h3>${stats.ocupacion}%</h3>
                                    <p>Nivel de Ocupación</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Distribución por Tipo de Residente</h6>
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar bg-success" style="width: ${stats.propietarios_pct}%">
                                    Propietarios (${stats.propietarios})
                                </div>
                                <div class="progress-bar bg-info" style="width: ${stats.arrendatarios_pct}%">
                                    Arrendatarios (${stats.arrendatarios})
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Estado de Pagos</h6>
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar bg-success" style="width: ${stats.al_dia_pct}%">
                                    Al día (${stats.al_dia})
                                </div>
                                <div class="progress-bar bg-warning" style="width: ${stats.mora_pct}%">
                                    En mora (${stats.mora})
                                </div>
                                <div class="progress-bar bg-danger" style="width: ${stats.suspendido_pct}%">
                                    Suspendidos (${stats.suspendido})
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="generateConjuntoReport(${stats.id})">
                        <i class="fas fa-file-pdf me-1"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    `;
    return modal;
}

// Función para generar reporte de conjunto
function generateConjuntoReport(conjuntoId) {
    showAlert('info', 'Generando reporte del conjunto...');
    
    // Simular generación de reporte
    setTimeout(() => {
        window.open(`<?= url('/admin/conjuntos/reporte/') ?>${conjuntoId}`, '_blank');
    }, 1000);
}

// Función para exportar conjuntos
function exportConjuntos() {
    showAlert('info', 'Exportando lista de conjuntos...');
    
    setTimeout(() => {
        window.location.href = '<?= url('/admin/conjuntos/export') ?>';
    }, 1000);
}

// Función para generar reporte global
function generateGlobalReport() {
    showAlert('info', 'Generando reporte global...');
    
    setTimeout(() => {
        window.open('<?= url('/admin/conjuntos/reporte-global') ?>', '_blank');
    }, 1000);
}

// Función para mostrar estadísticas globales
function showStatistics() {
    fetch('<?= url('/admin/conjuntos/estadisticas-globales') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = createGlobalStatsModal(data.stats);
                document.body.appendChild(modal);
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                
                modal.addEventListener('hidden.bs.modal', () => {
                    modal.remove();
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error al cargar estadísticas globales');
        });
}

// Función para crear modal de estadísticas globales
function createGlobalStatsModal(stats) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-bar me-2"></i>Estadísticas Globales del Sistema
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body text-center">
                                    <h2>${stats.total_conjuntos}</h2>
                                    <p>Conjuntos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body text-center">
                                    <h2>${stats.total_unidades}</h2>
                                    <p>Unidades</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body text-center">
                                    <h2>${stats.total_residentes}</h2>
                                    <p>Residentes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white mb-3">
                                <div class="card-body text-center">
                                    <h2>${stats.total_asambleas}</h2>
                                    <p>Asambleas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Top 5 Conjuntos por Residentes</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Conjunto</th>
                                            <th>Residentes</th>
                                            <th>Unidades</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${stats.top_conjuntos.map(c => `
                                            <tr>
                                                <td>${c.nombre}</td>
                                                <td>${c.residentes}</td>
                                                <td>${c.unidades}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Distribución por Ciudades</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Ciudad</th>
                                            <th>Conjuntos</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${stats.por_ciudad.map(c => `
                                            <tr>
                                                <td>${c.ciudad}</td>
                                                <td>${c.total}</td>
                                                <td>${c.porcentaje}%</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="generateGlobalReport()">
                        <i class="fas fa-file-pdf me-1"></i>Generar Reporte Global
                    </button>
                </div>
            </div>
        </div>
    `;
    return modal;
}

// Validaciones en tiempo real para el formulario
document.getElementById('user-email').addEventListener('blur', function() {
    const email = this.value;
    if (email) {
        fetch('<?= url('/admin/usuarios/check-email') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                this.classList.add('is-invalid');
                this.nextElementSibling.textContent = 'Este email ya está registrado';
            } else {
                this.classList.remove('is-invalid');
            }
        })
        .catch(error => console.error('Error:', error));
    }
});

document.getElementById('user-cedula').addEventListener('blur', function() {
    const cedula = this.value;
    if (cedula) {
        fetch('<?= url('/admin/usuarios/check-cedula') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cedula: cedula })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                this.classList.add('is-invalid');
                this.nextElementSibling.textContent = 'Esta cédula ya está registrada';
            } else {
                this.classList.remove('is-invalid');
            }
        })
        .catch(error => console.error('Error:', error));
    }
});

// Función para buscar usuarios en el modal
document.getElementById('search-users').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#users-table-container tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Función para eliminar usuario
function deleteUser(userId, userName) {
    if (confirm(`¿Está seguro de que quiere eliminar al residente ${userName}?`)) {
        fetch(`<?= url('/admin/usuarios/eliminar/') ?>${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ csrf_token: '<?= csrf_token() ?>' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Usuario eliminado correctamente');
                loadConjuntoUsers(currentConjuntoId);
                updateConjuntoStats(currentConjuntoId);
            } else {
                showAlert('error', data.message || 'Error al eliminar usuario');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar usuario');
        });
    }
}

// Función para editar usuario
function editUser(userId) {
    window.open(`<?= url('/admin/usuarios/editar/') ?>${userId}`, '_blank');
}

// Auto-generar contraseña al cargar
document.addEventListener('DOMContentLoaded', function() {
    // Generar contraseña automáticamente cuando se abra el modal
    document.getElementById('createUserModal').addEventListener('shown.bs.modal', function() {
        generatePassword();
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>
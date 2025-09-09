<?php $title = 'Gestión de Participantes'; $userRole = 'coordinador'; ?>
<?php include '../views/layouts/header.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JavaScript (IMPORTANTE: debe ir ANTES de tus scripts) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if (isset($assembly)): ?>
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
                        <i class="fas fa-user-plus me-2"></i>Agregar Existente
                    </button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="fas fa-user-plus me-2"></i>Crear Usuario
                    </button>
                </div>
            <?php endif; ?>
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
            <!-- Estadísticas de Participantes -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo count($participants ?? []); ?></h4>
                            <small class="text-muted">Total Participantes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h4 class="text-success">
                                <?php 
                                $votantes = 0;
                                if (isset($participants)) {
                                    foreach ($participants as $p) {
                                        if ($p['rol'] === 'votante') $votantes++;
                                    }
                                }
                                echo $votantes;
                                ?>
                            </h4>
                            <small class="text-muted">Votantes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h4 class="text-info">
                                <?php 
                                $operadores = 0;
                                if (isset($participants)) {
                                    foreach ($participants as $p) {
                                        if ($p['rol'] === 'operador') $operadores++;
                                    }
                                }
                                echo $operadores;
                                ?>
                            </h4>
                            <small class="text-muted">Operadores</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h4 class="text-success">
                                <?php 
                                $propietarios = 0;
                                if (isset($participants)) {
                                    foreach ($participants as $p) {
                                        if (!empty($p['apartamento'])) $propietarios++;
                                    }
                                }
                                echo $propietarios;
                                ?>
                            </h4>
                            <small class="text-muted">Propietarios</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h4 class="text-info">
                                <?php 
                                $coeficienteTotal = 0;
                                if (isset($participants)) {
                                    foreach ($participants as $p) {
                                        $coeficienteTotal += $p['coeficiente_asignado'] ?? 0;
                                    }
                                }
                                echo number_format($coeficienteTotal, 4);
                                ?>
                            </h4>
                            <small class="text-muted">Coeficiente Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h4 class="text-warning"><?php echo count($availableUsers ?? []); ?></h4>
                            <small class="text-muted">Disponibles</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Participantes -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-list me-2"></i>Participantes Registrados</h6>
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
                                    <th>Rol</th>
                                    <th>Apartamento</th>
                                    <th>Coeficiente</th>
                                    <th>Estado Pagos</th>
                                    <th>Asistencia</th>
                                    <th>Representación</th>
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
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                                                             style="width: 40px; height: 40px; font-size: 14px;">
                                                            <?php echo strtoupper(substr($participant['nombre'], 0, 1) . substr($participant['apellido'], 0, 1)); ?>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($participant['nombre'] . ' ' . $participant['apellido']); ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($participant['cedula']); ?>
                                                            <br>
                                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($participant['email']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $roleClass = '';
                                                $roleText = ucfirst($participant['rol']);
                                                switch($participant['rol']) {
                                                    case 'administrador':
                                                        $roleClass = 'bg-danger';
                                                        break;
                                                    case 'coordinador':
                                                        $roleClass = 'bg-warning';
                                                        break;
                                                    case 'operador':
                                                        $roleClass = 'bg-info';
                                                        break;
                                                    case 'votante':
                                                        $roleClass = 'bg-primary';
                                                        break;
                                                    default:
                                                        $roleClass = 'bg-secondary';
                                                }
                                                ?>
                                                <span class="badge <?php echo $roleClass; ?>"><?php echo $roleText; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo htmlspecialchars($participant['apartamento'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm" style="width: 120px;">
                                                    <input type="number" 
                                                           class="form-control coeficiente-input" 
                                                           value="<?php echo $participant['coeficiente_asignado']; ?>"
                                                           step="0.0001" 
                                                           min="0" 
                                                           max="1"
                                                           data-user-id="<?php echo $participant['usuario_id']; ?>">
                                                    <button class="btn btn-outline-secondary" 
                                                            onclick="updateCoeficiente(<?php echo $participant['usuario_id']; ?>)"
                                                            title="Actualizar coeficiente">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </div>
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
                                                    <?php if (!empty($participant['hora_ingreso'])): ?>
                                                        <br><small class="text-muted">
                                                            <?php echo date('H:i', strtotime($participant['hora_ingreso'])); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-times me-1"></i>Ausente
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($participant['es_representado'])): ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-user-tie me-1"></i>Representado
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Directo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="editParticipant(<?php echo $participant['usuario_id']; ?>)"
                                                            title="Editar participante">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($participant['rol'] === 'votante'): ?>
                                                        <button class="btn btn-outline-danger btn-sm" 
                                                                onclick="removeParticipant(<?php echo $participant['usuario_id']; ?>)"
                                                                title="Remover participante">
                                                            <i class="fas fa-user-minus"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users-slash fa-3x mb-3"></i>
                                                <p>No hay participantes registrados en esta asamblea</p>
                                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
                                                    <i class="fas fa-user-plus me-2"></i>Agregar Primer Participante
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resumen de Coeficientes -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-calculator me-2"></i>Resumen de Coeficientes</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="text-primary"><?php echo number_format($coeficienteTotal, 4); ?></h5>
                                    <small class="text-muted">Total Asignado</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-success"><?php echo number_format($coeficienteTotal * 100, 2); ?>%</h5>
                                    <small class="text-muted">Representación</small>
                                </div>
                            </div>
                            <?php if ($coeficienteTotal > 1): ?>
                                <div class="alert alert-warning mt-2 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    El coeficiente total excede 1.0000
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-chart-pie me-2"></i>Distribución por Estado</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $estadosCount = ['al_dia' => 0, 'mora' => 0, 'suspendido' => 0];
                            if (isset($participants)) {
                                foreach ($participants as $p) {
                                    $estado = $p['estado_pagos'] ?? 'al_dia';
                                    if (isset($estadosCount[$estado])) {
                                        $estadosCount[$estado]++;
                                    }
                                }
                            }
                            ?>
                            <div class="mb-2">
                                <small class="text-muted">Al día:</small>
                                <strong class="text-success"><?php echo $estadosCount['al_dia']; ?></strong>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">En mora:</small>
                                <strong class="text-warning"><?php echo $estadosCount['mora']; ?></strong>
                            </div>
                            <div class="mb-0">
                                <small class="text-muted">Suspendidos:</small>
                                <strong class="text-danger"><?php echo $estadosCount['suspendido']; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h5>Selecciona una Asamblea</h5>
                    <p class="text-muted">Selecciona una asamblea para gestionar sus participantes.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Modal para Agregar Participante Existente -->
<div class="modal fade" id="addParticipantModal" tabindex="-1" aria-labelledby="addParticipantModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addParticipantModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Agregar Participante Existente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addParticipantForm" method="POST" action="/Asambleas/public/coordinador/agregar-participante">
                    <?php if (isset($assembly)): ?>
                        <input type="hidden" name="asamblea_id" value="<?php echo $assembly['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="usuario_id" class="form-label">Usuario *</label>
                        <select class="form-select" name="usuario_id" id="usuario_id" required>
                            <option value="">Seleccione un usuario...</option>
                            <?php if (isset($availableUsers)): ?>
                                <?php foreach ($availableUsers as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" data-coeficiente="<?php echo $user['coeficiente'] ?? 0; ?>">
                                        <?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?>
                                        (<?php echo htmlspecialchars($user['cedula']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="coeficiente" class="form-label">Coeficiente</label>
                                <input type="number" class="form-control" name="coeficiente" id="coeficiente" 
                                       step="0.0001" min="0" max="1" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estado_pagos" class="form-label">Estado de Pagos</label>
                                <select class="form-select" name="estado_pagos" id="estado_pagos">
                                    <option value="al_dia">Al día</option>
                                    <option value="mora">En mora</option>
                                    <option value="suspendido">Suspendido</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="addParticipantForm" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Agregar Participante
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Unificado para Crear Usuario -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Crear Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Selector de tipo de usuario -->
                <div class="mb-4">
                    <label class="form-label">Tipo de Usuario a Crear:</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="user_type" id="type_votante" value="votante" checked onchange="changeUserType()">
                                <label class="form-check-label" for="type_votante">
                                    <i class="fas fa-user text-primary me-2"></i>
                                    <strong>Votante</strong>
                                    <br>
                                    <small class="text-muted">Usuario que puede votar en la asamblea</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="user_type" id="type_operador" value="operador" onchange="changeUserType()">
                                <label class="form-check-label" for="type_operador">
                                    <i class="fas fa-user-cog text-info me-2"></i>
                                    <strong>Operador</strong>
                                    <br>
                                    <small class="text-muted">Usuario que registra asistencia</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario dinámico -->
                <form id="createUserForm" method="POST" action="#">
                    <?php if (isset($assembly)): ?>
                        <input type="hidden" name="asamblea_id" value="<?php echo $assembly['id']; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="votante">
                    
                    <!-- Campos comunes -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cu_nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" id="cu_nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cu_apellido" class="form-label">Apellido *</label>
                                <input type="text" class="form-control" name="apellido" id="cu_apellido" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cu_cedula" class="form-label">Cédula *</label>
                                <input type="text" class="form-control" name="cedula" id="cu_cedula" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cu_email" class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" id="cu_email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cu_telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono" id="cu_telefono">
                            </div>
                        </div>
                        <div class="col-md-6" id="password_section">
                            <div class="mb-3">
                                <label for="cu_password" class="form-label">Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="cu_password" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </div>
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos específicos para VOTANTE -->
                    <div id="votante_fields">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cu_apartamento" class="form-label">Apartamento</label>
                                    <input type="text" class="form-control" name="apartamento" id="cu_apartamento">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cu_coeficiente" class="form-label">Coeficiente</label>
                                    <input type="number" class="form-control" name="coeficiente" id="cu_coeficiente" 
                                           step="0.0001" min="0" max="1" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cu_estado_pagos" class="form-label">Estado de Pagos</label>
                                    <select class="form-select" name="estado_pagos" id="cu_estado_pagos">
                                        <option value="al_dia">Al día</option>
                                        <option value="mora">En mora</option>
                                        <option value="suspendido">Suspendido</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="es_representado" 
                                           id="cu_es_representado" onchange="toggleRepresentante()">
                                    <label class="form-check-label" for="cu_es_representado">
                                        Es representado por otro votante
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" id="representante_section" style="display: none;">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="cu_representante_id" class="form-label">Representante</label>
                                    <select class="form-select" name="representante_id" id="cu_representante_id">
                                        <option value="">Seleccione un representante...</option>
                                        <?php if (isset($availableRepresentantes)): ?>
                                            <?php foreach ($availableRepresentantes as $rep): ?>
                                                <option value="<?php echo $rep['id']; ?>">
                                                    <?php echo htmlspecialchars($rep['nombre'] . ' ' . $rep['apellido']); ?>
                                                    (<?php echo htmlspecialchars($rep['cedula']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos específicos para OPERADOR -->
                    <div id="operador_fields" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            El operador podrá registrar asistencia y verificar usuarios en esta asamblea.
                        </div>
                    </div>

                    <!-- Alertas dinámicas -->
                    <div id="password_alert" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="password_message">Se generará una contraseña temporal.</span>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="submitCreateUser()">
                    <i class="fas fa-user-plus me-2"></i>
                    <span id="submit_text">Crear Votante</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Función para cambiar asamblea
function changeAssembly() {
    const select = document.getElementById('assemblySelect');
    const assemblyId = select.value;
    
    if (assemblyId) {
        window.location.href = `/Asambleas/public/coordinador/participantes?asamblea=${assemblyId}`;
    } else {
        window.location.href = '/Asambleas/public/coordinador/participantes';
    }
}

// Función para cambiar el tipo de usuario
function changeUserType() {
    const isVotante = document.getElementById('type_votante').checked;
    
    // Actualizar campos hidden
    document.getElementById('tipo_usuario').value = isVotante ? 'votante' : 'operador';
    
    // Mostrar/ocultar secciones
    const votanteFields = document.getElementById('votante_fields');
    const operadorFields = document.getElementById('operador_fields');
    const passwordSection = document.getElementById('password_section');
    const passwordAlert = document.getElementById('password_alert');
    const passwordMessage = document.getElementById('password_message');
    const submitText = document.getElementById('submit_text');
    const passwordInput = document.getElementById('cu_password');
    
    if (isVotante) {
        votanteFields.style.display = 'block';
        operadorFields.style.display = 'none';
        passwordSection.style.display = 'none';
        passwordAlert.style.display = 'block';
        passwordMessage.textContent = 'Se generará una contraseña temporal que será mostrada al crear el usuario.';
        submitText.textContent = 'Crear Votante';
        
        // CORRECCIÓN: Quitar required y limpiar valor para votantes
        passwordInput.required = false;
        passwordInput.value = '';
        passwordInput.removeAttribute('required');
    } else {
        votanteFields.style.display = 'none';
        operadorFields.style.display = 'block';
        passwordSection.style.display = 'block';
        passwordAlert.style.display = 'block';
        passwordMessage.textContent = 'Debe establecer una contraseña para el operador.';
        submitText.textContent = 'Crear Operador';
        
        // CORRECCIÓN: Hacer required el password para operadores
        passwordInput.required = true;
        passwordInput.setAttribute('required', 'required');
    }
    
    // Limpiar formulario
    clearUserForm();
}

// Función para enviar el formulario - CORREGIDA
function submitCreateUser() {
    const form = document.getElementById('createUserForm');
    const isVotante = document.getElementById('type_votante').checked;
    
    // CORRECCIÓN: Rutas corregidas para coincidir con index.php
    if (isVotante) {
        form.action = '/Asambleas/public/coordinador/crear-votante';
    } else {
        form.action = '/Asambleas/public/coordinador/crear-operador';
    }
    
    console.log('Enviando formulario a:', form.action); // Debug
    console.log('Tipo de usuario:', isVotante ? 'votante' : 'operador'); // Debug
    
    // Validar formulario
    if (validateCreateUserForm()) {
        // CORRECCIÓN: Para votantes, asegurar que password no cause problemas
        if (isVotante) {
            const passwordInput = document.getElementById('cu_password');
            passwordInput.removeAttribute('required');
            passwordInput.value = ''; // Limpiar por si tiene algo
        }
        
        form.submit();
    }
}

// Función de validación - CORREGIDA
function validateCreateUserForm() {
    const nombre = document.getElementById('cu_nombre').value.trim();
    const apellido = document.getElementById('cu_apellido').value.trim();
    const cedula = document.getElementById('cu_cedula').value.trim();
    const email = document.getElementById('cu_email').value.trim();
    const isVotante = document.getElementById('type_votante').checked;
    const password = document.getElementById('cu_password').value;
    
    // Validaciones básicas
    if (!nombre || !apellido || !cedula || !email) {
        showAlert('Los campos nombre, apellido, cédula y email son obligatorios', 'error');
        return false;
    }
    
    if (!validateEmail(email)) {
        showAlert('El formato del email no es válido', 'error');
        return false;
    }
    
    if (!validateCedula(cedula)) {
        showAlert('El formato de la cédula no es válido', 'error');
        return false;
    }
    
    // CORRECCIÓN: Validaciones específicas para operador
    if (!isVotante) {
        if (!password || password.length < 6) {
            showAlert('La contraseña del operador debe tener al menos 6 caracteres', 'error');
            return false;
        }
    }
    
    // Validaciones específicas para votante
    if (isVotante) {
        const coeficiente = parseFloat(document.getElementById('cu_coeficiente').value) || 0;
        if (coeficiente < 0 || coeficiente > 1) {
            showAlert('El coeficiente debe estar entre 0 y 1', 'error');
            return false;
        }
        
        const esRepresentado = document.getElementById('cu_es_representado').checked;
        const representanteId = document.getElementById('cu_representante_id').value;
        
        if (esRepresentado && !representanteId) {
            showAlert('Debe seleccionar un representante', 'error');
            return false;
        }
        
        // Verificar que el total no exceda 1.0
        const currentInputs = document.querySelectorAll('.coeficiente-input');
        let currentTotal = 0;
        currentInputs.forEach(input => {
            currentTotal += parseFloat(input.value) || 0;
        });
        
        if (currentTotal + coeficiente > 1.0) {
            if (!confirm('El coeficiente total excederá 1.0. ¿Desea continuar?')) {
                return false;
            }
        }
    }
    
    return true;
}

// Función para limpiar el formulario
function clearUserForm() {
    const form = document.getElementById('createUserForm');
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="password"]');
    inputs.forEach(input => {
        input.value = '';
        // Limpiar atributos de validación
        input.classList.remove('is-valid', 'is-invalid');
    });
    
    const selects = form.querySelectorAll('select');
    selects.forEach(select => select.selectedIndex = 0);
    
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    
    // Ocultar sección de representante
    document.getElementById('representante_section').style.display = 'none';
    
    // Limpiar mensajes de error
    const errorAlerts = form.querySelectorAll('.alert-danger');
    errorAlerts.forEach(alert => alert.remove());
}

// Event listeners mejorados
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Cargado - Inicializando...');
    
    // Inicializar con votante por defecto
    if (document.getElementById('type_votante')) {
        document.getElementById('type_votante').checked = true;
        changeUserType();
    }
    
    // Debug de Bootstrap y modales
    if (typeof bootstrap !== 'undefined') {
        console.log('✅ Bootstrap está disponible');
    } else {
        console.error('❌ Bootstrap NO está disponible');
    }
    
    // Verificar que las rutas estén correctas
    const testRoutes = [
        '/Asambleas/public/coordinador/crear-votante',
        '/Asambleas/public/coordinador/crear-operador'
    ];
    
    console.log('Rutas que se utilizarán:', testRoutes);
});

// Event listener para limpiar formularios al cerrar modales
document.getElementById('createUserModal')?.addEventListener('hidden.bs.modal', function () {
    console.log('Modal cerrado - Limpiando formulario');
    clearUserForm();
    // Resetear a votante por defecto
    if (document.getElementById('type_votante')) {
        document.getElementById('type_votante').checked = true;
        changeUserType();
    }
});

// CORRECCIÓN ADICIONAL: Prevenir envío doble del formulario
let formSubmitting = false;

// Función para mostrar/ocultar sección de representante
function toggleRepresentante() {
    const checkbox = document.getElementById('cu_es_representado');
    const section = document.getElementById('representante_section');
    const select = document.getElementById('cu_representante_id');
    
    if (checkbox.checked) {
        section.style.display = 'block';
        select.required = true;
    } else {
        section.style.display = 'none';
        select.required = false;
        select.value = '';
    }
}

// Función para generar contraseña
function generatePassword() {
    const password = generateSecurePassword(8);
    document.getElementById('cu_password').value = password;
    showAlert(`Contraseña generada: ${password}`, 'info');
}

// Función para generar contraseña segura
function generateSecurePassword(length = 8) {
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let password = '';
    
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    return password;
}

// Función para enviar el formulario
function submitCreateUser() {
    if (formSubmitting) {
        console.log('Formulario ya se está enviando...');
        return;
    }
    
    const form = document.getElementById('createUserForm');
    const isVotante = document.getElementById('type_votante').checked;
    
    // Rutas corregidas
    if (isVotante) {
        form.action = '/Asambleas/public/coordinador/crear-votante';
    } else {
        form.action = '/Asambleas/public/coordinador/crear-operador';
    }
    
    console.log('Enviando formulario a:', form.action);
    console.log('Datos del formulario:', new FormData(form));
    
    // Validar formulario
    if (validateCreateUserForm()) {
        formSubmitting = true;
        
        // Para votantes, limpiar password
        if (isVotante) {
            const passwordInput = document.getElementById('cu_password');
            passwordInput.removeAttribute('required');
            passwordInput.value = '';
        }
        
        // Agregar indicador de carga
        const submitBtn = document.querySelector('#createUserModal .btn-primary');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
        submitBtn.disabled = true;
        
        // Enviar formulario
        form.submit();
        
        // Reset después de un tiempo (por si hay error)
        setTimeout(() => {
            formSubmitting = false;
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 5000);
    }
}

// Función de validación
function validateCreateUserForm() {
    const nombre = document.getElementById('cu_nombre').value.trim();
    const apellido = document.getElementById('cu_apellido').value.trim();
    const cedula = document.getElementById('cu_cedula').value.trim();
    const email = document.getElementById('cu_email').value.trim();
    const isVotante = document.getElementById('type_votante').checked;
    const password = document.getElementById('cu_password').value;
    
    // Validaciones básicas
    if (!nombre || !apellido || !cedula || !email) {
        showAlert('Los campos nombre, apellido, cédula y email son obligatorios', 'error');
        return false;
    }
    
    if (!validateEmail(email)) {
        showAlert('El formato del email no es válido', 'error');
        return false;
    }
    
    if (!validateCedula(cedula)) {
        showAlert('El formato de la cédula no es válido', 'error');
        return false;
    }
    
    // Validaciones específicas para operador
    if (!isVotante) {
        if (!password || password.length < 6) {
            showAlert('La contraseña del operador debe tener al menos 6 caracteres', 'error');
            return false;
        }
    }
    
    // Validaciones específicas para votante
    if (isVotante) {
        const coeficiente = parseFloat(document.getElementById('cu_coeficiente').value) || 0;
        if (coeficiente < 0 || coeficiente > 1) {
            showAlert('El coeficiente debe estar entre 0 y 1', 'error');
            return false;
        }
        
        const esRepresentado = document.getElementById('cu_es_representado').checked;
        const representanteId = document.getElementById('cu_representante_id').value;
        
        if (esRepresentado && !representanteId) {
            showAlert('Debe seleccionar un representante', 'error');
            return false;
        }
        
        // Verificar que el total no exceda 1.0
        const currentInputs = document.querySelectorAll('.coeficiente-input');
        let currentTotal = 0;
        currentInputs.forEach(input => {
            currentTotal += parseFloat(input.value) || 0;
        });
        
        if (currentTotal + coeficiente > 1.0) {
            if (!confirm('El coeficiente total excederá 1.0. ¿Desea continuar?')) {
                return false;
            }
        }
    }
    
    return true;
}

// Función para actualizar coeficiente
function updateCoeficiente(userId) {
    const input = document.querySelector(`[data-user-id="${userId}"]`);
    const newValue = parseFloat(input.value) || 0;
    
    const data = {
        asamblea_id: <?php echo isset($assembly) ? $assembly['id'] : 0; ?>,
        usuario_id: userId,
        coeficiente: newValue
    };
    
    fetch('/Asambleas/public/coordinador/actualizar-coeficiente', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => {
        if (response.ok) {
            showAlert('Coeficiente actualizado correctamente', 'success');
            updateCoeficienteTotals();
        } else {
            showAlert('Error al actualizar coeficiente', 'error');
        }
    })
    .catch(error => {
        showAlert('Error de conexión', 'error');
    });
}

// Función para editar participante
function editParticipant(userId) {
    // Implementar modal de edición
    console.log('Editar participante:', userId);
}

// Función para remover participante
function removeParticipant(userId) {
    const row = document.querySelector(`tr[data-participant-id="${userId}"]`);
    const participantName = row ? row.querySelector('strong').textContent : 'el participante';
    
    if (confirm(`¿Está seguro de remover a ${participantName} de la asamblea?`)) {
        fetch(`/Asambleas/public/coordinador/remover-participante/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                asamblea_id: <?php echo isset($assembly) ? $assembly['id'] : 0; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (row) row.remove();
                showAlert('Participante removido correctamente', 'success');
                updateStatistics();
            } else {
                showAlert(data.error || 'Error al remover participante', 'error');
            }
        })
        .catch(error => {
            showAlert('Error de conexión', 'error');
        });
    }
}

// Función para mostrar alertas
function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'info' ? 'alert-info' : 'alert-danger';
    const icon = type === 'success' ? 'check-circle' : type === 'info' ? 'info-circle' : 'exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const main = document.querySelector('main');
    const firstCard = main.querySelector('.card');
    firstCard.insertAdjacentHTML('beforebegin', alertHtml);
    
    // Auto-hide después de 3 segundos
    setTimeout(() => {
        const alert = main.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 3000);
}

// Función para validar email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Función para validar cédula
function validateCedula(cedula) {
    cedula = cedula.replace(/[\s-]/g, '');
    
    if (!/^\d+$/.test(cedula)) {
        return false;
    }
    
    if (cedula.length < 6 || cedula.length > 12) {
        return false;
    }
    
    return true;
}

// Función para actualizar totales de coeficientes
function updateCoeficienteTotals() {
    const inputs = document.querySelectorAll('.coeficiente-input');
    let total = 0;
    
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    // Actualizar display del total
    const totalDisplay = document.querySelector('.text-primary');
    if (totalDisplay) {
        totalDisplay.textContent = total.toFixed(4);
    }
    
    // Mostrar advertencia si excede 1.0
    const warningAlert = document.querySelector('.alert-warning');
    if (total > 1.0) {
        if (!warningAlert) {
            const alertHtml = `
                <div class="alert alert-warning mt-2 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    El coeficiente total excede 1.0000
                </div>
            `;
            document.querySelector('.card-body').insertAdjacentHTML('beforeend', alertHtml);
        }
    } else if (warningAlert) {
        warningAlert.remove();
    }
}

// Función para actualizar estadísticas
function updateStatistics() {
    const rows = document.querySelectorAll('#participantsTable tbody tr[data-participant-id]');
    
    let totalParticipants = rows.length;
    let votantes = 0;
    let operadores = 0;
    let propietarios = 0;
    let coeficienteTotal = 0;
    
    rows.forEach(row => {
        const roleBadge = row.querySelectorAll('.badge')[0];
        const apartamentoBadge = row.querySelectorAll('.badge')[1];
        const coeficienteInput = row.querySelector('.coeficiente-input');
        
        if (roleBadge) {
            const role = roleBadge.textContent.toLowerCase();
            if (role.includes('votante')) votantes++;
            if (role.includes('operador')) operadores++;
        }
        
        if (apartamentoBadge && apartamentoBadge.textContent !== 'N/A') {
            propietarios++;
        }
        
        if (coeficienteInput) {
            coeficienteTotal += parseFloat(coeficienteInput.value) || 0;
        }
    });
    
    // Actualizar las tarjetas de estadísticas
    const statsCards = document.querySelectorAll('.row.mb-4 .card h4');
    if (statsCards.length >= 6) {
        statsCards[0].textContent = totalParticipants;
        statsCards[1].textContent = votantes;
        statsCards[2].textContent = operadores;
        statsCards[3].textContent = propietarios;
        statsCards[4].textContent = coeficienteTotal.toFixed(4);
    }
}

// Auto-completar coeficiente al seleccionar usuario
document.getElementById('usuario_id')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const coeficiente = selectedOption.getAttribute('data-coeficiente') || 0;
    document.getElementById('coeficiente').value = coeficiente;
});

// Validar formulario de agregar participante
document.getElementById('addParticipantForm')?.addEventListener('submit', function(e) {
    const userId = document.getElementById('usuario_id').value;
    const coeficiente = parseFloat(document.getElementById('coeficiente').value);
    
    if (!userId) {
        e.preventDefault();
        showAlert('Debe seleccionar un usuario', 'error');
        return;
    }
    
    if (coeficiente < 0 || coeficiente > 1) {
        e.preventDefault();
        showAlert('El coeficiente debe estar entre 0 y 1', 'error');
        return;
    }
    
    // Verificar que el total no exceda 1.0
    const currentInputs = document.querySelectorAll('.coeficiente-input');
    let currentTotal = 0;
    currentInputs.forEach(input => {
        currentTotal += parseFloat(input.value) || 0;
    });
    
    if (currentTotal + coeficiente > 1.0) {
        if (!confirm('El coeficiente total excederá 1.0. ¿Desea continuar?')) {
            e.preventDefault();
            return;
        }
    }
});

// Búsqueda de participantes
document.getElementById('searchParticipant')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#participantsTable tbody tr[data-participant-id]');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const searchableText = [
            row.querySelector('strong')?.textContent || '',
            row.querySelector('.text-muted')?.textContent || '',
            row.querySelectorAll('.badge')[0]?.textContent || '',
            row.querySelectorAll('.badge')[1]?.textContent || ''
        ].join(' ').toLowerCase();
        
        if (searchableText.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    updateSearchResults(visibleCount, rows.length);
});

// Función para actualizar resultados de búsqueda
function updateSearchResults(visible, total) {
    let messageRow = document.getElementById('searchResultsMessage');
    
    if (visible === 0 && total > 0) {
        if (!messageRow) {
            messageRow = document.createElement('tr');
            messageRow.id = 'searchResultsMessage';
            messageRow.innerHTML = `
                <td colspan="8" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-search fa-2x mb-3"></i>
                        <p>No se encontraron participantes que coincidan con la búsqueda</p>
                    </div>
                </td>
            `;
            document.querySelector('#participantsTable tbody').appendChild(messageRow);
        }
    } else if (messageRow) {
        messageRow.remove();
    }
}

// Event listeners para limpiar formularios al cerrar modales
document.getElementById('createUserModal').addEventListener('hidden.bs.modal', function () {
    clearUserForm();
    // Resetear a votante por defecto
    document.getElementById('type_votante').checked = true;
    changeUserType();
});

// Inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    changeUserType(); // Inicializar con votante por defecto
    
    // Debug de Bootstrap y modales
    console.log('DOM Cargado');
    
    if (typeof bootstrap !== 'undefined') {
        console.log('✅ Bootstrap está disponible');
    } else {
        console.error('❌ Bootstrap NO está disponible');
    }
    
    // Verificar modales
    const modals = ['addParticipantModal', 'createUserModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            console.log(`✅ Modal ${modalId} encontrado`);
        } else {
            console.error(`❌ Modal ${modalId} NO encontrado`);
        }
    });
});
if (typeof bootstrap !== 'undefined') {
    console.log('✅ Bootstrap está disponible:', bootstrap.Modal);
} else {
    console.error('❌ Bootstrap NO está disponible');
}

</script>
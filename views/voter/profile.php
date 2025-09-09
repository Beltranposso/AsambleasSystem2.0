<?php
// views/voter/profile.php
$pageTitle = 'Mi Perfil - Votante';
include '../views/layouts/header.php';
?>

<div class="main-content">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-user-circle"></i> Mi Perfil</h1>
            <p>Gestiona tu información personal y configuración de cuenta</p>
        </div>
        <div class="header-actions">
            <a href="/votante/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="profile-container">
        <!-- Información Personal -->
        <div class="profile-section">
            <div class="section-header">
                <h2><i class="fas fa-user"></i> Información Personal</h2>
                <button class="btn btn-outline" id="editPersonalInfo">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
            
            <div class="profile-card">
                <div class="avatar-section">
                    <div class="avatar-large">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="avatar-info">
                        <h3><?php echo htmlspecialchars($voterInfo['nombres'] . ' ' . $voterInfo['apellidos']); ?></h3>
                        <p>ID: <?php echo htmlspecialchars($voterInfo['cedula']); ?></p>
                        <span class="user-type">
                            <i class="fas fa-home"></i>
                            Propietario
                        </span>
                    </div>
                </div>

                <form id="personalInfoForm" class="profile-form" method="post" action="/votante/actualizar-perfil">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombres">Nombres</label>
                            <input type="text" id="nombres" name="nombres" 
                                   value="<?php echo htmlspecialchars($voterInfo['nombres']); ?>" 
                                   readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="apellidos">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" 
                                   value="<?php echo htmlspecialchars($voterInfo['apellidos']); ?>" 
                                   readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="cedula">Cédula</label>
                            <input type="text" id="cedula" name="cedula" 
                                   value="<?php echo htmlspecialchars($voterInfo['cedula']); ?>" 
                                   readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($voterInfo['email']); ?>" 
                                   readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono" 
                                   value="<?php echo htmlspecialchars($voterInfo['telefono'] ?? ''); ?>" 
                                   readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_registro">Fecha de Registro</label>
                            <input type="text" id="fecha_registro" 
                                   value="<?php echo date('d/m/Y', strtotime($voterInfo['created_at'] ?? 'now')); ?>" 
                                   readonly>
                        </div>
                    </div>
                    
                    <div class="form-actions" style="display: none;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelEdit">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Estadísticas de Participación -->
        <div class="profile-section">
            <div class="section-header">
                <h2><i class="fas fa-chart-bar"></i> Mi Participación</h2>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($myAssemblies ?? []); ?></h3>
                        <p>Propiedades</p>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($myVotings ?? []); ?></h3>
                        <p>Votaciones Participadas</p>
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($voterInfo['coeficiente_participacion'] ?? 0, 1); ?>%</h3>
                        <p>Coeficiente Total</p>
                    </div>
                </div>
                
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo date('Y') - date('Y', strtotime($voterInfo['created_at'] ?? 'now')); ?></h3>
                        <p>Años de Membresía</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mis Propiedades Detalladas -->
        <div class="profile-section">
            <div class="section-header">
                <h2><i class="fas fa-home"></i> Mis Propiedades</h2>
            </div>
            
            <div class="properties-detailed">
                <?php if (!empty($myAssemblies)): ?>
                    <?php foreach ($myAssemblies as $property): ?>
                        <div class="property-card">
                            <div class="property-header">
                                <h4><?php echo htmlspecialchars($property['descripcion'] ?? 'Propiedad'); ?></h4>
                                <span class="property-status active">Activa</span>
                            </div>
                            <div class="property-details">
                                <div class="detail-item">
                                    <span class="detail-label">Conjunto:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($property['conjunto_nombre'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Coeficiente:</span>
                                    <span class="detail-value"><?php echo number_format($property['coeficiente'] ?? 0, 2); ?>%</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Tipo:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($property['tipo_propiedad'] ?? 'Apartamento'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Estado de Pagos:</span>
                                    <span class="detail-value status-badge <?php echo ($property['al_dia'] ?? true) ? 'success' : 'danger'; ?>">
                                        <?php echo ($property['al_dia'] ?? true) ? 'Al día' : 'En mora'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-home fa-3x"></i>
                        <h3>No hay propiedades registradas</h3>
                        <p>Contacta al administrador para registrar tus propiedades</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="profile-section">
            <div class="section-header">
                <h2><i class="fas fa-lock"></i> Seguridad</h2>
            </div>
            
            <div class="security-card">
                <form id="changePasswordForm" method="post" action="/votante/cambiar-password">
                    <div class="form-group">
                        <label for="current_password">Contraseña Actual</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small class="form-text">Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Nueva Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.main-content {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.header-content h1 {
    margin: 0 0 0.5rem 0;
    color: #1a202c;
    display: flex;
    align-items: center;
}

.header-content i {
    margin-right: 0.75rem;
    color: #3182ce;
}

.header-content p {
    margin: 0;
    color: #718096;
}

.profile-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.profile-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.section-header h2 {
    margin: 0;
    color: #1a202c;
    display: flex;
    align-items: center;
}

.section-header i {
    margin-right: 0.75rem;
    color: #3182ce;
}

.profile-card {
    background: #f7fafc;
    border-radius: 8px;
    padding: 2rem;
}

.avatar-section {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 2rem;
    color: #718096;
    font-size: 2.5rem;
}

.avatar-info h3 {
    margin: 0 0 0.5rem 0;
    color: #1a202c;
    font-size: 1.5rem;
}

.avatar-info p {
    margin: 0 0 1rem 0;
    color: #718096;
}

.user-type {
    display: inline-flex;
    align-items: center;
    background: #e2e8f0;
    color: #4a5568;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.user-type i {
    margin-right: 0.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 0.5rem;
}

.form-group input {
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: white;
    color: #1a202c;
}

.form-group input[readonly] {
    background: #f7fafc;
    color: #718096;
}

.form-text {
    color: #718096;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-actions {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    border: 2px solid;
}

.stat-card.primary { border-color: #3182ce; }
.stat-card.success { border-color: #38a169; }
.stat-card.warning { border-color: #d69e2e; }
.stat-card.info { border-color: #805ad5; }

.stat-icon {
    margin-right: 1rem;
    font-size: 2rem;
}

.stat-card.primary .stat-icon { color: #3182ce; }
.stat-card.success .stat-icon { color: #38a169; }
.stat-card.warning .stat-icon { color: #d69e2e; }
.stat-card.info .stat-icon { color: #805ad5; }

.stat-content h3 {
    margin: 0 0 0.25rem 0;
    font-size: 2rem;
    font-weight: bold;
    color: #1a202c;
}

.stat-content p {
    margin: 0;
    color: #718096;
    font-size: 0.875rem;
}

.properties-detailed {
    display: grid;
    gap: 1rem;
}

.property-card {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.5rem;
}

.property-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.property-header h4 {
    margin: 0;
    color: #1a202c;
}

.property-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.property-status.active {
    background: #c6f6d5;
    color: #22543d;
}

.property-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-label {
    font-weight: 500;
    color: #4a5568;
}

.detail-value {
    color: #1a202c;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-badge.success {
    background: #c6f6d5;
    color: #22543d;
}

.status-badge.danger {
    background: #fed7d7;
    color: #c53030;
}

.security-card {
    background: #f7fafc;
    border-radius: 8px;
    padding: 2rem;
    max-width: 500px;
}

.btn {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.btn i {
    margin-right: 0.5rem;
}

.btn-primary {
    background: #3182ce;
    color: white;
}

.btn-primary:hover {
    background: #2c5aa0;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
}

.btn-outline {
    background: transparent;
    color: #3182ce;
    border: 1px solid #3182ce;
}

.btn-outline:hover {
    background: #3182ce;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #718096;
}

.empty-state i {
    color: #cbd5e0;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #4a5568;
    margin: 1rem 0 0.5rem 0;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .property-details {
        grid-template-columns: 1fr;
    }
    
    .detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .avatar-section {
        flex-direction: column;
        text-align: center;
    }
    
    .avatar-large {
        margin-right: 0;
        margin-bottom: 1rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editPersonalInfo');
    const cancelBtn = document.getElementById('cancelEdit');
    const form = document.getElementById('personalInfoForm');
    const formActions = form.querySelector('.form-actions');
    const inputs = form.querySelectorAll('input[readonly]');
    
    editBtn.addEventListener('click', function() {
        // Hacer editables los campos permitidos
        inputs.forEach(input => {
            if (input.id !== 'cedula' && input.id !== 'fecha_registro') {
                input.removeAttribute('readonly');
                input.style.background = 'white';
                input.style.color = '#1a202c';
            }
        });
        
        editBtn.style.display = 'none';
        formActions.style.display = 'flex';
    });
    
    cancelBtn.addEventListener('click', function() {
        // Volver a solo lectura
        inputs.forEach(input => {
            if (input.id !== 'cedula' && input.id !== 'fecha_registro') {
                input.setAttribute('readonly', true);
                input.style.background = '#f7fafc';
                input.style.color = '#718096';
            }
        });
        
        editBtn.style.display = 'inline-flex';
        formActions.style.display = 'none';
        
        // Restaurar valores originales
        form.reset();
    });
    
    // Validación de contraseñas
    const passwordForm = document.getElementById('changePasswordForm');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    passwordForm.addEventListener('submit', function(e) {
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return false;
        }
        
        if (newPassword.value.length < 8) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 8 caracteres');
            return false;
        }
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>

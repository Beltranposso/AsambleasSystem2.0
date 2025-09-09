<?php
$pageTitle = 'Dashboard - Votante';
require_once '../views/layouts/header.php';
?>

<div class="main-content">
    <!-- Header de Bienvenida -->
    <div class="welcome-section">
        <h1 class="welcome-title">Bienvenido, <?php echo htmlspecialchars(($voterInfo['nombres'] ?? $voterInfo['nombre'] ?? '') . ' ' . ($voterInfo['apellidos'] ?? $voterInfo['apellido'] ?? '')); ?></h1>
        <p class="welcome-subtitle">Gestiona tu participación en la asamblea y revisa las votaciones pendientes</p>
    </div>

    <div class="dashboard-grid">
        <!-- Información del Votante -->
        <div class="voter-card">
            <div class="voter-avatar">
                <div class="avatar-circle">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <div class="voter-info">
                <h3><?php echo htmlspecialchars(($voterInfo['nombres'] ?? $voterInfo['nombre'] ?? '') . ' ' . ($voterInfo['apellidos'] ?? $voterInfo['apellido'] ?? '')); ?></h3>
                <p class="voter-email"><?php echo htmlspecialchars($voterInfo['email'] ?? ''); ?></p>
                <span class="voter-badge">
                    <i class="fas fa-home"></i>
                    Propietario
                </span>
            </div>
            
            <!-- Estadísticas del Votante -->
            <div class="voter-stats">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-content">
                        <h4>Poder de Voto</h4>
                        <span class="stat-number"><?php echo number_format($voterInfo['coeficiente_participacion'] ?? 0, 0); ?></span>
                    </div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-content">
                        <h4>Propiedades</h4>
                        <span class="stat-number"><?php echo count($myAssemblies ?? []); ?></span>
                    </div>
                </div>
                
                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <h4>Coeficiente</h4>
                        <span class="stat-number"><?php echo number_format($voterInfo['coeficiente_participacion'] ?? 0, 1); ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="quick-actions">
            <h3>Acciones Rápidas</h3>
            <div class="action-buttons">
                <a href="/votante/perfil" class="action-btn">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar Datos
                </a>
                <a href="#votaciones-pendientes" class="action-btn">
                    <i class="fas fa-vote-yea"></i>
                    Ver Votaciones
                </a>
                <a href="/votante/documentos" class="action-btn">
                    <i class="fas fa-file-alt"></i>
                    Ver Documentos
                </a>
            </div>
        </div>
    </div>

    <!-- Mis Propiedades -->
    <div class="section-card">
        <div class="section-header">
            <h2><i class="fas fa-clipboard-list"></i> Mis Propiedades (<?php echo count($myAssemblies ?? []); ?>)</h2>
        </div>
        <div class="properties-list">
            <?php if (!empty($myAssemblies)): ?>
                <?php foreach ($myAssemblies as $property): ?>
                    <div class="property-item">
                        <div class="property-info">
                            <h4><?php echo htmlspecialchars($property['apartamento'] ?? $property['descripcion'] ?? 'Propiedad'); ?></h4>
                            <p class="property-details">
                                Conjunto: <?php echo htmlspecialchars($property['conjunto_nombre'] ?? 'N/A'); ?>
                                <?php if (!empty($property['apartamento'])): ?>
                                    - Apartamento: <?php echo htmlspecialchars($property['apartamento']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="property-status">
                            <span class="status-badge <?php echo ($property['al_dia'] ?? 1) ? 'active' : 'inactive'; ?>">
                                <?php echo ($property['al_dia'] ?? 1) ? 'Activa' : 'En mora'; ?>
                            </span>
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

    <!-- Votaciones Disponibles -->
    <div class="section-card" id="votaciones-pendientes">
        <div class="section-header">
            <h2><i class="fas fa-vote-yea"></i> Votaciones Disponibles</h2>
            <button class="btn btn-secondary" onclick="refreshVotings()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
        <div class="votings-list">
            <?php if (!empty($availableVotings)): ?>
                <?php foreach ($availableVotings as $voting): ?>
                    <div class="voting-item">
                        <div class="voting-info">
                            <h4><?php echo htmlspecialchars($voting['titulo']); ?></h4>
                            <p class="voting-assembly"><?php echo htmlspecialchars($voting['asamblea_titulo']); ?></p>
                            <p class="voting-description"><?php echo htmlspecialchars($voting['descripcion'] ?? ''); ?></p>
                            <div class="voting-meta">
                                <span class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <?php 
                                    $tiempoRestante = '';
                                    if (!empty($voting['fecha_cierre'])) {
                                        $ahora = new DateTime();
                                        $cierre = new DateTime($voting['fecha_cierre']);
                                        $diff = $ahora->diff($cierre);
                                        if ($cierre > $ahora) {
                                            $tiempoRestante = "Cierra en " . $diff->format('%h horas %i min');
                                        } else {
                                            $tiempoRestante = "Votación cerrada";
                                        }
                                    } else {
                                        $tiempoRestante = "Sin límite de tiempo";
                                    }
                                    echo $tiempoRestante;
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="voting-actions">
                            <?php if (!empty($voting['ya_voto'])): ?>
                                <span class="voting-status voted">
                                    <i class="fas fa-check"></i> Ya votaste
                                </span>
                                <button class="btn btn-outline" onclick="showVotingInfo(<?php echo $voting['id']; ?>)">
                                    <i class="fas fa-eye"></i> Ver Resultados
                                </button>
                            <?php else: ?>
                                <span class="voting-status open">Abierta</span>
                                <button class="btn btn-primary" onclick="openVotingModal(<?php echo $voting['id']; ?>)">
                                    <i class="fas fa-vote-yea"></i> Votar Ahora
                                </button>
                                <button class="btn btn-outline" onclick="showVotingInfo(<?php echo $voting['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Info
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-vote-yea fa-3x"></i>
                    <h3>No hay votaciones disponibles</h3>
                    <p>En este momento no hay votaciones abiertas en las que puedas participar</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Votación -->
<div id="votingModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 id="votingTitle">Ejercer mi Voto</h2>
            <button class="modal-close" onclick="closeVotingModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="voting-modal-content">
                <!-- Información de la votación -->
                <div class="voting-info-section">
                    <div id="votingDetails"></div>
                    
                    <!-- Información del votante -->
                    <div class="voter-voting-info">
                        <h4>Tu Información de Voto:</h4>
                        <div class="voter-details-modal">
                            <span><strong>Votante:</strong> <?php echo htmlspecialchars(($voterInfo['nombres'] ?? $voterInfo['nombre'] ?? '') . ' ' . ($voterInfo['apellidos'] ?? $voterInfo['apellido'] ?? '')); ?></span>
                            <span><strong>Coeficiente:</strong> <?php echo number_format($voterInfo['coeficiente_participacion'] ?? 0, 2); ?>%</span>
                        </div>
                    </div>
                </div>

                <!-- Formulario de votación -->
                <form id="modalVotingForm" class="voting-form-modal">
                    <input type="hidden" id="modalVotingId" name="voting_id">
                    
                    <div class="form-header-modal">
                        <h3>Selecciona tu Opción de Voto</h3>
                        <p>Marca una sola opción. Tu voto será confidencial y no podrá ser modificado.</p>
                    </div>
                    
                    <div id="votingOptions" class="voting-options-modal">
                        <!-- Las opciones se cargarán dinámicamente aquí -->
                    </div>
                    
                    <!-- Confirmación -->
                    <div class="voting-confirmation-modal">
                        <div class="confirmation-box-modal">
                            <h4>Confirmación de Voto</h4>
                            <div class="checkbox-group">
                                <input type="checkbox" id="confirmVoteModal" required>
                                <label for="confirmVoteModal">
                                    Confirmo que he leído toda la información y estoy seguro de mi decisión de voto.
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="modal-form-actions">
                     <button type="button" class="btn btn-primary" onclick="submitFinalVote()">
    <i class="fas fa-check"></i> Confirmar Voto
</button>
                        <button type="button" class="btn btn-secondary btn-large" onclick="closeVotingModal()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Información de Votación -->
<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Información de la Votación</h2>
            <button class="modal-close" onclick="closeInfoModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="votingInfoContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Final -->
<div id="finalConfirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmación Final</h2>
        </div>
        <div class="modal-body">
            <div class="final-confirmation">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>¿Estás seguro de tu decisión?</h3>
                <div class="vote-summary">
                    <p><strong>Votación:</strong> <span id="finalVotingTitle"></span></p>
                    <p><strong>Tu opción:</strong> <span id="finalSelectedOption"></span></p>
                </div>
                <div class="warning-text">
                    <p><i class="fas fa-info-circle"></i> Esta acción es irreversible.</p>
                </div>
                <div class="modal-actions">

                    <button type="button" class="btn btn-primary" id="confirmFinalVoteModal" onclick="console.log('Confirmar Voto clicked')">
                     <i class="fas fa-check"></i> Confirmar Voto
                    </button>

                    <button type="button" class="btn btn-secondary" onclick="closeFinalConfirmModal()">
                        <i class="fas fa-arrow-left"></i> Revisar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.welcome-section {
    margin-bottom: 2rem;
}

.welcome-title {
    font-size: 2rem;
    color: #1a202c;
    margin-bottom: 0.5rem;
}

.welcome-subtitle {
    color: #718096;
    font-size: 1.1rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.voter-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}

.voter-avatar {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: #718096;
    font-size: 1.5rem;
}

.voter-info h3 {
    margin: 0 0 0.25rem 0;
    color: #1a202c;
    font-size: 1.25rem;
}

.voter-email {
    color: #718096;
    margin: 0 0 0.5rem 0;
}

.voter-badge {
    display: inline-flex;
    align-items: center;
    background: #e2e8f0;
    color: #4a5568;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.voter-badge i {
    margin-right: 0.5rem;
}

.voter-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    border: 2px solid;
    display: flex;
    align-items: center;
}

.stat-card.blue { border-color: #3182ce; }
.stat-card.green { border-color: #38a169; }
.stat-card.purple { border-color: #805ad5; }

.stat-icon {
    margin-right: 1rem;
    font-size: 1.5rem;
}

.stat-card.blue .stat-icon { color: #3182ce; }
.stat-card.green .stat-icon { color: #38a169; }
.stat-card.purple .stat-icon { color: #805ad5; }

.stat-content h4 {
    margin: 0 0 0.25rem 0;
    font-size: 0.875rem;
    color: #718096;
    font-weight: 500;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1a202c;
}

.quick-actions {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}

.quick-actions h3 {
    margin: 0 0 1.5rem 0;
    color: #1a202c;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.action-btn {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #4a5568;
    text-decoration: none;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #edf2f7;
    border-color: #cbd5e0;
    color: #2d3748;
}

.action-btn i {
    margin-right: 0.75rem;
    color: #718096;
}

.section-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
    margin-bottom: 2rem;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
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

.property-item, .voting-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 1rem;
    transition: all 0.2s;
}

.voting-item:hover {
    border-color: #cbd5e0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.property-item:last-child, .voting-item:last-child {
    margin-bottom: 0;
}

.property-info h4, .voting-info h4 {
    margin: 0 0 0.5rem 0;
    color: #1a202c;
    font-size: 1.1rem;
}

.property-details, .voting-assembly, .voting-description {
    margin: 0 0 0.5rem 0;
    color: #718096;
    font-size: 0.875rem;
}

.voting-meta {
    display: flex;
    gap: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    color: #718096;
    font-size: 0.75rem;
}

.meta-item i {
    margin-right: 0.25rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-badge.active {
    background: #c6f6d5;
    color: #22543d;
}

.status-badge.inactive {
    background: #fed7d7;
    color: #c53030;
}

.voting-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-right: 1rem;
}

.voting-status.open {
    background: #c6f6d5;
    color: #22543d;
}

.voting-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.btn-large {
    padding: 0.75rem 1.5rem;
}

.btn i {
    margin-right: 0.5rem;
}

.btn-primary {
    background: #3182ce;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2c5aa0;
}

.btn-primary:disabled {
    background: #cbd5e0;
    color: #a0aec0;
    cursor: not-allowed;
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

/* Estilos del Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(2px);
}

.modal-content {
    background-color: white;
    margin: 2% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-large {
    max-width: 800px;
}

.modal-header {
    padding: 2rem 2rem 1rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: #1a202c;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #718096;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f7fafc;
    color: #4a5568;
}

.modal-body {
    padding: 2rem;
}

.voting-info-section {
    background: #f7fafc;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.voter-voting-info h4 {
    margin: 1rem 0 0.5rem 0;
    color: #1a202c;
}

.voter-details-modal {
    display: flex;
    gap: 2rem;
    font-size: 0.875rem;
    color: #4a5568;
}

.form-header-modal {
    margin-bottom: 2rem;
    text-align: center;
}

.form-header-modal h3 {
    margin: 0 0 0.5rem 0;
    color: #1a202c;
}

.form-header-modal p {
    margin: 0;
    color: #718096;
    font-size: 0.875rem;
}

.voting-options-modal {
    margin-bottom: 2rem;
}

.voting-option-modal {
    margin-bottom: 1rem;
}

.voting-option-modal input[type="radio"] {
    display: none;
}

.option-label-modal {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.option-label-modal:hover {
    border-color: #cbd5e0;
    background: #f7fafc;
}

.voting-option-modal input[type="radio"]:checked + .option-label-modal {
    border-color: #3182ce;
    background: #ebf8ff;
}

.option-content-modal {
    display: flex;
    align-items: center;
    flex: 1;
}

.option-number-modal {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #4a5568;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 1rem;
    flex-shrink: 0;
}

.voting-option-modal input[type="radio"]:checked + .option-label-modal .option-number-modal {
    background: #3182ce;
    color: white;
}

.option-text-modal h4 {
    margin: 0 0 0.25rem 0;
    color: #1a202c;
}

.option-text-modal p {
    margin: 0;
    color: #718096;
    font-size: 0.875rem;
}

.option-check-modal {
    width: 20px;
    height: 20px;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    transition: all 0.2s;
}

.voting-option-modal input[type="radio"]:checked + .option-label-modal .option-check-modal {
    border-color: #3182ce;
    background: #3182ce;
    color: white;
}

.voting-confirmation-modal {
    background: #fffbf0;
    border: 1px solid #f6d55c;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.confirmation-box-modal h4 {
    margin: 0 0 1rem 0;
    color: #1a202c;
}

.checkbox-group {
    display: flex;
    align-items: flex-start;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 0.75rem;
    margin-top: 0.25rem;
}

.checkbox-group label {
    color: #4a5568;
    line-height: 1.5;
    cursor: pointer;
    font-size: 0.875rem;
}

.modal-form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.final-confirmation {
    text-align: center;
}

.warning-icon {
    font-size: 3rem;
    color: #d69e2e;
    margin-bottom: 1rem;
}

.final-confirmation h3 {
    margin: 0 0 1.5rem 0;
    color: #1a202c;
}

.vote-summary {
    background: #f7fafc;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    text-align: left;
}

.vote-summary p {
    margin: 0.5rem 0;
    color: #4a5568;
}

.warning-text {
    background: #fffbf0;
    border: 1px solid #f6d55c;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 2rem;
}

.warning-text p {
    margin: 0;
    color: #744210;
    font-size: 0.875rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .voter-stats {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
    
    .stat-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .property-item, .voting-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .voting-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .modal-content {
        width: 95%;
        margin: 5% auto;
    }
    
    .voter-details-modal {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .modal-form-actions {
        flex-direction: column;
    }
    
    .modal-actions {
        flex-direction: column;
    }
}
</style>

<script>
    let currentVotingId = null;
    let currentVotingData = null;
    let isProcessingVote = false;
document.addEventListener('DOMContentLoaded', function() {
    
    window.APP_BASE_URL = window.location.origin + '/Asambleas/public';
    
    window.buildUrl = function(path) {
        return window.APP_BASE_URL + path;
    };
    
    window.submitFinalVote = async function() {
            console.log('submitFinalVote ejecutándose!');  // ← AGREGAR ESTO

        if (isProcessingVote) {
            return;
        }

       window.isProcessingVote = true;
        
        const submitButton = document.getElementById('confirmFinalVoteModal');
        if (!submitButton) {
            window.isProcessingVote = false;
            return;
        }
        
        const originalText = submitButton.innerHTML;
        const modalForm = document.getElementById('modalVotingForm');
        
        if (!modalForm) {
            window.isProcessingVote = false;
            return;
        }
        
        const formData = new FormData(modalForm);
        const votingId = formData.get('voting_id');
        const optionId = formData.get('option_id');
        
        if (!votingId || !optionId) {
            showErrorMessage('Por favor selecciona una opción');
            isProcessingVote = false;
            return;
        }
        
        try {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> la cagaste...';
            
            const submitUrl = window.buildUrl('/votante/procesar-voto');
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000);
            
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                },
                body: formData,
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            const responseText = await response.text();
            const contentType = response.headers.get('content-type') || '';
            
            if (contentType.includes('application/json')) {
                const result = JSON.parse(responseText);
                
                if (result.success === true) {
                    closeVotingModal();
                    closeFinalConfirmModal();
                    showSuccessMessage('¡Tu voto ha sido registrado exitosamente!');
                    
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                    
                    return;
                } else {
                    throw new Error(result.error || 'Error desconocido del servidor');
                }
                
            } else if (response.ok) {
                closeVotingModal();
                closeFinalConfirmModal();
                showSuccessMessage('¡Tu voto ha sido registrado exitosamente!');
                
                setTimeout(() => {
                    location.reload();
                }, 2000);
                
                return;
            } else {
                throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
            }
            
        } catch (error) {
            let userMessage = 'Error al enviar el voto';
            
            if (error.name === 'AbortError') {
                userMessage = 'Tiempo de espera agotado. El servidor tardó demasiado en responder.';
            } else if (error.message.includes('Failed to fetch')) {
                userMessage = 'Error de conexión. Verifica tu conexión a internet.';
            } else if (error.message.includes('NetworkError')) {
                userMessage = 'Error de red. Verifica tu conexión.';
            } else if (error.message.includes('ya has votado')) {
                userMessage = 'Ya has votado en esta votación';
            } else {
                userMessage = error.message;
            }
            
            showErrorMessage(userMessage);
            
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
            
            isProcessingVote = false;
        }
    };
    
    function handleVoteSubmit(e) {
        e.preventDefault();
        
        const selectedOption = document.querySelector('input[name="option_id"]:checked');
        
        if (!selectedOption) {
            showErrorMessage('Por favor selecciona una opción');
            return;
        }
        
        const optionText = selectedOption.parentNode.querySelector('.option-text-modal h4').textContent;
        
        document.getElementById('finalVotingTitle').textContent = currentVotingData.voting.titulo;
        document.getElementById('finalSelectedOption').textContent = optionText;
        
        document.getElementById('finalConfirmModal').style.display = 'block';
    }
    
    const modalVotingForm = document.getElementById('modalVotingForm');
    if (modalVotingForm) {
        modalVotingForm.addEventListener('submit', handleVoteSubmit);
    }
    
    const confirmVoteCheckbox = document.getElementById('confirmVoteModal');
    if (confirmVoteCheckbox) {
        confirmVoteCheckbox.addEventListener('change', validateModalForm);
    }
    
    const confirmFinalBtn = document.getElementById('confirmFinalVoteModal');
    if (confirmFinalBtn) {
        const newBtn = confirmFinalBtn.cloneNode(true);
        confirmFinalBtn.parentNode.replaceChild(newBtn, confirmFinalBtn);
        
        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            submitFinalVote();
        });
    }
    
    window.addEventListener('click', function(e) {
        if (e.target.id === 'votingModal') closeVotingModal();
        if (e.target.id === 'infoModal') closeInfoModal();
        if (e.target.id === 'finalConfirmModal') closeFinalConfirmModal();
    });
});

async function openVotingModal(votingId) {
    currentVotingId = votingId;
    
    try {
        document.getElementById('votingModal').style.display = 'block';
        document.getElementById('votingOptions').innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Cargando opciones de votación...</p>
            </div>
        `;
        
        const voteStatusData = new FormData();
        voteStatusData.append('voting_id', votingId);
        
        const statusResponse = await fetch(window.buildUrl('/votante/ajax/check-vote-status'), {
            method: 'POST',
            body: voteStatusData
        });
        
        if (statusResponse.ok) {
            const statusData = await statusResponse.json();
            
            if (statusData.hasVoted) {
                document.getElementById('votingOptions').innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: #22543d; background: #c6f6d5; border-radius: 8px;">
                        <i class="fas fa-check-circle fa-3x" style="margin-bottom: 1rem;"></i>
                        <h3>Ya has votado en esta votación</h3>
                        <p><strong>Tu voto:</strong> ${statusData.vote?.opcion || 'Registrado'}</p>
                        <button class="btn btn-secondary" onclick="closeVotingModal()" style="margin-top: 1rem;">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                `;
                return;
            }
            
            if (!statusData.canVote) {
                document.getElementById('votingOptions').innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: #c53030; background: #fed7d7; border-radius: 8px;">
                        <i class="fas fa-exclamation-triangle fa-3x" style="margin-bottom: 1rem;"></i>
                        <h3>No puedes votar en esta votación</h3>
                        <button class="btn btn-secondary" onclick="closeVotingModal()" style="margin-top: 1rem;">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                `;
                return;
            }
        }
        
        const formData = new FormData();
        formData.append('voting_id', votingId);
        
        const response = await fetch(window.buildUrl('/votante/ajax/voting-details'), {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const responseText = await response.text();
        const contentType = response.headers.get('content-type');
        
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Respuesta no es JSON válido');
        }
        
        const data = JSON.parse(responseText);
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        if (!data.success) {
            throw new Error('Respuesta no exitosa');
        }
        
       window.currentVotingData = data;
        updateVotingModal(data);
        
    } catch (error) {
        document.getElementById('votingOptions').innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #c53030;">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
                <p><strong>Error:</strong> ${error.message}</p>
                <button class="btn btn-secondary" onclick="closeVotingModal()">Cerrar</button>
                <button class="btn btn-outline" onclick="openVotingModal(${votingId})" style="margin-left: 0.5rem;">Reintentar</button>
            </div>
        `;
    }
}

function updateVotingModal(data) {
    const { voting, options } = data;
    
    document.getElementById('votingTitle').textContent = voting.titulo;
    document.getElementById('modalVotingId').value = voting.id;
    
    document.getElementById('votingDetails').innerHTML = `
        <h4>${voting.titulo}</h4>
        <p><strong>Asamblea:</strong> ${voting.asamblea_titulo}</p>
        ${voting.descripcion ? `<p><strong>Descripción:</strong> ${voting.descripcion}</p>` : ''}
        <p><strong>Estado:</strong> <span style="color: #22543d; font-weight: 500;">Abierta</span></p>
    `;
    
    let optionsHtml = '';
    if (options && options.length > 0) {
        options.forEach((option, index) => {
            optionsHtml += `
                <div class="voting-option-modal">
                    <input type="radio" id="modalOption_${option.id}" name="option_id" value="${option.id}" required>
                    <label for="modalOption_${option.id}" class="option-label-modal">
                        <div class="option-content-modal">
                            <div class="option-number-modal">${String.fromCharCode(65 + index)}</div>
                            <div class="option-text-modal">
                                <h4>${option.opcion}</h4>
                                ${option.descripcion ? `<p>${option.descripcion}</p>` : ''}
                            </div>
                        </div>
                        <div class="option-check-modal">
                            <i class="fas fa-check"></i>
                        </div>
                    </label>
                </div>
            `;
        });
    }
    
    document.getElementById('votingOptions').innerHTML = optionsHtml;
    
    const radioButtons = document.querySelectorAll('input[name="option_id"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', validateModalForm);
    });
    
    validateModalForm();
}
async function showVotingInfo(votingId) {
    console.log('📊 Mostrando información de votación ID:', votingId);
    
    try {
        // Mostrar el modal inmediatamente con loading
        document.getElementById('infoModal').style.display = 'block';
        document.getElementById('votingInfoContent').innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin fa-2x" style="color: #3182ce; margin-bottom: 1rem;"></i>
                <p>Cargando información de la votación...</p>
            </div>
        `;
        
        // Obtener información de la votación
        const formData = new FormData();
        formData.append('voting_id', votingId);
        
        const response = await fetch(window.buildUrl('/votante/ajax/voting-details'), {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const responseText = await response.text();
        const contentType = response.headers.get('content-type');
        
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Respuesta no es JSON válido');
        }
        
        const data = JSON.parse(responseText);
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        if (!data.success) {
            throw new Error('No se pudo obtener la información');
        }
        
        // Verificar estado del voto del usuario
        const voteStatusData = new FormData();
        voteStatusData.append('voting_id', votingId);
        
        const statusResponse = await fetch(window.buildUrl('/votante/ajax/check-vote-status'), {
            method: 'POST',
            body: voteStatusData
        });
        
        let voteStatus = null;
        if (statusResponse.ok) {
            voteStatus = await statusResponse.json();
        }
        
        // Mostrar la información completa
        displayVotingInfo(data, voteStatus);
        
    } catch (error) {
        console.error('Error obteniendo información:', error);
        document.getElementById('votingInfoContent').innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #c53030;">
                <i class="fas fa-exclamation-triangle fa-2x" style="margin-bottom: 1rem;"></i>
                <h3>Error al cargar información</h3>
                <p><strong>Detalles:</strong> ${error.message}</p>
                <button class="btn btn-secondary" onclick="closeInfoModal()" style="margin-top: 1rem;">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button class="btn btn-outline" onclick="showVotingInfo(${votingId})" style="margin-top: 1rem; margin-left: 0.5rem;">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `;
    }
}

// 🎨 FUNCIÓN PARA MOSTRAR LA INFORMACIÓN EN EL MODAL
// 🎨 FUNCIÓN ACTUALIZADA PARA MOSTRAR LA INFORMACIÓN EN EL MODAL
function displayVotingInfo(data, voteStatus) {
    const { voting, options, statistics } = data;
    
    // Calcular estadísticas mejoradas
    let totalVotes = 0;
    let totalCoeficiente = 0;
    let optionsWithVotes = [];
    
    if (options && options.length > 0) {
        options.forEach(option => {
            const votes = parseInt(option.votos) || 0;
            const coeficiente = parseFloat(option.coeficiente_total) || 0;
            const poder = parseFloat(option.poder_voto) || 0;
            
            totalVotes += votes;
            totalCoeficiente += coeficiente;
            
            optionsWithVotes.push({
                ...option,
                votes: votes,
                coeficiente: coeficiente,
                poder: poder
            });
        });
    }
    
    // Determinar estado de la votación
    let votingStatusBadge = '';
    let votingStatusText = '';
    
    if (voting.estado === 'abierta') {
        if (voting.fecha_cierre) {
            const now = new Date();
            const closeDate = new Date(voting.fecha_cierre);
            if (now > closeDate) {
                votingStatusBadge = '<span class="status-badge inactive">Cerrada (Expirada)</span>';
                votingStatusText = 'Esta votación ha expirado';
            } else {
                votingStatusBadge = '<span class="status-badge active">Abierta</span>';
                votingStatusText = 'Votación activa - Puedes participar';
            }
        } else {
            votingStatusBadge = '<span class="status-badge active">Abierta</span>';
            votingStatusText = 'Votación activa - Sin límite de tiempo';
        }
    } else {
        votingStatusBadge = '<span class="status-badge inactive">Cerrada</span>';
        votingStatusText = 'Esta votación ha sido cerrada';
    }
    
    // Estado del voto del usuario
    let userVoteSection = '';
    if (voteStatus && voteStatus.hasVoted) {
        userVoteSection = `
            <div class="user-vote-status" style="background: #c6f6d5; border: 1px solid #38a169; border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                <h4 style="color: #22543d; margin: 0 0 0.5rem 0;">
                    <i class="fas fa-check-circle"></i> Tu Participación
                </h4>
                <p style="margin: 0; color: #22543d;">
                    <strong>Ya has votado:</strong> ${voteStatus.vote?.opcion || 'Voto registrado'}
                </p>
                <small style="color: #2f855a;">
                    Tu voto fue registrado exitosamente y es confidencial.
                </small>
            </div>
        `;
    } else if (voteStatus && !voteStatus.canVote) {
        userVoteSection = `
            <div class="user-vote-status" style="background: #fed7d7; border: 1px solid #e53e3e; border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                <h4 style="color: #c53030; margin: 0 0 0.5rem 0;">
                    <i class="fas fa-exclamation-triangle"></i> Sin Acceso
                </h4>
                <p style="margin: 0; color: #c53030;">
                    No puedes participar en esta votación.
                </p>
            </div>
        `;
    } else if (voting.estado === 'abierta') {
        userVoteSection = `
            <div class="user-vote-status" style="background: #e6fffa; border: 1px solid #38b2ac; border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                <h4 style="color: #234e52; margin: 0 0 0.5rem 0;">
                    <i class="fas fa-vote-yea"></i> Puedes Votar
                </h4>
                <p style="margin: 0; color: #234e52;">
                    Aún no has participado en esta votación.
                </p>
                <button class="btn btn-primary" onclick="closeInfoModal(); openVotingModal(${voting.id});" style="margin-top: 0.5rem;">
                    <i class="fas fa-vote-yea"></i> Votar Ahora
                </button>
            </div>
        `;
    }
    
    // Sección de opciones con resultados MEJORADA
    let optionsSection = '';
    if (optionsWithVotes.length > 0) {
        optionsSection = `
            <div class="voting-options-info">
                <h4><i class="fas fa-chart-bar"></i> Resultados de Votación</h4>
                <div class="options-list">
        `;
        
        optionsWithVotes.forEach((option, index) => {
            const percentageVotos = totalVotes > 0 ? ((option.votes / totalVotes) * 100).toFixed(1) : 0;
            const percentageCoeficiente = totalCoeficiente > 0 ? ((option.coeficiente / totalCoeficiente) * 100).toFixed(1) : 0;
            const letter = String.fromCharCode(65 + index);
            
            optionsSection += `
                <div class="option-result" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background: white;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center;">
                            <span class="option-letter" style="width: 28px; height: 28px; border-radius: 50%; background: #3182ce; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 0.75rem; font-size: 0.9rem;">${letter}</span>
                            <strong style="font-size: 1.1rem; color: #1a202c;">${option.opcion}</strong>
                        </div>
                    </div>
                    
                    ${option.descripcion ? `<p style="margin: 0 0 0.75rem 0; color: #718096; font-size: 0.875rem; margin-left: 36px;">${option.descripcion}</p>` : ''}
                    
                    <!-- Estadísticas de votos -->
                    <div style="background: #f7fafc; border-radius: 6px; padding: 0.75rem; margin-bottom: 0.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                    <span style="font-size: 0.875rem; color: #4a5568;">👥 Votos</span>
                                    <span style="font-weight: bold; color: #3182ce;">${option.votes}</span>
                                </div>
                                <div class="progress-bar" style="background: #e2e8f0; border-radius: 4px; height: 6px; overflow: hidden;">
                                    <div style="background: #3182ce; height: 100%; width: ${percentageVotos}%; transition: width 0.3s ease;"></div>
                                </div>
                                <small style="color: #718096;">${percentageVotos}% del total</small>
                            </div>
                            
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                    <span style="font-size: 0.875rem; color: #4a5568;">⚖️ Poder de Voto</span>
                                    <span style="font-weight: bold; color: #805ad5;">${option.coeficiente.toFixed(2)}</span>
                                </div>
                                <div class="progress-bar" style="background: #e2e8f0; border-radius: 4px; height: 6px; overflow: hidden;">
                                    <div style="background: #805ad5; height: 100%; width: ${percentageCoeficiente}%; transition: width 0.3s ease;"></div>
                                </div>
                                <small style="color: #718096;">${percentageCoeficiente}% del total</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        optionsSection += `
                </div>
                
                <!-- Resumen general -->
                <div class="total-summary" style="background: #f7fafc; border-radius: 8px; padding: 1.5rem; margin-top: 1rem; border: 1px solid #e2e8f0;">
                    <h5 style="margin: 0 0 1rem 0; color: #1a202c;"><i class="fas fa-chart-pie"></i> Resumen de Participación</h5>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #3182ce;">${totalVotes}</div>
                            <div style="font-size: 0.875rem; color: #718096;">Total Votos</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #805ad5;">${totalCoeficiente.toFixed(2)}</div>
                            <div style="font-size: 0.875rem; color: #718096;">Poder Total</div>
                        </div>
                        ${statistics ? `
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: bold; color: #38a169;">${statistics.porcentaje_participacion || 0}%</div>
                                <div style="font-size: 0.875rem; color: #718096;">Participación</div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Información de fechas
    let dateInfo = '';
    if (voting.created_at) {
        dateInfo += `<p><strong>Creada:</strong> ${formatDate(voting.created_at)}</p>`;
    }
    if (voting.fecha_cierre) {
        dateInfo += `<p><strong>Cierra:</strong> ${formatDate(voting.fecha_cierre)}</p>`;
    }
    
    // Ensamblar todo el contenido
    const content = `
        <div class="voting-info-complete">
            <!-- Encabezado -->
            <div class="info-header" style="text-align: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                <h3 style="margin: 0 0 0.5rem 0; color: #1a202c;">${voting.titulo}</h3>
                <p style="color: #718096; margin: 0 0 1rem 0;">${voting.asamblea_titulo}</p>
                ${votingStatusBadge}
                <p style="margin: 0.5rem 0 0 0; color: #718096; font-size: 0.875rem;">${votingStatusText}</p>
            </div>
            
            <!-- Descripción -->
            ${voting.descripcion ? `
                <div class="voting-description" style="margin-bottom: 1.5rem;">
                    <h4><i class="fas fa-info-circle"></i> Descripción</h4>
                    <p style="color: #4a5568; line-height: 1.6;">${voting.descripcion}</p>
                </div>
            ` : ''}
            
            <!-- Estado del usuario -->
            ${userVoteSection}
            
            <!-- Opciones y resultados -->
            ${optionsSection}
            
            <!-- Información adicional -->
            <div class="additional-info" style="background: #f7fafc; border-radius: 8px; padding: 1rem; margin-top: 1.5rem;">
                <h4 style="margin: 0 0 0.5rem 0;"><i class="fas fa-calendar"></i> Información Adicional</h4>
                ${dateInfo}
                <p><strong>Tipo:</strong> ${voting.tipo_votacion || 'Ordinaria'}</p>
                <p><strong>Estado:</strong> ${voting.estado}</p>
            </div>
            
            <!-- Botones de acción -->
            <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <button class="btn btn-secondary" onclick="closeInfoModal()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                ${!voteStatus?.hasVoted && voteStatus?.canVote && voting.estado === 'abierta' ? `
                    <button class="btn btn-primary" onclick="closeInfoModal(); openVotingModal(${voting.id});" style="margin-left: 1rem;">
                        <i class="fas fa-vote-yea"></i> Ir a Votar
                    </button>
                ` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('votingInfoContent').innerHTML = content;
}   
function validateModalForm() {
    const optionSelected = document.querySelector('input[name="option_id"]:checked');
    const confirmChecked = document.getElementById('confirmVoteModal')?.checked;
    const submitBtn = document.getElementById('submitVoteModal');
    
    if (submitBtn) {
        submitBtn.disabled = !(optionSelected && confirmChecked);
    }
}

function refreshVotings() {
    location.reload();
}

function closeVotingModal() {
    document.getElementById('votingModal').style.display = 'none';
    window.currentVotingId = null;
    currentVotingData = null;
    
    const form = document.getElementById('modalVotingForm');
    if (form) form.reset();
    
    const checkbox = document.getElementById('confirmVoteModal');
    if (checkbox) checkbox.checked = false;
    
    const submitBtn = document.getElementById('submitVoteModal');
    if (submitBtn) submitBtn.disabled = true;
}

function closeInfoModal() {
    document.getElementById('infoModal').style.display = 'none';
}

function closeFinalConfirmModal() {
    document.getElementById('finalConfirmModal').style.display = 'none';
}

function showSuccessMessage(message) {
    const alertHtml = `
        <div class="alert alert-success" style="position: fixed; top: 20px; right: 20px; z-index: 1001; min-width: 300px; background: #c6f6d5; color: #22543d; padding: 1rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <i class="fas fa-check-circle"></i> ${message}
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.remove();
    }, 5000);
}

function showErrorMessage(message) {
    const alertHtml = `
        <div class="alert alert-error" style="position: fixed; top: 20px; right: 20px; z-index: 1001; min-width: 300px; background: #fed7d7; color: #c53030; padding: 1rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <i class="fas fa-exclamation-circle"></i> ${message}
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.remove();
    }, 8000);
}
</script>

<?php include '../views/layouts/footer.php'; ?>
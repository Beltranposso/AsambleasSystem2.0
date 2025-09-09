<?php
// views/voter/my_votings.php
$pageTitle = 'Mis Votaciones - Votante';
include '../views/layouts/header.php';
?>

<div class="main-content">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-vote-yea"></i> Mis Votaciones</h1>
            <p>Historial completo de tu participación en votaciones</p>
        </div>
        <div class="header-actions">
            <a href="/votante/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <button class="btn btn-outline" id="filterBtn">
                <i class="fas fa-filter"></i> Filtros
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-section" id="filtersSection" style="display: none;">
        <div class="filters-card">
            <h3>Filtrar Votaciones</h3>
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="dateRange">Período</label>
                    <select id="dateRange" class="filter-select">
                        <option value="">Todos los períodos</option>
                        <option value="this_year">Este año</option>
                        <option value="last_year">Año anterior</option>
                        <option value="last_6_months">Últimos 6 meses</option>
                        <option value="last_3_months">Últimos 3 meses</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="statusFilter">Estado</label>
                    <select id="statusFilter" class="filter-select">
                        <option value="">Todos los estados</option>
                        <option value="participada">Participada</option>
                        <option value="no_participada">No participada</option>
                        <option value="abierta">Abierta</option>
                        <option value="cerrada">Cerrada</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="assemblyFilter">Asamblea</label>
                    <select id="assemblyFilter" class="filter-select">
                        <option value="">Todas las asambleas</option>
                        <?php if (!empty($myAssemblies)): ?>
                            <?php foreach ($myAssemblies as $assembly): ?>
                                <option value="<?php echo $assembly['id']; ?>">
                                    <?php echo htmlspecialchars($assembly['titulo'] ?? 'Asamblea'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button class="btn btn-primary" id="applyFilters">
                        <i class="fas fa-search"></i> Aplicar
                    </button>
                    <button class="btn btn-secondary" id="clearFilters">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Participación -->
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($votings ?? []); ?></h3>
                    <p>Total Votaciones</p>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count(array_filter($votings ?? [], function($v) { return !empty($v['timestamp_voto']); })); ?></h3>
                    <p>Participadas</p>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count(array_filter($votings ?? [], function($v) { return empty($v['timestamp_voto']) && $v['estado'] == 'abierta'; })); ?></h3>
                    <p>Pendientes</p>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3><?php 
                        $total = count($votings ?? []);
                        $participated = count(array_filter($votings ?? [], function($v) { return !empty($v['timestamp_voto']); }));
                        echo $total > 0 ? number_format(($participated / $total) * 100, 1) : '0';
                    ?>%</h3>
                    <p>Participación</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Votaciones -->
    <div class="votings-section">
        <div class="section-header">
            <h2><i class="fas fa-list"></i> Historial de Votaciones</h2>
            <div class="view-options">
                <button class="view-btn active" data-view="list">
                    <i class="fas fa-list"></i>
                </button>
                <button class="view-btn" data-view="grid">
                    <i class="fas fa-th"></i>
                </button>
            </div>
        </div>

        <div class="votings-container" id="votingsContainer">
            <?php if (!empty($votings)): ?>
                <?php foreach ($votings as $voting): ?>
                    <div class="voting-card" data-status="<?php echo $voting['estado']; ?>" 
                         data-participated="<?php echo !empty($voting['timestamp_voto']) ? 'yes' : 'no'; ?>">
                        
                        <div class="voting-header">
                            <div class="voting-title">
                                <h3><?php echo htmlspecialchars($voting['titulo']); ?></h3>
                                <div class="voting-badges">
                                    <span class="status-badge <?php echo $voting['estado']; ?>">
                                        <?php echo ucfirst($voting['estado']); ?>
                                    </span>
                                    <?php if (!empty($voting['timestamp_voto'])): ?>
                                        <span class="participation-badge participated">
                                            <i class="fas fa-check"></i> Participaste
                                        </span>
                                    <?php else: ?>
                                        <span class="participation-badge not-participated">
                                            <i class="fas fa-times"></i> No participaste
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="voting-actions">
                                <?php if ($voting['estado'] == 'abierta' && empty($voting['timestamp_voto'])): ?>
                                    <a href="/votante/votar/<?php echo $voting['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-vote-yea"></i> Votar Ahora
                                    </a>
                                <?php elseif (!empty($voting['timestamp_voto'])): ?>
                                    <button class="btn btn-outline" onclick="showVoteDetails(<?php echo $voting['id']; ?>)">
                                        <i class="fas fa-eye"></i> Ver mi Voto
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-secondary" onclick="showVotingDetails(<?php echo $voting['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Detalles
                                </button>
                            </div>
                        </div>

                        <div class="voting-content">
                            <div class="voting-info">
                                <p class="voting-description">
                                    <?php echo htmlspecialchars($voting['descripcion'] ?? 'Sin descripción disponible'); ?>
                                </p>
                                
                                <div class="voting-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>
                                            <?php 
                                            if (!empty($voting['fecha_inicio'])) {
                                                echo 'Inicio: ' . date('d/m/Y H:i', strtotime($voting['fecha_inicio']));
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($voting['fecha_fin'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-calendar-times"></i>
                                            <span>Fin: <?php echo date('d/m/Y H:i', strtotime($voting['fecha_fin'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($voting['timestamp_voto'])): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Votaste: <?php echo date('d/m/Y H:i', strtotime($voting['timestamp_voto'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($voting['timestamp_voto']) && !empty($voting['opcion'])): ?>
                                <div class="vote-result">
                                    <h4>Tu Voto:</h4>
                                    <div class="vote-option">
                                        <i class="fas fa-check-circle"></i>
                                        <span><?php echo htmlspecialchars($voting['opcion']); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-vote-yea fa-3x"></i>
                    <h3>No hay votaciones registradas</h3>
                    <p>Aún no has participado en ninguna votación o no hay votaciones disponibles</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para detalles de votación -->
<div id="votingDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Detalles de la Votación</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="votingDetailsContent">
            <!-- Contenido cargado dinámicamente -->
        </div>
    </div>
</div>

<style>
.main-content {
    padding: 2rem;
    max-width: 1400px;
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

.header-actions {
    display: flex;
    gap: 1rem;
}

.filters-section {
    margin-bottom: 2rem;
}

.filters-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}

.filters-card h3 {
    margin: 0 0 1.5rem 0;
    color: #1a202c;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr) auto;
    gap: 1.5rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 0.5rem;
}

.filter-select {
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: white;
    color: #1a202c;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.stats-section {
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 2px solid;
}

.stat-card.primary { border-color: #3182ce; }
.stat-card.success { border-color: #38a169; }
.stat-card.warning { border-color: #d69e2e; }
.stat-card.info { border-color: #805ad5; }

.stat-icon {
    margin-right: 1.5rem;
    font-size: 2.5rem;
}

.stat-card.primary .stat-icon { color: #3182ce; }
.stat-card.success .stat-icon { color: #38a169; }
.stat-card.warning .stat-icon { color: #d69e2e; }
.stat-card.info .stat-icon { color: #805ad5; }

.stat-content h3 {
    margin: 0 0 0.25rem 0;
    font-size: 2.5rem;
    font-weight: bold;
    color: #1a202c;
}

.stat-content p {
    margin: 0;
    color: #718096;
    font-size: 1rem;
}

.votings-section {
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

.view-options {
    display: flex;
    gap: 0.5rem;
}

.view-btn {
    padding: 0.5rem;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #718096;
    cursor: pointer;
    transition: all 0.2s;
}

.view-btn.active,
.view-btn:hover {
    background: #3182ce;
    color: white;
    border-color: #3182ce;
}

.voting-card {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    transition: all 0.2s;
}

.voting-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #cbd5e0;
}

.voting-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.voting-title h3 {
    margin: 0 0 0.75rem 0;
    color: #1a202c;
    font-size: 1.25rem;
}

.voting-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-badge.abierta {
    background: #fed7d7;
    color: #c53030;
}

.status-badge.cerrada {
    background: #e2e8f0;
    color: #4a5568;
}

.status-badge.finalizada {
    background: #c6f6d5;
    color: #22543d;
}

.participation-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
}

.participation-badge i {
    margin-right: 0.25rem;
}

.participation-badge.participated {
    background: #c6f6d5;
    color: #22543d;
}

.participation-badge.not-participated {
    background: #fed7d7;
    color: #c53030;
}

.voting-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.voting-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.voting-description {
    color: #4a5568;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.voting-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.meta-item {
    display: flex;
    align-items: center;
    color: #718096;
    font-size: 0.875rem;
}

.meta-item i {
    margin-right: 0.5rem;
    color: #cbd5e0;
    width: 16px;
}

.vote-result {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
}

.vote-result h4 {
    margin: 0 0 1rem 0;
    color: #1a202c;
    font-size: 1rem;
}

.vote-option {
    display: flex;
    align-items: center;
    color: #38a169;
    font-weight: 500;
}

.vote-option i {
    margin-right: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1rem;
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
    padding: 4rem;
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

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 80%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
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
}

.modal-close:hover {
    color: #4a5568;
}

.modal-body {
    padding: 2rem;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .header-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .filter-actions {
        justify-content: stretch;
    }
    
    .filter-actions .btn {
        flex: 1;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .voting-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .voting-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .voting-content {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .voting-badges {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .voting-actions {
        flex-direction: column;
    }
    
    .voting-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad de filtros
    const filterBtn = document.getElementById('filterBtn');
    const filtersSection = document.getElementById('filtersSection');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    filterBtn.addEventListener('click', function() {
        filtersSection.style.display = filtersSection.style.display === 'none' ? 'block' : 'none';
    });
    
    applyFiltersBtn.addEventListener('click', function() {
        applyFilters();
    });
    
    clearFiltersBtn.addEventListener('click', function() {
        clearFilters();
    });
    
    // Funcionalidad de vistas
    const viewBtns = document.querySelectorAll('.view-btn');
    const votingsContainer = document.getElementById('votingsContainer');
    
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            viewBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const view = this.dataset.view;
            if (view === 'grid') {
                votingsContainer.classList.add('grid-view');
            } else {
                votingsContainer.classList.remove('grid-view');
            }
        });
    });
    
    // Modal
    const modal = document.getElementById('votingDetailsModal');
    const modalClose = document.querySelector('.modal-close');
    
    modalClose.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

function applyFilters() {
    const dateRange = document.getElementById('dateRange').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const assemblyFilter = document.getElementById('assemblyFilter').value;
    
    const votingCards = document.querySelectorAll('.voting-card');
    
    votingCards.forEach(card => {
        let showCard = true;
        
        // Filtro por estado
        if (statusFilter) {
            const cardStatus = card.dataset.status;
            const cardParticipated = card.dataset.participated;
            
            if (statusFilter === 'participada' && cardParticipated !== 'yes') {
                showCard = false;
            } else if (statusFilter === 'no_participada' && cardParticipated !== 'no') {
                showCard = false;
            } else if (statusFilter !== 'participada' && statusFilter !== 'no_participada' && cardStatus !== statusFilter) {
                showCard = false;
            }
        }
        
        // Aquí podrías agregar más lógica de filtrado para fechas y asambleas
        
        card.style.display = showCard ? 'block' : 'none';
    });
    
    // Ocultar filtros después de aplicar
    document.getElementById('filtersSection').style.display = 'none';
}

function clearFilters() {
    document.getElementById('dateRange').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('assemblyFilter').value = '';
    
    const votingCards = document.querySelectorAll('.voting-card');
    votingCards.forEach(card => {
        card.style.display = 'block';
    });
    
    // Ocultar filtros después de limpiar
    document.getElementById('filtersSection').style.display = 'none';
}

function showVotingDetails(votingId) {
    const modal = document.getElementById('votingDetailsModal');
    const content = document.getElementById('votingDetailsContent');
    
    // Aquí harías una llamada AJAX para obtener los detalles completos
    content.innerHTML = `
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i>
            Cargando detalles de la votación...
        </div>
    `;
    
    modal.style.display = 'block';
    
    // Simulación de carga de datos
    setTimeout(() => {
        content.innerHTML = `
            <div class="voting-detail">
                <h3>Detalles de la Votación #${votingId}</h3>
                <p>Aquí se mostrarían los detalles completos de la votación, incluyendo resultados, opciones, participación, etc.</p>
                <div class="detail-section">
                    <h4>Opciones de Votación:</h4>
                    <ul>
                        <li>Opción A - 45%</li>
                        <li>Opción B - 35%</li>
                        <li>Abstención - 20%</li>
                    </ul>
                </div>
                <div class="detail-section">
                    <h4>Estadísticas:</h4>
                    <p>Total de votos: 120</p>
                    <p>Participación: 85%</p>
                </div>
            </div>
        `;
    }, 1000);
}

function showVoteDetails(votingId) {
    const modal = document.getElementById('votingDetailsModal');
    const content = document.getElementById('votingDetailsContent');
    
    content.innerHTML = `
        <div class="vote-detail">
            <h3>Mi Voto en esta Votación</h3>
            <div class="vote-summary">
                <div class="vote-option-detail">
                    <i class="fas fa-check-circle"></i>
                    <span>Opción seleccionada: [Tu voto]</span>
                </div>
                <div class="vote-timestamp">
                    <i class="fas fa-clock"></i>
                    <span>Fecha y hora del voto: [Timestamp]</span>
                </div>
            </div>
            <p><small>Este voto es confidencial y solo tú puedes verlo.</small></p>
        </div>
    `;
    
    modal.style.display = 'block';
}
</script>

<?php include '../views/layouts/footer.php'; ?>

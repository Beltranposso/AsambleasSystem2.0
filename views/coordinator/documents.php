<?php
// views/voter/documents.php
$pageTitle = 'Documentos - Votante';
include '../views/layouts/header.php';
?>

<div class="main-content">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-file-alt"></i> Mis Documentos</h1>
            <p>Accede a documentos relacionados con las asambleas y votaciones</p>
        </div>
        <div class="header-actions">
            <a href="/dashboard/votante" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Sección en desarrollo -->
    <div class="section-card">
        <div class="development-notice">
            <div class="notice-icon">
                <i class="fas fa-tools fa-3x"></i>
            </div>
            <h2>Sección en Desarrollo</h2>
            <p>Esta funcionalidad estará disponible próximamente. Aquí podrás acceder a:</p>
            
            <div class="features-grid">
                <div class="feature-item">
                    <i class="fas fa-file-pdf"></i>
                    <h4>Actas de Asamblea</h4>
                    <p>Documentos oficiales de las asambleas</p>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-chart-bar"></i>
                    <h4>Reportes de Votación</h4>
                    <p>Resultados detallados de las votaciones</p>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-file-invoice"></i>
                    <h4>Estados de Cuenta</h4>
                    <p>Información financiera y pagos</p>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-certificate"></i>
                    <h4>Certificados</h4>
                    <p>Certificados de participación</p>
                </div>
            </div>
            
            <div class="contact-info">
                <p><i class="fas fa-info-circle"></i> 
                   Si necesitas acceso a algún documento específico, contacta al administrador.</p>
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

.section-card {
    background: white;
    border-radius: 12px;
    padding: 3rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}

.development-notice {
    text-align: center;
}

.notice-icon {
    color: #3182ce;
    margin-bottom: 2rem;
}

.development-notice h2 {
    color: #1a202c;
    margin-bottom: 1rem;
}

.development-notice > p {
    color: #718096;
    font-size: 1.1rem;
    margin-bottom: 3rem;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.feature-item {
    background: #f7fafc;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
}

.feature-item:hover {
    border-color: #cbd5e0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.feature-item i {
    font-size: 2.5rem;
    color: #3182ce;
    margin-bottom: 1rem;
}

.feature-item h4 {
    color: #1a202c;
    margin-bottom: 0.5rem;
}

.feature-item p {
    color: #718096;
    font-size: 0.875rem;
    margin: 0;
}

.contact-info {
    background: #ebf8ff;
    border: 1px solid #90cdf4;
    border-radius: 8px;
    padding: 1.5rem;
    color: #2c5aa0;
}

.contact-info p {
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
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

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .section-card {
        padding: 2rem;
    }
}
</style>

<?php include '../views/layouts/footer.php';?>
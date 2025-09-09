<?php $title = 'Reportes de Participación'; $userRole = 'coordinador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-chart-bar me-2"></i>Reportes de Participación</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <button type="button" class="btn btn-primary" onclick="exportReport()">
                    <i class="fas fa-download me-2"></i>Exportar Reporte
                </button>
            </div>
        </div>

        <!-- Filtros de Reporte -->
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-filter me-2"></i>Filtros de Reporte</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="" id="reportForm">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="asamblea" class="form-label">Asamblea:</label>
                            <select class="form-select" name="asamblea" id="asamblea">
                                <option value="">Todas las asambleas</option>
                                <?php if (isset($assemblies)): ?>
                                    <?php foreach ($assemblies as $asm): ?>
                                        <option value="<?php echo $asm['id']; ?>" 
                                                <?php echo (isset($_GET['asamblea']) && $_GET['asamblea'] == $asm['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($asm['titulo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="fecha_hasta" class="form-label">Fecha Hasta:</label>
                            <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta" 
                                   value="<?php echo $_GET['fecha_hasta'] ?? date('Y-m-t'); ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="fas fa-search me-2"></i>Generar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumen Ejecutivo -->
        <?php if (isset($attendanceStats)): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo $attendanceStats['total_asambleas'] ?? 0; ?></h4>
                            <small class="text-muted">Asambleas Analizadas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h4 class="text-info"><?php echo $attendanceStats['total_registros'] ?? 0; ?></h4>
                            <small class="text-muted">Total Registros</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h4 class="text-success"><?php echo $attendanceStats['total_asistencias'] ?? 0; ?></h4>
                            <small class="text-muted">Total Asistencias</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h4 class="text-warning"><?php echo number_format($attendanceStats['promedio_asistencia'] ?? 0, 1); ?>%</h4>
                            <small class="text-muted">Promedio Asistencia</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Reporte Detallado por Asamblea -->
        <?php if (isset($participationReport) && !empty($participationReport)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6><i class="fas fa-table me-2"></i>Detalle por Asamblea</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="reportTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Asamblea</th>
                                    <th>Fecha</th>
                                    <th>Registrados</th>
                                    <th>Asistentes</th>
                                    <th>% Asistencia</th>
                                    <th>Tendencia</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participationReport as $report): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($report['titulo']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($report['fecha_inicio'])); ?>
                                            <br>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($report['fecha_inicio'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $report['total_registrados']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?php echo $report['total_asistentes']; ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $percentage = $report['porcentaje_asistencia'] ?? 0;
                                            $progressClass = $percentage >= 75 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                                            ?>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar <?php echo $progressClass; ?>" 
                                                     style="width: <?php echo $percentage; ?>%">
                                                    <?php echo number_format($percentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            // Calcular tendencia comparando con la asamblea anterior
                                            $tendencia = 0; // Simplificado para el ejemplo
                                            if ($tendencia > 0): ?>
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> +<?php echo number_format($tendencia, 1); ?>%
                                                </span>
                                            <?php elseif ($tendencia < 0): ?>
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <?php echo number_format($tendencia, 1); ?>%
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> Sin cambio
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($percentage >= 75): ?>
                                                <span class="badge bg-success">Excelente</span>
                                            <?php elseif ($percentage >= 50): ?>
                                                <span class="badge bg-warning">Buena</span>
                                            <?php elseif ($percentage >= 25): ?>
                                                <span class="badge bg-orange">Regular</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Baja</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Gráficos y Análisis -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-line me-2"></i>Tendencia de Asistencia</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="attendanceChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-pie me-2"></i>Distribución de Asistencia</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="distributionChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis de Participantes Frecuentes -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-star me-2"></i>Participantes Más Activos</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php
                            // Datos de ejemplo para participantes más activos
                            $topParticipants = [
                                ['nombre' => 'María González', 'asistencia' => 95, 'asambleas' => 20],
                                ['nombre' => 'Carlos Mendoza', 'asistencia' => 90, 'asambleas' => 18],
                                ['nombre' => 'Ana Rodríguez', 'asistencia' => 85, 'asambleas' => 17],
                                ['nombre' => 'Luis Martínez', 'asistencia' => 80, 'asambleas' => 16],
                                ['nombre' => 'Carmen Jiménez', 'asistencia' => 75, 'asambleas' => 15]
                            ];
                            ?>
                            <?php foreach ($topParticipants as $index => $participant): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo $participant['nombre']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $participant['asambleas']; ?> asambleas</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success"><?php echo $participant['asistencia']; ?>%</span>
                                        <?php if ($index === 0): ?>
                                            <br><small class="text-warning"><i class="fas fa-trophy"></i> Top</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Participantes con Baja Asistencia</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php
                            // Datos de ejemplo para participantes con baja asistencia
                            $lowParticipants = [
                                ['nombre' => 'Roberto Vargas', 'asistencia' => 25, 'asambleas' => 4],
                                ['nombre' => 'Patricia Hernández', 'asistencia' => 30, 'asambleas' => 6],
                                ['nombre' => 'Diego Morales', 'asistencia' => 35, 'asambleas' => 7],
                                ['nombre' => 'Sandra López', 'asistencia' => 40, 'asambleas' => 8],
                                ['nombre' => 'Miguel Torres', 'asistencia' => 45, 'asambleas' => 9]
                            ];
                            ?>
                            <?php foreach ($lowParticipants as $participant): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo $participant['nombre']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $participant['asambleas']; ?> asambleas</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-warning"><?php echo $participant['asistencia']; ?>%</span>
                                        <br><small class="text-muted">Necesita seguimiento</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recomendaciones -->
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-lightbulb me-2"></i>Recomendaciones y Conclusiones</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success">Fortalezas Identificadas:</h6>
                        <ul class="list-unstyled">
                            <?php if (isset($attendanceStats) && ($attendanceStats['promedio_asistencia'] ?? 0) >= 70): ?>
                                <li><i class="fas fa-check text-success me-2"></i>Alta participación promedio (<?php echo number_format($attendanceStats['promedio_asistencia'], 1); ?>%)</li>
                            <?php endif; ?>
                            <li><i class="fas fa-check text-success me-2"></i>Grupo de participantes altamente comprometidos</li>
                            <li><i class="fas fa-check text-success me-2"></i>Consistencia en la asistencia de coordinadores</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning">Áreas de Mejora:</h6>
                        <ul class="list-unstyled">
                            <?php if (isset($attendanceStats) && ($attendanceStats['promedio_asistencia'] ?? 0) < 70): ?>
                                <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Mejorar estrategias de convocatoria</li>
                            <?php endif; ?>
                            <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Implementar seguimiento a participantes irregulares</li>
                            <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Evaluar horarios más convenientes</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="text-primary">Acciones Sugeridas:</h6>
                    <ol>
                        <li><strong>Seguimiento Personalizado:</strong> Contactar participantes con asistencia inferior al 50%</li>
                        <li><strong>Incentivos de Participación:</strong> Reconocer públicamente a los participantes más activos</li>
                        <li><strong>Optimización de Horarios:</strong> Analizar patrones de asistencia por día y hora</li>
                        <li><strong>Comunicación Mejorada:</strong> Enviar recordatorios y material previo a las asambleas</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Acciones de Exportación -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-download me-2"></i>Exportar Datos</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <button class="btn btn-success w-100" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-2"></i>Exportar a Excel
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-danger w-100" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf me-2"></i>Exportar a PDF
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info w-100" onclick="shareReport()">
                            <i class="fas fa-share me-2"></i>Compartir Reporte
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" onclick="scheduleReport()">
                            <i class="fas fa-calendar me-2"></i>Programar Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Tendencia de Asistencia
const attendanceCtx = document.getElementById('attendanceChart')?.getContext('2d');
if (attendanceCtx) {
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: [
                <?php 
                if (isset($participationReport)) {
                    foreach ($participationReport as $report) {
                        echo "'" . date('d/m', strtotime($report['fecha_inicio'])) . "',";
                    }
                }
                ?>
            ],
            datasets: [{
                label: 'Porcentaje de Asistencia',
                data: [
                    <?php 
                    if (isset($participationReport)) {
                        foreach ($participationReport as $report) {
                            echo ($report['porcentaje_asistencia'] ?? 0) . ",";
                        }
                    }
                    ?>
                ],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Asistencia: ' + context.parsed.y + '%';
                        }
                    }
                }
            }
        }
    });
}

// Gráfico de Distribución
const distributionCtx = document.getElementById('distributionChart')?.getContext('2d');
if (distributionCtx) {
    // Calcular distribución de rangos de asistencia
    let excelente = 0, buena = 0, regular = 0, baja = 0;
    
    <?php if (isset($participationReport)): ?>
        <?php foreach ($participationReport as $report): ?>
            <?php 
            $percentage = $report['porcentaje_asistencia'] ?? 0;
            if ($percentage >= 75) echo "excelente++;";
            elseif ($percentage >= 50) echo "buena++;";
            elseif ($percentage >= 25) echo "regular++;";
            else echo "baja++;";
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Excelente (75%+)', 'Buena (50-74%)', 'Regular (25-49%)', 'Baja (<25%)'],
            datasets: [{
                data: [excelente, buena, regular, baja],
                backgroundColor: [
                    '#198754', // success
                    '#ffc107', // warning
                    '#fd7e14', // orange
                    '#dc3545'  // danger
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function exportReport() {
    window.print();
}

function exportToExcel() {
    // Implementar exportación a Excel
    const params = new URLSearchParams(window.location.search);
    params.set('format', 'excel');
    window.location.href = '/Asambleas/public/coordinador/reportes/export?' + params.toString();
}

function exportToPDF() {
    // Implementar exportación a PDF
    const params = new URLSearchParams(window.location.search);
    params.set('format', 'pdf');
    window.location.href = '/Asambleas/public/coordinador/reportes/export?' + params.toString();
}

function shareReport() {
    // Implementar compartir reporte
    if (navigator.share) {
        navigator.share({
            title: 'Reporte de Participación',
            text: 'Reporte de participación en asambleas',
            url: window.location.href
        });
    } else {
        // Fallback: copiar al portapapeles
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Enlace copiado al portapapeles');
        });
    }
}

function scheduleReport() {
    // Implementar programación de reportes
    alert('Funcionalidad de programación de reportes disponible próximamente');
}

// Auto-actualizar fechas por defecto
document.getElementById('asamblea')?.addEventListener('change', function() {
    if (this.value) {
        // Si se selecciona una asamblea específica, ajustar fechas
        document.getElementById('reportForm').submit();
    }
});
</script>

<?php include '../views/layouts/footer.php'; ?>
                         
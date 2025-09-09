<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Asambleas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .test-users {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .test-user-btn {
            margin: 0.25rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-header">
                        <h2><i class="fas fa-users me-2"></i>Sistema de Asambleas</h2>
                        <p class="mb-0">Ingresa tus credenciales</p>
                    </div>
                    
                    <div class="login-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($_SESSION['success']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                        
                        <form action="/Asambleas/public/login" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                       placeholder="tu@email.com" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Contraseña
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="********" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">
                                    Recordarme
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </button>
                        </form>
                        
                        <!-- Modo desarrollo - Usuarios de prueba -->
                        <div class="test-users">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-flask me-2"></i>Modo Desarrollo - Usuarios de Prueba:
                            </h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary test-user-btn" 
                                        onclick="fillLogin('admin@test.com', 'admin123')">
                                    <i class="fas fa-user-shield me-1"></i>Administrador
                                </button>
                                <button class="btn btn-outline-success test-user-btn" 
                                        onclick="fillLogin('coordinador@test.com', 'coord123')">
                                    <i class="fas fa-user-tie me-1"></i>Coordinador
                                </button>
                                <button class="btn btn-outline-warning test-user-btn" 
                                        onclick="fillLogin('operador@test.com', 'oper123')">
                                    <i class="fas fa-user-cog me-1"></i>Operador
                                </button>
                                <button class="btn btn-outline-info test-user-btn" 
                                        onclick="fillLogin('votante@test.com', 'vote123')">
                                    <i class="fas fa-user me-1"></i>Votante
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i>
                                En desarrollo, cualquier email funcionará. Los botones de arriba son solo ejemplos.
                            </small>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <a href="/Asambleas/public/debug" class="text-decoration-none">
                                    <i class="fas fa-bug me-1"></i>Debug Info
                                </a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fillLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
        
        // Auto-enfocar el campo email
        document.getElementById('email').focus();
    </script>
</body>
</html>
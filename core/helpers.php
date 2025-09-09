<?php
// core/helpers.php - Funciones auxiliares globales

if (!function_exists('url')) {
    /**
     * Genera una URL completa para el sistema
     * @param string $path Ruta relativa
     * @return string URL completa
     */
    function url($path) {
        return 'http://localhost/Asambleas/public' . $path;
    }
}

if (!function_exists('asset')) {
    /**
     * Genera una URL para assets (CSS, JS, imágenes)
     * @param string $path Ruta del asset
     * @return string URL del asset
     */
    function asset($path) {
        return 'http://localhost/Asambleas/public/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirige a una URL
     * @param string $path Ruta de destino
     * @param array $params Parámetros GET opcionales
     */
    function redirect($path, $params = []) {
        $url = url($path);
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        header("Location: $url");
        exit;
    }
}

if (!function_exists('old')) {
    /**
     * Obtiene el valor anterior de un campo de formulario
     * @param string $key Nombre del campo
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    function old($key, $default = '') {
        return $_POST[$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Genera un token CSRF
     * @return string Token CSRF
     */
    function csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Genera un campo hidden con el token CSRF
     * @return string HTML del campo hidden
     */
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('formatDate')) {
    /**
     * Formatea una fecha
     * @param string $date Fecha a formatear
     * @param string $format Formato de salida
     * @return string Fecha formateada
     */
    function formatDate($date, $format = 'd/m/Y H:i') {
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        return date($format, strtotime($date));
    }
}

if (!function_exists('timeAgo')) {
    /**
     * Convierte una fecha en "tiempo transcurrido"
     * @param string $date Fecha
     * @return string Tiempo transcurrido
     */
    function timeAgo($date) {
        $time = time() - strtotime($date);
        
        if ($time < 60) return 'hace unos segundos';
        if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
        if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
        if ($time < 2592000) return 'hace ' . floor($time/86400) . ' días';
        if ($time < 31536000) return 'hace ' . floor($time/2592000) . ' meses';
        
        return 'hace ' . floor($time/31536000) . ' años';
    }
}

if (!function_exists('truncate')) {
    /**
     * Trunca un texto
     * @param string $text Texto a truncar
     * @param int $length Longitud máxima
     * @param string $suffix Sufijo a agregar
     * @return string Texto truncado
     */
    function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('statusBadge')) {
    /**
     * Genera un badge de Bootstrap según el estado
     * @param string $status Estado
     * @return string HTML del badge
     */
    function statusBadge($status) {
        $badges = [
            'activa' => 'success',
            'programada' => 'primary',
            'finalizada' => 'secondary',
            'cancelada' => 'danger',
            'al_dia' => 'success',
            'pendiente' => 'warning',
            'vencido' => 'danger'
        ];
        
        $class = $badges[$status] ?? 'secondary';
        return '<span class="badge bg-' . $class . '">' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('hasRole')) {
    /**
     * Verifica si el usuario actual tiene un rol específico
     * @param string $role Rol a verificar
     * @return bool
     */
    function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }
}

if (!function_exists('isActive')) {
    /**
     * Verifica si una ruta está activa
     * @param string $route Ruta a verificar
     * @return string Clase CSS 'active' si está activa
     */
    function isActive($route) {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = '/Asambleas/public';
        
        if (strpos($currentPath, $basePath) === 0) {
            $currentPath = substr($currentPath, strlen($basePath));
        }
        
        return $currentPath === $route ? 'active' : '';
    }
}
?>
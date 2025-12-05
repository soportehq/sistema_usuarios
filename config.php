<?php
// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zona horaria
date_default_timezone_set('America/Lima'); // Cambia según tu país

// CONEXIÓN A LA BASE DE DATOS
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sistema_usuarios;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// ==================== FUNCIONES SEGURAS (NO SE REDECLARAN) ====================

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADMINISTRADOR';
    }
}

if (!function_exists('tienePermiso')) {
    function tienePermiso($permiso) {
        if (isAdmin()) return true;
        if (!isset($_SESSION['user'])) return false;

        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT permisos FROM users WHERE id = ? AND estado = 'Activo'");
            $stmt->execute([$_SESSION['user']]);
            $user = $stmt->fetch();

            if (!$user || empty($user['permisos'])) return false;

            $permisos = json_decode($user['permisos'], true);
            return isset($permisos[$permiso]) && $permisos[$permiso] === true;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('e')) {
    function e($string) {
        htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// Opcional: Cerrar sesión al cerrar pestaña
if (!function_exists('cerrarSesionAutomatica')) {
    function cerrarSesionAutomatica() {
        if (isset($_GET['logout'])) {
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
        }
    }
}
?>
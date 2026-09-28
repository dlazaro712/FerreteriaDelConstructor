<?php
// views/usuarios/guardarResena.php

// Iniciar buffer de salida para evitar conflictos de cabeceras previas
ob_start();

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que la petición sea por método POST y que el usuario esté autenticado como cliente
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: " . BASE_URL . "views/clientes/resenas.php");
    exit();
}

// Requerir dependencias principales
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/ResenasController.php';

// Instanciar el controlador de reseñas
$resenaController = new ResenasController();

if (method_exists($resenaController, 'store')) {
    // Ejecutamos la función de guardado
    $resenaController->store();
    
    // Limpiamos cualquier salida previa del controlador que pueda corromper el header
    if (ob_get_length()) {
        ob_clean();
    }
    
    // Forzamos la redirección limpia hacia la página de reseñas sin errores 404
    header("Location: " . BASE_URL . "views/clientes/resenas.php");
    exit();
} else {
    if (ob_get_length()) {
        ob_clean();
    }
    // Fallback de seguridad si el método no existe
    header("Location: " . BASE_URL . "views/clientes/resenas.php?error=metodo_no_encontrado");
    exit();
}
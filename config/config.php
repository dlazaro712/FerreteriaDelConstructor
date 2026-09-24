<?php
// Evitar acceso directo
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    exit('Acceso no permitido');
}

// Definir la URL base absoluta para las vistas, redirecciones y assets
define('BASE_URL', 'http://localhost:8080/FerreteriaDelConstructor/');

// Opcional: Definir rutas físicas del servidor (útil para includes/requires seguros)
define('ROOT_PATH', realpath(__DIR__) . '/');
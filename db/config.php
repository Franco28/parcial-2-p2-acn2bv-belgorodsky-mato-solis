<?php

$servidor = "localhost";
$usuario_bd = "root";
$clave_bd = "";
$base_datos = "billie_discografia";

$conexion = mysqli_connect($servidor, $usuario_bd, $clave_bd, $base_datos);

if (!$conexion) {
    die(json_encode([
        'exito' => false,
        'error' => 'Error de conexión a la base de datos: ' . mysqli_connect_error()
    ], JSON_UNESCAPED_UNICODE));
}

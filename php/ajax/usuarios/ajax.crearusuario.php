<?php
include '../../../sesion.php';
include '../../classes/usuarios/class.usuario.php';

// Obtener el origen de la solicitud
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Verificar si el origen de la solicitud está en la lista de permitidos
if (in_array($origin, ALLOWED_HOSTS)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}

$Usuario = new Usuario($con);

$option = isset($_POST['option']) ? $_POST['option'] : '';

if ($option == 'crearUsuario') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $resultado = $Usuario->crearUsuario($nombre, $apellido, $email, $password);

    echo json_encode($resultado);
}

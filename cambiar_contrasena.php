<?php
// cambiar_contrasena.php
include 'php/classes/usuarios/class.usuario.php';
require 'conexion.php';

$Usuario = new Usuario($con);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && isset($_POST['password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];

    $resultado = $Usuario->cambiarContrasena($token, $password);
    echo $resultado;
}

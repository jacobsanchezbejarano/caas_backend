<?php
// cambiar_contrasena.php
include 'php/classes/usuarios/class.usuario.php';
require 'conexion.php';
require 'environment.php';

$Usuario = new Usuario($con, HOST);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && isset($_POST['password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];

    $resultado = $Usuario->cambiarContrasena($token, $password);
    echo $resultado;
}

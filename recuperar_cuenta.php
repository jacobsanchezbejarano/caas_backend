<?php
// resetear_contrasena.php
require 'sesion.php';
require_once 'php/encryption.php';
include 'php/classes/usuarios/class.usuario.php';

$Usuario = new Usuario($con, HOST);
if (isset($_GET['token'])) {
    $Usuario->mostrarFormularioRecuperacion($token);
}

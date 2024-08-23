<?php
session_name('sedeberenovar');
session_start([
    'cookie_lifetime' => 9970000,

]);
include("conexion.php");
include 'environment.php';
$con = conectar();

<?php
require_once 'class.sensitive.php';
require_once '../../encryption.php';
class Usuario extends Sensitive
{
    private $con;
    private $key;

    public function __construct($con)
    {
        $this->con = $con;
        $this->key = ENCRYPTION_KEY;
    }

    public function crearUsuario($nombre, $apellido, $email, $password)
    {
        // Generar un ID único para el usuario
        $id = bin2hex(random_bytes(12));

        $errores_password = $this->validarSeguridadContrasena($password);
        if (!empty($errores_password)) {
            return ['success' => false, 'errors' => $errores_password];
        }

        $nombre_encriptado = $this->encriptar($nombre, $this->key);
        $apellido_encriptado = $this->encriptar($apellido, $this->key);

        // Hashear la contraseña
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Valores predeterminados para los intentos fallidos y estado bloqueado
        $intentosFallidos = 0;
        $bloqueado = 0;

        // Consulta de inserción
        $query = "INSERT INTO usuarios (usuarios_id, usuarios_nombre, usuarios_apellido, usuarios_email, usuarios_password, usuarios_intentosFallidos, usuarios_bloqueado) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("sssssii", $id, $nombre_encriptado, $apellido_encriptado, $email, $hashedPassword, $intentosFallidos, $bloqueado);

        // Ejecutar la consulta y devolver respuesta
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }

    private static function validarSeguridadContrasena($password)
    {
        $errores = [];

        // Longitud mínima de 8 caracteres
        if (strlen($password) < 8) {
            $errores[] = 'ERROR_LONGITUD_MINIMA';
        }

        // Debe contener al menos una letra mayúscula
        if (!preg_match('/[A-Z]/', $password)) {
            $errores[] = 'ERROR_LETRA_MAYUSCULA';
        }

        // Debe contener al menos una letra minúscula
        if (!preg_match('/[a-z]/', $password)) {
            $errores[] = 'ERROR_LETRA_MINUSCULA';
        }

        // Debe contener al menos un número
        if (!preg_match('/[0-9]/', $password)) {
            $errores[] = 'ERROR_NUMERO';
        }

        // Debe contener al menos un carácter especial
        if (!preg_match('/[\W]/', $password)) {
            $errores[] = 'ERROR_CARACTER_ESPECIAL';
        }

        return $errores; // Devuelve un array de códigos de error
    }

    private function iniciarSesionUsuario($usuarios_id) {}
}

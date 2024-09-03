<?php
require_once 'class.sensitive.php';
class Usuario extends Sensitive
{
    private $con;
    private $key;
    private $host;

    public function __construct($con, $host)
    {
        $this->con = $con;
        $this->key = ENCRYPTION_KEY;
        $this->host = $host;
    }

    public function verUsuarios()
    {
        $id = null;
        $nombre = null;
        $apellido = null;
        $usuarios = [];

        $sql = "SELECT usuarios_id, usuarios_nombre, usuarios_apellido FROM usuarios"; // Ajusta los campos según tu estructura de base de datos
        $stmt = $this->con->prepare($sql);

        if ($stmt === false) {
            die('Error en la preparación de la consulta: ' . $this->con->error);
        }

        $stmt->execute();

        // Enlaza los resultados
        $stmt->bind_result($id, $nombre, $apellido);

        while ($stmt->fetch()) {
            $usuarios[] = [
                'id' => $id,
                'nombre' => $this->desencriptar($nombre, $this->key),
                'apellido' => $this->desencriptar($apellido, $this->key)
            ];
        }

        $stmt->close();

        if (empty($usuarios)) {
            return [
                'status' => 'error',
                'message' => 'No se encontraron usuarios.'
            ];
        } else {
            return [
                'status' => 'success',
                'usuarios' => $usuarios
            ];
        }
    }

    public function recuperarCuenta()
    {
        $email = null;
        // Verificar si el correo electrónico existe
        $stmt = $this->con->prepare("SELECT usuarios_id FROM usuarios WHERE usuarios_email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $userId = $stmt->fetch_assoc()['id'];

            // Generar un token único
            $token = bin2hex(random_bytes(50));
            $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

            // Guardar el token en la base de datos
            $stmt = $this->con->prepare("INSERT INTO recuperacion_cuenta (usuarios_id, recuperacion_cuenta_token, recuperacion_cuenta_expiracion) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $userId, $token, $expires);
            $stmt->execute();

            // Enviar el enlace de recuperación
            $resetLink = $this->host . "/resetear_contrasena.php?token=$token";
            $subject = "Recuperación de Contraseña";
            $message = "Haz clic en el siguiente enlace para restablecer tu contraseña: $resetLink";
            $this->sendMail($email, $subject, $message);
        }
    }

    public function sendMail($to, $subject, $message)
    {
        $headers = 'From: noreply@tu-dominio.com' . "\r\n" .
            'Reply-To: noreply@tu-dominio.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);
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

    public function mostrarFormularioRecuperacion($token)
    {
        // Verificar el token
        $stmt = $this->con->prepare("SELECT usuarios_id FROM recuperacion_cuenta WHERE recuperacion_cuenta_token = ? AND recuperacion_cuenta_expiracion > NOW()");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Mostrar formulario para ingresar nueva contraseña
            echo '<form method="POST" action="cambiar_contrasena.php">
                    <input type="hidden" name="token" value="' . htmlspecialchars($token) . '">
                    <label for="password">Nueva Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                    <button type="submit">Cambiar Contraseña</button>
                  </form>';
        } else {
            echo 'El enlace de recuperación es inválido o ha expirado.';
        }

        $stmt->close();
    }

    // Método para cambiar la contraseña
    public function cambiarContrasena($token, $password)
    {
        $userId = null;
        // Validar la contraseña
        if (!$this->validarSeguridadContrasena($password)) {
            return 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas, números y caracteres especiales.';
        }

        // Verificar el token
        $stmt = $this->con->prepare("SELECT usuarios_id FROM recuperacion_cuenta WHERE recuperacion_cuenta_token = ? AND recuperacion_cuenta_expiracion > NOW()");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Obtener user_id
            $stmt->bind_result($userId);
            $stmt->fetch();
            $stmt->close();

            // Actualizar la contraseña
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->con->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hashedPassword, $userId);
            $stmt->execute();

            // Eliminar el token
            $stmt = $this->con->prepare("DELETE FROM recuperacion_cuenta WHERE recuperacion_cuenta_token = ?");
            $stmt->bind_param('s', $token);
            $stmt->execute();

            return [
                'status' => 'success',
                'message' => 'Contraseña cambiada con éxito.'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'El enlace de recuperación es inválido o ha expirado.'
            ];
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

<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include '../../../environment.php';
require_once '../../encryption.php';

// Obtener el origen de la solicitud
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Verificar si el origen de la solicitud está en la lista de permitidos
if (!in_array($origin, ALLOWED_HOSTS)) {
    http_response_code(403); // Prohibido
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

require '../../../vendor/autoload.php'; // Necesario si usas JWT con Firebase
require '../../classes/usuarios/class.auth.php'; // Incluye la clase Auth
require '../../../conexion.php'; // Incluye la clase Auth

$con = conectar();
$Auth = new Auth($con, HOST);

// Obtener y sanitizar inputs
$input = json_decode(file_get_contents('php://input'), true);
$option = trim($input['option'] ?? '');
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

switch ($option) {
    case 'login':
        if (!empty($email) && !empty($password)) {
            $user = $Auth->authenticate($email, $password);
            if ($user) {
                $token = $Auth->generateToken($user);
                echo json_encode([
                    'status' => 'success',
                    'user' => $user,
                    'token' => $token
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Credenciales inválidas'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'El email y la contraseña son requeridos.'
            ]);
        }
        break;

    case 'verify':
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';

        if ($authHeader) {
            $arr = explode(" ", $authHeader);
            $jwt = $arr[1] ?? '';

            if ($jwt) {
                try {
                    $decoded = $Auth->verifyToken($jwt);
                    echo json_encode([
                        'status' => 'success',
                        'data' => $decoded->data
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Token inválido o expirado'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Token no proporcionado'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Encabezado de autorización no presente'
            ]);
        }
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Acción no especificada o no válida'
        ]);
        break;
}

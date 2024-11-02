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
require '../../classes/contabilidad_personal/class.cuentas.php'; // Incluye la clase Cuentas
require '../../../conexion.php'; // Incluye la clase Auth

$con = conectar();
$Auth = new Auth($con, HOST);
$Cuentas = new Cuentas($con, HOST);

// Obtener y sanitizar inputs
$input = json_decode(file_get_contents('php://input'), true);
$option = trim($input['option'] ?? '');
$email = trim($input['email'] ?? '');

$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';

if ($authHeader) {
    $arr = explode(" ", $authHeader);
    $jwt = $arr[1] ?? '';

    if ($jwt) {
        try {
            $decoded = $Auth->verifyToken($jwt);

            switch ($option) {
                case 'obtenerCuentasClasificadas':
                    $cuentas = $Cuentas->obtenerCuentasClasificadas($Cuentas->obtenerUsuarioId($decoded->data->email));
                    if ($cuentas['status'] == 'success') echo json_encode(['data' => $cuentas['cuentas']]);
                    break;

                default:
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Acción no especificada o no válida'
                    ]);
                    break;
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Token inválido o expirado'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Token no proporcionado'
        ]);
        exit;
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Encabezado de autorización no presente'
    ]);
    exit;
}

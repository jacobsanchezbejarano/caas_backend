<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once 'class.sensitive.php';

class Auth extends Sensitive
{
    private $key = "FHADHU34Y94TIH5GD45FGDFGISDFSDFG409Q559"; // Clave secreta para JWT
    public $con = null;
    public $host = null;

    public function __construct($con, $host)
    {
        $this->con = $con;
        $this->key = ENCRYPTION_KEY;
        $this->host = $host;
    }

    // Genera un token JWT para un usuario autenticado
    public function generateToken($user)
    {
        $payload = [
            "iss" => $this->host,
            "aud" => $this->host,
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + 3600, // Token válido por 1 hora
            "data" => [
                "email" => $user
            ]
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    // Verifica y decodifica un token JWT
    public function verifyToken($jwt)
    {
        try {
            return JWT::decode($jwt, new Key($this->key, 'HS256'));
        } catch (Exception $e) {
            throw new Exception('Token inválido o expirado');
        }
    }

    // Simula la verificación de credenciales en la base de datos
    public function authenticate($email, $password)
    {
        $consulta = "SELECT * FROM usuarios WHERE usuarios_email='$email' LIMIT 1";

        $resultado = mysqli_query($this->con, $consulta);

        $sfd = $this->con->query($consulta);

        while ($row = $sfd->fetch_assoc()) {
            $hashedPasswordFromDB = $row['usuarios_password'];
            if (password_verify($password, $hashedPasswordFromDB)) {
                return $row['usuarios_email'];
            }
        }
        return false;
    }
}

/*


header("Access-Control-Allow-Origin: *");




// Ejemplo de solicitud GET
fetch('https://tudominio.com/api.php', {
method: 'GET',
headers: {
'Content-Type': 'application/json'
}
})
.then(response => response.json())
.then(data => {
console.log(data); // Maneja la respuesta
})
.catch(error => {
console.error('Error:', error); // Maneja el error
});

// Ejemplo de solicitud POST
fetch('https://tudominio.com/api.php', {
method: 'POST',
headers: {
'Content-Type': 'application/json'
},
body: JSON.stringify({
nombre: 'Jacob',
edad: 25
})
})
.then(response => response.json())
.then(data => {
console.log(data); // Maneja la respuesta
})
.catch(error => {
console.error('Error:', error); // Maneja el error
});






import axios from 'axios';

// Ejemplo de solicitud GET
axios.get('https://tudominio.com/api.php')
.then(response => {
console.log(response.data); // Maneja la respuesta
})
.catch(error => {
console.error('Error:', error); // Maneja el error
});

// Ejemplo de solicitud POST
axios.post('https://tudominio.com/api.php', {
nombre: 'Jacob',
edad: 25
})
.then(response => {
console.log(response.data); // Maneja la respuesta
})
.catch(error => {
console.error('Error:', error); // Maneja el error
});








import axios from 'axios';

const login = (email, password) => {
axios.post('https://tudominio.com/api/login.php', {
email: email,
password: password
})
.then(response => {
if (response.data.token) {
// Guardar el token en almacenamiento seguro (AsyncStorage, SecureStore, etc.)
AsyncStorage.setItem('authToken', response.data.token);
}
})
.catch(error => {
console.error('Error al iniciar sesión:', error);
});
};









import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const fetchData = async () => {
const token = await AsyncStorage.getItem('authToken');

axios.get('https://tudominio.com/api/protected-endpoint.php', {
headers: {
'Authorization': `Bearer ${token}`
}
})
.then(response => {
console.log(response.data); // Procesa la respuesta del servidor
})
.catch(error => {
console.error('Error al obtener datos:', error);
});
};

*/
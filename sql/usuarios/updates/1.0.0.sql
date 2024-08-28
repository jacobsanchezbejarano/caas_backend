CREATE TABLE IF NOT EXISTS usuarios (
    usuarios_id VARCHAR(32) PRIMARY KEY,
    usuarios_nombre VARCHAR(255) NOT NULL,
    usuarios_apellido VARCHAR(255) NOT NULL,
    usuarios_email VARCHAR(100) NOT NULL UNIQUE,
    usuarios_password VARCHAR(255) NOT NULL,
    usuarios_fechaRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuarios_intentosFallidos INT(1) NOT NULL,
    usuarios_bloqueado INT(1) NOT NULL
);

CREATE TABLE IF NOT EXISTS sesiones (
    sesiones_id INT PRIMARY KEY AUTO_INCREMENT,
    usuarios_id INT(11),
    sesiones_inicio INT(1),
    timestamp DATETIME
);
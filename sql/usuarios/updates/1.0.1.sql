CREATE TABLE recuperacion_cuenta (
    recuperacion_cuenta_id INT AUTO_INCREMENT PRIMARY KEY,
    usuarios_id INT NOT NULL,
    recuperacion_cuenta_token VARCHAR(255) NOT NULL,
    recuperacion_cuenta_expiracion DATETIME NOT NULL
);
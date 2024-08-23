CREATE TABLE IF NOT EXISTS codigosactivacion (
    codigosactivacion_id INT PRIMARY KEY AUTO_INCREMENT,
    usuarios_id INT NOT NULL,
    codigosactivacion_generado VARCHAR(255) NOT NULL,
    codigosactivacion_usado INT(1) NOT NULL,
    codigosactivacion_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
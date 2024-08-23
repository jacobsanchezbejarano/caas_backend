CREATE TABLE IF NOT EXISTS metodospago (
    metodospago_id INT PRIMARY KEY AUTO_INCREMENT,
    metodospago_nombre VARCHAR(255) NOT NULL,
    metodospago_fechacreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pagos (
  pagos_id INT NOT NULL AUTO_INCREMENT,
  pagador_id int NOT NULL,
  pagos_monto DECIMAL(9,2) NOT NULL,
  pagos_fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  metodospago_id INT not null,
  PRIMARY KEY (pagos_id)
);
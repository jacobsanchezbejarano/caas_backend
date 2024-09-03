<?php

class Cuentas
{
    public $con = null;
    public $host = null;

    public function __construct($con, $host)
    {
        $this->con = $con;
        $this->host = $host;
    }

    public function crearLibroDiario($usuarios_id)
    {
        // Asegúrate de que el $usuarios_id sea seguro para usar en la consulta SQL
        $usuarios_id = (int)$usuarios_id;

        // Consulta SQL para crear la tabla librodiario
        $sqlLibroDiario = "CREATE TABLE IF NOT EXISTS librodiario$usuarios_id (
            librodiario_id INT(11) AUTO_INCREMENT PRIMARY KEY,
            librodiario_asiento INT(11),
            librodiario_fecha DATE,
            librodiario_hora TIME,
            librodiario_glosa TEXT,
            plandecuentas_id INT(11),
            librodiario_debe DECIMAL(17,3),
            librodiario_haber DECIMAL(17,3)
        )";

        // Consulta SQL para crear la tabla plandecuentas
        $sqlPlanDeCuentas = "CREATE TABLE IF NOT EXISTS plandecuentas$usuarios_id (
            plandecuentas_id INT(11) UNIQUE,
            plandecuentas_nombre VARCHAR(100),
            plandecuentas_FijoVariable VARCHAR(10),
            plandecuentas_presupuesto DECIMAL(17,3)
        )";

        // Ejecutar las consultas SQL
        $conn = $this->con; // Método que retorna la conexión a la base de datos

        if ($conn->query($sqlLibroDiario) === TRUE) {
            echo "Tabla librodiario$usuarios_id creada con éxito.";
        } else {
            echo "Error al crear la tabla librodiario$usuarios_id: " . $conn->error;
        }

        if ($conn->query($sqlPlanDeCuentas) === TRUE) {
            echo "Tabla plandecuentas$usuarios_id creada con éxito.";
        } else {
            echo "Error al crear la tabla plandecuentas$usuarios_id: " . $conn->error;
        }

        // Cerrar la conexión
        $conn->close();
    }

    public function dummyCuentas($usuarios_id)
    {
        // Consulta SQL para insertar datos en la tabla plandecuentas
        $sqlInsertCuentas = "INSERT INTO plandecuentas$usuarios_id (plandecuentas_codigo, plandecuentas_nombre, plandecuentas_tipo, plandecuentas_presupuesto) VALUES
                            ('400001', 'Ventas', '', 0.000),
                            ('400002', 'Sueldo', '', 0.000),
                            ('400003', 'Intereses Ganados', '', 0.000),
                            ('600001', 'Alimentación', 'Variable', 0.000),
                            ('600002', 'Restaurantes', 'Variable', 0.000),
                            ('600003', 'Bebidas', 'Variable', 0.000),
                            ('600004', 'Educación', 'Variable', 0.000),
                            ('600005', 'Alquiler', 'Fijo', 0.000),
                            ('600006', 'Intereses Pagados', 'Variable', 0.000),
                            ('600007', 'Internet', 'Fijo', 0.000),
                            ('600008', 'Luz', 'Variable', 0.000),
                            ('600009', 'Agua', 'Variable', 0.000),
                            ('100001', 'Caja', '', 0.000),
                            ('100002', 'Banco', '', 0.000)";

        // Ejecutar la consulta para insertar los datos
        if ($this->con->query($sqlInsertCuentas) === TRUE) {
            echo "Records inserted successfully";
        } else {
            echo "Error inserting records: " . $this->con->error;
        }
    }
}

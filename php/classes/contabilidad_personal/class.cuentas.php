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
        $mensaje = "";

        if ($conn->query($sqlLibroDiario) === TRUE) {
            $mensaje .= "Tabla librodiario$usuarios_id creada con éxito.";
        } else {
            $mensaje .= "Error al crear la tabla librodiario$usuarios_id: " . $conn->error;
        }

        if ($conn->query($sqlPlanDeCuentas) === TRUE) {
            $mensaje .= "Tabla plandecuentas$usuarios_id creada con éxito.";
        } else {
            $mensaje .= "Error al crear la tabla plandecuentas$usuarios_id: " . $conn->error;
        }
        return $mensaje;
    }

    public function dummyCuentas($usuarios_id)
    {
        // Consulta SQL para insertar datos en la tabla plandecuentas
        $sqlInsertCuentas = "INSERT INTO plandecuentas$usuarios_id (plandecuentas_id, plandecuentas_nombre, plandecuentas_FijoVariable, plandecuentas_presupuesto) VALUES
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
        $mensaje = "";
        try {
            if ($this->con->query($sqlInsertCuentas) === TRUE) {
                $mensaje .= "Records inserted successfully";
            } else {
                $mensaje .= "Error inserting records: " . $this->con->error;
            }
        } catch (Exception $e) {
            $mensaje .= "Error inserting records: " . $this->con->error;
        }


        return $mensaje;
    }



    public function obtenerUsuarioId($email)
    {
        $id = null;
        $stmt = $this->con->prepare("SELECT usuarios_id FROM usuarios WHERE usuarios_email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($id);

        while ($stmt->fetch()) {
            return $id;
        }
    }

    public function obtenerCuentasClasificadas($usuarios_id)
    {
        $mensaje = "";
        $mensaje .= $this->crearLibroDiario($usuarios_id);
        $mensaje .= $this->dummyCuentas($usuarios_id);
        $code = null;
        $nombreCuenta = null;
        $obtenerAllCuentas = "SELECT plandecuentas_id, plandecuentas_nombre FROM plandecuentas$usuarios_id ORDER BY plandecuentas_id";

        $stmt = $this->con->query($obtenerAllCuentas);

        while ($row = $stmt->fetch_assoc()) {
            $code = $row['plandecuentas_id'];
            $nombreCuenta = $row['plandecuentas_nombre'];

            switch ($code[0]) {
                case 1:
                    $tipo = 'Activo';
                    break;
                case 2:
                    $tipo = 'Pasivo';
                    break;
                case 3:
                    $tipo = 'Patrimonio';
                    break;
                case 4:
                    $tipo = 'Ingreso';
                    break;
                case 5:
                    $tipo = 'Costo';
                    break;
                case 6:
                    $tipo = 'Egreso';
                    break;
            }
            $cuentas[$tipo][] = [
                'accountNumber' => $code,
                'title' => $nombreCuenta,
            ];
        }

        // $stmt->close();

        if (empty($cuentas)) {
            return [
                'status' => 'error',
                'message' => 'No se encontraron cuentas.'
            ];
        } else {

            $allActivos = $cuentas['Activo'] ?? [];
            $allPasivos = $cuentas['Pasivo'] ?? [];
            $allPatrimonio = $cuentas['Patrimonio'] ?? [];
            $allIngresos = $cuentas['Ingreso'] ?? [];
            $allCostos = $cuentas['Costo'] ?? [];
            $allEgresos = $cuentas['Egreso'] ?? [];

            $cuentas = [];
            $cuentas['Activos'] = $allActivos == [] ? [] : $this->clasificarActivos($allActivos);
            $cuentas['Pasivos'] = $allPasivos == [] ? [] : $this->clasificarPasivos($allPasivos);
            $cuentas['Patrimonio'] = $allPatrimonio == [] ? [] : $this->clasificarPatrimonio($allPatrimonio);
            $cuentas['Ingresos'] = $allIngresos == [] ? [] : $this->clasificarIngresos($allIngresos);
            $cuentas['Costos'] = $allCostos == [] ? [] : $this->$allCostos;
            $cuentas['Egresos'] = $allEgresos == [] ? [] : $this->clasificarEgresos($allEgresos);

            return [
                'status' => 'success',
                'mensaje' => $mensaje,
                'cuentas' => $cuentas
            ];
        }
    }

    public function clasificarActivos($allActivos)
    {
        $activos = [];

        $activos['AC Disponibles'] = [];
        $activos['AC Inversiones Temporales'] = [];
        $activos['AC Exigibles'] = [];
        $activos['AC Realizables'] = [];
        $activos['AC Diferidos'] = [];
        $activos['ANC Exigibles>1año'] = [];
        $activos['ANC Inversiones permanentes'] = [];
        $activos['ANC Bienes de Uso'] = [];
        $activos['ANC Intangibles'] = [];
        $activos['ANC Gastos Dieferidos>1Año'] = [];

        foreach ($allActivos as $activo) {
            $subtipo = 'Undefined';
            switch ($activo['accountNumber'][0] . $activo['accountNumber'][1]) {
                case 10:
                    $subtipo = 'AC Disponibles';
                    break;
                case 11:
                    $subtipo = 'AC Inversiones Temporales';
                    break;
                case 12:
                    $subtipo = 'AC Exigibles';
                    break;
                case 13:
                    $subtipo = 'AC Realizables';
                    break;
                case 14:
                    $subtipo = 'AC Diferidos';
                    break;
                case 15:
                    $subtipo = 'ANC Exigibles>1año';
                    break;
                case 16:
                    $subtipo = 'ANC Inversiones permanentes';
                    break;
                case 17:
                    $subtipo = 'ANC Bienes de Uso';
                    break;
                case 18:
                    $subtipo = 'ANC Intangibles';
                    break;
                case 19:
                    $subtipo = 'ANC Gastos Dieferidos>1Año';
                    break;
            }
            $activos[$subtipo][] = $activo;
        }
        if (empty($activos)) {
            return [];
        } else {
            return $activos;
        }
    }
    public function clasificarPasivos($allPasivos)
    {
        $pasivos = [];

        $pasivos['Pasivos corrientes'] = [];
        $pasivos['Pasivos no corrientes'] = [];

        foreach ($allPasivos as $pasivo) {
            $prefix = $pasivo['accountNumber'][0] . $pasivo['accountNumber'][1];
            if ($prefix >= 20 && $prefix < 25) $prefix = 20;
            if ($prefix >= 25 && $prefix < 30) $prefix = 25;
            $subtipo = 'Undefined';
            switch ($prefix) {
                case 20:
                    $subtipo = 'Pasivos corrientes';
                    break;
                case 25:
                    $subtipo = 'Pasivos no corrientes';
                    break;
            }
            $pasivos[$subtipo][] = $pasivo;
        }
        if (empty($pasivos)) {
            return [];
        } else {
            return $pasivos;
        }
    }
    public function clasificarPatrimonio($allPatrimonio)
    {
        $patrimonioCuentas = [];

        $patrimonioCuentas['Capital'] = [];
        $patrimonioCuentas['Resultados'] = [];
        $patrimonioCuentas['Reservas'] = [];
        $patrimonioCuentas['Ajustes'] = [];

        foreach ($allPatrimonio as $patrimonio) {
            $prefix = $patrimonio['accountNumber'][0] . $patrimonio['accountNumber'][1] . $patrimonio['accountNumber'][2];
            if ($prefix >= 300 && $prefix < 325) $prefix = 300;
            if ($prefix >= 325 && $prefix < 350) $prefix = 325;
            if ($prefix >= 350 && $prefix < 375) $prefix = 350;
            if ($prefix >= 375 && $prefix < 400) $prefix = 375;
            $subtipo = 'Undefined';
            switch ($prefix) {
                case 300:
                    $subtipo = 'Capital';
                    break;
                case 325:
                    $subtipo = 'Resultados';
                    break;
                case 350:
                    $subtipo = 'Reservas';
                    break;
                case 375:
                    $subtipo = 'Ajustes';
                    break;
            }
            $patrimonioCuentas[$subtipo][] = $patrimonio;
        }
        if (empty($patrimonioCuentas)) {
            return [];
        } else {
            return $patrimonioCuentas;
        }
    }
    public function clasificarIngresos($allIngresos)
    {
        $ingresos = [];

        $ingresos['Ingresos'] = [];
        $ingresos['Ingresos Extraordinarios'] = [];

        foreach ($allIngresos as $ingreso) {
            $prefix = $ingreso['accountNumber'][0] . $ingreso['accountNumber'][1];
            if ($prefix >= 40 && $prefix < 46) $prefix = 40;
            if ($prefix >= 46 && $prefix < 50) $prefix = 46;
            $subtipo = 'Undefined';
            switch ($prefix) {
                case 40:
                    $subtipo = 'Ingresos';
                    break;
                case 46:
                    $subtipo = 'Ingresos Extraordinarios';
                    break;
            }
            $ingresos[$subtipo][] = $ingreso;
        }
        if (empty($ingresos)) {
            return [];
        } else {
            return $ingresos;
        }
    }
    public function clasificarEgresos($allEgresos)
    {
        $egresos = [];

        $egresos['Egresos'] = [];
        $egresos['Egresos Extraordinarios'] = [];

        foreach ($allEgresos as $egreso) {
            $prefix = $egreso['accountNumber'][0] . $egreso['accountNumber'][1];
            if ($prefix >= 60 && $prefix < 66) $prefix = 60;
            if ($prefix >= 66 && $prefix < 70) $prefix = 66;
            $subtipo = 'Undefined';
            switch ($prefix) {
                case 60:
                    $subtipo = 'Egresos';
                    break;
                case 66:
                    $subtipo = 'Egresos Extraordinarios';
                    break;
            }
            $egresos[$subtipo][] = $egreso;
        }
        if (empty($egresos)) {
            return [];
        } else {
            return $egresos;
        }
    }
}

<?php

function extraer_mes($numero)
{
    return [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    ][$numero];
}

function dia_semana($dia)
{
    return [
        '1' => 'Lunes',
        '2' => 'Martes',
        '3' => 'Miércoles',
        '4' => 'Jueves',
        '5' => 'Viernes',
        '6' => 'Sábado',
        '7' => 'Domingo',
    ][$dia];
}


function restarDias($fecha, $dias)
{
    $fecha = new DateTime($fecha);
    $fecha->sub(new DateInterval('P' . $dias . 'D')); // Restar 28 días

    $idfec1 = $fecha->format('Y-m-d'); // Imprimir la nueva fecha

    $first = substr($idfec1, 0, 8);
    $fec111 = $first . "01 00:00:00";

    return $fec111;
}

function verificarTablaExiste($nombreTabla)
{
    // Escapar el nombre de la tabla para evitar inyecciones SQL
    $nombreTabla = mysqli_real_escape_string($GLOBALS['con'], $nombreTabla);

    // Consulta para verificar si la tabla existe
    $query = "SELECT 1 FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' AND table_name = '$nombreTabla' LIMIT 1";
    $result = mysqli_query($GLOBALS['con'], $query);

    // Verificar si la consulta devuelve algún resultado
    if ($result && mysqli_num_rows($result) > 0) {
        return true; // La tabla existe
    } else {
        return false; // La tabla no existe
    }
}

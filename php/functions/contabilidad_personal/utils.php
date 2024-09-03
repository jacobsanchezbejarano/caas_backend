<?php

function obtenerTodosLosUsuariosDePago()
{
    return [];
}

function actualizarTablasTodosLosUsuarios($tabla, $nombre_campo, $valores)
{
    $nombres_usuario = obtenerTodosLosUsuariosDePago();

    foreach ($nombres_usuario as $usuario) {
        // Consulta para verificar si la columna existe
        $columnCheck = "SHOW COLUMNS FROM $tabla$usuario LIKE '$nombre_campo'";
        $result = $GLOBALS['con']->query($columnCheck);

        if ($result && $result->num_rows == 0) {
            // Si la columna no existe, se agrega
            $alterTable = "ALTER TABLE $tabla$usuario 
                           ADD COLUMN $nombre_campo $valores;";

            $resultAlter = $GLOBALS['con']->query($alterTable);

            if ($resultAlter) {
                echo "<br>Tabla $tabla$usuario actualizada correctamente.";
            } else {
                echo "<br>Error al actualizar la tabla $tabla$usuario: " . $GLOBALS['con']->error;
            }
        } else {
        }
    }
}

// EJEMPLO: actualizarTablasTodosLosUsuarios('librodiario', 'compras_estado', 'VARCHAR(15)');

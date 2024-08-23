<?php
// Ruta base de los módulos
$basePath = 'php/functions/';

// Cargar dinámicamente el archivo utils.php de cada módulo
foreach (MODULOS_INSTALADOS as $modulo) {
    $utilsPath = $basePath . $modulo . '/utils.php';

    if (file_exists($utilsPath)) {
        require_once $utilsPath;
    } else {
        echo "Error al incluir módulos, contacte al soporte.";
    }
}

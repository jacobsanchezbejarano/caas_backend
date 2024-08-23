<?php
$directory = '../sql/base/'; // Nombre del directorio donde se encuentran los archivos SQL

// Obtener la lista de archivos en el directorio
$files = scandir($directory);

// Iterar sobre los archivos
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
        $filePath = $directory . '/' . $file;

        // Leer el contenido del archivo SQL
        $sqlContent = file_get_contents($filePath);

        echo "<pre>{$sqlContent}</pre>";
    }
}
?>
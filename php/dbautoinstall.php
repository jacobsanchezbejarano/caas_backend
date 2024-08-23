<?php
include 'update_db_versions.php';
// Directorio base donde están las carpetas de bases de datos
$baseDir = 'sql';

// Obtener nombres de carpetas
$folders = array_filter(glob("$baseDir/*"), 'is_dir');

foreach ($folders as $folder) {
    $folderName = basename($folder);
    ensureFolderVersionExists($folderName, $con);
    updateDatabase($folderName, $con);
}

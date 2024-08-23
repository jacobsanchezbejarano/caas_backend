<?php

function ensureFolderVersionExists($folder, $con)
{
    $count = null;
    $stmt = $con->prepare("SELECT COUNT(*) FROM db_versions WHERE db_versions_folder = ?");
    $stmt->bind_param('s', $folder);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        $initialVersion = '0.0.0';
        $stmt = $con->prepare("INSERT INTO db_versions (db_versions_folder, db_versions_version) VALUES (?, ?)");
        $stmt->bind_param('ss', $folder, $initialVersion);
        $stmt->execute();
        $stmt->close();
    }
}

function updateDatabase($folder, $con)
{
    $latestVersion = getLatestVersion($folder, $con);

    // Obtener las versiones de los archivos en el directorio
    $sqlFiles = glob("sql/$folder/updates/*.sql");

    usort($sqlFiles, 'versionCompare');

    foreach ($sqlFiles as $file) {
        $version = getVersionFromFile($file);

        if (version_compare($version, $latestVersion, '>')) {
            applyUpdate($file, $con, $folder, $version);
        }
    }
}

function getLatestVersion($folder, $con)
{
    $version = null;
    $stmt = $con->prepare("SELECT db_versions_version FROM db_versions WHERE db_versions_folder = ? ORDER BY db_versions_id DESC LIMIT 1");
    $stmt->bind_param('s', $folder);
    $stmt->execute();
    $stmt->bind_result($version);
    $stmt->fetch();
    $stmt->close();

    return $version ? $version : '0.0.0';
}

function getVersionFromFile($file)
{
    return basename($file, '.sql');
}

function versionCompare($a, $b)
{
    return version_compare(getVersionFromFile($a), getVersionFromFile($b));
}

function applyUpdate($file, $con, $folder, $version)
{
    $count = null;
    $sql = file_get_contents($file);
    $queries = explode(';', $sql);

    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                if ($con->query($query) === FALSE) {
                    die("Error applying update $file: " . $con->error . "\nQuery: $query");
                }

                // Verificar si ya existe una entrada para el folder
                $stmt = $con->prepare("SELECT COUNT(*) FROM db_versions WHERE db_versions_folder = ?");
                $stmt->bind_param('s', $folder);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();

                if ($count > 0) {
                    // Actualizar la versión existente
                    $stmt = $con->prepare("UPDATE db_versions SET db_versions_version = ? WHERE db_versions_folder = ?");
                    $stmt->bind_param('ss', $version, $folder);
                } else {
                    // Insertar una nueva versión
                    $stmt = $con->prepare("INSERT INTO db_versions (db_versions_folder, db_versions_version) VALUES (?, ?)");
                    $stmt->bind_param('ss', $folder, $version);
                }

                $stmt->execute();
                $stmt->close();
            } catch (Exception $e) {
                echo "Error en actualización de módulo $folder: " . $e->getMessage() . "\n";
            }
        }
    }
}

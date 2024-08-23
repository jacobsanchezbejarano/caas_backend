CREATE TABLE IF NOT EXISTS db_versions (
    db_versions_id INT AUTO_INCREMENT PRIMARY KEY,
    db_versions_folder VARCHAR(255) NOT NULL,
    db_versions_version VARCHAR(255) NOT NULL,
    db_versions_applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
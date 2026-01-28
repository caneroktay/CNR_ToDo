-- Datenbank erstellen
CREATE DATABASE IF NOT EXISTS canerin_todo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE canerin_todo;

-- Benutzertabelle
CREATE TABLE IF NOT EXISTS benutzer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    benutzername VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    passwort VARCHAR(255) NOT NULL,
    erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Todo-Tabelle
CREATE TABLE IF NOT EXISTS todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    benutzer_id INT NOT NULL,
    titel VARCHAR(255) NOT NULL,
    beschreibung TEXT,
    erledigt BOOLEAN DEFAULT FALSE,
    prioritaet ENUM('niedrig', 'mittel', 'hoch') DEFAULT 'mittel',
    faelligkeit DATE,
    erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aktualisiert_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (benutzer_id) REFERENCES benutzer(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indizes für bessere Performance
CREATE INDEX idx_benutzer_id ON todos(benutzer_id);
CREATE INDEX idx_erledigt ON todos(erledigt);
CREATE INDEX idx_prioritaet ON todos(prioritaet);

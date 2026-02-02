<?php
// Datenbankkonfiguration
// define('DB_HOST', 'localhost');
define('DB_HOST', 'localhost:3307');
define('DB_NAME', 'canerin_todo');
define('DB_USER', 'root');
define('DB_PASS', '');

// Datenbankverbindung erstellen
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}

// Session starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hilfsfunktionen
function istAngemeldet() {
    return isset($_SESSION['benutzer_id']);
}

function weiterleitenWennNichtAngemeldet() {
    if (!istAngemeldet()) {
        header('Location: login.php');
        exit();
    }
}

function weiterleitenWennAngemeldet() {
    if (istAngemeldet()) {
        header('Location: dashboard.php');
        exit();
    }
}

function bereinigen($daten) {
    return htmlspecialchars(trim($daten), ENT_QUOTES, 'UTF-8');
}
?>

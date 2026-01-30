<?php
require_once 'config.php';

weiterleitenWennNichtAngemeldet();

$benutzer_id = $_SESSION['benutzer_id'];
$aktion = $_REQUEST['aktion'] ?? '';

try {
    switch ($aktion) {
        case 'erstellen':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $titel = bereinigen($_POST['titel']);
                $beschreibung = bereinigen($_POST['beschreibung'] ?? '');
                $prioritaet = $_POST['prioritaet'] ?? 'mittel';
                $faelligkeit = $_POST['faelligkeit'] ?? null;
                
                if (empty($titel)) {
                    throw new Exception('Titel ist erforderlich.');
                }
                
                if (!in_array($prioritaet, ['niedrig', 'mittel', 'hoch'])) {
                    $prioritaet = 'mittel';
                }
                
                $stmt = $pdo->prepare("INSERT INTO todos (benutzer_id, titel, beschreibung, prioritaet, faelligkeit) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$benutzer_id, $titel, $beschreibung, $prioritaet, $faelligkeit ?: null]);
                
                header('Location: dashboard.php?erfolg=erstellt');
                exit();
            }
            break;
            
        case 'bearbeiten':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = intval($_POST['id']);
                $titel = bereinigen($_POST['titel']);
                $beschreibung = bereinigen($_POST['beschreibung'] ?? '');
                $prioritaet = $_POST['prioritaet'] ?? 'mittel';
                $faelligkeit = $_POST['faelligkeit'] ?? null;
                
                if (empty($titel)) {
                    throw new Exception('Titel ist erforderlich.');
                }
                
                if (!in_array($prioritaet, ['niedrig', 'mittel', 'hoch'])) {
                    $prioritaet = 'mittel';
                }
                
                $stmt = $pdo->prepare("UPDATE todos SET titel = ?, beschreibung = ?, prioritaet = ?, faelligkeit = ? WHERE id = ? AND benutzer_id = ?");
                $stmt->execute([$titel, $beschreibung, $prioritaet, $faelligkeit ?: null, $id, $benutzer_id]);
                
                header('Location: dashboard.php?erfolg=bearbeitet');
                exit();
            }
            break;
            
        case 'erledigen':
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("UPDATE todos SET erledigt = 1 WHERE id = ? AND benutzer_id = ?");
            $stmt->execute([$id, $benutzer_id]);
            header('Location: dashboard.php?erfolg=erledigt');
            exit();
            break;
            
        case 'oeffnen':
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("UPDATE todos SET erledigt = 0 WHERE id = ? AND benutzer_id = ?");
            $stmt->execute([$id, $benutzer_id]);
            header('Location: dashboard.php?erfolg=geoeffnet');
            exit();
            break;
            
        case 'loeschen':
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("DELETE FROM todos WHERE id = ? AND benutzer_id = ?");
            $stmt->execute([$id, $benutzer_id]);
            header('Location: dashboard.php?erfolg=geloescht');
            exit();
            break;
            
        case 'abrufen':
            // Für AJAX-Anfragen zum Bearbeiten
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("SELECT * FROM todos WHERE id = ? AND benutzer_id = ?");
            $stmt->execute([$id, $benutzer_id]);
            $todo = $stmt->fetch();
            
            if ($todo) {
                header('Content-Type: application/json');
                echo json_encode(['erfolg' => true, 'todo' => $todo]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['erfolg' => false, 'nachricht' => 'Aufgabe nicht gefunden.']);
            }
            exit();
            break;
            
        default:
            header('Location: dashboard.php');
            exit();
    }
} catch (Exception $e) {
    header('Location: dashboard.php?fehler=' . urlencode($e->getMessage()));
    exit();
}
?>

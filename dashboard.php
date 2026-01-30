<?php
require_once 'config.php';

// Nur für angemeldete Benutzer
weiterleitenWennNichtAngemeldet();

$benutzer_id = $_SESSION['benutzer_id'];
$benutzername = $_SESSION['benutzername'];

// Statistiken abrufen
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as gesamt,
    SUM(CASE WHEN erledigt = 1 THEN 1 ELSE 0 END) as erledigt,
    SUM(CASE WHEN erledigt = 0 THEN 1 ELSE 0 END) as offen,
    SUM(CASE WHEN prioritaet = 'hoch' AND erledigt = 0 THEN 1 ELSE 0 END) as hoch_prioritaet
FROM todos WHERE benutzer_id = ?");
$stmt->execute([$benutzer_id]);
$stats = $stmt->fetch();

// Todos abrufen
$filter = $_GET['filter'] ?? 'alle';
$sql = "SELECT * FROM todos WHERE benutzer_id = ?";

if ($filter === 'offen') {
    $sql .= " AND erledigt = 0";
} elseif ($filter === 'erledigt') {
    $sql .= " AND erledigt = 1";
} elseif ($filter === 'hoch') {
    $sql .= " AND prioritaet = 'hoch' AND erledigt = 0";
}

$sql .= " ORDER BY 
    CASE 
        WHEN erledigt = 1 THEN 2 
        ELSE 1 
    END,
    CASE prioritaet 
        WHEN 'hoch' THEN 1 
        WHEN 'mittel' THEN 2 
        WHEN 'niedrig' THEN 3 
    END,
    faelligkeit ASC,
    erstellt_am DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$benutzer_id]);
$todos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#668dea">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Dashboard - CNR Todo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="apple-touch-icon" href="./img/todo_icon.png">
	<link rel="apple-touch-icon" sizes="152x152" href="./img/todo_icon.png">
	<link rel="apple-touch-icon" sizes="180x180" href="./img/todo_icon.png">
	<link rel="apple-touch-icon" sizes="167x167" href="./img/todo_icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="./img/todo_icon.png">
	<link rel="icon" type="image/png" sizes="16x16" href="./img/todo_icon.png">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($benutzername); ?>'s Todo</h1>
                <p class="user-info">Angemeldet als: <strong><?php echo htmlspecialchars($benutzername); ?></strong></p>
            </div>
            <div class="header-actions">
                <button onclick="oeffneNeueAufgabeModal()" class="btn btn-success btn-small">+ Neue Aufgabe</button>
                <a href="logout.php" class="btn btn-secondary btn-small">Abmelden</a>
            </div>
        </div>
        
        <!-- Statistiken -->
        <div class="stats">
            <div class="stat-card" onclick="window.location.href='dashboard.php?filter=alle'">
                <h3><?php echo $stats['gesamt']; ?></h3>
                <p>Gesamt Aufgaben</p>
            </div>
            <div class="stat-card" onclick="window.location.href='dashboard.php?filter=offen'">
                <h3><?php echo $stats['offen']; ?></h3>
                <p>Offene Aufgaben</p>
            </div>
            <div class="stat-card" onclick="window.location.href='dashboard.php?filter=erledigt'">
                <h3><?php echo $stats['erledigt']; ?></h3>
                <p>Erledigt</p>
            </div>
            <div class="stat-card" onclick="window.location.href='dashboard.php?filter=hoch'">
                <h3><?php echo $stats['hoch_prioritaet']; ?></h3>
                <p>Hohe Priorität</p>
            </div>
        </div>
        
        <!-- Hauptinhalt -->
        <div class="content">
            <h2 style="margin-bottom: 20px; color: var(--primär);">Meine Aufgaben</h2>
            
            
            <!-- Todos Liste -->
            <div class="todos-list">
                <?php if (empty($todos)): ?>
                    <div class="empty-state">
                        <h3>Keine Aufgaben gefunden</h3>
                        <p>Erstellen Sie Ihre erste Aufgabe, um loszulegen!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($todos as $todo): ?>
                        <div class="todo-item <?php echo $todo['erledigt'] ? 'erledigt' : ''; ?> prioritaet-<?php echo $todo['prioritaet']; ?>">
                            <div class="todo-header">
                                <div class="todo-title"><?php echo htmlspecialchars($todo['titel']); ?></div>
                                <div class="todo-badges">
                                    <span class="badge badge-<?php echo $todo['prioritaet']; ?>">
                                        <?php echo ucfirst($todo['prioritaet']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if ($todo['beschreibung']): ?>
                                <div class="todo-description">
                                    <?php echo nl2br(htmlspecialchars($todo['beschreibung'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="todo-meta">
                                <?php if ($todo['faelligkeit']): ?>
                                    <span>📅 Fällig: <?php echo date('d.m.Y', strtotime($todo['faelligkeit'])); ?></span>
                                <?php endif; ?>
                                <span>📝 Erstellt: <?php echo date('d.m.Y H:i', strtotime($todo['erstellt_am'])); ?></span>
                            </div>
                            
                            <div class="todo-actions">
                                <button onclick="bearbeiteAufgabe(<?php echo $todo['id']; ?>)" 
                                        class="btn btn-primary btn-icon">
                                    ✎ Bearbeiten
                                </button>
                                <button onclick="loescheAufgabe(<?php echo $todo['id']; ?>)" 
                                        class="btn btn-danger btn-icon">
                                    🗑 Löschen
                                </button>
                                <?php if (!$todo['erledigt']): ?>
                                    <button onclick="erledigeAufgabe(<?php echo $todo['id']; ?>)" 
                                            class="btn btn-success btn-icon">
                                        ✓ Als erledigt markieren
                                    </button>
                                <?php else: ?>
                                    <button onclick="oeffneAufgabe(<?php echo $todo['id']; ?>)" 
                                            class="btn btn-secondary btn-icon">
                                        ↺ Wieder öffnen
                                    </button>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal für neue/bearbeitete Aufgabe -->
    <div id="aufgabeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitel">Neue Aufgabe</h2>
                <button class="close-modal" onclick="schliesseModal()">&times;</button>
            </div>
            <form id="aufgabeForm" action="todo_actions.php" method="POST">
                <input type="hidden" name="aktion" id="formAktion" value="erstellen">
                <input type="hidden" name="id" id="formId" value="">
                
                <div class="form-group">
                    <label for="titel">Titel *</label>
                    <input type="text" id="titel" name="titel" required placeholder="Was möchten Sie erledigen?">
                </div>
                
                <div class="form-group">
                    <label for="beschreibung">Beschreibung</label>
                    <textarea id="beschreibung" name="beschreibung" placeholder="Weitere Details..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="prioritaet">Priorität</label>
                        <select id="prioritaet" name="prioritaet">
                            <option value="niedrig">Niedrig</option>
                            <option value="mittel" selected>Mittel</option>
                            <option value="hoch">Hoch</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="faelligkeit">Fälligkeitsdatum</label>
                        <input type="date" id="faelligkeit" name="faelligkeit">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
        </div>
    </div>
    
    <script>
        function oeffneNeueAufgabeModal() {
            document.getElementById('modalTitel').textContent = 'Neue Aufgabe';
            document.getElementById('formAktion').value = 'erstellen';
            document.getElementById('formId').value = '';
            document.getElementById('aufgabeForm').reset();
            document.getElementById('aufgabeModal').classList.add('active');
        }
        
        function schliesseModal() {
            document.getElementById('aufgabeModal').classList.remove('active');
        }
        
        function erledigeAufgabe(id) {
            if (confirm('Möchten Sie diese Aufgabe als erledigt markieren?')) {
                window.location.href = 'todo_actions.php?aktion=erledigen&id=' + id;
            }
        }
        
        function oeffneAufgabe(id) {
            if (confirm('Möchten Sie diese Aufgabe wieder öffnen?')) {
                window.location.href = 'todo_actions.php?aktion=oeffnen&id=' + id;
            }
        }
        
        function loescheAufgabe(id) {
            if (confirm('Möchten Sie diese Aufgabe wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) {
                window.location.href = 'todo_actions.php?aktion=loeschen&id=' + id;
            }
        }
        
        function bearbeiteAufgabe(id) {
            fetch('todo_actions.php?aktion=abrufen&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.erfolg) {
                        document.getElementById('modalTitel').textContent = 'Aufgabe bearbeiten';
                        document.getElementById('formAktion').value = 'bearbeiten';
                        document.getElementById('formId').value = data.todo.id;
                        document.getElementById('titel').value = data.todo.titel;
                        document.getElementById('beschreibung').value = data.todo.beschreibung || '';
                        document.getElementById('prioritaet').value = data.todo.prioritaet;
                        document.getElementById('faelligkeit').value = data.todo.faelligkeit || '';
                        document.getElementById('aufgabeModal').classList.add('active');
                    } else {
                        alert('Fehler beim Laden der Aufgabe.');
                    }
                })
                .catch(error => {
                    console.error('Fehler:', error);
                    alert('Ein Fehler ist aufgetreten.');
                });
        }
        
        // Modal schließen bei Klick außerhalb
        document.getElementById('aufgabeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                schliesseModal();
            }
        });
        
        // Modal schließen mit ESC-Taste
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                schliesseModal();
            }
        });
    </script>
</body>
</html>

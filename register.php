<?php
require_once 'config.php';

weiterleitenWennAngemeldet();

$fehler = '';
$erfolg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $benutzername = bereinigen($_POST['benutzername'] ?? '');
    $email = bereinigen($_POST['email'] ?? '');
    $passwort = $_POST['passwort'] ?? '';
    $passwort_wiederholen = $_POST['passwort_wiederholen'] ?? '';
    
    // Validierung
    if (empty($benutzername) || empty($email) || empty($passwort)) {
        $fehler = 'Bitte füllen Sie alle Felder aus.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fehler = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
    } elseif (strlen($benutzername) < 3) {
        $fehler = 'Der Benutzername muss mindestens 3 Zeichen lang sein.';
    } elseif (strlen($passwort) < 6) {
        $fehler = 'Das Passwort muss mindestens 6 Zeichen lang sein.';
    } elseif ($passwort !== $passwort_wiederholen) {
        $fehler = 'Die Passwörter stimmen nicht überein.';
    } else {
        try {
            // Prüfen ob Benutzername oder E-Mail bereits existiert
            $stmt = $pdo->prepare("SELECT id FROM benutzer WHERE benutzername = ? OR email = ?");
            $stmt->execute([$benutzername, $email]);
            
            if ($stmt->fetch()) {
                $fehler = 'Benutzername oder E-Mail-Adresse ist bereits vergeben.';
            } else {
                // Benutzer erstellen
                $passwort_hash = password_hash($passwort, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO benutzer (benutzername, email, passwort) VALUES (?, ?, ?)");
                $stmt->execute([$benutzername, $email, $passwort_hash]);
                
                $erfolg = 'Registrierung erfolgreich! Sie können sich jetzt anmelden.';
                
                // Nach 2 Sekunden zur Login-Seite weiterleiten
                header("refresh:2;url=login.php");
            }
        } catch (PDOException $e) {
            $fehler = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#668dea">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Registrierung - CNR Todo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="apple-touch-icon" href="./img/todo_icon.png">
	<link rel="apple-touch-icon" sizes="152x152" href="./img/todo_icon.png">
	<link rel="apple-touch-icon" sizes="180x180" href="./img/todo_icon.png">
	<link rel="apple-touch-icon" sizes="167x167" href="./img/todo_icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="./img/todo_icon.png">
	<link rel="icon" type="image/png" sizes="16x16" href="./img/todo_icon.png">
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>CNR Todo</h1>
            <p>Organisieren Sie Ihre Aufgaben</p>
        </div>
        
        <?php if ($fehler): ?>
            <div class="alert alert-error"><?php echo $fehler; ?></div>
        <?php endif; ?>
        
        <?php if ($erfolg): ?>
            <div class="alert alert-success"><?php echo $erfolg; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="benutzername">Benutzername</label>
                <input 
                    type="text" 
                    id="benutzername" 
                    name="benutzername" 
                    value="<?php echo htmlspecialchars($_POST['benutzername'] ?? ''); ?>"
                    required
                    placeholder="Ihr Benutzername"
                >
            </div>
            
            <div class="form-group">
                <label for="email">E-Mail-Adresse</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                    placeholder="ihre.email@beispiel.de"
                >
            </div>
            
            <div class="form-group">
                <label for="passwort">Passwort</label>
                <input 
                    type="password" 
                    id="passwort" 
                    name="passwort" 
                    required
                    placeholder="Mindestens 6 Zeichen"
                >
            </div>
            
            <div class="form-group">
                <label for="passwort_wiederholen">Passwort wiederholen</label>
                <input 
                    type="password" 
                    id="passwort_wiederholen" 
                    name="passwort_wiederholen" 
                    required
                    placeholder="Passwort bestätigen"
                >
            </div>
            
            <button type="submit" class="btn btn-primary">Registrieren</button>
        </form>
        
        <div class="link">
            Haben Sie bereits ein Konto? <a href="login.php">Jetzt anmelden</a>
        </div>
    </div>
</body>
</html>

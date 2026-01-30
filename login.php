<?php
require_once 'config.php';

// Weiterleiten wenn bereits angemeldet
weiterleitenWennAngemeldet();

$fehler = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $benutzername = bereinigen($_POST['benutzername'] ?? '');
    $passwort = $_POST['passwort'] ?? '';
    
    if (empty($benutzername) || empty($passwort)) {
        $fehler = 'Bitte geben Sie Benutzername und Passwort ein.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, benutzername, passwort FROM benutzer WHERE benutzername = ? OR email = ?");
            $stmt->execute([$benutzername, $benutzername]);
            $benutzer = $stmt->fetch();
            
            if ($benutzer && password_verify($passwort, $benutzer['passwort'])) {
                // Login erfolgreich
                $_SESSION['benutzer_id'] = $benutzer['id'];
                $_SESSION['benutzername'] = $benutzer['benutzername'];
                header('Location: dashboard.php');
                exit();
            } else {
                $fehler = 'Ungültiger Benutzername oder Passwort.';
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
    <title>Anmeldung - CNR Todo</title>
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
            <p>Willkommen zurück!</p>
        </div>
        
        <?php if ($fehler): ?>
            <div class="alert alert-error"><?php echo $fehler; ?></div>
        <?php endif; ?>

        <!-- Avatar -->
        <div class="login-container" style="text-align: center;">

            <div class="avatar-box">
                <img id="avatar" src="image/normal.png" alt="Avatar" width="200">
            </div>
        
        </div>
        <!-- Avatar ends -->
        

        <form method="POST" action="">
            <div class="form-group">
                <label for="benutzername">Benutzername oder E-Mail</label>
                <input 
                    type="text" 
                    id="benutzername" 
                    name="benutzername" 
                    onfocus="chngavatar('image/unten.png')" 
                    onblur="chngavatar('image/normal.png')"
                    value="<?php echo htmlspecialchars($_POST['benutzername'] ?? ''); ?>"
                    required
                    placeholder="Ihr Benutzername oder E-Mail"
                   
                >
            </div>
            
            <div class="form-group">
                <label for="passwort">Passwort</label>
                <input 
                    type="password" 
                    id="passwort" 
                    name="passwort" 
                    onfocus="chngavatar('image/passwort.png')" 
                    onblur="chngavatar('image/normal.png')"
                    required
                    placeholder="Ihr Passwort"
                >
            </div>
            
            <button type="submit" class="btn btn-primary">Anmelden</button>
        </form>
        
        <div class="link">
            Noch kein Konto? <a href="register.php">Jetzt registrieren</a>
        </div>
        <div class="container"> 
            <span>&copy <a href="https://caneroktay.com/">caneroktay.com</a> All Rights Received.</span>
    </div>
    </div>

    

    <script>        
        function chngavatar(imagePath) {
            document.getElementById('avatar').src = imagePath;
            }

        
        window.addEventListener('load', function() {
            const errorAlert = document.querySelector('.alert.alert-error');
            if (errorAlert) {
                chngavatar('image/fehler.png');
            }
        });

            document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            chngavatar('image/fehler.png');
            
            setTimeout(() => {
                chngavatar('image/normal.png');
            }, 3000);
            });

    </script>

</body>
</html>

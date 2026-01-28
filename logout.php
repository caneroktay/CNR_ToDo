<?php
require_once 'config.php';

// Session beenden
session_destroy();

// Zur Login-Seite weiterleiten
header('Location: login.php');
exit();
?>

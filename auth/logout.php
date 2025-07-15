<?php
require_once '../config/config.php';

// Distruge toate datele din sesiune
$_SESSION = array();

// Distruge cookie-ul de sesiune dacă există
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Distruge sesiunea
session_destroy();

// Pornește o sesiune nouă pentru mesajul de confirmare
session_start();
$_SESSION['success'] = 'Te-ai deconectat cu succes!';

// Redirect la homepage
redirect('../index.php');

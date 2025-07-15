<?php
require_once 'config/config.php';

echo "<h1>Debug Sesiune AUTO RARES</h1>";

echo "<h2>Informații sesiune:</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Activă' : 'Inactivă') . "</p>";

echo "<h2>Date din sesiune:</h2>";
if (!empty($_SESSION)) {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    echo "<p>Sesiunea este goală</p>";
}

echo "<h2>Test funcții:</h2>";
echo "<p><strong>isLoggedIn():</strong> " . (isLoggedIn() ? 'DA' : 'NU') . "</p>";
echo "<p><strong>isAdmin():</strong> " . (isAdmin() ? 'DA' : 'NU') . "</p>";
echo "<p><strong>isSeller():</strong> " . (isSeller() ? 'DA' : 'NU') . "</p>";

echo "<h2>Acțiuni:</h2>";
echo "<p><a href='auth/login.php'>Login</a> | <a href='auth/logout.php'>Logout</a> | <a href='setup-admin.php'>Setup Admin</a></p>";

echo "<h2>Pentru a vedea debug info în header:</h2>";
echo "<p>Adaugă <code>?debug=1</code> la URL-ul oricărei pagini</p>";

<?php
require_once 'database.php';

// Configurație aplicație - ACTUALIZEAZĂ ACESTE VALORI
define('SITE_NAME', 'AUTO RARES');

// Detectează automat URL-ul de bază
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];

// Extrage path-ul până la folder-ul aplicației
$script_name = $_SERVER['SCRIPT_NAME'];
$path = dirname($script_name);
if ($path === '/') {
    $path = '';
}

define('SITE_URL', $protocol . '://' . $host . $path);
define('BASE_PATH', $path);

// Constante pentru upload
define('UPLOAD_PATH', 'uploads/cars/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Pornește sesiunea
session_start();

// Funcții helper
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isSeller()
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'seller';
}

function redirect($url)
{
    // Dacă URL-ul nu începe cu http, adaugă BASE_PATH
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = BASE_PATH . '/' . ltrim($url, '/');
    }
    header("Location: $url");
    exit();
}

function formatPrice($price)
{
    return number_format($price, 0, ',', '.') . ' €';
}

function formatMileage($mileage)
{
    return number_format($mileage, 0, ',', '.') . ' km';
}

function getCarStatusText($status)
{
    $statuses = [
        'pending' => 'În așteptare',
        'approved' => 'Aprobată',
        'rejected' => 'Respinsă',
        'sold' => 'Vândută'
    ];
    return $statuses[$status] ?? 'Necunoscut';
}

function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Funcție pentru URL-uri corecte
function url($path = '')
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

<?php
// Configurație pentru DBngin pe macOS
$host = '127.0.0.1'; // Folosește IP în loc de localhost
$port = '3306'; // SCHIMBĂ cu portul din DBngin (ex: 33060, 3307, etc.)
$dbname = 'parcauto_rares';
$username = 'root';
$password = ''; // Sau parola din DBngin dacă ai setat una

try {
    // Pentru DBngin, folosește întotdeauna TCP cu port explicit
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "<!-- Conectat cu succes la DBngin MySQL pe port $port -->";
} catch (PDOException $e) {
    die("Conexiune eșuată: " . $e->getMessage() .
        "<br><br>Verifică în DBngin:<br>" .
        "1. Dacă MySQL este pornit (buton verde)<br>" .
        "2. Care este portul exact<br>" .
        "3. Dacă ai setat o parolă pentru root");
}

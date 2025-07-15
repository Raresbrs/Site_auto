<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isSeller()) {
    echo json_encode(['success' => false, 'message' => 'Acces interzis']);
    exit;
}

if ($_POST && isset($_POST['car_id'])) {
    $car_id = intval($_POST['car_id']);

    // Verifică dacă mașina aparține utilizatorului
    $stmt = $pdo->prepare("SELECT id FROM cars WHERE id = ? AND seller_id = ?");
    $stmt->execute([$car_id, $_SESSION['user_id']]);

    if ($stmt->fetch()) {
        $update_stmt = $pdo->prepare("UPDATE cars SET status = 'sold' WHERE id = ?");
        if ($update_stmt->execute([$car_id])) {
            echo json_encode(['success' => true, 'message' => 'Mașina a fost marcată ca vândută']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Eroare la actualizare']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Mașina nu a fost găsită']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Date invalide']);
}

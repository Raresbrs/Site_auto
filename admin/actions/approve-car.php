<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Acces interzis']);
    exit;
}

if ($_POST && isset($_POST['car_id']) && isset($_POST['action'])) {
    $car_id = intval($_POST['car_id']);
    $action = $_POST['action'];
    $reason = $_POST['reason'] ?? '';

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE cars SET status = 'approved' WHERE id = ?");
        if ($stmt->execute([$car_id])) {
            echo json_encode(['success' => true, 'message' => 'Mașina a fost aprobată']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Eroare la aprobare']);
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE cars SET status = 'rejected', rejection_reason = ? WHERE id = ?");
        if ($stmt->execute([$reason, $car_id])) {
            echo json_encode(['success' => true, 'message' => 'Mașina a fost respinsă']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Eroare la respingere']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Acțiune invalidă']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Date invalide']);
}

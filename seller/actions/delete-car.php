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
        try {
            $pdo->beginTransaction();

            // Șterge imaginile din baza de date și de pe disk
            $images_stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE car_id = ?");
            $images_stmt->execute([$car_id]);
            $images = $images_stmt->fetchAll();

            foreach ($images as $image) {
                $file_path = '../../' . $image['image_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            // Șterge din baza de date (cascade va șterge automat imaginile și mesajele)
            $delete_stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
            $delete_stmt->execute([$car_id]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Mașina a fost ștearsă cu succes']);
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => 'Eroare la ștergere']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Mașina nu a fost găsită']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Date invalide']);
}

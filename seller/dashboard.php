<?php
require_once '../config/config.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('../auth/login.php');
}

$page_title = 'Anunțurile Mele';

// Obține statistici
$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_cars,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_cars,
        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_cars,
        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_cars,
        COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_cars,
        SUM(views) as total_views
    FROM cars WHERE seller_id = ?
");
$stats_stmt->execute([$_SESSION['user_id']]);
$stats = $stats_stmt->fetch();

// Obține mașinile
$cars_stmt = $pdo->prepare("
    SELECT c.*, ci.image_path 
    FROM cars c 
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_main = 1 
    WHERE c.seller_id = ? 
    ORDER BY c.created_at DESC
");
$cars_stmt->execute([$_SESSION['user_id']]);
$cars = $cars_stmt->fetchAll();

// Obține mesajele recente
$messages_stmt = $pdo->prepare("
    SELECT cm.*, c.brand, c.model 
    FROM contact_messages cm 
    LEFT JOIN cars c ON cm.car_id = c.id 
    WHERE c.seller_id = ? 
    ORDER BY cm.created_at DESC 
    LIMIT 5
");
$messages_stmt->execute([$_SESSION['user_id']]);
$recent_messages = $messages_stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard Vânzător</h2>
        <a href="add-car.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Adaugă Mașină Nouă
        </a>
    </div>

    <!-- Statistici -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-car fa-2x text-primary mb-2"></i>
                    <h4><?php echo $stats['total_cars']; ?></h4>
                    <small class="text-muted">Total Mașini</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h4><?php echo $stats['pending_cars']; ?></h4>
                    <small class="text-muted">În Așteptare</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check fa-2x text-success mb-2"></i>
                    <h4><?php echo $stats['approved_cars']; ?></h4>
                    <small class="text-muted">Aprobate</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-times fa-2x text-danger mb-2"></i>
                    <h4><?php echo $stats['rejected_cars']; ?></h4>
                    <small class="text-muted">Respinse</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-handshake fa-2x text-info mb-2"></i>
                    <h4><?php echo $stats['sold_cars']; ?></h4>
                    <small class="text-muted">Vândute</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-eye fa-2x text-secondary mb-2"></i>
                    <h4><?php echo $stats['total_views']; ?></h4>
                    <small class="text-muted">Vizualizări</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lista mașinilor -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Mașinile Tale</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($cars)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-car fa-3x text-muted mb-3"></i>
                            <h5>Nu ai încă nicio mașină</h5>
                            <p class="text-muted">Adaugă prima ta mașină pentru a începe să vinzi.</p>
                            <a href="add-car.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Adaugă Mașină
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mașină</th>
                                        <th>Preț</th>
                                        <th>Status</th>
                                        <th>Vizualizări</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cars as $car): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($car['image_path']): ?>
                                                        <img src="../<?php echo htmlspecialchars($car['image_path']); ?>"
                                                            class="rounded me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                            style="width: 60px; height: 40px;">
                                                            <i class="fas fa-car text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo $car['year']; ?> • <?php echo formatMileage($car['mileage']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><strong><?php echo formatPrice($car['price']); ?></strong></td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'sold' => 'secondary'
                                                ];
                                                $status_text = [
                                                    'pending' => 'În așteptare',
                                                    'approved' => 'Aprobată',
                                                    'rejected' => 'Respinsă',
                                                    'sold' => 'Vândută'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $status_class[$car['status']]; ?>">
                                                    <?php echo $status_text[$car['status']]; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $car['views']; ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($car['status'] === 'approved'): ?>
                                                        <a href="../car-details.php?id=<?php echo $car['id']; ?>"
                                                            class="btn btn-outline-primary" target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="edit-car.php?id=<?php echo $car['id']; ?>"
                                                        class="btn btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($car['status'] === 'approved'): ?>
                                                        <button class="btn btn-outline-success"
                                                            onclick="markAsSold(<?php echo $car['id']; ?>)">
                                                            <i class="fas fa-handshake"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-danger"
                                                        onclick="deleteCar(<?php echo $car['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mesaje recente -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-envelope me-2"></i>Mesaje Recente</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_messages)): ?>
                        <p class="text-muted text-center py-3">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Nu ai mesaje încă
                        </p>
                    <?php else: ?>
                        <?php foreach ($recent_messages as $message): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                    <small class="text-muted"><?php echo date('d.m.Y', strtotime($message['created_at'])); ?></small>
                                </div>
                                <small class="text-muted">
                                    Pentru: <?php echo htmlspecialchars($message['brand'] . ' ' . $message['model']); ?>
                                </small>
                                <p class="mb-1"><?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>...</p>
                                <small>
                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                        <?php echo htmlspecialchars($message['email']); ?>
                                    </a>
                                    <?php if ($message['phone']): ?>
                                        | <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>">
                                            <?php echo htmlspecialchars($message['phone']); ?>
                                        </a>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="messages.php" class="btn btn-sm btn-outline-primary">Vezi Toate Mesajele</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function markAsSold(carId) {
        if (confirm('Ești sigur că vrei să marchezi această mașină ca vândută?')) {
            fetch('actions/mark-sold.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'car_id=' + carId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('A apărut o eroare: ' + data.message);
                    }
                });
        }
    }

    function deleteCar(carId) {
        if (confirm('Ești sigur că vrei să ștergi această mașină? Această acțiune nu poate fi anulată.')) {
            fetch('actions/delete-car.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'car_id=' + carId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('A apărut o eroare: ' + data.message);
                    }
                });
        }
    }
</script>

<?php include '../includes/footer.php'; ?>
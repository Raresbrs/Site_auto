<?php
require_once '../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$page_title = 'Dashboard Admin';

// Obține statistici generale
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_cars,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_cars,
        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_cars,
        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_cars,
        COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_cars
    FROM cars
");
$cars_stats = $stats_stmt->fetch();

$users_stats = $pdo->query("
    SELECT 
        COUNT(*) as total_users,
        COUNT(CASE WHEN user_type = 'seller' THEN 1 END) as sellers,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users
    FROM users
")->fetch();

$messages_stats = $pdo->query("
    SELECT 
        COUNT(*) as total_messages,
        COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread_messages
    FROM contact_messages
")->fetch();

// Obține mașinile în așteptare
$pending_cars_stmt = $pdo->prepare("
    SELECT c.*, ci.image_path, u.full_name as seller_name 
    FROM cars c 
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_main = 1 
    LEFT JOIN users u ON c.seller_id = u.id 
    WHERE c.status = 'pending' 
    ORDER BY c.created_at ASC 
    LIMIT 10
");
$pending_cars_stmt->execute();
$pending_cars = $pending_cars_stmt->fetchAll();

// Obține mesajele recente
$recent_messages_stmt = $pdo->prepare("
    SELECT cm.*, c.brand, c.model, u.full_name as seller_name
    FROM contact_messages cm 
    LEFT JOIN cars c ON cm.car_id = c.id 
    LEFT JOIN users u ON c.seller_id = u.id 
    ORDER BY cm.created_at DESC 
    LIMIT 5
");
$recent_messages_stmt->execute();
$recent_messages = $recent_messages_stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container py-4">
    <h2>Dashboard Administrator</h2>
    <p class="text-muted">Bine ai venit în panoul de administrare AUTO RARES</p>

    <!-- Statistici -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $cars_stats['total_cars']; ?></h4>
                            <p class="mb-0">Total Mașini</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-car fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $cars_stats['pending_cars']; ?></h4>
                            <p class="mb-0">În Așteptare</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $users_stats['sellers']; ?></h4>
                            <p class="mb-0">Vânzători</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $messages_stats['unread_messages']; ?></h4>
                            <p class="mb-0">Mesaje Noi</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acțiuni rapide -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Acțiuni Rapide</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="manage-cars.php?status=pending" class="btn btn-warning w-100">
                                <i class="fas fa-clock me-2"></i>Aprobare Mașini
                                <?php if ($cars_stats['pending_cars'] > 0): ?>
                                    <span class="badge bg-light text-dark ms-2"><?php echo $cars_stats['pending_cars']; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage-cars.php" class="btn btn-primary w-100">
                                <i class="fas fa-car me-2"></i>Toate Mașinile
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage-users.php" class="btn btn-info w-100">
                                <i class="fas fa-users me-2"></i>Utilizatori
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="messages.php" class="btn btn-success w-100">
                                <i class="fas fa-envelope me-2"></i>Mesaje
                                <?php if ($messages_stats['unread_messages'] > 0): ?>
                                    <span class="badge bg-light text-dark ms-2"><?php echo $messages_stats['unread_messages']; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Mașini în așteptare -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-clock me-2"></i>Mașini în Așteptare Aprobare</h5>
                    <a href="manage-cars.php?status=pending" class="btn btn-sm btn-outline-primary">Vezi Toate</a>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_cars)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">Nu sunt mașini în așteptare!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mașină</th>
                                        <th>Vânzător</th>
                                        <th>Preț</th>
                                        <th>Data</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_cars as $car): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($car['image_path']): ?>
                                                        <img src="../<?php echo htmlspecialchars($car['image_path']); ?>"
                                                            class="rounded me-3" style="width: 50px; height: 35px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                            style="width: 50px; height: 35px;">
                                                            <i class="fas fa-car text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo $car['year']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($car['seller_name']); ?></td>
                                            <td><?php echo formatPrice($car['price']); ?></td>
                                            <td><?php echo date('d.m.Y', strtotime($car['created_at'])); ?></td>
                                            <td>

                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-success" onclick="approveCar(<?php echo $car['id']; ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger" onclick="rejectCar(<?php echo $car['id']; ?>)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <a href="view-car.php?id=<?php echo $car['id']; ?>" class="btn btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-envelope me-2"></i>Mesaje Recente</h5>
                    <a href="messages.php" class="btn btn-sm btn-outline-primary">Vezi Toate</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_messages)): ?>
                        <p class="text-muted text-center py-3">Nu sunt mesaje încă</p>
                    <?php else: ?>
                        <?php foreach ($recent_messages as $message): ?>
                            <div class="border-bottom pb-2 mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                    <small class="text-muted"><?php echo date('d.m', strtotime($message['created_at'])); ?></small>
                                </div>
                                <small class="text-muted">
                                    Pentru: <?php echo htmlspecialchars($message['brand'] . ' ' . $message['model']); ?>
                                </small>
                                <p class="mb-1 small"><?php echo htmlspecialchars(substr($message['message'], 0, 80)); ?>...</p>
                                <small class="text-primary"><?php echo htmlspecialchars($message['seller_name']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistici detaliate -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Statistici Detaliate</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Mașini Aprobate</span>
                            <span class="badge bg-success"><?php echo $cars_stats['approved_cars']; ?></span>
                        </div>
                        <div class="progress mt-1" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $cars_stats['total_cars'] > 0 ? ($cars_stats['approved_cars'] / $cars_stats['total_cars']) * 100 : 0; ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Mașini Vândute</span>
                            <span class="badge bg-secondary"><?php echo $cars_stats['sold_cars']; ?></span>
                        </div>
                        <div class="progress mt-1" style="height: 5px;">
                            <div class="progress-bar bg-secondary" style="width: <?php echo $cars_stats['total_cars'] > 0 ? ($cars_stats['sold_cars'] / $cars_stats['total_cars']) * 100 : 0; ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Utilizatori Activi</span>
                            <span class="badge bg-info"><?php echo $users_stats['active_users']; ?></span>
                        </div>
                        <div class="progress mt-1" style="height: 5px;">
                            <div class="progress-bar bg-info" style="width: <?php echo $users_stats['total_users'] > 0 ? ($users_stats['active_users'] / $users_stats['total_users']) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function approveCar(carId) {
        if (confirm('Ești sigur că vrei să aprovi această mașină?')) {
            fetch('actions/approve-car.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'car_id=' + carId + '&action=approve'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Eroare: ' + data.message);
                    }
                });
        }
    }

    function rejectCar(carId) {
        const reason = prompt('Motiv respingere (opțional):');
        if (reason !== null) {
            fetch('actions/approve-car.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'car_id=' + carId + '&action=reject&reason=' + encodeURIComponent(reason)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Eroare: ' + data.message);
                    }
                });
        }
    }
</script>

<?php include '../includes/footer.php'; ?>
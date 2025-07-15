<?php
require_once '../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$page_title = 'Gestionare Mașini';

// Filtre
$status_filter = $_GET['status'] ?? '';
$brand_filter = $_GET['brand'] ?? '';
$search = $_GET['search'] ?? '';

// Paginare
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Construire query
$where = ['1=1'];
$params = [];

if (!empty($status_filter)) {
    $where[] = "c.status = ?";
    $params[] = $status_filter;
}

if (!empty($brand_filter)) {
    $where[] = "c.brand = ?";
    $params[] = $brand_filter;
}

if (!empty($search)) {
    $where[] = "(c.brand LIKE ? OR c.model LIKE ? OR u.full_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = implode(' AND ', $where);

// Obține mașinile
$sql = "
    SELECT c.*, ci.image_path, u.full_name as seller_name, u.email as seller_email
    FROM cars c 
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_main = 1 
    LEFT JOIN users u ON c.seller_id = u.id 
    WHERE $where_clause 
    ORDER BY c.created_at DESC 
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

// Contorizare pentru paginare
$count_sql = "SELECT COUNT(*) FROM cars c LEFT JOIN users u ON c.seller_id = u.id WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_cars = $count_stmt->fetchColumn();
$total_pages = ceil($total_cars / $per_page);

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestionare Mașini</h2>
        <div class="btn-group">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Filtre -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Toate</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>În așteptare</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Aprobate</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Respinse</option>
                        <option value="sold" <?php echo $status_filter === 'sold' ? 'selected' : ''; ?>>Vândute</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Marcă</label>
                    <select name="brand" class="form-select">
                        <option value="">Toate mărcile</option>
                        <option value="Audi" <?php echo $brand_filter === 'Audi' ? 'selected' : ''; ?>>Audi</option>
                        <option value="BMW" <?php echo $brand_filter === 'BMW' ? 'selected' : ''; ?>>BMW</option>
                        <option value="Mercedes" <?php echo $brand_filter === 'Mercedes' ? 'selected' : ''; ?>>Mercedes</option>
                        <option value="Volkswagen" <?php echo $brand_filter === 'Volkswagen' ? 'selected' : ''; ?>>Volkswagen</option>
                        <option value="Ford" <?php echo $brand_filter === 'Ford' ? 'selected' : ''; ?>>Ford</option>
                        <option value="Opel" <?php echo $brand_filter === 'Opel' ? 'selected' : ''; ?>>Opel</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Căutare</label>
                    <input type="text" name="search" class="form-control"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Marcă, model sau nume vânzător...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Filtrează</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rezultate -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Mașini (<?php echo $total_cars; ?> găsite)</h5>
                <?php if ($status_filter === 'pending'): ?>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-success" onclick="bulkApprove()">
                            <i class="fas fa-check me-2"></i>Aprobă Selectate
                        </button>
                        <button class="btn btn-danger" onclick="bulkReject()">
                            <i class="fas fa-times me-2"></i>Respinge Selectate
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($cars)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5>Nicio mașină găsită</h5>
                    <p class="text-muted">Încearcă să modifici filtrele de căutare</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <?php if ($status_filter === 'pending'): ?>
                                    <th width="30">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                <?php endif; ?>
                                <th>Mașină</th>
                                <th>Vânzător</th>
                                <th>Preț</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Vizualizări</th>
                                <th width="120">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                                <tr>
                                    <?php if ($status_filter === 'pending'): ?>
                                        <td>
                                            <input type="checkbox" name="selected_cars[]" value="<?php echo $car['id']; ?>" class="car-checkbox">
                                        </td>
                                    <?php endif; ?>
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
                                    <td>
                                        <div>
                                            <?php echo htmlspecialchars($car['seller_name']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($car['seller_email']); ?></small>
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
                                    <td><?php echo date('d.m.Y H:i', strtotime($car['created_at'])); ?></td>
                                    <td><?php echo $car['views']; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view-car.php?id=<?php echo $car['id']; ?>"
                                                class="btn btn-outline-info" title="Vezi detalii">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($car['status'] === 'pending'): ?>
                                                <button class="btn btn-outline-success"
                                                    onclick="approveCar(<?php echo $car['id']; ?>)" title="Aprobă">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-outline-danger"
                                                    onclick="rejectCar(<?php echo $car['id']; ?>)" title="Respinge">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php elseif ($car['status'] === 'approved'): ?>
                                                <a href="../car-details.php?id=<?php echo $car['id']; ?>"
                                                    class="btn btn-outline-primary" target="_blank" title="Vezi public">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-danger"
                                                onclick="deleteCar(<?php echo $car['id']; ?>)" title="Șterge">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginare -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Paginare mașini">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.car-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    function getSelectedCars() {
        const selected = [];
        document.querySelectorAll('.car-checkbox:checked').forEach(checkbox => {
            selected.push(checkbox.value);
        });
        return selected;
    }

    function bulkApprove() {
        const selected = getSelectedCars();
        if (selected.length === 0) {
            alert('Te rugăm să selectezi cel puțin o mașină');
            return;
        }

        if (confirm(`Ești sigur că vrei să apropi ${selected.length} mașini?`)) {
            fetch('actions/bulk-actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=approve&car_ids=' + selected.join(',')
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

    function bulkReject() {
        const selected = getSelectedCars();
        if (selected.length === 0) {
            alert('Te rugăm să selectezi cel puțin o mașină');
            return;
        }

        const reason = prompt('Motiv respingere (opțional):');
        if (reason !== null) {
            fetch('actions/bulk-actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=reject&car_ids=' + selected.join(',') + '&reason=' + encodeURIComponent(reason)
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

    function approveCar(carId) {
        if (confirm('Ești sigur că vrei să apropi această mașină?')) {
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
                        alert('Eroare: ' + data.message);
                    }
                });
        }
    }
</script>

<?php include '../includes/footer.php'; ?>
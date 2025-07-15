<?php
require_once 'config/config.php';
$page_title = 'Toate Mașinile';

// Parametri de căutare
$brand = $_GET['brand'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$year_min = $_GET['year_min'] ?? '';
$year_max = $_GET['year_max'] ?? '';
$fuel_type = $_GET['fuel_type'] ?? '';
$transmission = $_GET['transmission'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Paginare
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Construire query
$where = ["c.status = 'approved'"];
$params = [];

if (!empty($brand)) {
    $where[] = "c.brand = ?";
    $params[] = $brand;
}

if (!empty($price_min)) {
    $where[] = "c.price >= ?";
    $params[] = $price_min;
}

if (!empty($price_max)) {
    $where[] = "c.price <= ?";
    $params[] = $price_max;
}

if (!empty($year_min)) {
    $where[] = "c.year >= ?";
    $params[] = $year_min;
}

if (!empty($year_max)) {
    $where[] = "c.year <= ?";
    $params[] = $year_max;
}

if (!empty($fuel_type)) {
    $where[] = "c.fuel_type = ?";
    $params[] = $fuel_type;
}

if (!empty($transmission)) {
    $where[] = "c.transmission = ?";
    $params[] = $transmission;
}

// Sortare
$order_by = "c.created_at DESC";
switch ($sort) {
    case 'price_asc':
        $order_by = "c.price ASC";
        break;
    case 'price_desc':
        $order_by = "c.price DESC";
        break;
    case 'year_desc':
        $order_by = "c.year DESC";
        break;
    case 'mileage_asc':
        $order_by = "c.mileage ASC";
        break;
}

$where_clause = implode(' AND ', $where);

// Obține mașinile
$sql = "
    SELECT c.*, ci.image_path, u.full_name as seller_name 
    FROM cars c 
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_main = 1 
    LEFT JOIN users u ON c.seller_id = u.id 
    WHERE $where_clause 
    ORDER BY $order_by 
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

// Numără totalul pentru paginare
$count_sql = "SELECT COUNT(*) FROM cars c WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_cars = $count_stmt->fetchColumn();
$total_pages = ceil($total_cars / $per_page);

include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Filtru și sortare -->
    <div class="row mb-4">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-filter me-2"></i>Filtrează</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="cars.php">
                        <div class="mb-3">
                            <label class="form-label">Marcă</label>
                            <select name="brand" class="form-select">
                                <option value="">Toate mărcile</option>
                                <option value="Audi" <?php echo $brand === 'Audi' ? 'selected' : ''; ?>>Audi</option>
                                <option value="BMW" <?php echo $brand === 'BMW' ? 'selected' : ''; ?>>BMW</option>
                                <option value="Mercedes" <?php echo $brand === 'Mercedes' ? 'selected' : ''; ?>>Mercedes</option>
                                <option value="Volkswagen" <?php echo $brand === 'Volkswagen' ? 'selected' : ''; ?>>Volkswagen</option>
                                <option value="Skoda" <?php echo $brand === 'Skoda' ? 'selected' : ''; ?>>Skoda</option>
                                <option value="Ford" <?php echo $brand === 'Ford' ? 'selected' : ''; ?>>Ford</option>
                                <option value="Opel" <?php echo $brand === 'Opel' ? 'selected' : ''; ?>>Opel</option>
                                <option value="Renault" <?php echo $brand === 'Renault' ? 'selected' : ''; ?>>Renault</option>
                                <option value="Peugeot" <?php echo $brand === 'Peugeot' ? 'selected' : ''; ?>>Peugeot</option>
                                <option value="Dacia" <?php echo $brand === 'Dacia' ? 'selected' : ''; ?>>Dacia</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Preț min</label>
                                <input type="number" name="price_min" class="form-control"
                                    value="<?php echo htmlspecialchars($price_min); ?>" placeholder="€">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Preț max</label>
                                <input type="number" name="price_max" class="form-control"
                                    value="<?php echo htmlspecialchars($price_max); ?>" placeholder="€">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">An min</label>
                                <input type="number" name="year_min" class="form-control"
                                    value="<?php echo htmlspecialchars($year_min); ?>" placeholder="2010">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">An max</label>
                                <input type="number" name="year_max" class="form-control"
                                    value="<?php echo htmlspecialchars($year_max); ?>" placeholder="2024">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Combustibil</label>
                            <select name="fuel_type" class="form-select">
                                <option value="">Toate tipurile</option>
                                <option value="benzina" <?php echo $fuel_type === 'benzina' ? 'selected' : ''; ?>>Benzină</option>
                                <option value="diesel" <?php echo $fuel_type === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                                <option value="gpl" <?php echo $fuel_type === 'gpl' ? 'selected' : ''; ?>>GPL</option>
                                <option value="hibrid" <?php echo $fuel_type === 'hibrid' ? 'selected' : ''; ?>>Hibrid</option>
                                <option value="electric" <?php echo $fuel_type === 'electric' ? 'selected' : ''; ?>>Electric</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Transmisie</label>
                            <select name="transmission" class="form-select">
                                <option value="">Toate tipurile</option>
                                <option value="manuala" <?php echo $transmission === 'manuala' ? 'selected' : ''; ?>>Manuală</option>
                                <option value="automata" <?php echo $transmission === 'automata' ? 'selected' : ''; ?>>Automată</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-search me-2"></i>Caută
                        </button>
                        <a href="cars.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-2"></i>Resetează
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- Header cu rezultate și sortare -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3>Mașini Disponibile</h3>
                    <p class="text-muted mb-0">Găsite <?php echo $total_cars; ?> mașini</p>
                </div>
                <div>
                    <form method="GET" class="d-flex" id="sortForm">
                        <!-- Păstrează parametrii de căutare -->
                        <?php foreach ($_GET as $key => $value): ?>
                            <?php if ($key !== 'sort' && $key !== 'page'): ?>
                                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Cele mai noi</option>
                            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Preț crescător</option>
                            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Preț descrescător</option>
                            <option value="year_desc" <?php echo $sort === 'year_desc' ? 'selected' : ''; ?>>An descrescător</option>
                            <option value="mileage_asc" <?php echo $sort === 'mileage_asc' ? 'selected' : ''; ?>>Kilometraj crescător</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Lista mașinilor -->
            <div class="row">
                <?php if (empty($cars)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4>Nicio mașină găsită</h4>
                            <p class="text-muted">Încearcă să modifici criteriile de căutare.</p>
                            <a href="cars.php" class="btn btn-primary">Vezi toate mașinile</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($cars as $car): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card car-card">
                                <?php if ($car['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars($car['image_path']); ?>"
                                        class="card-img-top car-image"
                                        alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>">
                                <?php else: ?>
                                    <div class="card-img-top car-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-car fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h5>
                                    <p class="car-price"><?php echo formatPrice($car['price']); ?></p>

                                    <div class="car-details">
                                        <div class="car-detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <small><?php echo $car['year']; ?></small>
                                        </div>
                                        <div class="car-detail-item">
                                            <i class="fas fa-road"></i>
                                            <small><?php echo formatMileage($car['mileage']); ?></small>
                                        </div>
                                        <div class="car-detail-item">
                                            <i class="fas fa-gas-pump"></i>
                                            <small><?php echo ucfirst($car['fuel_type']); ?></small>
                                        </div>
                                        <div class="car-detail-item">
                                            <i class="fas fa-cogs"></i>
                                            <small><?php echo ucfirst($car['transmission']); ?></small>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="car-details.php?id=<?php echo $car['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye me-1"></i>Vezi Detalii
                                        </a>
                                        <small class="text-muted">
                                            <i class="fas fa-eye me-1"></i><?php echo $car['views']; ?> vizualizări
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Paginare -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Paginare mașini">
                    <ul class="pagination justify-content-center">
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
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
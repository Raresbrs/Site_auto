<?php
require_once 'config/config.php';
$page_title = 'Acasă';

// Obține mașinile aprobate recent
$stmt = $pdo->prepare("
    SELECT c.*, ci.image_path, u.full_name as seller_name 
    FROM cars c 
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_main = 1 
    LEFT JOIN users u ON c.seller_id = u.id 
    WHERE c.status = 'approved' 
    ORDER BY c.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$featured_cars = $stmt->fetchAll();

// Statistici
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_cars,
        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_cars,
        COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_cars
    FROM cars
");
$stats = $stats_stmt->fetch();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1>AUTO RARES</h1>
                    <p class="lead">Găsește mașina perfectă pentru tine din colecția noastră de vehicule second-hand verificate și de calitate.</p>
                    <a href="cars.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-car me-2"></i>Vezi Toate Mașinile
                    </a>
                    <a href="auth/register.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-plus me-2"></i>Vinde Mașina Ta
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="stat-item">
                            <h3 class="display-4"><?php echo $stats['approved_cars']; ?></h3>
                            <p>Mașini Disponibile</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <h3 class="display-4"><?php echo $stats['sold_cars']; ?></h3>
                            <p>Mașini Vândute</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <h3 class="display-4">100%</h3>
                            <p>Satisfacție</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Form -->
<div class="container">
    <div class="search-form">
        <form action="cars.php" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Marcă</label>
                    <select name="brand" class="form-select">
                        <option value="">Toate mărcile</option>
                        <option value="Audi">Audi</option>
                        <option value="BMW">BMW</option>
                        <option value="Mercedes">Mercedes</option>
                        <option value="Volkswagen">Volkswagen</option>
                        <option value="Skoda">Skoda</option>
                        <option value="Ford">Ford</option>
                        <option value="Opel">Opel</option>
                        <option value="Renault">Renault</option>
                        <option value="Peugeot">Peugeot</option>
                        <option value="Dacia">Dacia</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Preț minim</label>
                    <input type="number" name="price_min" class="form-control" placeholder="€">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Preț maxim</label>
                    <input type="number" name="price_max" class="form-control" placeholder="€">
                </div>
                <div class="col-md-2">
                    <label class="form-label">An minim</label>
                    <input type="number" name="year_min" class="form-control" placeholder="2010">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Caută
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Featured Cars -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Mașini Recomandate</h2>
            <p class="text-muted">Cele mai noi adăugări în colecția noastră</p>
        </div>

        <div class="row">
            <?php foreach ($featured_cars as $car): ?>
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
        </div>

        <div class="text-center">
            <a href="cars.php" class="btn btn-outline-primary btn-lg">
                Vezi Toate Mașinile <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2>De Ce AUTO RARES?</h2>
        </div>

        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="feature-item">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h4>Mașini Verificate</h4>
                    <p>Toate mașinile sunt verificate tehnic înainte de a fi publicate pe site.</p>
                </div>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-item">
                    <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                    <h4>Tranzacții Sigure</h4>
                    <p>Medierea completă a procesului de vânzare-cumpărare pentru siguranța tuturor.</p>
                </div>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-item">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h4>Suport 24/7</h4>
                    <p>Echipa noastră este mereu disponibilă pentru a vă ajuta cu orice întrebare.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
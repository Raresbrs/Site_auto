<?php
require_once 'config/config.php';

$car_id = intval($_GET['id'] ?? 0);

if (!$car_id) {
    redirect('cars.php');
}

// Incrementează vizualizările
$pdo->prepare("UPDATE cars SET views = views + 1 WHERE id = ?")->execute([$car_id]);

// Obține detaliile mașinii
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name as seller_name, u.phone as seller_phone, u.email as seller_email 
    FROM cars c 
    LEFT JOIN users u ON c.seller_id = u.id 
    WHERE c.id = ? AND c.status = 'approved'
");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    redirect('cars.php');
}

// Obține imaginile
$stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY is_main DESC, upload_order ASC");
$stmt->execute([$car_id]);
$images = $stmt->fetchAll();

// Procesare mesaj contact
$message_sent = false;
$error = '';

if ($_POST && isset($_POST['send_message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Numele, email-ul și mesajul sunt obligatorii.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (car_id, name, email, phone, message) 
            VALUES (?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$car_id, $name, $email, $phone, $message])) {
            $message_sent = true;
        } else {
            $error = 'A apărut o eroare la trimiterea mesajului.';
        }
    }
}

$page_title = $car['brand'] . ' ' . $car['model'] . ' ' . $car['year'];
include 'includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Acasă</a></li>
            <li class="breadcrumb-item"><a href="cars.php">Mașini</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Galerie imagini -->
        <div class="col-lg-8">
            <div class="car-gallery mb-4">
                <?php if (!empty($images)): ?>
                    <!-- Imagine principală -->
                    <div class="main-image mb-3">
                        <img src="<?php echo htmlspecialchars($images[0]['image_path']); ?>"
                            class="img-fluid rounded"
                            alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>"
                            id="mainImage">
                    </div>

                    <!-- Miniaturi -->
                    <?php if (count($images) > 1): ?>
                        <div class="thumbnails">
                            <div class="row">
                                <?php foreach ($images as $index => $image): ?>
                                    <div class="col-2 mb-2">
                                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                            class="img-fluid rounded thumbnail-img <?php echo $index === 0 ? 'active' : ''; ?>"
                                            alt="Imagine <?php echo $index + 1; ?>"
                                            onclick="changeMainImage('<?php echo htmlspecialchars($image['image_path']); ?>', this)">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="main-image mb-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 400px;">
                            <i class="fas fa-car fa-5x text-muted"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Detalii mașină -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-info-circle me-2"></i>Detalii Tehnice</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Marcă:</strong></td>
                                    <td><?php echo htmlspecialchars($car['brand']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Model:</strong></td>
                                    <td><?php echo htmlspecialchars($car['model']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>An fabricație:</strong></td>
                                    <td><?php echo $car['year']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Kilometraj:</strong></td>
                                    <td><?php echo formatMileage($car['mileage']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Combustibil:</strong></td>
                                    <td><?php echo ucfirst($car['fuel_type']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Transmisie:</strong></td>
                                    <td><?php echo ucfirst($car['transmission']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Capacitate motor:</strong></td>
                                    <td><?php echo $car['engine_capacity'] ? $car['engine_capacity'] . ' cm³' : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Putere:</strong></td>
                                    <td><?php echo $car['power_hp'] ? $car['power_hp'] . ' CP' : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Culoare:</strong></td>
                                    <td><?php echo $car['color'] ? htmlspecialchars($car['color']) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Număr uși:</strong></td>
                                    <td><?php echo $car['doors'] ?: '-'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Număr locuri:</strong></td>
                                    <td><?php echo $car['seats'] ?: '-'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Categorie:</strong></td>
                                    <td><?php echo ucfirst($car['category']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descriere -->
            <?php if ($car['description']): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-align-left me-2"></i>Descriere</h4>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dotări -->
            <?php if ($car['features']): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><i class="fas fa-list me-2"></i>Dotări</h4>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($car['features'])); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Preț și info de bază -->
            <div class="card mb-4 border-primary">
                <div class="card-body text-center">
                    <h2 class="car-price text-primary mb-3"><?php echo formatPrice($car['price']); ?></h2>
                    <h4 class="mb-3"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h4>

                    <div class="car-details mb-4">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <i class="fas fa-calendar text-primary"></i>
                                <small class="d-block"><?php echo $car['year']; ?></small>
                            </div>
                            <div class="col-6 mb-2">
                                <i class="fas fa-road text-primary"></i>
                                <small class="d-block"><?php echo formatMileage($car['mileage']); ?></small>
                            </div>
                            <div class="col-6 mb-2">
                                <i class="fas fa-gas-pump text-primary"></i>
                                <small class="d-block"><?php echo ucfirst($car['fuel_type']); ?></small>
                            </div>
                            <div class="col-6 mb-2">
                                <i class="fas fa-cogs text-primary"></i>
                                <small class="d-block"><?php echo ucfirst($car['transmission']); ?></small>
                            </div>
                        </div>
                    </div>

                    <?php if ($car['location']): ?>
                        <p class="text-muted">
                            <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($car['location']); ?>
                        </p>
                    <?php endif; ?>

                    <p class="text-muted">
                        <i class="fas fa-eye me-2"></i><?php echo $car['views']; ?> vizualizări
                    </p>
                </div>
            </div>

            <!-- Contact vânzător -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-user me-2"></i>Vânzător</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo htmlspecialchars($car['seller_name']); ?></h6>

                    <?php if ($car['seller_phone']): ?>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:<?php echo htmlspecialchars($car['seller_phone']); ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($car['seller_phone']); ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <p class="mb-3">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:<?php echo htmlspecialchars($car['seller_email']); ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($car['seller_email']); ?>
                        </a>
                    </p>

                    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <i class="fas fa-envelope me-2"></i>Trimite Mesaj
                    </button>
                </div>
            </div>

            <!-- Informații adiționale -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info me-2"></i>Informații Adiționale</h5>
                </div>
                <div class="card-body">
                    <?php if ($car['vin']): ?>
                        <p><strong>VIN:</strong> <?php echo htmlspecialchars($car['vin']); ?></p>
                    <?php endif; ?>

                    <?php if ($car['registration_number']): ?>
                        <p><strong>Număr înmatriculare:</strong> <?php echo htmlspecialchars($car['registration_number']); ?></p>
                    <?php endif; ?>

                    <?php if ($car['first_registration']): ?>
                        <p><strong>Prima înmatriculare:</strong> <?php echo date('d.m.Y', strtotime($car['first_registration'])); ?></p>
                    <?php endif; ?>

                    <p><strong>Anunț publicat:</strong> <?php echo date('d.m.Y', strtotime($car['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contact -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contactează Vânzătorul</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if ($message_sent): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>Mesajul a fost trimis cu succes!
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Numele tău *</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefon</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                            value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Mesaj *</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required
                            placeholder="Sunt interesată/interesat de <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>. Vă rog să mă contactați."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
                    <button type="submit" name="send_message" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Trimite Mesaj
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($message_sent): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('contactModal'));
            modal.show();
        });
    </script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
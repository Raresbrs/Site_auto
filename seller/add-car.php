<?php
require_once '../config/config.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('../auth/login.php');
}

$page_title = 'Adaugă Mașină Nouă';

$success = false;
$error = '';

if ($_POST) {
    // Validare și procesare formular
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $year = intval($_POST['year']);
    $price = floatval($_POST['price']);
    $mileage = intval($_POST['mileage']);
    $fuel_type = $_POST['fuel_type'];
    $transmission = $_POST['transmission'];
    $engine_capacity = intval($_POST['engine_capacity']) ?: null;
    $power_hp = intval($_POST['power_hp']) ?: null;
    $color = trim($_POST['color']) ?: null;
    $doors = intval($_POST['doors']) ?: null;
    $seats = intval($_POST['seats']) ?: null;
    $category = $_POST['category'];
    $description = trim($_POST['description']) ?: null;
    $features = trim($_POST['features']) ?: null;
    $vin = trim($_POST['vin']) ?: null;
    $registration_number = trim($_POST['registration_number']) ?: null;
    $first_registration = $_POST['first_registration'] ?: null;
    $location = trim($_POST['location']) ?: null;

    // Validări
    if (empty($brand) || empty($model) || !$year || !$price || !$mileage) {
        $error = 'Câmpurile obligatorii trebuie completate.';
    } elseif ($year < 1900 || $year > date('Y') + 1) {
        $error = 'Anul fabricației nu este valid.';
    } elseif ($price <= 0) {
        $error = 'Prețul trebuie să fie mai mare de 0.';
    } elseif ($mileage < 0) {
        $error = 'Kilometrajul nu poate fi negativ.';
    } else {
        try {
            $pdo->beginTransaction();

            // Inserare mașină
            $stmt = $pdo->prepare("
                INSERT INTO cars (
                    seller_id, brand, model, year, price, mileage, fuel_type, transmission,
                    engine_capacity, power_hp, color, doors, seats, category, description,
                    features, vin, registration_number, first_registration, location, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                $brand,
                $model,
                $year,
                $price,
                $mileage,
                $fuel_type,
                $transmission,
                $engine_capacity,
                $power_hp,
                $color,
                $doors,
                $seats,
                $category,
                $description,
                $features,
                $vin,
                $registration_number,
                $first_registration,
                $location
            ]);

            $car_id = $pdo->lastInsertId();

            // Upload imagini
            if (!empty($_FILES['images']['name'][0])) {
                if (!is_dir('../' . UPLOAD_PATH)) {
                    mkdir('../' . UPLOAD_PATH, 0755, true);
                }

                $upload_order = 0;
                foreach ($_FILES['images']['name'] as $key => $filename) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

                        if (in_array($file_extension, $allowed_extensions)) {
                            $new_filename = $car_id . '_' . time() . '_' . $upload_order . '.' . $file_extension;
                            $upload_path = '../' . UPLOAD_PATH . $new_filename;

                            if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $upload_path)) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO car_images (car_id, image_path, is_main, upload_order) 
                                    VALUES (?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $car_id,
                                    UPLOAD_PATH . $new_filename,
                                    $upload_order === 0 ? 1 : 0,
                                    $upload_order
                                ]);
                                $upload_order++;
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollback();
            $error = 'A apărut o eroare la salvarea mașinii. Te rugăm să încerci din nou.';
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-plus-circle me-2"></i>Adaugă Mașină Nouă</h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4>Mașina a fost adăugată cu succes!</h4>
                            <p>Anunțul tău va fi verificat de administratori și va fi publicat în maxim 24 de ore.</p>
                            <a href="dashboard.php" class="btn btn-primary">Înapoi la Dashboard</a>
                            <a href="add-car.php" class="btn btn-outline-primary">Adaugă Altă Mașină</a>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <!-- Informații de bază -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Informații de Bază</h5>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="brand" class="form-label">Marcă *</label>
                                    <select class="form-select" id="brand" name="brand" required>
                                        <option value="">Selectează marca</option>
                                        <option value="Audi" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Audi') ? 'selected' : ''; ?>>Audi</option>
                                        <option value="BMW" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'BMW') ? 'selected' : ''; ?>>BMW</option>
                                        <option value="Mercedes" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Mercedes') ? 'selected' : ''; ?>>Mercedes</option>
                                        <option value="Volkswagen" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Volkswagen') ? 'selected' : ''; ?>>Volkswagen</option>
                                        <option value="Skoda" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Skoda') ? 'selected' : ''; ?>>Skoda</option>
                                        <option value="Ford" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Ford') ? 'selected' : ''; ?>>Ford</option>
                                        <option value="Opel" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Opel') ? 'selected' : ''; ?>>Opel</option>
                                        <option value="Renault" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Renault') ? 'selected' : ''; ?>>Renault</option>
                                        <option value="Peugeot" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Peugeot') ? 'selected' : ''; ?>>Peugeot</option>
                                        <option value="Dacia" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Dacia') ? 'selected' : ''; ?>>Dacia</option>
                                        <option value="Toyota" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Toyota') ? 'selected' : ''; ?>>Toyota</option>
                                        <option value="Hyundai" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Hyundai') ? 'selected' : ''; ?>>Hyundai</option>
                                        <option value="Kia" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Kia') ? 'selected' : ''; ?>>Kia</option>
                                        <option value="Nissan" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Nissan') ? 'selected' : ''; ?>>Nissan</option>
                                        <option value="Mazda" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Mazda') ? 'selected' : ''; ?>>Mazda</option>
                                        <option value="Honda" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Honda') ? 'selected' : ''; ?>>Honda</option>
                                        <option value="Altele" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Altele') ? 'selected' : ''; ?>>Altele</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="model" class="form-label">Model *</label>
                                    <input type="text" class="form-control" id="model" name="model"
                                        value="<?php echo isset($_POST['model']) ? htmlspecialchars($_POST['model']) : ''; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="year" class="form-label">An fabricație *</label>
                                    <input type="number" class="form-control" id="year" name="year" min="1950" max="<?php echo date('Y') + 1; ?>"
                                        value="<?php echo isset($_POST['year']) ? intval($_POST['year']) : ''; ?>" required>
                                </div>
                            </div>

                            <!-- Preț și kilometraj -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Preț și Utilizare</h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Preț (€) *</label>
                                    <input type="number" class="form-control" id="price" name="price" min="1" step="0.01"
                                        value="<?php echo isset($_POST['price']) ? floatval($_POST['price']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="mileage" class="form-label">Kilometraj *</label>
                                    <input type="number" class="form-control" id="mileage" name="mileage" min="0"
                                        value="<?php echo isset($_POST['mileage']) ? intval($_POST['mileage']) : ''; ?>" required>
                                </div>
                            </div>

                            <!-- Specificații tehnice -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Specificații Tehnice</h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fuel_type" class="form-label">Combustibil *</label>
                                    <select class="form-select" id="fuel_type" name="fuel_type" required>
                                        <option value="">Selectează combustibilul</option>
                                        <option value="benzina" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'benzina') ? 'selected' : ''; ?>>Benzină</option>
                                        <option value="diesel" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                                        <option value="gpl" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'gpl') ? 'selected' : ''; ?>>GPL</option>
                                        <option value="hibrid" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'hibrid') ? 'selected' : ''; ?>>Hibrid</option>
                                        <option value="electric" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'electric') ? 'selected' : ''; ?>>Electric</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="transmission" class="form-label">Transmisie *</label>
                                    <select class="form-select" id="transmission" name="transmission" required>
                                        <option value="">Selectează transmisia</option>
                                        <option value="manuala" <?php echo (isset($_POST['transmission']) && $_POST['transmission'] === 'manuala') ? 'selected' : ''; ?>>Manuală</option>
                                        <option value="automata" <?php echo (isset($_POST['transmission']) && $_POST['transmission'] === 'automata') ? 'selected' : ''; ?>>Automată</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="engine_capacity" class="form-label">Capacitate motor (cm³)</label>
                                    <input type="number" class="form-control" id="engine_capacity" name="engine_capacity" min="1"
                                        value="<?php echo isset($_POST['engine_capacity']) ? intval($_POST['engine_capacity']) : ''; ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="power_hp" class="form-label">Putere (CP)</label>
                                    <input type="number" class="form-control" id="power_hp" name="power_hp" min="1"
                                        value="<?php echo isset($_POST['power_hp']) ? intval($_POST['power_hp']) : ''; ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="category" class="form-label">Categorie *</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Selectează categoria</option>
                                        <option value="berlina" <?php echo (isset($_POST['category']) && $_POST['category'] === 'berlina') ? 'selected' : ''; ?>>Berlină</option>
                                        <option value="break" <?php echo (isset($_POST['category']) && $_POST['category'] === 'break') ? 'selected' : ''; ?>>Break</option>
                                        <option value="suv" <?php echo (isset($_POST['category']) && $_POST['category'] === 'suv') ? 'selected' : ''; ?>>SUV</option>
                                        <option value="hatchback" <?php echo (isset($_POST['category']) && $_POST['category'] === 'hatchback') ? 'selected' : ''; ?>>Hatchback</option>
                                        <option value="coupe" <?php echo (isset($_POST['category']) && $_POST['category'] === 'coupe') ? 'selected' : ''; ?>>Coupe</option>
                                        <option value="cabrio" <?php echo (isset($_POST['category']) && $_POST['category'] === 'cabrio') ? 'selected' : ''; ?>>Cabrio</option>
                                        <option value="monovolum" <?php echo (isset($_POST['category']) && $_POST['category'] === 'monovolum') ? 'selected' : ''; ?>>Monovolum</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Caracteristici -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Caracteristici</h5>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="color" class="form-label">Culoare</label>
                                    <input type="text" class="form-control" id="color" name="color"
                                        value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : ''; ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="doors" class="form-label">Număr uși</label>
                                    <select class="form-select" id="doors" name="doors">
                                        <option value="">Selectează</option>
                                        <option value="2" <?php echo (isset($_POST['doors']) && $_POST['doors'] == 2) ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo (isset($_POST['doors']) && $_POST['doors'] == 3) ? 'selected' : ''; ?>>3</option>
                                        <option value="4" <?php echo (isset($_POST['doors']) && $_POST['doors'] == 4) ? 'selected' : ''; ?>>4</option>
                                        <option value="5" <?php echo (isset($_POST['doors']) && $_POST['doors'] == 5) ? 'selected' : ''; ?>>5</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="seats" class="form-label">Număr locuri</label>
                                    <select class="form-select" id="seats" name="seats">
                                        <option value="">Selectează</option>
                                        <option value="2" <?php echo (isset($_POST['seats']) && $_POST['seats'] == 2) ? 'selected' : ''; ?>>2</option>
                                        <option value="4" <?php echo (isset($_POST['seats']) && $_POST['seats'] == 4) ? 'selected' : ''; ?>>4</option>
                                        <option value="5" <?php echo (isset($_POST['seats']) && $_POST['seats'] == 5) ? 'selected' : ''; ?>>5</option>
                                        <option value="7" <?php echo (isset($_POST['seats']) && $_POST['seats'] == 7) ? 'selected' : ''; ?>>7</option>
                                        <option value="8" <?php echo (isset($_POST['seats']) && $_POST['seats'] == 8) ? 'selected' : ''; ?>>8</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Informații legale -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Informații Legale</h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="vin" class="form-label">VIN</label>
                                    <input type="text" class="form-control" id="vin" name="vin" maxlength="17"
                                        value="<?php echo isset($_POST['vin']) ? htmlspecialchars($_POST['vin']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="registration_number" class="form-label">Număr înmatriculare</label>
                                    <input type="text" class="form-control" id="registration_number" name="registration_number"
                                        value="<?php echo isset($_POST['registration_number']) ? htmlspecialchars($_POST['registration_number']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="first_registration" class="form-label">Prima înmatriculare</label>
                                    <input type="date" class="form-control" id="first_registration" name="first_registration"
                                        value="<?php echo isset($_POST['first_registration']) ? $_POST['first_registration'] : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="location" class="form-label">Locația mașinii</label>
                                    <input type="text" class="form-control" id="location" name="location"
                                        placeholder="ex: București, Sector 1"
                                        value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Descriere și dotări -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Descriere și Dotări</h5>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label">Descriere</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"
                                        placeholder="Descrie mașina ta: starea generală, istoricul, orice informații importante pentru cumpărători..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="features" class="form-label">Dotări și Opțiuni</label>
                                    <textarea class="form-control" id="features" name="features" rows="4"
                                        placeholder="ex: Aer condiționat, GPS, Scaune încălzite, Piele naturală, etc."><?php echo isset($_POST['features']) ? htmlspecialchars($_POST['features']) : ''; ?></textarea>
                                </div>
                            </div>

                            <!-- Upload imagini -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Imagini</h5>
                                </div>
                                <div class="col-12">
                                    <label for="images" class="form-label">Selectează imagini (maxim 10)</label>
                                    <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                                    <div class="form-text">Formateacceptate: JPG, JPEG, PNG, WEBP. Mărime maximă: 5MB per imagine.</div>
                                    <div id="image-preview" class="image-gallery mt-3"></div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-save me-2"></i>Adaugă Mașina
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary btn-lg px-5 ms-3">
                                    <i class="fas fa-times me-2"></i>Anulează
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('images').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';

        const files = Array.from(e.target.files);

        if (files.length > 10) {
            alert('Poți selecta maxim 10 imagini');
            e.target.value = '';
            return;
        }

        files.forEach((file, index) => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`Imaginea ${file.name} este prea mare. Mărimea maximă este 5MB.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'gallery-image';
                div.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index + 1}">
                <button type="button" class="remove-image" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
                ${index === 0 ? '<span class="badge bg-primary position-absolute top-0 start-0 m-2">Principală</span>' : ''}
            `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
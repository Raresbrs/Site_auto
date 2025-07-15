<?php
require_once '../config/config.php';
$page_title = 'Înregistrare';

if (isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    // Validări
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Toate câmpurile obligatorii trebuie completate.';
    } elseif ($password !== $confirm_password) {
        $error = 'Parolele nu coincid.';
    } elseif (strlen($password) < 6) {
        $error = 'Parola trebuie să aibă cel puțin 6 caractere.';
    } else {
        // Verifică dacă utilizatorul există deja
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = 'Username-ul sau email-ul există deja.';
        } else {
            // Înregistrează utilizatorul
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, full_name, phone, user_type) 
                VALUES (?, ?, ?, ?, ?, 'seller')
            ");

            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                $success = 'Contul a fost creat cu succes! Poți să te conectezi acum.';
            } else {
                $error = 'A apărut o eroare la înregistrare. Te rugăm să încerci din nou.';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h3>Înregistrare</h3>
                        <p class="text-muted">Creează-ți cont pentru a vinde mașini pe AUTO RARES</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <a href="login.php" class="alert-link">Conectează-te aici</a>
                        </div>
                    <?php endif; ?>

                    <?php if (!$success): ?>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Nume Complet *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name"
                                        value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Telefon</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Parolă *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmă Parola *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Înregistrează-te
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Ai deja cont? <a href="login.php">Conectează-te aici</a></p>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
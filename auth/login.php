<?php
$page_title = 'Autentificare';
require_once '../config/config.php';

// Redirect dacă deja e logat
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } elseif (isSeller()) {
        redirect('seller/dashboard.php');
    } else {
        redirect('index.php');
    }
}

$error = '';

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Te rog completează toate câmpurile';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password, full_name, user_type FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Setează toate datele necesare în sesiune
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name']; // Folosește full_name ca user_name
                $_SESSION['user_type'] = $user['user_type'];

                // Mesaj de succes
                $_SESSION['success'] = 'Bun venit, ' . $user['full_name'] . '!';

                // Redirect în funcție de tipul utilizatorului
                if ($user['user_type'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } elseif ($user['user_type'] === 'seller') {
                    redirect('../seller/dashboard.php');
                } else {
                    redirect('../index.php');
                }
            } else {
                $error = 'Email sau parolă incorectă';
            }
        } catch (PDOException $e) {
            $error = 'Eroare la autentificare: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header text-center">
                    <h3><i class="fas fa-sign-in-alt me-2"></i>Autentificare</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Parolă</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">
                                Ține-mă minte
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Autentificare
                            </button>
                        </div>
                    </form>

                    <hr>

                    <div class="text-center">
                        <p class="mb-2">Nu ai cont? <a href="<?php echo url('auth/register.php'); ?>">Înregistrează-te</a></p>
                        <p class="mb-0"><a href="<?php echo url('auth/forgot-password.php'); ?>">Ai uitat parola?</a></p>
                    </div>

                    <!-- Demo accounts info -->
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <strong>Conturi de test:</strong><br>
                            <small>
                                Admin: admin@autorares.ro<br>
                                Seller: seller@autorares.ro<br>
                                (Creează conturile cu setup-admin.php)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
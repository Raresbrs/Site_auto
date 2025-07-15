<?php
require_once 'config/config.php';

$admin_created = false;
$error = '';

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);

    if (empty($email) || empty($password) || empty($full_name)) {
        $error = 'Toate câmpurile sunt obligatorii';
    } elseif ($password !== $confirm_password) {
        $error = 'Parolele nu se potrivesc';
    } elseif (strlen($password) < 6) {
        $error = 'Parola trebuie să aibă minim 6 caractere';
    } else {
        try {
            // Verifică dacă deja există un admin
            $stmt = $pdo->prepare("SELECT id FROM users WHERE user_type = 'admin' LIMIT 1");
            $stmt->execute();
            $existing_admin = $stmt->fetch();

            if ($existing_admin) {
                // Actualizează adminul existent
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET email = ?, password = ?, full_name = ? 
                    WHERE user_type = 'admin'
                ");
                $stmt->execute([$email, $hashed_password, $full_name]);
                $admin_created = true;
                $message = "Contul de admin a fost actualizat cu succes!";
            } else {
                // Creează un admin nou
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (email, password, full_name, user_type) 
                    VALUES (?, ?, ?, 'admin')
                ");
                $stmt->execute([$email, $hashed_password, $full_name]);
                $admin_created = true;
                $message = "Contul de admin a fost creat cu succes!";
            }
        } catch (PDOException $e) {
            $error = 'Eroare: ' . $e->getMessage();
        }
    }
}

// Verifică dacă există deja un admin
try {
    $stmt = $pdo->prepare("SELECT email, full_name FROM users WHERE user_type = 'admin' LIMIT 1");
    $stmt->execute();
    $existing_admin = $stmt->fetch();
} catch (PDOException $e) {
    $existing_admin = null;
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin - AUTO RARES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-user-shield me-2"></i>Setup Cont Admin</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($admin_created): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                            </div>
                            <div class="text-center">
                                <a href="auth/login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Autentifică-te ca Admin
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if ($existing_admin): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Există deja un admin: <strong><?php echo htmlspecialchars($existing_admin['email']); ?></strong>
                                    <br>Completează formularul pentru a actualiza contul.
                                </div>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nume complet</label>
                                    <input type="text" name="full_name" class="form-control"
                                        value="<?php echo $existing_admin ? htmlspecialchars($existing_admin['full_name']) : ''; ?>"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?php echo $existing_admin ? htmlspecialchars($existing_admin['email']) : ''; ?>"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Parolă</label>
                                    <input type="password" name="password" class="form-control"
                                        minlength="6" required>
                                    <div class="form-text">Minim 6 caractere</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirmă parola</label>
                                    <input type="password" name="confirm_password" class="form-control"
                                        minlength="6" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $existing_admin ? 'Actualizează Admin' : 'Creează Admin'; ?>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Înapoi la site
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>